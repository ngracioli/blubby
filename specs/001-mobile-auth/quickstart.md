# Validation Guide: Mobile Registration and Sign-In

**Status**: Planned validation. Authentication endpoints and tests do not exist yet. Commands below are implementation/delivery instructions, not a record of successful execution.

## Prerequisites

- Read [spec.md](spec.md), [plan.md](plan.md), [research.md](research.md), [data-model.md](data-model.md), and [HTTP contract](contracts/auth.md).
- Use the existing backend dependencies, Docker/Sail, PHP 8.5 and PostgreSQL 18. Do not install another authentication stack or Redis.
- Verify Docker access and inspect services before starting the existing development stack. Run PHP/Composer/Artisan only through Sail.
- Tests must use the disposable PostgreSQL `testing` database provisioned by Sail, or CI's isolated `blubby_test`; never development/shared data. Confirm effective connection/database and clear DB_URL overrides before any RefreshDatabase setup. Print only driver and database name, never passwords/connection URLs.
- The implementation must add a test-bootstrap guard rejecting unexpected database names/connections before destructive setup. Tests of transactions/DDL must have the same safety guard.

From repository root, inspect the environment:

```sh
cd backend
./vendor/bin/sail ps
```

If the existing development services are stopped, start them through Sail as part of implementation setup. No service is started by this planning phase.

```sh
./vendor/bin/sail up -d
./vendor/bin/sail composer show laravel/framework
./vendor/bin/sail composer show laravel/sanctum
./vendor/bin/sail artisan make:test --help
./vendor/bin/sail artisan migrate --help
```

## Test-first implementation gate

1. Add the smallest observable HTTP test under `tests/Feature/Api/Auth` using Sail's PHPUnit generator. Generate `Api/Auth/RegistrationTest`, not `Feature/Api/Auth/RegistrationTest`.
2. Run the test before implementation. It should fail because the requested endpoint/behavior is missing. An unavailable database, syntax error, or missing test is not a valid red test.
3. Implement the smallest responsible behavior and rerun the same test until green. Repeat for materially distinct security/session behavior.
4. If the meaningful failure cannot run, report the actual obstacle before implementing behavior; do not substitute SQLite or static source checks for PostgreSQL proof.

After those test files exist:

```sh
./vendor/bin/sail artisan test --compact tests/Feature/Api/Auth/RegistrationTest.php
./vendor/bin/sail artisan test --compact tests/Feature/Api/Auth
./vendor/bin/sail artisan test --compact tests/Feature/Domain/Auth
./vendor/bin/sail artisan test --compact
```

The normal PHPUnit cache/session are array-backed. Admission tests must explicitly select the shared database cache. Session tests must use actual issued Bearer credentials; `Sanctum::actingAs()` bypasses the guard behavior being tested. Ordinary `RefreshDatabase` tests alone do not prove concurrent writes.

## Required end-to-end scenarios

### Registration and credentials

- Valid registration returns `201`, one account, and one usable token; lookup returns the registered identity without a second sign-in.
- Each missing required field, malformed UUID/email, oversized value, NUL-containing password, mismatched confirmation, and non-allowlisted privilege input produces a controlled result without unwanted writes.
- Independently omit uppercase, lowercase, digit, and punctuation/symbol. Each registration fails. Whitespace-only replacement for a symbol fails. A composition-valid 11-character password fails; exactly 12 characters passes. Exercise multibyte inputs at the 72-byte bound and ensure no truncation. Uppercase and lowercase separated by a literal line break still satisfy their independent checks; whitespace does not substitute for a symbol.
- Correct existing shorter-password login still works. Unknown email and incorrect password have identical public status/body, no token, and equivalent configured-cost password-check work.
- Email casing/ASCII surrounding whitespace identify the same account. A non-ASCII separator around an email is rejected rather than silently removed by global TrimStrings. Concurrent duplicate registrations yield one account; token-insert failure rolls back the new user. Do not test rollback merely by asserting a mocked call.
- Current-user responses preserve existing unwrapped public fields and exclude credentials. User-supplied ownership/verification/abilities cannot escalate access.

### Session continuity and revocation

