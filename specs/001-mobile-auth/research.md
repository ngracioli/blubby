# Research: Mobile Registration and Sign-In

**Date**: 2026-10-29
**Scope**: Resolve the technical decisions in [spec.md](spec.md), without implementing backend behavior.

## Evidence and research method

Boost MCP tools are not exposed in this session. Research therefore used the installed package source, committed configuration, and official documentation. Locked and installed versions agree: Laravel 13.39.0, Sanctum 4.3.3, Boost 2.10.3, Laravel MCP 1.1.2, Sail 1.69.0, Pint 1.32.1, PHPUnit 12.5.42. Compose selects PHP 8.5 and PostgreSQL 18. These are file observations, not live runtime or database inspection.

Existing participants are `User`, `AppServiceProvider`, the Sanctum token table, database cache/locks, and `GET /api/user`. There is no login, registration, device identity, limiter, or authentication test implementation to reuse. `.ai/rules/` is absent. The architecture, naming, patterns, API, security, database, testing, and canonical backend-development guides govern the design.

## 1. Responsibility and transport

**Decision**: Use the Auth owner. Keep HTTP classes in `Http/*/Api/Auth`, conditional business participants in `Domain/Auth`, and `User` in `Models`. Use native Sanctum Bearer tokens and Eloquent. Retain unversioned `/api/register`, `/api/login`, `/api/logout`, and `/api/user` as the task-specific authentication operations already discussed. Names are `auth.register`, `auth.login`, `auth.logout`, and `auth.user`.

Registration gets `RegisterUserAction` because creating a user and its initial token must be atomic. `AuthenticateUserAction` owns credential verification, optional framework password rehash, and token issuance. `MobileSessionAuthenticator` owns the rolling-session validity/renewal transaction; `AuthenticationAttemptLimiter` owns concurrent admission shared by registration and login. Lookup and logout remain simple HTTP coordination/direct Eloquent operations. No Repository, DTO, interface, event, custom guard, or general authentication service is justified.

**Rationale**: These extractions have concrete transactions or shared rules; pass-through layers would add no value. Sanctum's documented mobile mode issues a token per named device. Configure `sanctum.guard` as an empty array for this Bearer-only API; do not enable stateful SPA middleware or disable web CSRF.

**Alternatives considered**: Cookie authentication is appropriate for a future browser SPA, not this native-mobile scope. OAuth/refresh-token infrastructure, Sanctum replacement, and per-controller Action/Service chains are unnecessary.

