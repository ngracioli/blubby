# Operations

Owns environment configuration, deployment, migrations, scheduled work, observability, backups, and recovery.

Hosting and deployment topology remain undecided. Before deployment, define secret injection, health checks, migration execution, rollback limitations, and database restoration procedures.

Automatic recurrence posting needs documented scheduling, timezone handling, duplicate prevention, and recovery after downtime. Do not assume an in-memory timer provides reliable execution.

Define useful operational logs and metrics without sensitive payloads. A backup policy is incomplete until restoration has been exercised and its recovery expectations recorded.
