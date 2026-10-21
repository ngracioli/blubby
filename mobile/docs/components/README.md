# Components

Owns component responsibilities, composition, props, events, accessibility, and reuse criteria.

- Keep feature-specific components with the feature that owns them.
- Share a component when it represents the same responsibility for real consumers, not merely similar markup.
- Shared visual components do not fetch data or decide financial rules.
- Prefer composition and explicit props over inheritance or a growing list of mode flags.
- Document loading, empty, error, disabled, and interaction states where applicable.
- Document accessible labels, focus behavior, touch targets, and dynamic text behavior.

## Responsiveness and platform boundaries

- Screens must wrap top-level layout in safe area management (e.g. `ScreenContainer` with `react-native-safe-area-context`) to respect notches, Dynamic Island, status bar, and home indicators on iOS and Android.
- Support compact screens (e.g. iPhone SE, small Androids) by allowing vertical scroll when content exceeds viewport height. Never use fixed pixel heights that cause clipping.
- Support tablets and large screens by constraining max layout width (default `maxWidth: 480`) and centering content horizontally rather than stretching full width.
- Handle keyboard avoidance (`KeyboardAvoidingView`) and dismiss behavior on input screens.

Use [design-system foundations](../design-system/README.md) for visual decisions and [pattern guardrails](../architecture/design-patterns.md) before introducing behavioral abstractions.