**Evidence**: `backend/docs/architecture/overview.md`, `backend/docs/architecture/naming.md`, `backend/routes/api.php`, and [Sanctum mobile authentication](https://laravel.com/docs/13.x/sanctum#mobile-application-authentication).

## 2. Rolling expiry and concurrency

**Decision**: Keep `sanctum.expiration = null`; every issued token has a non-null `expires_at` equal to issuance time plus 2,592,000 seconds. `expires_at` is the authoritative rolling deadline. Set `sanctum.last_used_at = false` and register the native `Sanctum::authenticateAccessTokensUsing` callback through `AppServiceProvider`.

The callback delegates to `MobileSessionAuthenticator`. If native validity is false, deny; never override native rejection. Otherwise use a short transaction on the token connection, re-read the token with `lockForUpdate()`, and sample server time after acquiring the row. Require an existing User owner and a persisted deadline strictly greater than that decision time. Update `last_used_at` and `expires_at` monotonically; synchronize the authenticated token instance from the persisted result. Return true only after commit. No HTTP globals enter the domain participant. Failed persistence denies access with a controlled unavailable response.

Logout deletes the authenticated token row. A callback that obtains the row first may authenticate an already in-flight request before logout; logout then deletes it. A callback after deletion finds no row and denies. Renewal cannot insert deleted rows. This defines concurrency ordering without claiming cancellation of already authorized requests.

A forward migration initializes only legacy null deadlines from `coalesce(last_used_at, created_at) + 30 days`, never from migration time. Already inactive tokens remain expired. This avoids granting old credentials a fresh period or leaving null-expiry tokens immortal. Keep populated historical deadlines on rollback rather than silently restoring immortal credentials.

**Rationale**: Installed `Guard.php` invokes the callback before its default last-used write. Doing the inactivity check in middleware after authentication could see an already refreshed timestamp. Disabling native tracking avoids stale, out-of-order last-used writes. A fresh locked row avoids renewal/logout races and regressing deadlines.

**Alternatives considered**: Global 43,200-minute expiration is creation-based and violates the spec. Post-authentication last-used checks, unconditional token saves, and a custom guard are rejected. An atomic conditional update is viable but row locking makes the fresh-time and legacy-state rules easier to prove at this scope.

**Evidence**: `backend/vendor/laravel/sanctum/src/Guard.php`, `src/SanctumServiceProvider.php`, `src/Sanctum.php`; [PostgreSQL row locking](https://www.postgresql.org/docs/18/explicit-locking.html#LOCKING-ROWS).

## 3. Independent attempt limits and exact admission

**Decision**: Initial operational defaults below are project choices, not claims about settings used by other apps. Declare them in `config/auth.php` under a dedicated mobile-auth section and keep contract/tests synchronized.

| Operation | Independent dimension | Admitted attempts | Window from first admitted attempt |
| --- | --- | --- | --- |
| Login | IP address | 60 | 60 seconds |
| Login | Installation UUID | 10 | 60 seconds |
| Login | Normalized email | 5 | 300 seconds |
| Registration | IP address | 10 | 3,600 seconds |
| Registration | Installation UUID | 3 | 3,600 seconds |

Count all admitted requests, including invalid credentials/input and successful requests; do not clear counters on success. A request denied by any exhausted dimension consumes no further budgets and does not slide the existing expiry. After expiry, a new window starts on the next admission. Return generic `429` with `Retry-After` equal to the longest currently blocking remaining interval, rounded up to at least one second. Do not disclose which dimension or whether an email exists.

Use native named limit definitions and `Illuminate\Cache\RateLimiter` with an explicitly selected database cache repository, never test/default array cache or failover memory. `AuthenticationAttemptLimiter` opens a short transaction on that SAME PostgreSQL connection; obtain transaction-scoped advisory locks for all applicable dimension keys in deterministic signed-numeric order, check every budget, then increment all only if every check passes. Reserve attempts and commit BEFORE hashing passwords or running the controller. Use a namespaced HMAC-SHA-256 of canonical dimension values; derive stable signed 64-bit advisory identifiers from those digests without platform-dependent overflow. Collision tests must establish deterministic encoding; an accidental collision only serializes unrelated attempts, never combines their counters.

Set local lock and statement timeouts of two seconds for this short admission transaction. Lock/cache/database failure returns controlled `503` and performs no credential verification or writes; do not fail open. Do not add plain `throttle` middleware on these routes as a second competing counter policy. Separate limits remain independent, not a single concatenated IP/email/device key. Counters and timers use the same database connection so transaction rollback rolls back partial reservations.

**Rationale**: Installed `ThrottleRequests` checks limits separately from increments. DatabaseStore's increment lock alone does not make check-and-admit atomic. PostgreSQL transaction locks provide exact concurrent admission without a new table, Redis, or a lease that can expire while a process is paused.

**Alternatives considered**: Stock throttle alone fails the spec's concurrent-budget test. Redis requires unnecessary infrastructure. A new custom attempts table duplicates native counter/timer behavior. Database cache locks have finite leases and need extra proof for overrun; advisory locks release with transaction end.

**Evidence**: Installed `ThrottleRequests.php`, `RateLimiter.php`, `DatabaseStore.php`, and `DatabaseLock.php`; [Laravel rate limiting](https://laravel.com/docs/13.x/rate-limiting) and [PostgreSQL advisory locks](https://www.postgresql.org/docs/18/explicit-locking.html#ADVISORY-LOCKS).

## 4. Device and network signals

**Decision**: Require a `device_id` UUIDv4, distinct from `device_name`, on registration/login. It represents an app installation; future mobile integration generates it randomly once and persists it privately until reinstall/reset. It is not an account credential or trusted proof of physical hardware. Normalize UUID text to lowercase and use only its HMAC in attempt keys; no device registry, tracking table, or hardware fingerprint is needed.

Middleware evaluates IP, well-formed normalized-email, and well-formed UUID budgets BEFORE Form Request validation. Missing/malformed device IDs still consume applicable IP/email admission and then fail with `422`. A syntactically valid forged/rotated UUID can start a different device bucket, but cannot reset the independent IP/email budgets. Changing a label changes no bucket. Document this limitation instead of claiming spoof-resistant device detection. Source addresses come from Laravel's resolved client IP; leave proxies untrusted by default and allowlist only actual deployment proxies before honoring forwarded IPs. Canonicalize IPv4/IPv6 representation before hashing; never trust a requester-selected forwarded header directly.

**Rationale**: App-scoped random identifiers avoid unnecessary hardware identifiers. The spec requires limits for recognized installations, not an unapproved attestation integration. IP-only limits are insufficient for distributed attacks; device-only limits are insufficient because a client can rotate identifiers. Shared-IP restrictions never revoke existing sessions.

**Alternatives considered**: `device_name` is attacker-controlled and non-unique. IMEI/MAC/advertising IDs, Play Integrity/App Attest, signed device provisioning, and fingerprinting introduce permissions/integrations or scope not requested.

**Evidence**: [Android identifier guidance](https://developer.android.com/identity/user-data-ids). It distinguishes app-instance identifiers from genuine-device abuse attestation; no attestation guarantee is asserted here.

## 5. Password and input validation

**Decision**: Keep installed bcrypt and Laravel hashing facilities. Registration requires at least 12 Unicode characters and at least one uppercase letter, lowercase letter, digit, and non-whitespace punctuation/symbol. Use Laravel validation with independent Unicode uppercase, lowercase, number, and punctuation/symbol checks. Native `symbols()` alone accepts separators; native `mixedCase()` uses a lookahead that does not match across line breaks. Neither is sufficient alone for the declared literal-password policy. Maximum password size is 72 UTF-8 bytes, with NUL rejected, to avoid bcrypt truncation/errors. Apply the byte bound and NUL rejection to confirmation and login too, but do not apply registration minimum/composition to existing-password verification. Never trim or normalize passwords. Preserve native TrimStrings password exclusions. In `bootstrap/app.php`, use the installed middleware configuration to exempt `email` from global TrimStrings as well; otherwise its Unicode-aware trimming would run before the declared ASCII-only normalizer. Requests and admission middleware share that exact ASCII normalizer.

Use a fixed, valid dummy bcrypt digest with the configured work factor for missing-user verification; perform the same password-check path without computing a fresh dummy hash on every failed request. Place this non-secret dummy digest beside hash configuration and verify its work factor when hashing configuration changes. User absence and wrong password share a `401` shape/message. Successful logins may use Laravel's password rehash facilities without changing the plaintext password.

Name: trimmed, required, maximum 255 characters. Email: required ASCII email, maximum 254 characters, trimmed of surrounding ASCII whitespace and lowercased. Device label: trimmed, required, maximum 100 characters. UUID: required, canonical UUIDv4, 36 characters. Reject NUL and control characters in labels; password whitespace remains literal. Bound request bodies at the deployment/app ingress and reject oversized bodies with `413`; use 16 KiB as the authentication-body limit. Validation errors retain Laravel's `message`/`errors` shape; new user-facing messages are Portuguese. Reject attempts to submit privilege/ownership fields or ignore them through explicit allowlisting, never assign them.

**Rationale**: Retaining bcrypt avoids changing infrastructure or invalidating existing hashes. Its byte limit must not be confused with character count. ASCII email keeps application and PostgreSQL lowercasing aligned in this initial scope, without inventing internationalized-address normalization.

**Alternatives considered**: Symbol validation that accepts whitespace violates the approved policy. Silently truncating long passwords or imposing composition on login breaks verification semantics. Password strength requirements are the user's explicit policy, not a universal standard. Internationalized email delivery/normalization and breached-password remote services are not introduced here.

**Evidence**: Installed framework `Rules/Password.php`, `Hashing/BcryptHasher.php`, `config/hashing.php`, and `TrimStrings.php`; [PHP bcrypt limitations](https://www.php.net/manual/en/function.password-hash.php).

## 6. Email integrity and migration safety

**Decision**: Add a forward migration creating `users_email_normalized_unique` on the expression `lower(btrim(email, E' \t\n\r\013\014'))`. Requests and lookup use the same surrounding ASCII whitespace set (space, tab, LF, CR, vertical tab, form feed), lowercase ASCII email, and bound query parameters. New writes store the canonical email. Existing stored casing/spacing is not rewritten; lookup and uniqueness use the expression.

Preflight existing addresses for normalized collisions and non-ASCII legacy values before rollout. Fail safely without merging/deleting accounts or silently rewriting identities. Existing ASCII stored addresses, including whitespace/casing differences, remain findable. A legacy non-ASCII address requires an explicit migration decision before enabling this bounded initial contract; do not ship around that preflight failure.

Keep the existing unique constraint. Catch only email-related unique violations outside the rolled-back registration transaction and translate to the same email validation error. A failed token insert rolls back the account. No financial records or new models are created.

**Rationale**: Validation alone cannot prevent concurrent duplicates. An expression index enforces the same identity rule for existing and new rows without destructive backfill.

**Alternatives considered**: Rewriting the original migration, installing citext, normalizing through an unbounded backfill, and automatic account merging are unnecessary or unsafe.

**Evidence**: Existing users migration, [PostgreSQL expression indexes](https://www.postgresql.org/docs/18/indexes-expressional.html), and [database guide](../../backend/docs/database/README.md).

## 7. Abilities, cleanup, privacy, and delivery proof

**Decision**: New tokens receive only `user:read` and `session:revoke`. Require the corresponding native Sanctum ability for current-user lookup and logout; retain existing wildcard-token compatibility. All sessions use authoritative User ownership. Responses expose explicit public fields only; issuance returns the plaintext token once and sends `Cache-Control: no-store`. Never log authorization headers, passwords, or device identifiers.

Schedule native `sanctum:prune-expired --hours=24` daily using existing scheduler facilities; expired access is denied immediately regardless of cleanup. Keep global creation-based expiration null. The native delete predicate rechecks stored deadlines rather than deleting a preselected list of IDs, preserving concurrently renewed rows. Reuse database cache lazy expiration; avoid a new permanent attempt ledger.

Use real issued Bearer tokens in session tests, not `Sanctum::actingAs`, which bypasses guard validity. Use PostgreSQL 18, explicit database cache for limiter proof, and committed fixtures visible to independent processes for races. First obtain a meaningful failing test, then implement the smallest responsible behavior. Preserve `/api/user`'s current unwrapped public fields. No mobile screen, provider login, recovery flow, or new dependency is included.

**Alternatives considered**: All-abilities tokens, a new cleanup command, deleting users on inactivity, and array-cache concurrency tests are rejected.

**Evidence**: Installed Sanctum `Console/Commands/PruneExpired.php`; [backend testing guide](../../backend/docs/testing/README.md).

## Research resolution

All planning questions have selected designs. No unresolved technical clarification remains. Initial throttle budgets and payload bounds are explicit operational defaults to validate and tune, not inferred product requirements. Runtime overrides, migration preflight, lock behavior, hash-cost parity, and deployment proxy/HTTPS configuration must be verified during implementation/delivery; this research does not claim those checks passed.
