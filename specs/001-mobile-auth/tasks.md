# Tasks: Mobile Registration and Sign-In

**Input**: Design documents in `specs/001-mobile-auth/`: [spec](spec.md), [plan](plan.md), [research](research.md), [data model](data-model.md), [contract](contracts/auth.md), and [validation guide](quickstart.md).

**Prerequisites**: Read repository `AGENTS.md`, `backend/AGENTS.md`, `backend/docs/agent-workflow.md`, applicable backend architecture/API/security/database/testing guides, and canonical `backend/.ai/skills/backend-development/SKILL.md`. Read the installed testing-best-practices skill before test authoring. No dependency upgrades, new infrastructure, mobile UI, social login, recovery, or financial behavior.

**Tests**: Required by the specification, plan, and project constitution. Each behavior test task includes writing and RUNNING the focused test, observing failure caused by missing behavior before implementation. An environment, syntax, fixture, or import error is not acceptable proof. Test existing behavior before refactoring it. Stop behavior implementation and report the obstacle if meaningful proof cannot run.

**Organization**: Five P1 user-story phases, each independently testable using existing users/actual tokens when appropriate. Earlier phases can be demonstrated internally; none authorizes public release before all required security/session gates pass.

## Format: `[ID] [P?] [Story] Description`

- All task checkboxes remain unchecked until the task and its verification actually finish.
- `[P]` identifies independent files in the explicitly named parallel wave, after that wave's prerequisites. It does not override test-first sequencing.
- `[US1]` through `[US5]` map to the specification's stories. Shared setup/foundation/final tasks have no story label.
- Paths are relative to repository root. PHP/Composer/Artisan execution uses Sail from `backend/`. Use inspected, noninteractive Artisan generators; do not create unused scaffold.
- Migration filenames below are reserved concrete targets. Generate with Sail and use the stated timestamped filename, checking for collisions and preserving migration order. Never edit existing shared migrations.

## Phase 1: Setup (Shared Infrastructure)

**Purpose**: Confirm the real environment and protect existing work before implementation.

- [X] T001 Inspect the working tree, installed framework/Sanctum sources, Sail services and generator help; read the applicable guides and record actual versions, baseline checks and blockers in `specs/001-mobile-auth/quickstart.md` without secrets or starting unrelated services.
- [X] T002 Configure a pre-RefreshDatabase safety assertion in `backend/tests/TestCase.php` and verify `backend/phpunit.xml`/CI overrides select only PostgreSQL `testing` or `blubby_test` with no unsafe DB_URL; reject unexpected databases before any destructive setup, retain existing test conventions, and run the scaffold suite.

## Phase 2: Foundational (Blocking Prerequisites)

**Purpose**: Establish compatibility proof and the smallest shared concurrency fixture support. No user-story implementation starts before T001–T004 complete.

- [X] T003 [P] Capture the existing unwrapped `GET /api/user` fields, timestamp serialization, hidden credentials and unauthenticated denial in `backend/tests/Feature/Api/Auth/CurrentUserTest.php`; run this preservation baseline before changing its route/serialization.
- [X] T004 [P] Add the reusable, actually consumed concurrency harness in `backend/tests/Support/AuthConcurrency.php` for registration/session/admission tests: guard every child database, create committed fixtures on independent connections/processes, coordinate barriers, and clean only isolated fixtures; verify its operation against PostgreSQL without exposing credentials or creating a production helper abstraction.

**Checkpoint**: Baseline passes, disposable database is confirmed, and independent-process fixture setup is usable. Tests requiring the shared database must run serially unless given separately isolated databases.

## Phase 3: User Story 1 — Create an Account (Priority: P1)

**Goal**: Register one account and its initial named-device session atomically, with normalized identity and the approved password policy.

**Independent Test**: A valid registration returns `201` and an issued token that retrieves the new user's own identity through the existing route. Invalid input creates nothing. Force token insertion failure and concurrent duplicate registration to prove rollback and uniqueness.

