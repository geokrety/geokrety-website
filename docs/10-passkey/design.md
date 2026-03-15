# design.md — Passkey Authentication for geokrety.org

**Feature**: WebAuthn / Passkey login support
**Project**: geokrety.org (PHP)
**Spec Workflow Phase**: DESIGN
**Strategy**: MVP-first (Confidence 82% — Medium)
**Date**: 2026-03-14

---

## Decision Record — Library Choice

```
Decision: Use web-auth/webauthn-lib ^4.x as the server-side WebAuthn library
Context: PHP project needing full CBOR/COSE crypto, attestation verification, counter management
Options:
  A) web-auth/webauthn-lib — mature, actively maintained, PSR-compliant, extensive docs
  B) lbuchs/webauthn — simpler but handles fewer attestation formats
  C) Roll our own — not feasible; COSE/CBOR parsing is non-trivial
Rationale: Option A handles all required attestation formats including `none` (Bitwarden),
  packed, and TPM. PSR-4 autoloading fits existing Composer setup.
Impact: Adds ~3 composer packages; no runtime performance concern
Review: Reassess if library goes unmaintained (check at next major PHP upgrade)
```

---

## Decision Record — rpId Strategy

```
Decision: rpId = "geokrety.org" on all subdomain environments; "localhost" on local dev
Context: Passkeys are bound to rpId; shared rpId allows credential interoperability across
  staging.geokrety.org and geokrety.org if they share a DB, or isolated testing if they don't.
Options:
  A) rpId per environment — fully isolated, no accidental cross-env logins
  B) rpId = "geokrety.org" everywhere — interoperability possible; matches production
Rationale: Option B gives more realistic staging tests and future flexibility to share
  a read replica. localhost is forced to "localhost" by the WebAuthn spec anyway.
Impact: rpId must come from config, not be hardcoded
Review: If staging ever handles real user data, re-evaluate shared-DB credential strategy
```

---

## Architecture Overview

```
┌─────────────────────────────────────────────────────────┐
│                     Browser                             │
│  ┌─────────────────────────────────────────────────┐   │
│  │  passkey.js  (@simplewebauthn/browser optional) │   │
│  │  navigator.credentials.create() / .get()        │   │
│  └──────────────────┬──────────────────────────────┘   │
└─────────────────────┼───────────────────────────────────┘
                      │ HTTPS JSON
┌─────────────────────┼───────────────────────────────────┐
│  geokrety.org (PHP) │                                   │
│                     ▼                                   │
│  ┌──────────────────────────────────────────────────┐  │
│  │  PasskeyController                               │  │
│  │  POST /api/passkey/register/begin                │  │
│  │  POST /api/passkey/register/complete             │  │
│  │  POST /api/passkey/login/begin                   │  │
│  │  POST /api/passkey/login/complete                │  │
│  │  GET  /api/passkey/credentials          (auth)   │  │
│  │  DELETE /api/passkey/credentials/{id}  (auth)    │  │
│  │  PATCH /api/passkey/credentials/{id}   (auth)    │  │
│  └──────────────┬───────────────────────────────────┘  │
│                 │                                       │
│  ┌──────────────▼───────────────────────────────────┐  │
│  │  PasskeyService                                  │  │
│  │  - generateRegistrationOptions()                 │  │
│  │  - verifyRegistration()                          │  │
│  │  - generateAuthenticationOptions()               │  │
│  │  - verifyAuthentication()                        │  │
│  └──────────────┬───────────────────────────────────┘  │
│                 │                                       │
│  ┌──────────────▼──────────┐  ┌───────────────────┐   │
│  │  web-auth/webauthn-lib  │  │  PasskeyRepository │   │
│  │  (CBOR/COSE/verify)     │  │  (DB read/write)   │   │
│  └─────────────────────────┘  └─────────┬─────────┘   │
│                                          │              │
└──────────────────────────────────────────┼─────────────┘
                                           │
                              ┌────────────▼────────────┐
                              │  MySQL: user_passkeys   │
                              └─────────────────────────┘
```

---

## Data Models

### New table: `user_passkeys`

