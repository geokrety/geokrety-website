# tasks.md — Passkey Authentication for geokrety.org

**Feature**: WebAuthn / Passkey login support
**Project**: geokrety.org (PHP)
**Spec Workflow Phase**: IMPLEMENT
**Execution strategy**: MVP-first (Confidence 82%)
**Date**: 2026-03-14

---

## Status Legend

| Symbol | Meaning |
|---|---|
| `[ ]` | Not started |
| `[~]` | In progress |
| `[x]` | Done |
| `[!]` | Blocked |

---

## MVP Scope (Phase 1)

Deliver a working end-to-end passkey registration + login with password fallback intact. No AAGUID metadata lookup, no post-login nudge, no discoverable credentials UI in phase 1.

---

## TASK-01 — Environment & Dependencies

**Description**: Install and configure the `web-auth/webauthn-lib` PHP library and optionally the `@simplewebauthn/browser` JS package.
**Expected outcome**: Library available via Composer autoload; JS bundle available in `public/js/`.
**Dependencies**: none

- [ ] **T01-A** Run `composer require web-auth/webauthn-lib` and verify installation
- [ ] **T01-B** Add `WEBAUTHN_RP_ID`, `WEBAUTHN_RP_NAME`, `WEBAUTHN_TIMEOUT`, `WEBAUTHN_ATTESTATION`, `WEBAUTHN_USER_VERIFICATION` to `.env.example` and config loader
- [ ] **T01-C** Add `rpId` auto-detection: read `WEBAUTHN_RP_ID` from env; fall back to `"localhost"` if `$_SERVER['HTTP_HOST'] === 'localhost'`
- [ ] **T01-D** (optional) `npm install @simplewebauthn/browser` and bundle or include via CDN fallback
- [ ] **T01-E** Confirm `ext-openssl`, `ext-mbstring`, `ext-json` are present in all environment PHP configs

---

## TASK-02 — Database Migration

**Description**: Create the `user_passkeys` table.
**Expected outcome**: Migration runs cleanly on all environments; rollback also works.
**Dependencies**: T01

- [ ] **T02-A** Write migration: `db/migrations/YYYYMMDD_create_user_passkeys.sql` (see design.md schema)
- [ ] **T02-B** Write rollback: `DROP TABLE IF EXISTS user_passkeys;`
- [ ] **T02-C** Run migration on local dev DB and verify schema with `DESCRIBE user_passkeys`
- [ ] **T02-D** Add migration to CI pipeline so staging picks it up on next deploy

---

## TASK-03 — PasskeyCredential Entity

**Description**: Immutable value object representing a stored credential row.
**Expected outcome**: Typed PHP class with constructor, getters, and a static factory from DB row array.
**Dependencies**: T02

- [ ] **T03-A** Create `src/Entity/PasskeyCredential.php`
  - Properties: `id`, `userId`, `credentialId` (binary string), `publicKey` (binary string), `signCount`, `aaguid`, `label`, `createdAt`, `lastUsedAt`
  - Static factory: `PasskeyCredential::fromRow(array $row): self`

---

## TASK-04 — PasskeyRepository

**Description**: Database access layer for `user_passkeys`.
**Expected outcome**: CRUD operations using prepared statements; no business logic.
**Dependencies**: T03

- [ ] **T04-A** Create `src/Repository/PasskeyRepository.php` with methods:
  - `findByCredentialId(string $credentialId): ?PasskeyCredential`
  - `findByUserId(int $userId): PasskeyCredential[]`
  - `insert(PasskeyCredential $cred): int` (returns new id)
  - `updateSignCountAndLastUsed(int $id, int $signCount): void`
  - `updateLabel(int $id, int $userId, string $label): bool`
  - `deleteByIdAndUserId(int $id, int $userId): bool`
- [ ] **T04-B** Write unit tests for all methods using a test DB fixture (`tests/Unit/PasskeyRepositoryTest.php`)
- [ ] **T04-C** Verify uniqueness constraint on `credential_id` is enforced at DB level (test duplicate insert)

---

## TASK-05 — PasskeyService

**Description**: Business logic layer wrapping `web-auth/webauthn-lib`.
**Expected outcome**: Methods for generating and verifying registration and authentication ceremonies.
**Dependencies**: T04