### Tests for User Story 1 — write and run before implementation

- [X] T005 [P] [US1] Write and run registration HTTP cases in `backend/tests/Feature/Api/Auth/RegistrationTest.php`: required fields/UUID, 12-character minimum, each missing upper/lower/digit/symbol category, 11/12-character boundary, whitespace-not-symbol, line-break composition, 72-byte UTF-8/NUL bounds, exact confirmation, label/email/body bounds, malformed JSON, unassignable privileges and issuance/public-field/no-store contract; confirm missing behavior fails.
- [X] T006 [P] [US1] Write and run actual account/token rollback and simultaneous normalized-email registration cases in `backend/tests/Feature/Domain/Auth/RegistrationAtomicityTest.php`, using T004 and a controlled token-insert failure; assert one winning account/token and a controlled loser error rather than testing mocked delegation.
- [X] T007 [P] [US1] Write and run PostgreSQL identity/index/up-down-up cases in `backend/tests/Feature/Domain/Auth/EmailIdentityTest.php`: exact ASCII whitespace/case normalization, non-ASCII input rejection, unchanged legacy casing, duplicate/non-ASCII legacy preflight failure, named index and non-destructive rollback; confirm the missing identity constraint fails.

### Implementation for User Story 1

- [X] T008 [US1] After T005–T007 show intended failures, generate `backend/database/migrations/2026_10_29_000001_add_normalized_email_index_to_users_table.php` and add the shared canonical email normalizer/lookup predicate to `backend/app/Models/User.php`; use the exact expression/ASCII set in `specs/001-mobile-auth/data-model.md`, retain the old unique constraint, stop on legacy collisions/non-ASCII addresses, and remove only the new index on down.
- [X] T009 [US1] Implement bounded registration validation in `backend/app/Http/Requests/Api/Auth/RegisterRequest.php` and the required HTTP-boundary configuration in `backend/bootstrap/app.php`: exempt email from global Unicode trimming, preserve password bytes, use independent Unicode class checks, normalize UUID/email consistently, return native Portuguese validation errors, and enforce controlled 16-KiB/JSON failures without disabling web CSRF.
- [X] T010 [US1] Implement `backend/app/Http/Resources/Api/Auth/UserResource.php` with only the existing public identity fields and timestamp formats, no queries or credential fields, and an explicit unwrapped serialization option for current-user compatibility.
- [X] T011 [US1] Implement `backend/app/Domain/Auth/Actions/RegisterUserAction.php` using validated typed inputs and one account/token transaction; issue only `user:read`/`session:revoke` with a non-null 2,592,000-second deadline, retain hashed password casting, configure the inactivity interval in `backend/config/auth.php`, and translate only email uniqueness failures after rollback at the HTTP boundary.
- [X] T012 [US1] Implement `backend/app/Http/Controllers/Api/Auth/RegistrationController.php` and named `POST /api/register` in `backend/routes/api.php`; return the single `data` issuance envelope and `Cache-Control: no-store` only after commit, never expose credentials in errors, and introduce no extra service/repository.
- [X] T013 [US1] Rerun `backend/tests/Feature/Api/Auth/RegistrationTest.php`, `backend/tests/Feature/Domain/Auth/RegistrationAtomicityTest.php`, `backend/tests/Feature/Domain/Auth/EmailIdentityTest.php` and T003's compatibility baseline; fix introduced failures, prove migration transitions only on disposable PostgreSQL, and record actual outcomes in `specs/001-mobile-auth/quickstart.md`.

**Checkpoint**: US1 works as an internal registration demonstration. Do not publicly enable registration before US3–US5 and final verification finish.

## Phase 4: User Story 2 — Sign In and Retrieve My Identity (Priority: P1)

**Goal**: Authenticate existing accounts, issue independent device sessions, and return only the current user's compatible public identity.

**Independent Test**: Use a factory-created account without registering it, sign in, retrieve its identity, and prove wrong credentials cannot issue a token. A second session must not revoke the first.

