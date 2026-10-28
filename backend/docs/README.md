# Backend documentation

These documents define documentation structure and development conventions. Laravel and PostgreSQL remain proposed choices, not implemented infrastructure.

| Topic | Guide |
| --- | --- |
| Architecture and responsibilities | [Architecture](architecture/overview.md) |
| Identifier and file conventions | [Naming](architecture/naming.md) |
| Pattern selection and guardrails | [Design patterns](architecture/design-patterns.md) |
| Financial behavior | [Domain](domain/README.md) |
| Persistence | [Database](database/README.md) |
| HTTP contracts | [API](api/README.md) |
| Authentication and isolation | [Security](security/README.md) |
| Verification | [Testing](testing/README.md) |
| Deployment and recovery | [Operations](operations/README.md) |

[The MVP](../../mvp.md) defines product scope. Pattern examples are conditional design examples, not additional product requirements. Shared API, money, date, and session decisions must agree with the mobile documentation.
