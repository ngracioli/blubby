# Project instructions

- All project documentation must be written in English. Preserve intentional Portuguese product UI examples and original external asset/Figma names and links.
- Write code identifiers in English.
- Before preparing or executing any commit, you MUST read and follow the [commit skill](.agents/skills/commit/SKILL.md).

## Design pattern discipline

- Before creating or changing behavior, or performing structural refactors, read the applicable guides:
  - Frontend: [architecture](frontend/docs/architecture/overview.md), [design patterns](frontend/docs/architecture/design-patterns.md), and [naming](frontend/docs/architecture/naming.md).
  - Backend: [architecture](backend/docs/architecture/overview.md), [design patterns](backend/docs/architecture/design-patterns.md), and [naming](backend/docs/architecture/naming.md).
  - Read both sets for work spanning frontend and backend. Stack and architecture proposals remain proposals until explicitly decided; examples do not approve technologies or product scope.
- Inspect and reuse existing implementations first. Start with the smallest adequate function, composition, platform capability, or framework facility. Apply a documented pattern when a concrete current problem meets its guide's criteria and simpler options fail to satisfy the requirement. Not every feature needs a pattern.
- Evaluate each pattern on its own criteria. Strategy requires at least two actual interchangeable algorithms; this is not a universal two-implementation requirement. Adapter may bridge a single external integration with an incompatible interface.
- Do not introduce speculative interfaces, classes, layers, variants, or out-of-scope features to justify a pattern. Keep participants within their owning feature or business module unless actual shared consumers justify extraction.
- Name custom participants according to the applicable naming guide and their actual roles and structure. Preserve framework and external API names. A GoF suffix alone does not establish a pattern, and a naming rule does not require creating an interface or wrapper.
- For each newly adopted pattern, add a brief record in the relevant feature documentation: current problem, concrete participants, rejected simpler alternative, and behavioral proof. Do not duplicate the pattern catalogs. For a previously deferred pattern, document its adoption criteria and naming before implementation.
- Test the relevant behavior and contracts, including financial invariants and failure handling where affected. Review both for a useful missing pattern and for unnecessary abstraction; avoid unrelated refactors.
- For nontrivial behavior changes or structural refactors, include one sentence in the final report identifying the pattern reused or adopted, or why the simpler approach suffices. Documentation-only changes, typos, and trivial edits need no pattern ceremony.