### Tests for User Story 2 — write and run before implementation

- [X] T014 [P] [US2] Write and run `backend/tests/Feature/Api/Auth/LoginTest.php` for success/envelope/abilities, normalized identity, field/72-byte bounds, preserved literal passwords, existing weak-password compatibility, same unknown-email/wrong-password `401`, same configured-cost dummy-hash verification, optional rehash and token-write failure semantics, and independent sessions with matching labels/UUIDs; confirm intended failures.
- [X] T015 [P] [US2] Extend and run `backend/tests/Feature/Api/Auth/CurrentUserTest.php` for real Bearer-only access, missing/malformed credentials, missing ability `403`, native wildcard-token compatibility, server-derived identity despite submitted user IDs, and unchanged unwrapped public fields; confirm newly required behaviors fail before integration.

### Implementation for User Story 2

- [X] T016 [US2] Implement `backend/app/Http/Requests/Api/Auth/LoginRequest.php` with shared canonical email/UUID handling, required device label, bounded literal password and no registration composition/minimum rule; retain the input/error contract in `specs/001-mobile-auth/contracts/auth.md`.
- [X] T017 [US2] Implement `backend/app/Domain/Auth/Actions/AuthenticateUserAction.php` and the non-secret fixed dummy bcrypt digest configuration in `backend/config/auth.php`; use equivalent configured work for absent users, exact credential verification, conditional framework rehash and transactional session issuance, with no session/cookie authentication or plaintext logs.
- [X] T018 [US2] Implement login coordination in `backend/app/Http/Controllers/Api/Auth/SessionController.php` and named `POST /api/login` in `backend/routes/api.php`; preserve generic Portuguese credential errors, `200` issuance/no-store, controlled persistence-unavailable responses, and independent existing sessions.
- [X] T019 [US2] Integrate explicit current-user serialization and `user:read` ability in `backend/routes/api.php`, native ability middleware aliases in `backend/bootstrap/app.php`, and Bearer-only `sanctum.guard` configuration in `backend/config/sanctum.php`; preserve unwrapped public fields and web CSRF without adding a User policy or custom guard.
- [X] T020 [US2] Rerun `backend/tests/Feature/Api/Auth/LoginTest.php`, `backend/tests/Feature/Api/Auth/CurrentUserTest.php` and the registration slice; verify dummy-hash work factor against actual hashing configuration and record results in `specs/001-mobile-auth/quickstart.md`.

**Checkpoint**: US2 can be tested with pre-existing users, independently of US1's registration flow. It reuses US1's shared serialization/identity/issuance contract implementation; public delivery still waits for security gates.

## Phase 5: User Story 3 — Stay Signed In While Active (Priority: P1)

**Goal**: Renew only valid sessions before their per-device 30-day inactivity deadline, without reviving old credentials or regressing concurrent activity.

**Independent Test**: Issue actual tokens for fixture users, advance server time to days 29/31 and exact deadline boundaries, and prove active continuity, expiry/no revival, and independent device deadlines.

### Tests for User Story 3 — write and run before implementation

- [X] T021 [P] [US3] Write and run `backend/tests/Feature/Api/Auth/SessionInactivityTest.php` using actual issued Bearer tokens and declared timestamp precision: creation/first use, day-29/day-31 continuity, just-before/exact/after deadline, no expired/revoked/failed-auth renewal, independent sessions, null-deadline fail-closed and unavailable renewal storage; do not use `Sanctum::actingAs`.
- [X] T022 [P] [US3] Write and run `backend/tests/Feature/Domain/Auth/SessionConcurrencyTest.php` with T004 for fresh-row recheck after stale initial lookup, concurrent renewal ordering, no deadline/last-used regression, and deletion before callback acceptance; confirm incorrect/missing atomic renewal fails without substituting sequential tests.
- [X] T023 [P] [US3] Write and run `backend/tests/Feature/Domain/Auth/LegacySessionMigrationTest.php` for null deadlines derived from historical last-use/creation, expired credentials receiving no migration-time grace, preserved explicit deadlines, UTC/elapsed-time semantics and honest non-destructive down/up behavior.

