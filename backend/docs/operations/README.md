# Operations

Owns environment configuration, deployment, migrations, scheduled work, observability, backups, and recovery.

GitHub Actions runs CI for backend changes. Hosting and deployment topology remain undecided, so no automatic deployment is configured. Before deployment, define secret injection, health checks, migration execution, rollback limitations, and database restoration procedures.

Automatic recurrence posting needs documented scheduling, timezone handling, duplicate prevention, and recovery after downtime. Do not assume an in-memory timer provides reliable execution.

Define useful operational logs and metrics without sensitive payloads. A backup policy is incomplete until restoration has been exercised and its recovery expectations recorded.

## Current drivers and asynchronous work

Sessions, cache and queues use PostgreSQL-backed Laravel drivers; storage is local and mail is logged. Database queue workers still need an explicit process: a row in `jobs` is not execution. For future asynchronous features, define worker supervision, failed-job inspection, bounded retries/backoff, timeout shorter than `retry_after`, idempotency, deploy-time worker restart and observable failure recovery. Scheduling needs a real scheduler process. Hosting, supervision and delivery guarantees remain undecided; do not introduce Redis, Horizon or cloud services as hidden prerequisites.

Do not hold database transactions while making remote calls. Use after-commit dispatch when work depends on committed rows, and document its crash-window limitation. Replay-safe handlers and database constraints matter even with uniqueness/overlap middleware. Match queue/storage/lock capabilities to the installed drivers before selecting middleware.

## Tooling maintenance

[Agent workflow and Boost maintenance](../agent-workflow.md) owns guideline sources, generated files, MCP discovery and safe `boost:update` procedures. Tooling updates must not run migrations, reset databases, delete volumes or install new dependencies as incidental steps.

## Authentication rollout prerequisites

Run the scheduler (`sanctum:prune-expired --hours=24` is registered daily); serve over HTTPS with `APP_DEBUG=false`; configure trusted proxies only for real deployment proxies; point `AUTH_DUMMY_PASSWORD_HASH` at a bcrypt digest of the production cost; use the shared PostgreSQL `cache` table for attempt counters. Preflight normalized email collisions and non-ASCII legacy emails before migrating, and avoid a mixed-version fleet with incompatible token-expiry code.
