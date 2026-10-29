# Data Model: Mobile Registration and Sign-In

**Status**: Planned design, not deployed schema. See [research](research.md) and [HTTP contract](contracts/auth.md).

## User account — existing `users`

Ownership: Auth. Keep `App\Models\User` and its current integer primary key, timestamps, hashed password cast, fillable allowlist, and hidden credential fields.

| Field | Existing storage | Authentication rule |
| --- | --- | --- |
| `id` | bigint primary key | Assigned by persistence, never supplied as authentication identity |
| `name` | varchar(255), required | Trimmed, 1–255 characters, no control characters |
| `email` | varchar(255), required, existing unique constraint | Input 1–254 ASCII characters; valid email; trim surrounding ASCII whitespace; lowercase on new writes |
| `password` | varchar(255), required | Bcrypt verifier only; plaintext never persisted or returned |
| `email_verified_at` | nullable timestamp | Registration leaves null; never client-assignable; verification not required in this delivery |
| `remember_token` | nullable string | Existing field, hidden; no cookie/remember-me flow added |
| `created_at`, `updated_at` | timestamps | Existing public fields remain compatible |

Add the unique expression index `users_email_normalized_unique` on `lower(btrim(email, E' \t\n\r\013\014'))`. Retain the original unique constraint. Use the identical expression for legacy lookup and duplicate checks, with bound input values. The whitespace set is ASCII space, tab, LF, CR, vertical tab, and form feed; do not use an unspecified Unicode trim implementation.

### Password validation

- Registration: at least 12 Unicode characters; at least one uppercase letter, lowercase letter, numeric digit, and non-whitespace punctuation or symbol. Separators alone do not meet the symbol requirement.
- Registration, confirmation, and login: maximum 72 UTF-8 bytes, valid string encoding, no NUL, no silent truncation or normalization. Confirmation must match the password exactly.
- Login: required nonempty password, but no registration minimum/composition requirement; existing shorter passwords can still authenticate.
- Use independent Unicode character-class checks so whitespace never counts as a symbol and a literal line break does not hide otherwise present uppercase/lowercase letters.
- Exempt `email` from global TrimStrings before performing the declared ASCII normalization; passwords retain the existing exclusions.
- Native framework hashing/casts remain authoritative. New plaintext input is hashed exactly once; existing verified hashes are not rehashed as plaintext.
- A missing-user login checks a non-secret, fixed, same-work-factor dummy digest before returning the generic credential error. No raw candidate password or digest enters logs.

### Account state

There is no new account status enum or verification gate. Registration creates an account and token atomically. Failure creates neither. Duplicate identity conflicts leave the existing account unchanged. No inactivity behavior deletes an account.

## Device session — existing `personal_access_tokens`

Reuse Sanctum's model/table; no custom token model or second session table.

| Field | Existing storage | Planned semantics |
| --- | --- | --- |
| `id` | bigint primary key | Server-assigned token identifier |
| `tokenable_type`, `tokenable_id` | polymorphic owner | Must resolve to the authenticated `User`; never accepted from request input |
| `name` | text | Required device label, trimmed, 1–100 characters, no control characters |
| `token` | unique 64-character string | Sanctum's SHA-256 token verifier; plaintext returned only at issuance |
| `abilities` | nullable text | New sessions receive only `user:read` and `session:revoke` |
| `last_used_at` | nullable timestamp | Null until first authenticated activity; thereafter updated monotonically by the authoritative renewal transaction |
| `expires_at` | nullable timestamp with existing index | Required non-null for newly issued sessions; rolling deadline, exactly 2,592,000 seconds after issuance/latest accepted activity |
| `created_at`, `updated_at` | timestamps | Framework-managed metadata; original creation does not limit active-session lifetime |

Use UTC server time and elapsed seconds, not client clocks or calendar-month arithmetic. Existing timestamp precision must be reflected in boundary tests; do not claim subsecond precision beyond stored values. All comparisons use the same declared storage precision.

### Session transitions