### Implementation for User Story 3

- [X] T024 [US3] After T023's intended failure, generate `backend/database/migrations/2026_10_29_000002_initialize_legacy_session_deadlines.php`; fill only null deadlines from historical activity, retain initialized values on down, change no account data and verify it runs after the existing token migration.
- [X] T025 [US3] Implement `backend/app/Domain/Auth/Services/MobileSessionAuthenticator.php` with native-validity preservation, short same-token-connection transaction, fresh `lockForUpdate` row, server time sampled after locking, strict future deadline, monotonic timestamps and no missing-row recreation; return validity, not an HTTP response, and deny safely on persistence failure.
- [X] T026 [US3] Wire the native Sanctum authentication callback in `backend/app/Providers/AppServiceProvider.php` and set `expiration = null`/`last_used_at = false` in `backend/config/sanctum.php`; synchronize the authenticated token instance with committed state and add no middleware-after-auth workaround or replacement guard.
- [X] T027 [US3] Write and run `backend/tests/Feature/Domain/Auth/SessionCleanupTest.php` for expired-row pruning, account/active-session preservation, cleanup-versus-renewal interleaving and the missing daily scheduler registration; retain passing native-command baseline cases and confirm the new scheduling requirement fails before wiring it.
- [X] T028 [US3] Register daily native `sanctum:prune-expired --hours=24` in `backend/routes/console.php`, using existing scheduling facilities and live deadline predicates; keep immediate expiry denial independent of cleanup and introduce no custom cleanup command.
- [X] T029 [US3] Run `backend/tests/Feature/Api/Auth/SessionInactivityTest.php` and the three US3 domain suites with real PostgreSQL/token processing, then rerun prior auth slices; record actual boundary, migration and concurrency outcomes in `specs/001-mobile-auth/quickstart.md`.

**Checkpoint**: Active use extends only its own session. Exactly 30 inactive days denies access; already expired/deleted sessions never return. Independent token fixtures avoid requiring repeated registration for this proof.

## Phase 6: User Story 4 — Sign Out of the Current Device (Priority: P1)

**Goal**: Revoke the requesting token without changing other sessions or claiming cancellation of requests already authorized.

**Independent Test**: Issue two real tokens for one fixture user, sign out one, prove it now returns `401`, and prove the other still works.

### Tests for User Story 4 — write and run before implementation

- [X] T030 [P] [US4] Write and run `backend/tests/Feature/Api/Auth/LogoutTest.php` for `204`/empty body, post-logout denial, other-device preservation, invalid/expired/revoked logout, absent `session:revoke` ability and forged token/user IDs not selecting another session; confirm missing logout behavior fails.
- [X] T031 [P] [US4] Write and run `backend/tests/Feature/Domain/Auth/SessionRevocationConcurrencyTest.php` with T004 and the US3 renewal callback: interleave logout/renewal and prove a deleted token is never recreated, while explicitly allowing a request authorized before deletion to finish.

### Implementation for User Story 4

- [X] T032 [US4] Implement current-token deletion in `backend/app/Http/Controllers/Api/Auth/SessionController.php` and named Bearer/`session:revoke` protected `POST /api/logout` in `backend/routes/api.php`; return empty `204`, require authentication on repeats, and never select a token from submitted identifiers or revoke the account's other tokens.
- [X] T033 [US4] Rerun `backend/tests/Feature/Api/Auth/LogoutTest.php`, `backend/tests/Feature/Domain/Auth/SessionRevocationConcurrencyTest.php` and US3 session tests; record actual independent-device and race results in `specs/001-mobile-auth/quickstart.md`.

**Checkpoint**: US4 works with issued fixture tokens; registration and login are not required to exercise its own acceptance cases.

