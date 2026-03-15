# requirements.md — Passkey Authentication for geokrety.org

**Feature**: WebAuthn / Passkey login support
**Project**: geokrety.org (PHP)
**Spec Workflow Phase**: ANALYZE
**Confidence Score**: 82% (Medium) — WebAuthn spec is well-defined; risk is in PHP library integration, multi-environment `rpId` strategy, and UX fallback flows.
**Date**: 2026-03-14

---

## Stakeholder Context

geokrety.org is an open-source geocaching platform. Users log in with username/password today. The goal is to add passkey support as a **passwordless option**, compatible with FIDO2 authenticators including Bitwarden, iCloud Keychain, Google Password Manager, and hardware keys (YubiKey).

Environments:
- `geokrety.org` — production
- `staging.geokrety.org` — staging
- `dev.geokrety.org` — development / testing
- `localhost` — local developer machines

---

## User Stories

### US-01 — Register a passkey
> As a logged-in user, I want to register a passkey from my account settings, so that I can log in without a password in the future.

### US-02 — Log in with a passkey
> As a registered user with a passkey, I want to log in using my authenticator (device biometrics or Bitwarden), so that I don't need to type my password.

### US-03 — Manage passkeys
> As a logged-in user, I want to view, name, and revoke my registered passkeys, so that I can maintain control over my account's authenticators.

### US-04 — Fallback to password
> As a user without a passkey (or whose authenticator is unavailable), I want to still be able to log in with my password, so that I'm never locked out.

### US-05 — Multi-environment key reuse
> As a developer or tester, I want passkeys registered on staging or dev to share the same `rpId` as production, so that the auth flow can be realistically tested without registering separate credentials per environment.

### US-06 — First-time passkey prompt
> As a user logging in with a password, I want to be offered the option to register a passkey after a successful login, so that I'm gently encouraged to adopt the feature.

---

## Acceptance Criteria (EARS Notation)

### Registration

**REQ-R01**
`WHEN an authenticated user submits a passkey registration request, THE SYSTEM SHALL generate a cryptographically random challenge (≥16 bytes), associate it with the user's session, and return it alongside the rpId, rpName, and user descriptor.`

**REQ-R02**
`WHEN the browser returns a PublicKeyCredential after navigator.credentials.create(), THE SYSTEM SHALL verify the attestation response, extract and store the credential ID (bytes), public key (COSE format), sign counter, and AAGUID, linked to the authenticated user.`

**REQ-R03**
`WHEN a user attempts to register a passkey with a credential ID already present in the database, THE SYSTEM SHALL reject the registration and return a 409 Conflict response with a descriptive error message.`

**REQ-R04**
`WHEN passkey registration succeeds, THE SYSTEM SHALL allow the user to assign a human-readable label (e.g. "iPhone 15", "Bitwarden") to the credential, defaulting to the AAGUID-resolved device name if available.`

**REQ-R05**
`THE SYSTEM SHALL store at minimum: credential_id (BLOB, unique), public_key (BLOB), sign_count (INT UNSIGNED), user_id (FK), label (VARCHAR 255), aaguid (CHAR 36), created_at (TIMESTAMP), last_used_at (TIMESTAMP NULL).`

### Authentication

**REQ-A01**
`WHEN an unauthenticated user initiates passkey login, THE SYSTEM SHALL generate a new challenge, store it in the session with an expiry of 5 minutes, and return it with the list of allowedCredentials for that user (if a username was provided) or an empty list (for discoverable credentials).`

**REQ-A02**
`WHEN the browser returns a signed assertion via navigator.credentials.get(), THE SYSTEM SHALL verify: (a) the challenge matches and has not expired, (b) the rpIdHash matches SHA-256 of the configured rpId, (c) the user presence flag is set, (d) the signature is valid against the stored public key, (e) the sign count is greater than the stored value (or both are zero).`

**REQ-A03**
`WHEN all assertion checks in REQ-A02 pass, THE SYSTEM SHALL update last_used_at and sign_count for the credential, create an authenticated session, and return a success response.`

**REQ-A04**
`IF the sign count in the assertion is less than or equal to the stored sign count AND the stored sign count is not zero, THE SYSTEM SHALL reject the authentication, log a security event, and return a 401 response indicating a possible cloned authenticator.`

**REQ-A05**
`WHEN passkey authentication fails for any reason, THE SYSTEM SHALL NOT reveal whether the failure was due to an unrecognised credential, an invalid signature, or a counter anomaly — returning a generic "Authentication failed" message in all cases.`

