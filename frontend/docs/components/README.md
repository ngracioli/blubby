# Components

Owns component responsibilities, composition, props, events, accessibility, and reuse criteria.

- Keep feature-specific components with the feature that owns them.
- Share a component when it represents the same responsibility for real consumers, not merely similar markup.
- Shared visual components do not fetch data or decide financial rules.
- Prefer composition and explicit props over inheritance or a growing list of mode flags.
- Document loading, empty, error, disabled, and interaction states where applicable.
- Document accessible labels, focus behavior, touch targets, and dynamic text behavior.

Use [design-system foundations](../design-system/README.md) for visual decisions and [pattern guardrails](../architecture/design-patterns.md) before introducing behavioral abstractions.