## Phase 7: User Story 5 — Limit Abusive Authentication Attempts (Priority: P1)

**Goal**: Enforce independent temporary IP/device/email budgets atomically before expensive credential work, including concurrent or manipulated-input attempts.

**Independent Test**: Exhaust one dimension at a time while varying all others, test exact retry boundaries, and race requests for one remaining slot using the real database cache. Blocked attempts issue no credentials or account writes.

### Tests for User Story 5 — write and run before implementation

- [X] T034 [P] [US5] Write and run `backend/tests/Feature/Api/Auth/AuthenticationThrottleTest.php` for every contract budget and boundary, all-admitted-attempt counting, no success reset, generic `429`/longest positive Retry-After, no denied-request reservation/window extension, recovery after expiry, preserved active sessions and no permanent lockout; explicitly use database cache and confirm absent limits fail.
- [X] T035 [P] [US5] Write and run `backend/tests/Feature/Domain/Auth/AuthenticationAdmissionConcurrencyTest.php` with T004 for one-slot simultaneous admission per dimension, overlapping/reversed dimension sets, deterministic HMAC/advisory-key encoding without overflow, collision isolation, partial-reservation rollback, same-connection persistence and bounded lock/database failure before hashing.
- [X] T036 [P] [US5] Write and run `backend/tests/Feature/Api/Auth/AuthenticationSourceTest.php` for required UUID versus device label, missing/malformed/rotated/forged IDs retaining IP/email restrictions, malformed-email fallback, canonical IPv4/IPv6, untrusted forwarded headers, temporary shared-IP effects, and controlled `503` without authentication on limiter failures.

### Implementation for User Story 5

- [X] T037 [US5] Declare the five independent initial budgets/windows in `backend/config/auth.php` and native named definitions in `backend/app/Providers/AppServiceProvider.php`: login IP 60/60s, installation 10/60s, email 5/300s; registration IP 10/3600s, installation 3/3600s; configure non-secret key namespaces and explicit database-cache selection without changing dependencies or global test cache.
- [X] T038 [US5] Implement `backend/app/Domain/Auth/Services/AuthenticationAttemptLimiter.php` using the explicit database-backed native RateLimiter and a transaction on that same connection, sorted transaction-scoped advisory locks, check-all/reserve-all admission, fixed windows, namespaced HMAC keys, two-second local lock/statement timeouts, and decision/retry results; commit before hashing/controller work and never fail open or retain raw personal identifiers.
- [X] T039 [US5] Implement `backend/app/Http/Middleware/Auth/ThrottleAuthentication.php` to derive bounded canonical framework-resolved IP/email/UUID signals, share the exact email normalizer, apply every valid independent dimension before Form Request validation, retain IP/email checks for invalid devices, and map exhausted/unavailable decisions to safe `429`/`503` responses without leaking dimensions or account existence.
- [X] T040 [US5] Wire admission middleware onto registration/login in `backend/routes/api.php` and the required ordering/source configuration in `backend/bootstrap/app.php`; do not combine it with competing stock throttle counters, trust only actual configured deployment proxies, retain the earlier body/JSON bounds and never accept forwarded headers directly from untrusted peers.
- [X] T041 [US5] Complete known authentication/admission failure rendering and secret redaction in `backend/bootstrap/app.php` and the Auth HTTP participants; verify against `backend/tests/Feature/Api/Auth/AuthenticationSourceTest.php` that no password/hash/token/device ID enters public errors/logs and that limiter failure prevents user/token creation and credential verification without swallowing unrelated exceptions.
- [X] T042 [US5] Run all three US5 suites with explicit PostgreSQL database cache and independent processes, then rerun all auth HTTP/domain cases with budgets enabled so fixtures do not accidentally exhaust unrelated dimensions; record actual concurrency, retry and outage results in `specs/001-mobile-auth/quickstart.md`.