**REQ-A06**
`WHILE a passkey login challenge is pending, THE SYSTEM SHALL invalidate it after 5 minutes or upon first use, whichever comes first.`

### Credential Management

**REQ-M01**
`WHEN an authenticated user requests their passkey list, THE SYSTEM SHALL return all credentials belonging to that user, showing label, created_at, last_used_at, and device type (derived from AAGUID where possible).`

**REQ-M02**
`WHEN an authenticated user submits a delete request for one of their credentials, THE SYSTEM SHALL remove it from the database and confirm deletion, provided the credential belongs to that user.`

**REQ-M03**
`IF a user attempts to delete a passkey that belongs to a different user, THE SYSTEM SHALL return a 403 Forbidden response.`

**REQ-M04**
`WHEN an authenticated user renames a passkey, THE SYSTEM SHALL update the label (max 255 characters) and confirm the update.`

### Fallback & Compatibility

**REQ-F01**
`THE SYSTEM SHALL preserve the existing username/password login path unchanged; passkeys are additive and optional.`

**REQ-F02**
`WHEN a user successfully logs in via password and has no registered passkeys, THE SYSTEM SHALL display a one-time prompt offering to register a passkey.`

**REQ-F03**
`WHERE the user's browser does not support navigator.credentials (WebAuthn unavailable), THE SYSTEM SHALL hide passkey UI elements and silently fall back to password-only mode.`

### Multi-environment

**REQ-E01**
`THE SYSTEM SHALL use rpId = "geokrety.org" across production, staging, and dev environments, so that credentials are interoperable between environments sharing the same database.`

**REQ-E02**
`WHEN running on localhost, THE SYSTEM SHALL use rpId = "localhost", since the WebAuthn spec prohibits cross-origin rpId claims for non-subdomain origins.`

**REQ-E03**
`THE SYSTEM SHALL derive rpId from a configuration value (not hardcoded), so that each environment can set it independently via environment variable or config file.`

### Security

**REQ-S01**
`THE SYSTEM SHALL only serve passkey registration and authentication endpoints over HTTPS (TLS); any request over HTTP SHALL be rejected with a 400 response.`

**REQ-S02**
`THE SYSTEM SHALL rate-limit passkey login attempts to 10 per IP per minute, returning 429 Too Many Requests on breach.`

**REQ-S03**
`THE SYSTEM SHALL log all passkey registration events, successful logins, failed login attempts, and credential deletions to the application audit log, including timestamp, user_id, credential_id, and IP address.`

---

## Dependency Graph

```
Browser WebAuthn API (navigator.credentials)
    └── PHP: web-auth/webauthn-lib (^4.x)
            ├── ext-mbstring
            ├── ext-json
            ├── ext-openssl
            └── web-auth/cose-lib (transitive)

Frontend: @simplewebauthn/browser (optional, smooths edge cases)

Database: existing MySQL/MariaDB users table
    └── new table: user_passkeys

Session: existing PHP session infrastructure
    └── challenge stored as $_SESSION['passkey_challenge']

Config: existing .env / config.php
    └── WEBAUTHN_RP_ID, WEBAUTHN_RP_NAME
```

**Risks:**

| Risk | Likelihood | Mitigation |
|---|---|---|
| Browser support gaps (Safari < 16, Firefox mobile) | Low | Feature-detect; graceful hide |
| AAGUID metadata unavailable for device name | Medium | Fall back to user-supplied label |
| Bitwarden sends `none` attestation | Low | Accept `none`; attestation is optional |
| Sign-count always 0 (some platform authenticators) | Medium | Allow zero-counter pass-through per spec |
| Shared DB across staging/prod causes credential collision | Low (opt-in) | Document clearly; separate DBs by default |

---

## Edge Case Matrix

| Scenario | Expected behaviour |
|---|---|
| User registers same device twice | REQ-R03 — reject with 409 |
| Challenge expires before user responds | REQ-A06 — 401, prompt to retry |
| User deletes only passkey | Allowed; password fallback remains |
| Authenticator cloned (counter regression) | REQ-A04 — reject + security log |
| No passkeys registered, user visits login | Passkey button hidden / greyed |
| rpId mismatch (served on wrong domain) | Browser rejects before server sees it |
| Concurrent login sessions with same challenge | Challenge is single-use (REQ-A06) |
| User renames passkey to empty string | Validate min length 1, return 422 |
| WebAuthn not supported (IE, old Android) | REQ-F03 — hide UI, password only |
