# Shared UI component rules

This is the required guide for creating and changing mobile UI components. It defines the contract for Blubby's personal component library: Figma-backed defaults with complete visual customization through typed props, composition, and explicit style overrides.

The implemented library includes [Button](button.md), [Input](input.md), [Divider](divider.md), and [ScreenContainer](screen-container.md). These rules also apply to future components. See [verification](verification.md) for evidence and remaining runtime checks. Research was consulted on October 22, 2026. Project policies below adapt the sources to this repository; source libraries are references, not approved dependencies.

## 1. Ownership and location

- Put domain-agnostic design-system primitives in `mobile/src/shared/components/` from their first consumer. Buttons, text inputs, and other actual library controls must not be implemented privately inside a screen.
- Keep screens, forms, provider authentication orchestration, navigation, API calls, and business rules in their owning feature. Shared controls receive values and callbacks; they do not import features.
- Keep other feature-specific compositions local until actual consumers across features share the same responsibility. Similar markup alone does not justify extraction.
- Follow the [architecture](../architecture/overview.md), [naming conventions](../architecture/naming.md), and [pattern guardrails](../architecture/design-patterns.md). Start with a focused `button.tsx` or `input.tsx`; split supporting files only when their responsibilities justify it.
- Export components and their public props through `src/shared/components/index.ts`. Screens consume the public API through `@/shared/components`.
- Use React composition, props, native controls, and plain functions first. Do not add factories, inheritance, generic component engines, or a new dependency solely to support variants.

## 2. Figma defaults and design tokens

- Follow the [Figma design skill](../../../.agents/skills/figma-design/SKILL.md) and inspect the official nodes before implementing visual defaults. Record the relevant node links in component documentation.
- The official Figma file owns default visuals. When new or changed component/state designs are supplied or discovered during relevant work, update affected tokens, styles, actual consumers, and documentation in the same task while preserving typed customization and interaction/accessibility contracts.
- Track every custom state without a Figma specification as provisional in its component documentation: identify the state, current appearance, missing specification, and corresponding node link when available. Provisional appearances are not approved presets. When the corresponding design becomes available, replace provisional defaults, verify the actual consumers against that node, and update the record with the design source and verification status. This rule applies when relevant work is requested; it does not imply background monitoring of Figma.
- Keep shared colors, typography, spacing, radii, and shadows in `src/shared/theme/`. Use those tokens for library defaults instead of copying literals into components and screens.
- A preset is an approved visual recipe. Do not infer additional sizes, error colors, disabled palettes, focus treatments, or animations from unrelated screens. Resolve missing visual specifications through the Figma workflow.
- Define semantic error and disabled tokens when approved. Do not repurpose a financial token such as `expense` as a generic validation error merely because both are red.
- Separate the default appearance from the customization contract. Consumers can override dimensions, colors, typography, borders, radii, spacing, and supported state styles without changing component source.
- Provider branding remains subject to its documented asset and presentation requirements. Provider-specific composition must not hard-code authentication behavior into a generic button.

## 3. Public API design

- Define and export `<Component>Props`. Derive compatible native props from `PressableProps`, `TextInputProps`, or the actual native primitive rather than maintaining copied definitions.
- Use `Omit` when intentionally replacing a native prop. Document its new meaning and destination. Consume library-only props before forwarding remaining props to the correct native element.
- Preserve familiar native event names and signatures, accessibility props, `testID`, keyboard options, and refs. Do not rename `onPress` to `onClick` or wrap native events in custom payloads without a concrete need.
- Use independent, typed dimensions: `variant` describes visual treatment, `size` selects an approved size preset or an explicit dimension, and `tone` describes semantic color intent. Preset union values must match approved component specifications. Numeric overrides do not establish new Figma presets.
- Keep independent states such as `disabled` and `loading` as booleans. Avoid contradictory flags such as `primary`, `secondary`, and `outlined` on the same control. Use TypeScript unions when modes require different props.
- Use explicit defaults. Document every public prop, its type, default, interaction with other props, and platform limitations.
- Provide icon or accessory slots instead of adding a flag for every icon, label arrangement, or feature. Do not introduce arbitrary nested configuration objects when ordinary props or children express the requirement.
- Preserve existing consumers when evolving the API. A breaking change requires updating all actual callers and documentation in the same change.