**Checkpoint**: Every independent budget and failure path is proven. Recognized-installation limiting is best-effort: replacing a valid UUID can change its device bucket, but never resets a still-applicable IP/email restriction. No hardware attestation is claimed.

## Phase 8: Polish & Cross-Cutting Concerns

**Purpose**: Reconcile implemented behavior and prove the complete feature before public delivery. No unrelated cleanup or speculative optimization.

- [X] T043 Update `backend/docs/api/README.md`, `backend/docs/security/README.md`, `backend/docs/database/README.md`, `backend/docs/testing/README.md`, `backend/docs/architecture/overview.md` and the affected limits in `docs/flows/authentication-entry.md` to distinguish implemented backend behavior from still-unimplemented mobile/social/recovery work; document Auth ownership, internal entry contracts, transaction owners, the justified Action/service split, operational defaults and device limitations without claiming a new GoF pattern.
- [X] T044 Update only stale authentication implementation-status text in `backend/.ai/guidelines/project-architecture.md`, then follow `backend/.ai/skills/boost-maintenance/SKILL.md` to regenerate `backend/AGENTS.md` and other configured outputs through Sail twice; prove stable regeneration, preserved native guidance/official skills/imports/MCP configuration, and no changes to unrelated architecture or discovery settings.
- [X] T045 Execute focused auth suites and the complete backend suite, then Sail Pint on changed PHP (explicit-path fallback if Git is unavailable in-container), route/schedule inspection and whitespace checks; record exact results in `specs/001-mobile-auth/quickstart.md`, rerun affected checks after any correction, and do not run unrelated mobile/build/Biome checks unless their inputs changed.
- [X] T046 Validate rollout prerequisites against `specs/001-mobile-auth/data-model.md`, `specs/001-mobile-auth/contracts/auth.md` and `backend/docs/operations/README.md`: safe legacy preflight/recovery, no mixed-version expiry rollout, HTTPS/debug/proxy settings, shared-cache connection, scheduler availability and configured-cost dummy hash; record evidence or explicit rollout blockers in `specs/001-mobile-auth/quickstart.md` without deploying or modifying infrastructure.
- [X] T047 Review the connected implementation using `backend/.ai/skills/backend-review/SKILL.md` and a cavecrew reviewer, map FR-001–FR-015 and SC-001–SC-009 to actual proof in `specs/001-mobile-auth/quickstart.md`, correct introduced findings with regression checks and update `specs/001-mobile-auth/tasks.md` honestly; do not mark unrun production checks or unfinished tasks complete.

**Final checkpoint**: All five P1 stories and required checks pass, remaining rollout blockers are explicit, and backend implementation status is accurate. No automatic commit, push, deployment, or mobile integration is included.

## Dependencies & Execution Order

### Phase dependencies

Setup T001–T002 precedes foundation T003–T004. Foundation blocks all story implementation. The default order below matches the plan's development sequence and serializes shared wiring files:

```text
Setup -> Foundation -> US1 -> US2 -> US3 -> US4 -> US5 -> Final validation
```

This is an execution/dependency graph, not a claim that every story needs the registration endpoint to be tested. US2 can use factory accounts; US3/US4 can use real tokens issued for fixture users. US5 cases create the minimal existing users/inputs needed to test each dimension.

### Specific dependency edges

- T003 and T004 depend on T002 and can be authored independently. Run shared-database checks serially or with isolated databases.
- US1 red wave T005–T007 depends on foundation. T008–T012 follow genuine failures; T013 closes US1. T011 owns issuance semantics reused by later work, not a new generic token service.
- US2 T014/T015 follows T013; T016–T019 follow the relevant red tests. T019 preserves T003's compatibility baseline and introduces ability/cookie assertions; T020 closes US2.
- US3 T021–T023 follows T020. T024 follows T023; T025/T026 follow T021/T022 and T024. T027 runs after that integration and before T028's schedule change. T029 closes US3.
- US4 T030/T031 follows T029; T032 follows their intended failures; T033 closes US4.
- US5 T034–T036 follows T033. T037 follows the red wave; T038 follows T035/T037; T039 follows T034/T036/T038; T040/T041 complete HTTP integration; T042 closes US5.
- T043–T047 follows T042. T044 must finish regeneration before final checks/review. A defect found at any gate returns to its responsible test/implementation task and then reruns affected checks.

