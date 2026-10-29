# Implementation Plan: Mobile Registration and Sign-In

**Branch**: `main` | **Date**: 2026-10-29 | **Spec**: [spec.md](spec.md)

**Input**: Feature specification from `specs/001-mobile-auth/spec.md`.

The setup script reports feature identifier `001-mobile-auth` as `BRANCH`; `git branch --show-current` confirms the actual Git branch is `main`. No branch was created or switched.

## Summary

Deliver backend email/password registration, login, current-user lookup, and current-session logout with Sanctum Bearer tokens and Eloquent/PostgreSQL. Each session expires after 30 days of inactivity, renewed only by valid authentication before its deadline. Require 12-character passwords with upper/lowercase, digit, and non-whitespace symbol. Protect authentication with independent IP, installation, and login-email limits, including simultaneous attempts.

Reuse the existing User, token schema, database cache, framework validation/hashing, and native Sanctum extension points. No mobile implementation, social providers, refresh-token system, financial behavior, or dependency changes. [Research](research.md) resolves the decisions; [contract](contracts/auth.md) defines the HTTP interface.

## Technical Context

**Language/Version**: PHP 8.5 selected by Sail; Composer PHP constraint remains unchanged.

**Primary Dependencies**: Laravel 13.39.0, Sanctum 4.3.3, existing Sail 1.69.0; Boost 2.10.3/MCP 1.1.2 are development tooling, not authentication dependencies.

**Storage**: PostgreSQL 18, existing `users`, `personal_access_tokens`, `cache`, and `cache_locks`. Add normalized email expression index; initialize legacy null token deadlines from historical activity. No device registry, attempt ledger, or new business table.

**Testing**: PHPUnit 12.5.42 through Sail, PostgreSQL disposable database, explicit shared database cache for limit tests, independent processes/connections for concurrency, Pint 1.32.1 after PHP implementation.

**Target Platform**: Existing Linux/Sail backend serving native iOS/Android clients over HTTPS outside local development. No SPA cookies or frontend changes.

**Project Type**: Existing Laravel web service inside a mobile/backend monorepo.

**Performance Goals**: Bounded indexed identity/token lookups; no external calls in authentication. Admission transactions exclude password hashing and controller work and have two-second lock/statement timeouts. Do not invent load/SLA promises before measuring deployed bcrypt cost and capacity.

**Constraints**: 30 elapsed days, no creation-based logout, no revival, independent device sessions, exact concurrent attempt budgets, compatible current-user response, atomic registration, no plaintext credential storage/logging, no new dependency/infrastructure. Device signal is a rotatable installation UUID, not verified hardware identity.

**Scale/Scope**: Four HTTP operations, one Auth owner, existing persistence/cache and scheduler. Initial operating budgets are explicit in the contract; no unsupported user-count estimate.

## Constitution Check

*Gate evaluated before research and again after design.*

| Principle / workflow | Before research | After design | Evidence |
| --- | --- | --- | --- |
| I. Explicit scope | Pass | Pass | Spec and user amendments govern the four operations, password policy, and inactivity rule. Operational defaults are labelled; no provider/recovery work. |
| II. Financial correctness | Pass | Pass | No financial data or behavior changes. Account/token registration is atomic. |
| III. Privacy and authorization | Pass | Pass | Bearer owner, explicit abilities/public fields, independent limits, revoked/expired denial, trusted proxy handling, and safe error/log contracts. |
| IV. Approved visuals/accessibility | Pass | Pass | No UI work; Figma/mobile integration remains separate. |
| V. Ownership and simplicity | Pass | Pass | Auth-owned Actions/services have actual transactions/shared rules; direct Eloquent, no speculative Repository/DTO/guard/event hierarchy. |
| Test-first and evidence | Pass | Pass | Quickstart requires failing observable tests before implementation, PostgreSQL races, and honest reporting. Planning checks are not runtime proof. |
| Compatibility and recovery | Pass | Pass | Existing current-user fields preserved; forward-only additive index and historical token-deadline initialization, no auto-merge or deletion. |

No constitution exception is needed. The constitution's existing ratification-date TODO is unrelated metadata and is not changed by this feature. Runtime gates remain delivery requirements, not claims of implementation completion.

## Project Structure

### Documentation (this feature)

```text
specs/001-mobile-auth/
├── spec.md
├── checklists/requirements.md
├── plan.md
├── research.md
├── data-model.md
├── quickstart.md
└── contracts/auth.md
```

`tasks.md` belongs to the subsequent `$speckit-tasks` phase and is not generated here.

