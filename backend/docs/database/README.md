# Database

Owns the entity relationship model, constraints, indexes, financial representation, transactions, and migrations. PostgreSQL is the selected database; the local Sail environment uses PostgreSQL 18.

Before implementation, specify money precision, currency assumptions, rounding, date-only values, instants, and recurrence timezone behavior. Select exact monetary representations rather than floating-point storage.

Define atomic operations for transfers, bill payments, recurrence posting, and reversals. Document how retries and concurrent requests avoid duplicate effects. Ownership and referential constraints must agree with the authorization model.

Each migration needs a compatibility and recovery strategy appropriate to its data changes. Backup and restore responsibilities belong in [operations](../operations/README.md).

## PostgreSQL 18 rules

- Use primary keys, `NOT NULL`, foreign keys, unique constraints and row-local `CHECK` constraints to protect persisted invariants. Validation improves errors but cannot serialize concurrent writers. `CHECK` does not establish cross-row or cross-table invariants; `NULL` may satisfy a check unless separately forbidden. PostgreSQL has no MySQL-style unsigned integer type: express nonnegative requirements with a check.
- Primary/unique constraints create supporting indexes. Referencing foreign-key columns are not automatically indexed: add an index when joins, ownership filtering or parent changes justify it, not blindly on every column. Define delete behavior explicitly; cascading financial history deletion requires a product retention decision.
- Scope uniqueness correctly (for example actor + replay key). PostgreSQL treats nulls as distinct by default; choose `NULLS NOT DISTINCT` or a suitable partial index only when the business rule requires it. Case-sensitive text uniqueness does not implement case-insensitive email identity; define normalization and an appropriate index together before authentication work.
- Store money as agreed integer minor units or exact `numeric(precision, scale)`, never float/double. Bound accepted values and precision; avoid implicit PHP float conversions of numeric strings. Do not choose a currency, rounding or JSON money format from an example. Use `date` for a calendar date and timezone-aware instants for moments; document timezone conversion semantics. Use `jsonb` for genuinely flexible data, not as a substitute for relational keys and constraints. Keep current scaffold key types unless a feature justifies changing them.
- Use bindings for values. Allowlist sort columns/directions and dynamic identifiers; bindings do not secure raw identifiers. Use `exists()`/aggregates and SQL filtering rather than loading every model. Fetch only required columns while retaining relationship keys.
- Index actual filter/join/order shapes. A future `(user_id, occurred_at, id)` index is justified only by its real query. Prefer ordinary B-tree indexes first; expression, partial, GIN and covering indexes need a measured query. Account for write/storage cost and current statistics. Use `EXPLAIN`; `EXPLAIN ANALYZE` executes the statement, so run risky plans only against disposable data. A sequential scan can be correct for a small table.
- Bound page size. `paginate()` provides totals with a count query; `simplePaginate()` avoids it when totals are not needed. Cursor pagination helps large changing datasets when the contract supports it and ordering is deterministic, non-null and unique (include an ID tie-breaker). Test filters and equal timestamps across page boundaries. Do not replace an existing pagination contract incidentally.

## Atomicity and concurrency

The [outer application operation](../architecture/overview.md#transactions-events-and-execution) owns `DB::transaction()`. Read Committed, PostgreSQL's default, does not prevent every lost update or multi-row invariant violation. For read-modify-write, use `lockForUpdate()` inside the transaction, a guarded atomic update with affected-row checking, or an explicitly designed isolation strategy. Lock multiple rows in stable key order. Test independent connections/transactions; a single sequential test does not prove race safety.

Unique constraints arbitrate duplicate claims. Handle the identified constraint violation at the transaction boundary; a PostgreSQL transaction is aborted after a statement error unless rolled back to a savepoint. Do not catch a duplicate insert and continue querying inside the same failed transaction. Laravel's deadlock retry support is available, but callbacks may rerun: keep irreversible I/O outside them and prove retry safety. Serializable transactions require retry handling for serialization failures; do not switch isolation globally as a shortcut.

Persist all required financial effects and replay identity atomically. After-commit side effects and delivery limits are covered by the architecture guide. Database cache/queue storage is not a financial ledger or an exactly-once execution guarantee.

## Migrations and fixtures

Use Laravel's schema builder for ordinary DDL. PostgreSQL-specific SQL is acceptable for a required check/expression/index feature; name it, bind where possible and test against PostgreSQL 18. Never edit an already-shared migration to change deployed schema; add a forward migration. Keep unrelated backfills separate and bounded. A destructive rollback cannot recover deleted data: document recovery honestly.

For a live growing table, plan compatible expand/backfill/contract changes and lock duration. `CREATE INDEX CONCURRENTLY` cannot run inside a transaction block; a Laravel migration using it must deliberately opt out of migration transactions, handle invalid indexes after failure and specify cleanup/retry. It is unnecessary ceremony for a new empty table. Never run `migrate:fresh`, rollback or volume deletion against shared/local user data just to validate documentation. Exercise schema changes on a disposable PostgreSQL database and test constraints, defaults, factory states and down/forward recovery where meaningful.

References: PostgreSQL 18 [constraints](https://www.postgresql.org/docs/18/ddl-constraints.html), [isolation](https://www.postgresql.org/docs/18/transaction-iso.html), [indexes](https://www.postgresql.org/docs/18/indexes.html), [numeric types](https://www.postgresql.org/docs/18/datatype-numeric.html), [concurrent indexes](https://www.postgresql.org/docs/18/sql-createindex.html), [EXPLAIN](https://www.postgresql.org/docs/18/using-explain.html). These rules deliberately avoid MySQL-specific assumptions.