- [ ] **T05-A** Create `src/Service/PasskeyService.php`
- [ ] **T05-B** Implement `generateRegistrationOptions(int $userId, string $username): array`
  - Generate 32-byte random challenge via `random_bytes(32)`
  - Build `PublicKeyCredentialCreationOptions`
  - Store challenge + expiry in `$_SESSION`
  - Return JSON-serialisable options array
- [ ] **T05-C** Implement `verifyRegistration(array $credential): PasskeyCredential`
  - Load challenge from session; check expiry
  - Call webauthn-lib attestation verifier
  - Invalidate session challenge immediately after use
  - Return `PasskeyCredential` ready for persistence
- [ ] **T05-D** Implement `generateAuthenticationOptions(?string $username): array`
  - Generate challenge, store in session with expiry
  - If username provided, populate `allowCredentials` from repository
  - Return options array
- [ ] **T05-E** Implement `verifyAuthentication(array $assertion): int` (returns `userId`)
  - Load challenge from session; check expiry
  - Look up credential by ID from assertion
  - Call webauthn-lib assertion verifier (checks signature, counter, rpIdHash, user presence)
  - Handle counter = 0 pass-through
  - If counter regression: log security event + throw exception
  - Update sign count + last_used_at via repository
  - Return authenticated user ID
- [ ] **T05-F** Write unit tests (`tests/Unit/PasskeyServiceTest.php`) mocking webauthn-lib
  - Test: expired challenge rejected
  - Test: counter regression rejected
  - Test: counter = 0 allowed
  - Test: valid registration stores credential
  - Test: valid authentication returns user ID

---

## TASK-06 — PasskeyController

**Description**: HTTP layer; validates input, calls service, returns JSON.
**Expected outcome**: All 7 API endpoints working with correct status codes and response shapes per design.md contracts.
**Dependencies**: T05

- [ ] **T06-A** Create `src/Controller/PasskeyController.php`
- [ ] **T06-B** Implement `POST /api/passkey/register/begin` — requires auth session; returns 200 options
- [ ] **T06-C** Implement `POST /api/passkey/register/complete` — requires auth session; returns 200 or 409
- [ ] **T06-D** Implement `POST /api/passkey/login/begin` — public; returns 200 options
- [ ] **T06-E** Implement `POST /api/passkey/login/complete` — public; on success creates session + returns 200
- [ ] **T06-F** Implement `GET /api/passkey/credentials` — requires auth; returns credential list
- [ ] **T06-G** Implement `DELETE /api/passkey/credentials/{id}` — requires auth + ownership; returns 204 or 403
- [ ] **T06-H** Implement `PATCH /api/passkey/credentials/{id}` — requires auth + ownership; returns 200 or 403
- [ ] **T06-I** Add HTTPS enforcement middleware/check per REQ-S01
- [ ] **T06-J** Add rate limiting per REQ-S02 (10 req/min/IP on login endpoints)
- [ ] **T06-K** Register routes in existing router
- [ ] **T06-L** Write integration tests covering all endpoints and error codes (`tests/Integration/PasskeyRegistrationTest.php`, `PasskeyAuthenticationTest.php`)

---

## TASK-07 — Frontend (passkey.js)

**Description**: Browser-side JS for registration and authentication ceremonies.
**Expected outcome**: Working passkey UI on login page and account settings.
**Dependencies**: T06

- [ ] **T07-A** Create `public/js/passkey.js`
  - Feature-detect `window.PublicKeyCredential`; hide UI if absent
  - `registerPasskey()`: fetch begin options → `credentials.create()` → POST complete
  - `loginWithPasskey()`: fetch begin options → `credentials.get()` → POST complete → redirect
  - Handle base64url encoding/decoding of binary fields
  - Surface user-friendly errors (timeout, cancelled, not supported)
- [ ] **T07-B** Add "Add a passkey" button to account settings page
  - On click: call `registerPasskey()`
  - On success: reload passkey list
- [ ] **T07-C** Add "Sign in with a passkey" button to login page (alongside existing form)
  - If WebAuthn not supported: hide button, show nothing
- [ ] **T07-D** Add passkey list to account settings (`templates/account/passkeys.html.twig`)
  - Show: label, device type (from aaguid if available, else "Passkey"), created_at, last_used_at
  - Delete button with confirmation dialog
  - Inline rename (click label → edit in place → save)
- [ ] **T07-E** Test in Chrome, Firefox, Safari (latest), and with Bitwarden extension active