```sql
CREATE TABLE user_passkeys (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id         INT UNSIGNED NOT NULL,
    credential_id   VARBINARY(1024) NOT NULL,
    public_key      BLOB NOT NULL,          -- COSE-encoded
    sign_count      INT UNSIGNED NOT NULL DEFAULT 0,
    aaguid          CHAR(36) NOT NULL DEFAULT '00000000-0000-0000-0000-000000000000',
    label           VARCHAR(255) NOT NULL DEFAULT 'My passkey',
    created_at      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    last_used_at    TIMESTAMP NULL,

    UNIQUE KEY uq_credential_id (credential_id(255)),
    INDEX idx_user_id (user_id),
    CONSTRAINT fk_passkey_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### Session keys (stored in `$_SESSION`)

| Key | Type | TTL | Purpose |
|---|---|---|---|
| `passkey_reg_challenge` | `string` (base64url) | 5 min | Registration challenge |
| `passkey_auth_challenge` | `string` (base64url) | 5 min | Authentication challenge |
| `passkey_challenge_expires` | `int` (unix ts) | — | Challenge expiry |

---

## API Contracts

All endpoints return `Content-Type: application/json`. All error responses follow:
```json
{ "error": "Human-readable message", "code": "MACHINE_CODE" }
```

### POST /api/passkey/register/begin
**Auth required**: yes (logged-in session)
**Request**: empty body
**Response 200**:
```json
{
  "challenge": "<base64url>",
  "rp": { "id": "geokrety.org", "name": "GeoKrety" },
  "user": { "id": "<base64url user_id>", "name": "username", "displayName": "Username" },
  "pubKeyCredParams": [
    { "type": "public-key", "alg": -7 },
    { "type": "public-key", "alg": -257 }
  ],
  "timeout": 60000,
  "attestation": "none",
  "authenticatorSelection": {
    "residentKey": "preferred",
    "userVerification": "preferred"
  }
}
```

### POST /api/passkey/register/complete
**Auth required**: yes
**Request**: raw `PublicKeyCredential` JSON from browser
**Response 200**: `{ "id": <new_credential_db_id>, "label": "My passkey" }`
**Response 409**: credential already registered
**Response 400**: verification failed

### POST /api/passkey/login/begin
**Auth required**: no
**Request**: `{ "username": "optional" }`
**Response 200**:
```json
{
  "challenge": "<base64url>",
  "timeout": 60000,
  "rpId": "geokrety.org",
  "userVerification": "preferred",
  "allowCredentials": []
}
```
`allowCredentials` is populated if username was supplied and has registered passkeys.

### POST /api/passkey/login/complete
**Auth required**: no
**Request**: raw assertion JSON from browser
**Response 200**: `{ "redirect": "/dashboard" }`
**Response 401**: authentication failed (generic)

### GET /api/passkey/credentials
**Auth required**: yes
**Response 200**: array of `{ id, label, aaguid, device_name, created_at, last_used_at }`

### DELETE /api/passkey/credentials/{id}
**Auth required**: yes (must own credential)
**Response 204**: deleted
**Response 403**: not owner

### PATCH /api/passkey/credentials/{id}
**Auth required**: yes (must own credential)
**Request**: `{ "label": "New name" }`
**Response 200**: updated credential

---

## Sequence Diagrams

### Registration flow

```
User browser              PasskeyController        PasskeyService          DB
     |                           |                       |                  |
     |-- POST /register/begin -->|                       |                  |
     |                           |-- generateOptions() ->|                  |
     |                           |<-- options + chall ---|                  |
     |                           | store challenge in    |                  |
     |                           | $_SESSION             |                  |
     |<-- 200 options ------------|                       |                  |
     |                           |                       |                  |
     | navigator.credentials     |                       |                  |
     |   .create(options)        |                       |                  |
     | [user gesture / biometric]|                       |                  |
     |                           |                       |                  |
     |-- POST /register/complete >|                       |                  |
     |    (credential JSON)       |-- verifyReg() ------->|                  |
     |                           |                       |-- INSERT -------->|
     |                           |<-- verified + pubkey--|<-- ok ------------|
     |<-- 200 { id, label } ------|                       |                  |
