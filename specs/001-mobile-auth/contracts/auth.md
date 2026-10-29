# Authentication HTTP Contract

**Status**: Planned. Unversioned JSON API for native mobile clients. Backend implementation and mobile integration are separate work.

## Common rules

- Use HTTPS outside local development. Send `Accept: application/json`; write operations send `Content-Type: application/json`.
- Protected operations require `Authorization: Bearer <token>`. Cookies alone do not authenticate these mobile operations; web CSRF remains enabled.
- Authentication responses use `Cache-Control: no-store`. Never log request passwords, token bodies, authorization headers, or installation identifiers.
- Registration/login issue only `user:read` and `session:revoke` abilities. Native wildcard tokens remain compatible. Insufficient ability returns `403`; credentials missing, invalid, expired, or revoked return `401`.
- Body size limit for registration/login is 16 KiB; larger requests return `413` before expensive processing. Malformed JSON returns controlled `400` without credentials or parser internals.
- Only allowlisted fields are used. Client-submitted IDs, privileges, verification timestamps, token abilities, or session deadlines cannot override server decisions.
- New public messages are Portuguese. Use native Laravel JSON error structure; no custom success flag or nested duplicate envelopes.

## Input fields

| Field | Registration | Login | Rule |
| --- | --- | --- | --- |
| `name` | Required | Not used | Trimmed; 1–255 characters; no control characters |
| `email` | Required | Required | Valid ASCII email; at most 254 characters; remove surrounding ASCII whitespace, then lowercase |
| `password` | Required | Required | Exact nonempty string; at most 72 UTF-8 bytes; reject NUL. Registration additionally requires at least 12 characters and upper/lowercase, digit, non-whitespace punctuation/symbol |
| `password_confirmation` | Required | Not used | Same byte/NUL bounds and exact equality to the registration password |
| `device_name` | Required | Required | Trimmed human-readable label; 1–100 characters; no control characters; not a device security identifier |
| `device_id` | Required | Required | UUIDv4, 36 characters; canonical lowercase; per-installation abuse signal, not proof of hardware or account ownership |

Surrounding email whitespace means ASCII space, tab, LF, CR, vertical tab, and form feed. Passwords are never trimmed or case-normalized. Login does not enforce new-password minimum/composition on existing passwords. See [data-model.md](../data-model.md) for persistence identity and byte-count semantics.

## Operations

| Method / path | Route name | Authentication | Success |
| --- | --- | --- | --- |
| `POST /api/register` | `auth.register` | Public; registration admission limits | `201` with issued session and public user |
| `POST /api/login` | `auth.login` | Public; login admission limits | `200` with issued session and public user |
| `GET /api/user` | `auth.user` | Bearer; `user:read` ability | `200`, existing unwrapped current-user identity |
| `POST /api/logout` | `auth.logout` | Bearer; `session:revoke` ability | `204`, empty body |

### Registration

A valid request creates one account and its initial session in one transaction. Return success only after commit. Invalid fields or duplicate normalized email return `422`; concurrent email conflicts receive the same controlled validation shape. Initial session failure leaves no account. A successful registration retry is not idempotent: a repeated email is rejected rather than returning credentials for an existing account.

### Login

Verify the normalized email and exact password. Unknown email and incorrect password both return `401` with the identical message `E-mail ou senha inválidos.` and no field-specific account-existence signal. Perform a comparable password-check path for absent accounts using the configured dummy digest. Invalid input shape returns `422` before credential checking, after admission limits. Each successful login issues an independent token; matching device labels/UUIDs do not silently revoke or reuse an earlier session.

### Session issuance response

Registration (`201`) and login (`200`) return this single envelope. Example values are illustrative, not production credentials:

```json
{
  "data": {
    "user": {
      "id": 1,
      "name": "Pessoa Exemplo",
      "email": "pessoa@example.com",
      "email_verified_at": null,
      "created_at": "2026-10-29T12:00:00.000000Z",
      "updated_at": "2026-10-29T12:00:00.000000Z"
    },
    "token": "<issued-once-bearer-token>",
    "token_type": "Bearer"
  }
}
```

