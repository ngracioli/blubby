# Backend documentation

These documents define backend conventions: Laravel-first domain-oriented organization with conditional Actions/services, conventional Eloquent Models and one deployable. The backend is Laravel 13 with PostgreSQL, Docker Compose through Laravel Sail, Laravel Boost for AI tooling, and Laravel Pint for PHP formatting. Biome formats and lints supported JavaScript, CSS, and JSON files; it does not format PHP.

| Topic | Guide |
| --- | --- |
| Architecture and responsibilities | [Architecture](architecture/overview.md) |
| Identifier and file conventions | [Naming](architecture/naming.md) |
| Pattern selection and guardrails | [Design patterns](architecture/design-patterns.md) |
| Adopted decision, alternatives, research and scenarios | [Architecture evidence](architecture/decision.md) |
| PHP and Laravel implementation | [Conventions](architecture/conventions.md) |
| Task routing, skills and Boost regeneration | [Agent workflow](agent-workflow.md) |
| Financial behavior | [Domain](domain/README.md) |
| Persistence | [Database](database/README.md) |
| HTTP contracts | [API](api/README.md) |
| Authentication and isolation | [Security](security/README.md) |
| Verification | [Testing](testing/README.md) |
| Deployment and recovery | [Operations](operations/README.md) |

[The MVP](../../mvp.md) defines product scope. Pattern examples are conditional design examples, not additional product requirements. Shared API, money, date, and session decisions must agree with the mobile documentation.

Start backend behavior/refactor work with architecture, naming and relevant patterns. Then consult only affected topic owners above. Architecture is adopted; proposed product/infrastructure choices remain explicitly proposed. Generated Boost instructions provide framework knowledge; these documents own project-specific decisions.
