# Backend testing

Owns domain tests, application integration tests, persistence checks, API contracts, and architecture checks.

Prioritize the MVP acceptance criteria: cross-user isolation, transfer conservation, installment allocation, closing-day boundaries, full bill payment without duplicate expense, recurrence cancellation, and projection carryover.

Test financial mutations for retries, concurrent updates, and rollback after partial failure. Use a representative real database for behavior that depends on constraints or transaction semantics.

Verify adopted patterns through their contracts and actual variations. Do not add a pattern merely to enable mocks. No test framework, database fixture, or architecture verification tool has been installed at this stage.