User fields are explicitly serialized. No password, remember token, credential verifier, abilities storage, or other private metadata appears. `expires_at` is not exposed as a fixed logout date: the deadline moves with activity. No refresh token is issued.

### Current user compatibility

`GET /api/user` retains its existing unwrapped shape: `id`, `name`, `email`, nullable `email_verified_at`, `created_at`, and `updated_at`. Timestamps retain Laravel's existing serialization. It never accepts a user identifier to select someone else's account and never returns the Bearer token.

### Inactivity and logout

Every protected authentication checks the current persisted deadline before renewal. A session remains valid only while its deadline is strictly in the future. Valid authentication restarts its own 2,592,000-second window; at the exact deadline access is denied. Offline openings and failed authentication do not count. A valid session may renew even when later resource authorization fails; validity and resource permission are separate concerns.

Logout authenticates and deletes only the requesting token. A second logout using that revoked token returns `401`, not success for another session. Other sessions remain unchanged. Requests authorized before logout may finish, but renewal cannot restore the deleted row.

## Attempt limits

Initial operational defaults, not a universal industry policy:

| Operation | Dimension | Budget | Window |
| --- | --- | --- | --- |
| Login | IP | 60 attempts | 60 seconds |
| Login | Installation UUID | 10 attempts | 60 seconds |
| Login | Normalized email | 5 attempts | 300 seconds |
| Registration | IP | 10 attempts | 3,600 seconds |
| Registration | Installation UUID | 3 attempts | 3,600 seconds |

Every applicable budget is checked independently. Only if all permit admission are they reserved atomically. The first admitted attempt starts each fixed window. Count admitted requests regardless of later validation/credential result; do not clear on success. Blocked requests neither consume other budgets nor extend retry windows. After a window expires, admission is possible if no other budget blocks it.

Changing IP, email, or device does not reset a different still-recognized dimension. Device names do not participate. Missing/malformed UUIDs still encounter valid IP/email restrictions, then receive `422` if admitted. Malformed emails encounter IP/device restrictions without creating unbounded malformed-email buckets. A fabricated valid UUID can start a new installation bucket; independent IP/email caps are never waived. The backend does not claim hardware attestation or immunity to distributed attacks rotating every signal.

Use only the framework-resolved client IP after actual trusted-proxy configuration; forged forwarding headers from untrusted peers cannot choose an IP bucket. Shared-IP blocking is temporary and never revokes existing sessions. Installation identifiers are bounded, canonicalized, and HMAC-hashed in cache keys, not retained as tracking history.

### Rate-limit response

Return `429` with a positive integer `Retry-After` header covering the longest currently violated window; do not identify the email/device/IP dimension in the response. Example body:

```json
{"message":"Muitas tentativas. Tente novamente mais tarde."}
```

An attempt admitted immediately before a limit is exhausted may finish. Concurrent admission cannot exceed the declared budget. Atomic-admission persistence/lock failure returns `503`, not an unprotected credential attempt. A new attempt after the restriction ends is eligible; repeated new attacks may trigger later windows, but no permanent account lock flag is set.

## Error contract

| Condition | Status | Shape / effect |
| --- | --- | --- |
| Invalid fields or duplicate registration email | `422` | Native `message` and `errors` keyed by field; no account/session creation |
| Unknown email / wrong password | `401` | Identical generic Portuguese message; no session issuance |
| Missing / invalid / expired / revoked Bearer | `401` | Generic authentication error; no identity disclosure or renewal |
| Insufficient token ability | `403` | Generic permission error; no protected operation |
| Admission budget exhausted | `429` | Generic message and `Retry-After`; no account/session creation |
| Malformed JSON | `400` | Generic message; no internal parser details |
| Oversized body | `413` | Generic message; no hashing/persistence |
| Authentication persistence/admission unavailable | `503` | Generic temporary-unavailability message; no fail-open authentication |

Do not translate unrelated unexpected exceptions into credential errors or success. Unhandled failures use safe centralized production error rendering; no stack traces, SQL, credentials, or provider details. Existing framework authentication/authorization error copy may remain compatible; new credential and validation copy is Portuguese. This feature introduces no browser authentication, account recovery, verification, social login, or session-list endpoints.
