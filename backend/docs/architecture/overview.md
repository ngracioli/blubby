# Backend architecture

## Status

Documentation conventions are established here. A Laravel application is the proposed baseline; module boundaries, database, and hosting are not finalized.

## Proposed responsibilities

- Organize modules by business capability rather than global controller, service, and repository folders.
- HTTP entry points validate transport input and delegate application behavior.
- Application operations coordinate authorization, domain behavior, and transaction boundaries.
- Domain behavior owns financial invariants and calculations.
- Persistence and external integrations own storage and protocol details.

Candidate capabilities include accounts, entries, cards, recurrences, planning, and identity. Validate their dependencies before defining final packages. Modules expose deliberate interfaces; callers must not reach into another module's persistence internals.

Start with one deployable application. Additional layers, events, interfaces, and deployable services require concrete needs.

Follow [naming conventions](naming.md) and [pattern guardrails](design-patterns.md). Do not place all strategies or factories in global pattern folders; keep them with their owning capability.

## References

- [Laravel documentation](https://laravel.com/docs).
