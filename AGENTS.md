# Project instructions

- For backend work started from repository root, read [backend agent instructions](backend/AGENTS.md) and use [backend task routing](backend/docs/agent-workflow.md). Backend-local skills are not assumed to be discovered recursively from root; read the relevant canonical `backend/.ai/skills/<name>/SKILL.md` explicitly when the client has not loaded it.

- All project documentation must be written in English. Preserve intentional Portuguese product UI examples and original external asset/Figma names and links.
- Write code identifiers in English.
- Before preparing or executing any commit, you MUST read and follow the [commit skill](.agents/skills/commit/SKILL.md).
- Before designing, implementing, or changing any UI, screen, or visual component, you MUST read and follow the [figma-design skill](.agents/skills/figma-design/SKILL.md).
- Before creating or changing mobile UI components, you MUST read and follow the [shared component rules](mobile/docs/components/README.md). Design-system primitives belong in `mobile/src/shared/components/` from their first consumer, use Figma-backed defaults, and expose typed customization without weakening interaction or accessibility contracts.
- Before composing a mobile screen, identify its required components and states against the Figma design and existing library. Reuse existing components; implement or extend missing domain-agnostic primitives in `mobile/src/shared/components/` first, within the same task, following the shared component rules. Keep feature-specific compositions in their owning feature. Screens must not copy shared primitive styles or interaction logic, or bypass library controls with private native implementations. Native layout containers and text remain allowed; do not extract every `View` or `Text` or create speculative components.
- The official Figma file owns default component visuals. When new or changed component/state designs are supplied or discovered during relevant work, align affected tokens, styles, actual consumers, and documentation in the same task while preserving the customization API. Record custom states without a Figma specification as provisional, not approved presets, and replace their default visuals when the corresponding design becomes available.

## Design pattern discipline

- Before creating or changing behavior, or performing structural refactors, read the applicable guides:
  - Mobile: [architecture](mobile/docs/architecture/overview.md), [design patterns](mobile/docs/architecture/design-patterns.md), and [naming](mobile/docs/architecture/naming.md).
  - Backend: [architecture](backend/docs/architecture/overview.md), [design patterns](backend/docs/architecture/design-patterns.md), and [naming](backend/docs/architecture/naming.md).
  - Read both sets for work spanning mobile and backend. Stack and architecture proposals remain proposals until explicitly decided; examples do not approve technologies or product scope.
- Inspect and reuse existing implementations first. Start with the smallest adequate function, composition, platform capability, or framework facility. Apply a documented pattern when a concrete current problem meets its guide's criteria and simpler options fail to satisfy the requirement. Not every feature needs a pattern.
- Before creating or moving files, follow the applicable architecture and naming guides' documented directory hierarchy and locations. Check the guide's examples and existing feature structure before choosing a path; do not invent a directory convention when the guides already define the file's location. If the guides leave the location open, choose the smallest consistent location and document a new convention only when it will be reused.
- Evaluate each pattern on its own criteria. Strategy requires at least two actual interchangeable algorithms; this is not a universal two-implementation requirement. Adapter may bridge a single external integration with an incompatible interface.
- Do not introduce speculative interfaces, classes, layers, variants, or out-of-scope features to justify a pattern. Keep participants within their owning feature or business module unless actual shared consumers justify extraction.
- Name custom participants according to the applicable naming guide and their actual roles and structure. Preserve framework and external API names. A GoF suffix alone does not establish a pattern, and a naming rule does not require creating an interface or wrapper.
- For each newly adopted pattern, add a brief record in the relevant feature documentation: current problem, concrete participants, rejected simpler alternative, and behavioral proof. Do not duplicate the pattern catalogs. For a previously deferred pattern, document its adoption criteria and naming before implementation.
- Test the relevant behavior and contracts, including financial invariants and failure handling where affected. Review both for a useful missing pattern and for unnecessary abstraction; avoid unrelated refactors.
- For nontrivial behavior changes or structural refactors, include one sentence in the final report identifying the pattern reused or adopted, or why the simpler approach suffices. Documentation-only changes, typos, and trivial edits need no pattern ceremony.

## Test-first discipline

- For bug fixes and behavior changes, write the smallest test that captures the observable behavior before changing implementation code.
- Run that test first. Confirm it fails because the behavior is missing or incorrect, not because the test or environment is broken. Do not write implementation code until this failure is confirmed.
- Then implement the smallest fix and rerun the test until it passes. Run the relevant existing suite before finishing.
- Keep tests only when they cover a distinct behavior or defect. Do not add duplicate or artificial tests to satisfy a count.
- For refactors that must preserve behavior, run the relevant existing tests before and after; do not invent a failing test. For documentation-only, formatting-only, and configuration-only changes with no meaningful behavioral contract, skip tests and use an appropriate focused check.
- If a meaningful failing test cannot be written or run, explain why before changing implementation code and ask how to proceed.