### Source Code (repository root)

Planned locations only; do not scaffold unused files during planning:

```text
backend/
├── app/
│   ├── Domain/Auth/
│   │   ├── Actions/{RegisterUserAction,AuthenticateUserAction}.php
│   │   └── Services/{MobileSessionAuthenticator,AuthenticationAttemptLimiter}.php
│   ├── Http/
│   │   ├── Controllers/Api/Auth/{RegistrationController,SessionController}.php
│   │   ├── Requests/Api/Auth/{RegisterRequest,LoginRequest}.php
│   │   ├── Resources/Api/Auth/UserResource.php
│   │   └── Middleware/Auth/ThrottleAuthentication.php
│   ├── Models/User.php
│   └── Providers/AppServiceProvider.php
├── bootstrap/app.php
├── config/{auth,sanctum}.php
├── database/migrations/  # additive normalized-email index and legacy deadline initialization
├── routes/{api,console}.php
├── docs/{api,security,database,testing}/README.md
└── tests/Feature/
    ├── Api/Auth/          # HTTP, validation, identity, issued-token, abilities, limits
    └── Domain/Auth/       # transactions, concurrent admission/renewal/revocation/cleanup
```

**Structure Decision**: Reuse existing framework roots and the Auth owner throughout. Registration and credential authentication Actions own multi-write/failure semantics. The two services encapsulate nontrivial atomic admission and native-guard renewal, each with real callers and proof. Controllers coordinate HTTP, Form Requests normalize/validate, UserResource allowlists the existing public fields. Preserve the existing current-user route's unwrapped response while enforcing Bearer validity and abilities. No new User policy is needed just to return the authenticated principal. Actions accept explicit typed/validated inputs and return the user/issued token, never a Form Request or HTTP response. Admission returns a decision/retry interval; HTTP middleware maps it to the contract. The native callback passes only the token and native validity to the renewal service. These are internal Auth operations; future CLI/job callers must establish the same validation and admission contracts before reuse.

## Phase 0: Research Outcome

Completed in [research.md](research.md): installed APIs, native callback timing, concurrency-safe rolling deadline, strict independent admission using native counters plus PostgreSQL advisory locks, installation UUID limitations, trusted IPs, bcrypt byte limits/independent Unicode composition checks, global email-trimming exclusion, normalized uniqueness, cleanup, and test isolation. No unresolved research item remains.

## Phase 1: Design Outcome

- [data-model.md](data-model.md): existing entities, validation, state transitions, constraints, and migration recovery.
- [contracts/auth.md](contracts/auth.md): fields, responses, transport, abilities, explicit limit budgets/windows, identity signals, and errors.
- [quickstart.md](quickstart.md): test-first implementation gate and runnable post-implementation checks, including real-token and concurrent PostgreSQL cases.

## Delivery Order and Acceptance Gates

1. Generate tests with Sail after inspecting Artisan help. Confirm database isolation; obtain a failing registration or login case caused by missing behavior, not a broken environment. If meaningful failure cannot run, stop implementation and report the obstacle.
2. Add forward schema changes and Auth-owned registration/login with transactional issuance, normalized email identity, explicit fields, and strong-password rules. Prove rollback, duplicate races, and missing-user error parity.
3. Integrate native Sanctum callback, Bearer-only configuration, abilities, current-session logout, and native cleanup schedule. Prove exact inactivity boundaries, day-29/day-31 continuity, per-session independence, stale/revoked races, and legacy token behavior.
4. Add independent admission definitions/middleware/service with explicit database cache and transaction locks; prove every budget boundary, invalid/rotated device handling, forwarded-IP handling, simultaneous admission, timeout failure, and release after windows. Authentication must not ship publicly before this gate passes.
5. Update actual API/security/database/testing documentation from planned to implemented state, run focused/full suites and Pint, then review against FR-001 through FR-015 and SC-001 through SC-009. Record actual results and deployment prerequisites.

These are ordering gates for task generation, not permission to expand scope or publish incomplete authentication.

## Pattern and Complexity Decision

Reuse Laravel middleware/validation/Resources, Sanctum callbacks, Eloquent, transactions, and native rate-counter facilities. Single-use-case Actions are justified by account/token consistency and credential/issuance branching. Dedicated atomic admission and session-authentication services are justified by shared security and concurrency rules. No new GoF pattern is adopted; simpler framework composition satisfies this feature. PostgreSQL-specific locks are necessary for the spec's strict concurrent admission, without introducing a replacement cache or persistence abstraction.
