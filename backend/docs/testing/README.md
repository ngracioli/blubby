# Backend testing

Owns domain tests, application integration tests, persistence checks, API contracts, and architecture checks.

Prioritize the MVP acceptance criteria: cross-user isolation, transfer conservation, installment allocation, closing-day boundaries, full bill payment without duplicate expense, recurrence cancellation, and projection carryover.

Test financial mutations for retries, concurrent updates, and rollback after partial failure. Use a representative real database for behavior that depends on constraints or transaction semantics.

Run `php artisan test` for the PHPUnit suite. GitHub Actions runs Composer validation, Pint, Biome, the frontend build, PostgreSQL migrations, and PHPUnit on pull requests and pushes to `main`; see [backend CI](../../../.github/workflows/backend-ci.yml).

The Laravel scaffold currently has example tests only. Add domain and integration tests as product behavior is implemented. Verify adopted patterns through their contracts and actual variations. Do not add a pattern merely to enable mocks.