1. **Issue**: Persist a hash and initial deadline, return plaintext once after successful commit.
2. **Authenticate and renew**: Sanctum verifies Bearer hash/provider. Its callback rejects native invalidity; otherwise lock and re-read the persisted row, sample current time at storage precision, and require `expires_at > now`. Set last-used and deadline monotonically, commit, then authorize the request.
3. **Expire**: At `now >= expires_at`, authentication fails. This is a derived condition, not a stored enum or a periodic-job dependency. Failed requests never reset it.
4. **Revoke**: Logout deletes only the authenticated row. It never changes another token or the account. Any already authorized request can finish; later authentication must fail.
5. **Prune**: Native daily pruning deletes rows whose deadline is more than 24 hours past. Denial does not wait for pruning. Use the live predicate in the delete, not a stale preselected ID list.

Concurrent renewal serializes on the token row and never shortens the deadline. Concurrent logout or cleanup may delete first, in which case renewal fails; renewal never recreates a missing token. Native last-used tracking is disabled to prevent a second unsynchronized write after the callback.

### Legacy null deadlines

A forward migration fills only null `expires_at` values using original `coalesce(last_used_at, created_at) + interval '30 days'`. Do not use migration execution time. Preserve existing explicit deadlines. Old inactive tokens stay unusable. The callback fails closed if it encounters an unexpected null deadline after rollout.

## Attempt restriction — existing database cache

No new attempts entity/table/model. The domain service uses native counter and timer entries in the explicit PostgreSQL-backed cache repository.

| Logical attribute | Representation / rule |
| --- | --- |
| Operation | `login` or `register`; separate policies and counters |
| Dimension | Canonical IP, canonical installation UUID, or normalized login email |
| Key | Versioned operation/dimension prefix plus HMAC-SHA-256 of the bounded canonical value; never raw personal identifiers |
| Count | Number of admitted requests in the current window, including requests that later fail validation or credentials |
| Window end | Fixed expiry from first admitted request, not extended by rejected attempts |
| Budget / duration | Explicit values in the [contract](contracts/auth.md#attempt-limits) and `config/auth.php` |

Each admission transaction locks all applicable dimension identifiers in deterministic order using PostgreSQL transaction advisory locks, checks every budget, reserves them together, and commits before credential work. Native cache reads/writes and advisory locks must share the same database connection and transaction. No array cache fallback. A denial reserves nothing; a later invalid request has already consumed its admitted slots. Existing sessions remain usable when unauthenticated attempt limits are exhausted.

Expired cache values are logically absent. Reuse the database cache's lazy cleanup; do not retain raw identity attempt histories. A change to key derivation or application HMAC key resets operational windows and must be treated as an operational security change.

## Installation signal — request value, not persisted hardware identity

`device_id` is a required UUIDv4 string (36 characters, canonical lowercase). It is distinct from `device_name` and from session credentials. Its future client lifecycle is one random UUID per app installation; reinstall/reset may replace it. Backend stores its digest only in expiring attempt keys; it is not stored in User or token records and is not used to merge sessions.

Missing/invalid signals are rejected after applicable IP/email admission checks. Valid-looking fabricated IDs are indistinguishable from new installations; independent IP/email protection remains mandatory. This design does not claim device attestation or stop a distributed attacker rotating both IPs and installation IDs.

## Migration compatibility and recovery

- Create two ordered transactional forward migrations: first the normalized email index, then the legacy deadline initializer. Never edit the shared users/token migrations.
- Preflight normalized duplicate addresses and non-ASCII legacy addresses before enabling the ASCII input contract. Stop with a safe operator report if either exists; do not merge, delete, or disclose user rows in normal output.
- The table is initially small; ordinary transactional index creation is adequate. Reassess locking before rollout if table size changes; do not introduce concurrent-index machinery speculatively.
- The index migration commits independently; index creation failure rolls back only that migration. The later legacy deadline initializer runs in its own transaction, preserving populated deadlines and all accounts. If initialization fails, its transaction rolls back while the committed index remains intact; block rollout until the failure is resolved. New authentication endpoints must not be made public before both migrations succeed.
- The index migration's `down()` drops only the new expression index. The deadline initializer's `down()` retains populated historical deadlines: their previous null values cannot safely be reconstructed. Do not claim a fully reversible data backfill or restore immortal tokens silently.
- Old application code may interpret retained deadlines as absolute; deploy/rollback the authentication feature as a unit and avoid a mixed-version fleet while changing expiry semantics. Rollback disables new endpoints if needed; it must not silently remove security constraints from publicly active authentication.
- Verify up/down/up only on disposable PostgreSQL data. Never run destructive migration/reset commands against local user or shared data.