---

## TASK-08 — Post-login Registration Nudge (Phase 1.5)

**Description**: After a successful password login, prompt users without passkeys to register one.
**Expected outcome**: One-time modal/banner shown; dismissable; not shown again once dismissed or passkey registered.
**Dependencies**: T07

- [ ] **T08-A** On successful password login, check if user has 0 passkeys
- [ ] **T08-B** If 0 passkeys and nudge not dismissed, add `show_passkey_nudge = true` to session
- [ ] **T08-C** Add nudge banner/modal to post-login template (`templates/login/passkey_prompt.html.twig`)
- [ ] **T08-D** "Not now" dismisses for the session; "Don't ask again" sets a user preference in DB
- [ ] **T08-E** Add `passkey_nudge_dismissed` boolean column to `users` table (migration)

---

## TASK-09 — Security & Audit Logging

**Description**: Wire up audit logging for all passkey events.
**Expected outcome**: All security-relevant events appear in application audit log.
**Dependencies**: T06

- [ ] **T09-A** Log passkey registration (user_id, credential_id partial, IP, timestamp)
- [ ] **T09-B** Log successful passkey login (user_id, credential_id partial, IP)
- [ ] **T09-C** Log failed passkey login attempts (IP, attempted credential_id if available)
- [ ] **T09-D** Log counter regression events at ERROR level with full context
- [ ] **T09-E** Log credential deletion (user_id, credential_id partial)

---

## TASK-10 — Multi-environment Verification

**Description**: Confirm rpId configuration works correctly across all environments.
**Expected outcome**: Passkey registered on staging works on staging; rpId is always from config.
**Dependencies**: T01, T06

- [ ] **T10-A** Deploy to staging with `WEBAUTHN_RP_ID=geokrety.org`
- [ ] **T10-B** Register a passkey on staging; confirm it is stored with rpId = `geokrety.org`
- [ ] **T10-C** Attempt login on staging; confirm success
- [ ] **T10-D** Attempt to use staging credential on production (separate DB) — expect 401 (credential not found)
- [ ] **T10-E** Confirm localhost uses `rpId = localhost` and passkeys work in local dev setup

---

## TASK-11 — Documentation

**Description**: Document the feature for contributors and operators.
**Expected outcome**: README section + operator runbook.
**Dependencies**: all above

- [ ] **T11-A** Add "Passkey authentication" section to project README
  - How to enable, required PHP extensions, env vars
- [ ] **T11-B** Add operator note: what to do when a user reports a cloned-authenticator lockout
- [ ] **T11-C** Update `CHANGELOG.md` with new feature entry
- [ ] **T11-D** Archive intermediate agent work to `.agent_work/passkey/`

---

## Dependency Order (implementation sequence)

```
T01 → T02 → T03 → T04 → T05 → T06 → T07
                                  ↓
                              T08, T09, T10, T11
```

---

## Effort Estimates

| Task | Estimate | Notes |
|---|---|---|
| T01 | S (2h) | Composer + env config |
| T02 | S (1h) | Single migration |
| T03 | S (1h) | Simple value object |
| T04 | M (3h) | Repository + tests |
| T05 | L (6h) | Core crypto logic + tests |
| T06 | L (5h) | 7 endpoints + middleware + tests |
| T07 | M (4h) | JS + templates |
| T08 | S (2h) | Nudge flow |
| T09 | S (1h) | Logging hooks |
| T10 | S (2h) | Env testing |
| T11 | S (1h) | Docs |
| **Total** | **~28h** | |

---

## Technical Debt Pre-registered

```
[Technical Debt] - AAGUID metadata lookup not implemented
Priority: Low
Location: src/Service/PasskeyService.php, src/Entity/PasskeyCredential.php
Reason: Phase 1 skips FIDO MDS lookup to reduce scope
Impact: Device names shown as "Passkey" rather than "iPhone 15" or "Bitwarden"
Remediation: Integrate fido-alliance/metadata-service MDS3 feed; cache AAGUID → name mapping
Effort: M

[Technical Debt] - Discoverable credential / usernameless login not implemented
Priority: Low
Location: PasskeyController loginBegin, passkey.js
Reason: Phase 1 requires username input before passkey login
Impact: Users cannot use the "just tap the key" zero-friction flow
Remediation: Set residentKey=required on registration; send empty allowCredentials on login begin
Effort: S
```