- With controlled server time, authenticate on day 29 and again on day 31: no new sign-in required.
- Test just before, exactly at, and after 30 elapsed days without activity using actual stored timestamp precision. Exactly at the deadline must fail; repeated expired requests never restore validity.
- Two sessions have independent rolling deadlines. Renew one, expire/revoke the other, and verify each remains independent.
- Logout returns `204`; reused credentials return `401`. Invalid logout never touches another session. Missing ability returns `403`; missing Bearer cannot authenticate through a web cookie.
- Native callback must not accept a token deleted after initial lookup. Concurrent accepted activity never regresses last-used/deadline; concurrent logout never allows a later authentication or recreates the row.
- Legacy null deadlines derive from historical activity: old inactive credentials remain expired; no migration-time grace period.

### Independent attempt limits

For each [contract budget](contracts/auth.md#attempt-limits), test the last allowed attempt, first rejected attempt, `Retry-After`, just before retry expiry, and eligibility at expiry. Set up other dimensions so they do not hide the tested budget.

- Exhaust login IP while varying email/UUID; exhaust installation while varying IP/email; exhaust email while varying IP/UUID.
- Exhaust registration IP while varying email/UUID; exhaust registration installation while varying IP/email.
- Changing only the device label changes no device counter. Missing/malformed/rotated/forged UUIDs never waive applicable IP/email restrictions. A valid rotated UUID's limited device-only protection is explicit, not represented as hardware attestation.
- Forwarded headers from an untrusted peer cannot choose a different IP bucket. Equivalent IPv6 spellings canonicalize consistently.
- Blocked requests create no account/token and do not extend retry windows. Successful requests consume budgets too. Temporary source restrictions do not revoke active sessions or permanently lock accounts.
- Admission lock/persistence failures return `503` before hashing/user creation. Body-size/JSON failures are controlled and reveal no request secrets.

### PostgreSQL concurrency proof

Use committed fixture setup and independent processes/connections, coordinated with barriers; child processes do not share a parent's uncommitted RefreshDatabase transaction. Tests must assert the actual driver/name guard and explicit database cache on every connection.

1. Leave one admission slot, release simultaneous requests for the same restricted dimension, and assert at most one is admitted. Repeat for IP, device, email, and overlapping dimension sets. Reverse key input order to detect deadlocks; verify deterministic lock order and partial-reservation rollback on failure.
2. Interleave two renewals after initial token lookup and assert fresh persisted validity and nondecreasing timestamps/deadlines.
3. Interleave renewal with deletion/logout; an authorization accepted before deletion may finish, but any later authentication fails and no row is recreated.
4. Interleave native cleanup with renewal and assert no active/renewed row is removed. Assert old expired rows are removed and account rows remain intact.
5. Interleave duplicate normalized registrations and verify the unique constraint and controlled error outside the rolled-back transaction.

Do not claim concurrency safety from sequential tests or array cache. Use real expired rows and the native prune command for cleanup tests; do not preselect then delete stale IDs.

## Schema and recovery checks

Run schema up/down/up tests only inside a disposable PostgreSQL test harness guarded to `testing`/`blubby_test`. The harness creates legacy casing/whitespace and token-deadline fixtures, exercises migration transitions, and verifies the normalized index and non-destructive rollback. Do not run bare rollback/fresh commands against the development database.

Preflight normalized collisions and legacy non-ASCII emails before rollout. Failure must stop migration/rollout without merging or deleting users. Index rollback preserves accounts and the intentionally retained historical deadline initialization. Avoid a mixed-version fleet with incompatible expiry code.

## Final verification after implementation

```sh
./vendor/bin/sail artisan route:list --path=api --except-vendor
./vendor/bin/sail artisan schedule:list
./vendor/bin/sail artisan test --compact
./vendor/bin/sail bin pint --dirty --format agent
```

If Pint cannot see Git inside the backend-only mount, pass the explicit changed PHP files as documented in the backend testing guide. Check `sanctum:prune-expired --help` before scheduling daily pruning with `--hours=24`. Running the scheduler in deployment, actual HTTPS termination, trusted proxies, production debug settings, and same-cost dummy hash are rollout prerequisites.

Update backend contract/security/testing docs and report exact commands, results, and blockers. Runtime feature tests, migration tests, concurrency tests, Pint, and production checks remain unexecuted until implementation; planning validates documentation only.

## Implementation execution — 2026-10-29

- T001: working tree initially contained only the feature's untracked `specs/` artifacts. All 16 requirements-quality checklist items passed; no extension hooks exist.
- Boost MCP tools are unavailable in this session; installed source and official documentation from research are the fallback. Sail Composer confirms Laravel 13.39.0 and Sanctum 4.3.3. Sail PHP reports 8.5.12 with pcntl available.
- Docker initially required sandbox escalation. Authorized Sail access succeeded; existing services were stopped and `./vendor/bin/sail up -d` started the existing PostgreSQL/application containers without replacing infrastructure or volumes.
- Inspected Sail generator help for tests, classes, migrations, controllers, requests and Resources. Verified existing PHP/Node ignores and added missing repository/Docker-context ignores; package publishing, ESLint, Prettier, Terraform and Helm configuration are not part of this backend change.
- T002–T003: added a pre-RefreshDatabase PostgreSQL/name/URL guard in `tests/TestCase.php`, including actual `current_database()` confirmation. Added two current-user preservation cases with a real Bearer token. Sail full suite passed 8 tests / 212 assertions; focused current-user suite passed 2 tests / 3 assertions. Focused Pint passed. Deliberate unsafe-database guard branch testing remains to be covered before final review.
- T004: independent-process concurrency harness added with ready/start barriers, isolated-database checks, no-active-transaction requirement, bounded child termination and sanitized errors. Harness suite passed 3 tests / 6 assertions; focused Pint and whitespace checks passed. Product races are not yet proven.
- T005–T013: red evidence was `201` expected / `404` actual on registration, ordinary case-sensitive index admitting a normalized duplicate, and missing-route failures for atomicity. After implementation, Sail passed 47 tests / 307 assertions across RegistrationTest, RegistrationAtomicityTest, EmailIdentityTest and CurrentUserTest (2,445 ms). This includes real simultaneous duplicate requests and token-insert rollback against PostgreSQL.
- Implementation placement refinement: `Http/Middleware/Auth/ValidateAuthenticationPayload.php` provides the shared registration/login JSON/body boundary rather than duplicating parser/size handling in Form Requests or putting request execution logic in bootstrap configuration. It is an actual HTTP consumer, not a new domain abstraction. No frontend changed.
- T014–T020: login red was `200` expected / `404` actual; missing token ability red was `403` expected / `200` actual. Implemented Bearer-only login, same-message credential failures, fixed same-cost dummy verification, optional rehash with token issuance in one transaction, and explicit compatible identity serialization/abilities. Sail passed 72 tests / 416 assertions across LoginTest, CurrentUserTest and RegistrationTest (2,199 ms). PHPUnit uses a non-secret bcrypt-cost-4 dummy digest; development bcrypt cost is 12 and its default dummy digest matches.
- T021–T026: inactivity, concurrency and legacy-migration suites plus `MobileSessionAuthenticator` (locked fresh-row recheck, strict future deadline, monotonic timestamps), Sanctum callback wiring, `expiration = null`/`last_used_at = false`, and the legacy deadline migration. Sail passed 19 tests / 65 assertions across SessionInactivityTest, SessionConcurrencyTest and LegacySessionMigrationTest (red-first evidence was recorded by the interrupted prior run and is not re-verified here).
- T027–T029: SessionCleanupTest red was missing scheduler registration (`Limpeza diária de sessões não agendada`); after `Schedule::command('sanctum:prune-expired --hours=24')->daily()` in `routes/console.php`, it passed 3 tests / 10 assertions, including real prune-versus-renewal processes. Full Sail suite: 109 tests / 741 assertions passed (5.6 s).
- T030–T033: logout red was `204` expected / `404` actual (route missing). Added `POST /api/logout` (`auth:sanctum` + `abilities:session:revoke`) deleting only `currentAccessToken()`, empty `204` with `no-store`. LogoutTest + SessionRevocationConcurrencyTest passed 6 tests / 23 assertions, including a real logout-versus-renewal process race (deleted token never recreated; in-flight authorized request may finish). Full suite passed afterwards.
- T034–T042: red was 19 of 22 US5 cases failing (no limits). Implemented `AuthenticationAttemptLimiter` (explicit database cache store, transaction on the store's own connection, sorted `pg_advisory_xact_lock`, check-all/reserve-all, 2 s local lock/statement timeouts, HMAC-SHA-256 keys), `ThrottleAuthentication` middleware (aliased `auth.throttle:login|registration`, after payload bounds, before Form Request) and `auth.mobile.limits` config. Gotcha found by tests: the container auto-injected the default (array) `RateLimiter` into the limiter, silently bypassing the database store; fixed with an explicit binding in `AppServiceProvider`. Refinement: budgets live in config rather than `RateLimiter::for` named definitions (no consumer needs them). Forwarded headers are ignored because no trusted proxies are configured. Full Sail suite with budgets enabled: 137 tests / 1025 assertions passed, including real simultaneous admission (exact 5/10 admitted), overlapping dimension sets without deadlock, injected partial-reservation rollback, 2 s lock-contention failure → `503`, and dropped-cache-table → `503` with no account/session created.
- T043–T044: updated backend API/security/database/testing/operations/architecture docs and the authentication-entry flow status text; `backend/.ai/guidelines/project-architecture.md` status line updated. `sail artisan boost:update --no-discover --no-interaction` run twice: second run produced no further change (`AGENTS.md` identical); only the intended `AGENTS.md` status line changed, `CLAUDE.md` import untouched.
- T045: full Sail suite 137 tests / 1025 assertions passed; Pint (`sail php vendor/bin/pint` on app/tests/routes/config/database/bootstrap files — `pint --dirty` and multi-file `sail bin pint` do not work in this backend-only mount) fixed import ordering in `AppServiceProvider.php`/`bootstrap/app.php`, then `--test` passed; `route:list --path=api` shows register/login/logout/user; `schedule:list` shows daily `sanctum:prune-expired --hours=24`; `git diff --check` clean. No mobile/Biome checks run (inputs unchanged).
- T046 (rollout prerequisites, documented in `backend/docs/operations/README.md`, nothing deployed): scheduler, HTTPS/`APP_DEBUG=false`, trusted proxies, production-cost `AUTH_DUMMY_PASSWORD_HASH`, shared cache table and legacy email preflight remain deployment blockers to verify; not executed against production. The legacy-collision preflight is enforced by the migration test, not run on real data.
- T047: cavecrew reviewer found 0 critical / 3 risk / 1 nit. Fixed with regression proof: (1) email budget signal now uses the same `ascii` + `email:rfc` predicate as the Form Requests (red: `pessoa@localhost` got `401` instead of `429`; green after fix); (2) limiter failures now log only the exception class before the `503`. Not changed: trusted-proxy configuration (no deployment proxy exists; remains a documented rollout blocker, T046) and uppercase `device_id` acceptance (throttle lowercases the signal; contract-only nit, left as is). Reviewer confirmed limiter atomicity, locked session renewal, logout scope, `no-store` coverage, password non-trimming and dummy-hash work. Final suite: 138 tests / 1026 assertions passed; Pint passed.
- Requirement proof map: FR registration/identity → RegistrationTest, RegistrationAtomicityTest, EmailIdentityTest; login/enumeration/abilities → LoginTest, CurrentUserTest; 30-day inactivity/legacy/cleanup → SessionInactivityTest, SessionConcurrencyTest, LegacySessionMigrationTest, SessionCleanupTest; logout → LogoutTest, SessionRevocationConcurrencyTest; limits → AuthenticationThrottleTest, AuthenticationSourceTest, AuthenticationAdmissionConcurrencyTest. Production checks (HTTPS, proxies, scheduler, hash cost) are unrun.

## Mobile integration

The Expo app now connects registration, login, current-user restoration, and logout to this contract. Native sessions store the bearer token and installation UUID with Expo SecureStore; web keeps them in memory and requires sign-in after reload. Verified with `npm run typecheck` and `npm run lint` from `mobile/`. Device rendering and live API requests were not exercised.
