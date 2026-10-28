# Database

Owns the entity relationship model, constraints, indexes, financial representation, transactions, and migrations. PostgreSQL is the selected database; the local Sail environment uses PostgreSQL 18.

Before implementation, specify money precision, currency assumptions, rounding, date-only values, instants, and recurrence timezone behavior. Select exact monetary representations rather than floating-point storage.

Define atomic operations for transfers, bill payments, recurrence posting, and reversals. Document how retries and concurrent requests avoid duplicate effects. Ownership and referential constraints must agree with the authorization model.

Each migration needs a compatibility and recovery strategy appropriate to its data changes. Backup and restore responsibilities belong in [operations](../operations/README.md).