```

### Authentication flow

```
User browser              PasskeyController        PasskeyService          DB
     |                           |                       |                  |
     |-- POST /login/begin ------>|                       |                  |
     |    { username? }           |-- genAuthOptions() -->|                  |
     |                           |                       |-- SELECT creds -->|
     |                           |<-- challenge + creds--|<-- rows ----------|
     |<-- 200 options ------------|                       |                  |
     |                           |                       |                  |
     | navigator.credentials     |                       |                  |
     |   .get(options)           |                       |                  |
     | [user gesture / biometric]|                       |                  |
     |                           |                       |                  |
     |-- POST /login/complete --->|                       |                  |
     |    (assertion JSON)        |-- verifyAuth() ------>|                  |
     |                           |                       |-- SELECT pubkey ->|
     |                           |                       |<-- row -----------|
     |                           |                       | verify sig + ctr  |
     |                           |                       |-- UPDATE ctr ---->|
     |                           | create session        |                  |
     |<-- 200 { redirect } -------|                       |                  |
```

---

## File Structure

```
src/
  Controller/
    PasskeyController.php       # HTTP layer, input validation, JSON responses
  Service/
    PasskeyService.php          # Business logic, wraps webauthn-lib
  Repository/
    PasskeyRepository.php       # DB access for user_passkeys table
  Entity/
    PasskeyCredential.php       # Value object: credential_id, public_key, sign_count, …

config/
  webauthn.php                  # rpId, rpName, timeout, attestation preferences

db/migrations/
  YYYYMMDD_create_user_passkeys.sql

templates/
  account/passkeys.html.twig    # Passkey management UI (list, rename, delete)
  login/passkey_prompt.html.twig # Post-password registration nudge

public/js/
  passkey.js                    # Registration + authentication browser logic

tests/
  Unit/
    PasskeyServiceTest.php
    PasskeyRepositoryTest.php
  Integration/
    PasskeyRegistrationTest.php
    PasskeyAuthenticationTest.php
```

---

## Error Matrix

| Condition | HTTP | Code | Log level |
|---|---|---|---|
| Challenge expired | 401 | `CHALLENGE_EXPIRED` | INFO |
| rpIdHash mismatch | 401 | `AUTH_FAILED` | WARNING |
| Invalid signature | 401 | `AUTH_FAILED` | WARNING |
| Counter regression | 401 | `AUTH_FAILED` | ERROR (+ security alert) |
| Credential not found | 401 | `AUTH_FAILED` | INFO |
| Credential already exists | 409 | `CREDENTIAL_EXISTS` | INFO |
| Credential belongs to other user | 403 | `FORBIDDEN` | WARNING |
| HTTP instead of HTTPS | 400 | `HTTPS_REQUIRED` | INFO |
| Rate limit exceeded | 429 | `RATE_LIMITED` | INFO |
| WebAuthn lib internal error | 500 | `INTERNAL_ERROR` | ERROR |

All 401 responses use the same user-facing message: `"Authentication failed"` regardless of internal code.

---

## Unit Testing Strategy

- **PasskeyService**: mock `webauthn-lib` internals; test challenge generation, expiry, counter logic
- **PasskeyRepository**: use SQLite in-memory or a test DB fixture; test CRUD and uniqueness constraint
- **PasskeyController**: use a test HTTP client; assert response shapes and status codes
- **Integration**: spin up a test DB; run full registration → authentication round-trip with a pre-baked test credential fixture (real COSE key pair generated at test setup time)
- **Edge cases to cover explicitly**: counter = 0 pass-through, challenge expiry, duplicate credential rejection, unknown credential login attempt

---

## Configuration

Add to `.env` / config:

```env
WEBAUTHN_RP_ID=geokrety.org          # "localhost" on local dev
WEBAUTHN_RP_NAME=GeoKrety
WEBAUTHN_TIMEOUT=60000               # ms
WEBAUTHN_ATTESTATION=none            # none | indirect | direct
WEBAUTHN_USER_VERIFICATION=preferred # required | preferred | discouraged
```

---

## Security Considerations

- Challenges MUST be cryptographically random (`random_bytes(32)`) — never `rand()` or `uniqid()`
- Challenge stored server-side in session only; never re-used
- All DB queries use prepared statements via existing PDO infrastructure
- Sign counter is checked and updated atomically in a DB transaction
- Credential IDs are stored as raw bytes (VARBINARY), not base64 strings, to avoid encoding ambiguities
- AAGUID lookup against FIDO MDS (optional enhancement) for device name display — not required for security
