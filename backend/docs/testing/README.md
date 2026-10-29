# Backend testing

Owns domain tests, application integration tests, persistence checks, API contracts, and architecture checks.

Prioritize the MVP acceptance criteria: cross-user isolation, transfer conservation, installment allocation, closing-day boundaries, full bill payment without duplicate expense, recurrence cancellation, and projection carryover.

Test financial mutations for retries, concurrent updates, and rollback after partial failure. Use a representative real database for behavior that depends on constraints or transaction semantics.

Run `./vendor/bin/sail artisan test --compact` from `backend/` for the PHPUnit suite. GitHub Actions runs Composer validation, Pint, Biome, the frontend build, PostgreSQL migrations, and PHPUnit on pull requests and pushes to `main`; see [backend CI](../../../.github/workflows/backend-ci.yml).

The Laravel scaffold currently has example tests only. Add domain and integration tests as product behavior is implemented. Verify adopted patterns through their contracts and actual variations. Do not add a pattern merely to enable mocks.

## Choosing proof

The project placement and behavioral grouping below take precedence over generic mirrored-class paths and one-test-class-per-class advice in Boost's `testing-best-practices/rules/naming.md`. Preserve that official source; the canonical project guideline records this explicit exception.

- Bugs and behavior changes: write the smallest observable test first. Run it and confirm a failure caused by the missing/wrong behavior, then change implementation. If that proof cannot run, explain the obstacle and ask how to proceed before editing behavior. Pure refactors run existing tests before and after, with no artificial failing test. Documentation/configuration-only changes use focused validation rather than invented behavior tests.
- Put HTTP contracts, validation, authorization and serialization in `tests/Feature/Api/<Domain>/`. Put non-HTTP database-backed domain operations and cross-domain integration in `tests/Feature/Domain/<OwningDomain>/`. Use `tests/Unit/Domain/<Domain>/` for pure rules/calculations without the Laravel application. Test a shared rule at its owner, and each caller's orchestration at the caller; do not duplicate a complete rule suite or require unit tests for pass-through code. Name tests for behavior, not private methods or file counts. Generate PHPUnit tests with Sail Artisan; do not replace PHPUnit with Pest.
- Use factories with intentional states and `RefreshDatabase` on the disposable testing database for persistence tests. `phpunit.xml` selects database `testing` with array cache/session and synchronous queues; local Sail provisions that test database. CI explicitly selects its own PostgreSQL database. Confirm the connection before destructive test setup. Do not change `.env` to point tests at development data.
- Auth concurrency tests (`tests/Feature/Domain/Auth`, `DatabaseMigrations` with committed fixtures) fork real processes through `tests/Support/AuthConcurrency.php`; `TestCase` refuses any database other than `testing`/`blubby_test`. Limiter tests select the explicit `database` cache store although `CACHE_STORE=array`.
- Use PostgreSQL 18 for constraints, locks, numeric semantics, JSONB and PostgreSQL SQL. SQLite cannot prove these contracts. Concurrent mutation tests need separate connections/processes with controlled interleaving and committed setup visible to them; a single test transaction is insufficient. Assert final financial state, rollback and duplicate prevention, not merely returned status.
- Use real Eloquent/database integration for persistence. Fake external HTTP, mail, notifications, queue/event boundaries with Laravel facilities when proving dispatch decisions. Also test the actual Job/listener behavior and failure/retry path; `Queue::fake()` alone cannot prove execution. Avoid blanket event fakes that disable model/factory behavior under test.
- For relevant list endpoints, compare query growth with small and larger fixtures or prohibit unintended lazy loading in that test. Avoid brittle exact query counts unless the count is the contract. Test pagination with equal sort values and authorization filters.
- Keep pattern tests focused: all real Strategy variants, Adapter error translation, Decorator contract preservation, State transitions and ordered Pipeline short-circuiting. No tests that only assert a class implements an interface.

## Local commands and scope

From `backend/` with Sail running:

```sh
./vendor/bin/sail artisan make:test Api/Transfers/TransferFundsTest --phpunit --no-interaction
./vendor/bin/sail artisan test --compact tests/Feature/Api/Transfers/TransferFundsTest.php
./vendor/bin/sail artisan test --compact
./vendor/bin/sail bin pint --dirty --format agent
```

For non-HTTP transfer integration, use `make:test Domain/Transfers/TransferFundsTest --phpunit --no-interaction`; pure calculation tests use `make:test Domain/Transactions/InstallmentAllocatorTest --unit --phpunit --no-interaction`, all prefixed with `./vendor/bin/sail artisan`. Match the class namespace to its test path. Test cross-domain collaboration through the owning use case, including rollback of collaborators and invariant-preserving calls.

The feature path above is hypothetical; do not run it until that test exists. Use `--unit` to generate pure tests; do not include an extra `Feature/` prefix in the generator name. Run the narrow relevant test during iteration and the relevant existing suite before completion. Pint applies only when PHP changes. Biome applies only to configured JavaScript/CSS/JSON paths, not PHP or Markdown; inspect `biome.json` before choosing a command. Asset builds, migrations and full CI checks are necessary when their inputs change, not mechanically for prose.

In this monorepo Sail mounts `backend/`, while Git metadata lives at repository root. Pint `--dirty` can therefore report that Git is unavailable inside the container. In that case, identify changed PHP paths from the host diff and pass those explicit paths to Sail Pint (for example `./vendor/bin/sail bin pint config/boost.php --format agent`). Do not mount the whole repository or change infrastructure just for file selection.

Pao 1.1.5 is installed output/diagnostic tooling, not a new test framework or Artisan test command. It detects supported agent execution; keep its normal output. For diagnosis only, `./vendor/bin/sail exec -T -e PAO_FORCE=1 laravel.test php artisan test --compact` forces agent output, and `PAO_DISABLE=1` through the same `exec -T -e` form restores ordinary diagnostics. Check actual output and exit status; do not claim Pao ran merely because it is installed. Use Pail/read-log-entries/last-error for relevant failures without leaking sensitive logs.

Report exact executed commands and outcomes separately from recommended checks. Record environment blockers; never describe an unrun PostgreSQL/concurrency test as passed. The current example suite proves scaffold behavior only.
