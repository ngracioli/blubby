# Backend architecture

## Status

Documentation conventions are established here. A Spring Boot modular monolith is the proposed baseline; stack selection, module boundaries, database, and hosting are not finalized.

## Proposed responsibilities

- Organize modules by business capability rather than global controller, service, and repository folders.
- HTTP entry points validate transport input and delegate application behavior.
- Application operations coordinate authorization, domain behavior, and transaction boundaries.
- Domain behavior owns financial invariants and calculations.
- Persistence and external integrations own storage and protocol details.

Candidate capabilities include accounts, entries, cards, recurrences, planning, and identity. Validate their dependencies before defining final packages. Modules expose deliberate interfaces; callers must not reach into another module's persistence internals.

Start with one deployable application. Additional layers, events, interfaces, and deployable services require concrete needs. Spring Modulith is an optional verification tool, not a prerequisite.

Follow [naming conventions](naming.md) and [pattern guardrails](design-patterns.md). Do not place all strategies or factories in global pattern folders; keep them with their owning capability.

## References

- [Spring Boot code structure](https://docs.spring.io/spring-boot/reference/using/structuring-your-code.html).
- [Spring Modulith](https://spring.io/projects/spring-modulith/).
