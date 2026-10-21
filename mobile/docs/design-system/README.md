# Design system

Owns semantic color tokens, typography, spacing, sizing, radii, icons, and reusable visual states.

Use the [existing wireframe references](../../../assets/wireframes/README.md) as input. Assets and studies do not establish a production component library.

Document token names and roles before assigning them throughout the app. Prefer semantic names such as `textPrimary` over names tied to one screen. Describe contrast, text scaling, focus, errors, and disabled states. Never distinguish forecast and completed entries through color alone.

Keep product-specific content separate from visual primitives. Record platform differences only where the interaction or rendering actually differs.

## Implementation tokens

Design tokens derived from Figma are maintained in `src/shared/theme/`:
- `colors.ts`: Semantic palette (backgrounds, surfaces, text, primary oceanic blues, projection cyans, and financial statuses).
- `typography.ts`: Manrope type scale (`amount`, `amountCompact`, `heading1`–`3`, `body`, `label`, `caption`).
- `spacing.ts`: Padding, margin, gap, and standard horizontal screen insets.
- `radii.ts`: Border radii for chips, cards, buttons, and containers.
- `shadows.ts`: Cross-platform elevation tokens.