### Within each story

Write and run the focused test first; fix test/environment mistakes before treating failure as evidence. Implement only the responsible behavior after the intended failure. Run its proof, then the relevant existing cases. Broader reruns are justified at story integration/final gates or after actual changes, not as repeated busywork.

## Parallel Execution Examples

These are **test-authoring waves**, not permission to implement before red tests or to run destructive tests against the same database simultaneously. No production-file parallelism is claimed because routes, configuration, providers and controllers are shared.

| Story | Tasks safe to author in parallel | Prerequisite | Independent files |
| --- | --- | --- | --- |
| US1 | T005, T006, T007 | T001–T004 complete | RegistrationTest, RegistrationAtomicityTest, EmailIdentityTest |
| US2 | T014, T015 | T013 complete | LoginTest, CurrentUserTest |
| US3 | T021, T022, T023 | T020 complete | SessionInactivityTest, SessionConcurrencyTest, LegacySessionMigrationTest |
| US4 | T030, T031 | T029 complete | LogoutTest, SessionRevocationConcurrencyTest |
| US5 | T034, T035, T036 | T033 complete | AuthenticationThrottleTest, AuthenticationAdmissionConcurrencyTest, AuthenticationSourceTest |

T003/T004 are another independent authoring pair after T002. Concurrent test execution requires separately isolated PostgreSQL databases; real race tests deliberately coordinate their own shared fixtures. Use cavecrew agents for qualifying investigation, bounded edits and review; never share writes to the same source file between parallel workers.

## Requirement Coverage

| Requirement / outcome | Principal tasks |
| --- | --- |
| FR-001 / SC-001 registration | T005, T009–T013 |
| FR-002 / SC-005 identity and concurrent uniqueness | T006–T008, T013, T016–T020 |
| FR-003 / SC-009 password policy and compatibility | T005, T009, T014, T016–T020 |
| FR-004 atomic account/token creation | T006, T011–T013 |
| FR-005 generic credential verification | T014, T016–T020 |
| FR-006 independent issued sessions | T011–T014, T017–T020 |
| FR-007–FR-009 / SC-003–SC-004 inactivity and session isolation | T021–T029, T030–T033 |
| FR-010 / SC-002 public identity and denials | T003, T010, T014–T015, T019–T020 |
| FR-011 revocation | T030–T033 |
| FR-012 / SC-007 credential privacy | T005, T010–T015, T018–T020, T036, T041, T045–T047 |
| FR-013 / SC-006 independent exact admission | T034–T042 |
| FR-014 bounded/allowlisted inputs | T005, T009, T014, T016, T036, T039–T041 |
| FR-015 / SC-008 safe obsolete-session cleanup | T023–T029 |

## Implementation Strategy

### Smallest internal increment

Complete setup/foundation and US1, then demonstrate registration and current-user retrieval using an actual token. This is an internal testable slice, not a production-ready authentication release: rolling sessions, logout and attempt protection are all mandatory P1 scope.

### Incremental delivery

Add US2, US3, US4, and US5 in the listed order, validating each independently without breaking prior behavior. Complete the final documentation, regeneration, checks and review gates before describing the feature as ready for public rollout. Actual deployment needs a separate authorized action; this file does not request or perform it.

### Stop conditions and limits

All required behavior, contracts, meaningful security/concurrency proof and relevant existing checks must be complete. Do not add recovery, social providers, session dashboards, attestation, generic abstractions, dependencies or infrastructure. If environment/preflight/production evidence is unavailable, finish unaffected work and report the precise blocker; never replace missing proof with a checked box. Commits are not automatic; follow the project commit skill if a commit is later authorized.