React supports passing JSX and callbacks through props; TypeScript unions can express valid combinations. See [React props](https://react.dev/learn/passing-props-to-a-component) and [TypeScript unions](https://www.typescriptlang.org/docs/handbook/unions-and-intersections.html).

## 4. Customization and precedence

Every component must document its styling surface. Customization must reach each meaningful visible part without requiring consumers to copy the component.

- Use `style` for the documented root, and explicit part styles where needed, such as `contentStyle`, `labelStyle`, `inputStyle`, and `helperTextStyle`. State whether a native prop's destination differs from the wrapper's destination.
- Accept React Native `StyleProp<ViewStyle>` or `StyleProp<TextStyle>` as appropriate, including style arrays and registered styles. Do not restrict overrides to plain objects.
- Support custom dimensions through styles even when `size` presets exist. Consumers must be able to change width, minimum height, padding, and text size without adding a preset for every numeric value.
- Support custom foreground, background, border, and indicator colors through typed color props or documented part styles. Choose one canonical mechanism per concern; avoid duplicate props with unclear precedence.
- Allow state-specific overrides through a documented state-aware callback or typed state styles. A default-only background override is insufficient when pressed, error, or disabled styles can replace it.
- Apply visual layers in this order: base tokens, variant and size presets, semantic tone, active state defaults, consumer overrides. Consumer overrides have final visual precedence for the targeted part.
- Document state combinations. Disabled controls do not receive interactive feedback; input focus must not hide an invalid indication. Loading can coexist with an error tone while still blocking repeated activation.
- Visual overrides must never re-enable disabled interaction, clear required accessibility state, or change controlled input semantics. Apply authoritative behavior after forwarded native props so prop spread order cannot bypass it.
- Repeated overrides that represent an approved product style should become a shared preset. One-off overrides remain supported without automatically expanding the global theme.

React Native documents style arrays and last-entry precedence. Restyle demonstrates theme variants with final consumer style overrides. See [native styles](https://reactnative.dev/docs/style), [Restyle variants](https://shopify.github.io/restyle/fundamentals/variants/), and [Restyle overrides](https://shopify.github.io/restyle/fundamentals/overriding-styles/).

## 5. Button contract

Before accepting a shared button implementation, verify these capabilities:

| Concern | Required contract |
| --- | --- |
| Appearance | Approved `variant` and `size` presets, plus custom dimensions and part styles. |
| Error appearance | A typed semantic error tone independent of disabled or loading state. An error tone alone does not block activation. |
| Colors | Foreground, background, border, and indicator customization, including active states. |
| Icons | Optional leading and trailing content, no reserved icon gap when absent, and an accessible name for icon-only usage. Use a render callback when icons need resolved color, size, or state. |
| Disabled | Block activation and expose disabled accessibility state. Changing opacity alone is insufficient. |
| Loading | When supported, show progress, expose busy state, block repeated activation, and avoid unnecessary width changes. |
| Native behavior | Use `Pressable` or an existing suitable interactive primitive. Preserve compatible native handlers, touch-area props, and refs. A styled `View` or image alone is not a button. |
| Layout | Allow long labels, dynamic text, and custom width without clipping content. Document wrapping behavior. |

Use `disabled || loading` as the effective activation block when loading is part of the button contract. Keep persistent states in props and derive transient press feedback from the native interaction primitive.

[React Native Pressable](https://reactnative.dev/docs/pressable) provides press interactions and touch-area controls. [React Native Paper's Button source](https://github.com/callstack/react-native-paper/blob/main/src/components/Button/Button.tsx) illustrates separate visual modes, colors, icons, loading, and part styles. Its complete API is not a checklist to copy.

## 6. Input contract

- Build on native `TextInput` and retain compatible native capabilities: `value`, `defaultValue`, `onChangeText`, selection, keyboard configuration, autofill, secure entry, multiline, and refs.
- Support controlled values without copying `value` into competing internal state. Preserve uncontrolled `defaultValue` usage when no controlled value is supplied. Do not switch modes during a mounted input's lifetime.
- Keep validation decisions in the feature. The shared input presents invalid state, error text, and helper text supplied by the caller.
- Document a single invalid-state contract. If both an invalid flag and error text are supported, define their relationship, including invalid presentation without a visible message.
- Distinguish disabled from read-only. Both prevent editing, but must expose their intended semantics. Neither caller-supplied native props nor styling can override an explicit disabled state.
- Support labels and optional leading/trailing accessories. Placeholder text must not be the only accessible label. A password visibility action needs its own accessible name and interaction target.
- Forward refs to the native input or an explicitly typed handle so callers can use `focus`, `blur`, and supported native methods. Use the installed React version's ref mechanism.
- Preserve caller `onFocus` and `onBlur` handlers when tracking visual focus internally. Do not swallow native events or move form state into the visual primitive.
- Document root, input, label, accessory, and helper/error styling destinations. Account for multiline content, keyboard appearance, and platform-specific rendering.

See [native TextInput behavior](https://reactnative.dev/docs/textinput) and [React Native Paper's TextInput source](https://github.com/callstack/react-native-paper/blob/main/src/components/TextInput/TextInput.tsx) for native prop reuse and exposed input methods.

## 7. Accessibility and responsive layout

- Expose the correct role, accessible name, disabled state, and busy state where applicable. Decorative icons must not duplicate announcements.
- Communicate errors with text and appropriate accessible semantics, not color alone. Check VoiceOver and TalkBack because their behavior differs.
- Preserve system text scaling. Do not disable font scaling or impose fixed heights that clip enlarged labels merely to match a reference screenshot.
- Document and verify platform-appropriate touch targets. Use `hitSlop` where needed, accounting for parent bounds and overlapping controls.
- Preserve focus visibility and keyboard interaction on supported platforms. Respect RTL layout and reduced-motion settings when the component uses animation.
- Keep screen safe areas and overall max width in `ScreenContainer`; individual controls must not add screen-level insets. Center large-screen layouts and allow compact screens to scroll when needed.
- Input screens own keyboard avoidance and dismissal behavior. Shared inputs expose the native hooks needed for that behavior.

See [React Native accessibility](https://reactnative.dev/docs/accessibility). Consumers remain responsible for checking contrast and accessibility after custom visual overrides.

## 8. Performance and dependencies

- Keep rendering pure. Do not fetch data, mutate props, or synchronize derived visual values with effects.
- Define static styles outside render using `StyleSheet.create`; keep dynamic style resolution small and local. This is a readability baseline, not a claim that inline objects are inherently slow.
- Keep transient input and interaction state local. Do not create an application-wide context update for each keystroke or button press.
- Add `memo`, `useMemo`, or `useCallback` only for a measured problem, with a representative before/after check. Do not promise render elimination or assume React Compiler is enabled.
- Reuse the installed icon system. Avoid importing an entire icon catalog for a single control, adding a library for a few style variants, or building a custom theme provider before real requirements justify it.
- Keep defaults centralized in the existing theme. If runtime theme overrides become required, use a typed contract rather than a loosely merged configuration object.

[React's memo guidance](https://react.dev/reference/react/memo) treats memoization as a performance optimization, not a correctness mechanism or guarantee.

## 9. Documentation and acceptance checks

Each implemented component must have a concise API reference alongside this guide, named `<component-name>.md`. Include its purpose, Figma node links, props/defaults, state combinations, style destinations and precedence, native limitations, and usage examples. Examples must cover default appearance and meaningful customization; do not add a preview app or Storybook dependency solely to satisfy this rule.

Before declaring a component complete:

- Verify the public API and consumers type-check using the existing project tooling.
- Check default rendering against the actual Figma reference. Verify custom size, custom colors, icons present/absent, and relevant state combinations.
- Test meaningful behavior: disabled/loading cannot activate; enabled activation calls the handler correctly; controlled inputs report edits; native events and refs still work; style overrides do not bypass behavior. Prefer interaction assertions over snapshots of implementation details.
- Verify accessibility names and states, large text, narrow layouts, long content, and relevant platform differences. Component tests do not prove native keyboard or screen-reader behavior; check those on supported runtimes.
- Cover representative interactions between variants, states, and overrides instead of blindly enumerating every possible prop combination.
- Update actual consumers and public exports together. Screens must not retain private copies of the primitive's styles or interaction logic.
- Report exactly what was checked and what remains unverified. Do not claim Figma fidelity from matching JSX structure alone.

[React Native's testing overview](https://reactnative.dev/docs/testing-overview) explains the roles and limitations of static, component, and device-level checks. Add verification proportional to the behavior being introduced; do not add tests merely to restate static markup.

## 10. Research and maintenance

The linked primary sources support the API, styling, accessibility, and performance practices above. The required ownership boundary, prop naming policy, precedence order, and acceptance checklist are Blubby decisions based on the requested library contract.

Check source guidance against the versions actually installed in `mobile/package.json` before adopting an API. Research links and example libraries do not authorize dependency changes, invented Figma states, npm publication, or a standalone library package.

Update this guide when the public component contract changes. Keep the mandatory entry in root `AGENTS.md` so future component work reads these rules.
