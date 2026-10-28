# Button

`Button` is a shared `Pressable` with typed appearance, native interactions, accessible states, and customizable content. It uses ordinary React composition and native press state; no additional behavioral pattern is required.

The visual sources are [Button, node 24:26](https://www.figma.com/design/QYrBUue2AdyrjvJLiMu7F2/blubby?node-id=24-26), including Light and Dark states, and the solid, outline, and text actions in [Welcome, node 88:340](https://www.figma.com/design/QYrBUue2AdyrjvJLiMu7F2/blubby?node-id=88-340). `tone="error"` applies the semantic error token as customization; it does not claim a separate Figma error-button variant. The numeric sizing API does not introduce additional named Figma size presets.

## Public props

| Prop | Type | Default and contract |
| --- | --- | --- |
| `children` | `ReactNode` excluding null, undefined, and booleans, or `(state: ButtonState) => ReactNode` | Required unless `accessibilityLabel` is supplied for an icon-only control. Strings and numbers receive the built-in label; custom nodes render directly. |
| `accessibilityLabel` | `string` | Native label behavior when omitted. Required by the type when `children` is absent. Provide it for custom content that has no useful accessible name. |
| `ref` | `Ref<ViewInstance>` | Unset. Forwarded to the native `Pressable` view. |
| `variant` | `'solid' \| 'outline' \| 'text'` | `'solid'`. Solid fills the background; outline uses a 1-point border; text uses a transparent background without a border. |
| `tone` | `'primary' \| 'error'` | `'primary'`. Chooses the accent color independently of loading or disabled state. Error tone does not block activation. |
| `appearance` | `'light' \| 'dark'` | `'light'`. Explicit palette selection; does not subscribe to system appearance. |
| `colors` | `Partial<ControlColors>` | No overrides. Shallow overrides the selected palette. |
| `size` | `number` | `52`, from `theme.spacing.actionButtonHeight`. Minimum root height in React Native layout units, not a fixed height. |
| `iconSize` | `number` | `20`. Passed to accessory render callbacks; supplied nodes manage their own size. |
| `disabled` | Native `PressableProps['disabled']` | `false`. Blocks press/long-press activation and exposes disabled state. |
| `loading` | `boolean` | `false`. Blocks activation, exposes busy state, and replaces the leading icon with progress content. |
| `loadingLabel` | `string` | Unset. A nonempty value replaces normal content while loading and suppresses the default spinner. An explicit `loadingIndicator` still renders. |
| `leadingIcon` | `Accessory<ButtonState>` | Unset. Optional decorative content before the label; replaced during loading. |
| `trailingIcon` | `Accessory<ButtonState>` | Unset. Optional decorative content after the label; remains during loading. |
| `loadingIndicator` | `Accessory<ButtonState>` | Unset. Loading uses a small native `ActivityIndicator`, except when a nonempty `loadingLabel` is supplied. A custom node or callback replaces that fallback; `null` suppresses it. |
| `style` | `StatefulStyle<ButtonState, ViewStyle>` | Unset. Overrides the root `Pressable`. |
| `contentStyle` | `StatefulStyle<ButtonState, ViewStyle>` | Unset. Overrides the inner horizontal content view. |
| `labelStyle` | `StatefulStyle<ButtonState, TextStyle>` | Unset. Overrides built-in label typography and color. Does not style custom child nodes. |
| `iconStyle` | `StatefulStyle<ButtonState, ViewStyle>` | Unset. Styles each rendered icon/indicator wrapper. Does not directly recolor its children. |
| `labelProps` | `Omit<TextProps, 'children' \| 'style'>` | Unset. Forwarded to the built-in `Text` for string or numeric content. |
| Other native props | `Omit<PressableProps, 'children' \| 'style'>` | Forwarded to `Pressable`, including `onPress`, `onLongPress`, `onPressIn`, `onPressOut`, `hitSlop`, accessibility props, and `testID`. Focus and hover handlers are composed with internal state tracking. |

## Styling, colors, and state

`StatefulStyle` accepts a native style, registered style, style array, or `(state) => style`. `ButtonState` contains `pressed`, `focused`, `hovered`, `disabled`, and `loading`. The disabled field represents effective blocking, including loading and caller-supplied disabled accessibility state. Pressed, focused, and hovered are false while blocked.

`Accessory<ButtonState>` accepts a React node or `(state) => ReactNode`. The callback receives all button state fields plus resolved `color` and numeric `size`. No wrapper or icon gap renders for an absent accessory. Leading and trailing wrappers are hidden from accessibility because the button owns the accessible name. Put separate interactive actions outside those decorative slots.

Defaults use theme button typography, radius, spacing, centered wrapping labels, and a 52-point minimum height. Each part applies base styles, variant/size/tone and active-state styles, then its consumer style last. `labelProps.style` is intentionally unavailable. For custom children, consumers own layout, text scaling, color, and accessibility.

All palette roles and Light/Dark values are defined in [control-colors.ts](../../src/shared/theme/control-colors.ts). Button uses `primary` or `error` as its accent, `onPrimary` for solid foreground, `pressed` for pressed primary solid background, `focus` for a focused outline, and `disabledBackground`/`disabledContent` when disabled without loading. `surface`, `text`, `secondary`, and `border` are available in the shared palette for other controls; they do not independently style this button. Customize indicator color with `loadingIndicator`; customize an icon's color through its render callback. State style callbacks provide final background, border, text, and layout overrides for every state.

Loading preserves the active palette while blocking interaction. Disabled without loading uses disabled colors, including a filled disabled background for all variants. Error remains independent of loading; focused controls get an outline. Defaults do not add a separate hover treatment or pressed-error color. Callbacks can supply those treatments.

`disabled`, `loading`, `accessibilityState.disabled`, or `aria-disabled` block press and long press. Managed disabled/busy accessibility values and activation handlers apply after native props, so visual overrides cannot restore activation. The default role is `button`; native accessibility overrides remain the caller's responsibility.

## Usage

```tsx
import { Button } from '@/shared/components';

<Button onPress={submit}>Criar conta</Button>
<Button variant="outline" disabled>Indisponível</Button>
<Button tone="error" loading loadingLabel="Salvando…">Salvar</Button>
```

```tsx
<Button
  size={64}
  appearance="dark"
  colors={{ primary: '#155E75', onPrimary: '#FFFFFF' }}
  leadingIcon={({ color, size }) => <SaveIcon color={color} size={size} />}
  style={({ pressed }) => ({ borderRadius: 12, opacity: pressed ? 0.85 : 1 })}
  contentStyle={{ gap: 12 }}
  labelStyle={{ fontSize: 18 }}
  onPress={save}
>
  Salvar alterações
</Button>

<Button
  accessibilityLabel="Fechar"
  leadingIcon={({ color, size }) => <CloseIcon color={color} size={size} />}
  onPress={close}
/>
```

Custom children can use `children={({ loading }) => <CustomContent loading={loading} />}`. Define the referenced feature handlers and icon components at the call site.

## Limits and verification

### Provisional visual states

The current Figma Button `24:26` defines solid primary Light/Dark default, pressed, disabled, and loading states. Welcome `88:340` additionally supplies idle outline provider controls and a text action. The following capabilities remain customizable but do not yet have dedicated Figma variants:

| Capability | Current source or fallback | Replace when |
| --- | --- | --- |
| Error tone | Semantic error color from Field `24:119`; this is not a separately approved error-button recipe. | Figma supplies an error-button variant, including interaction states. |
| Outline/text interaction states | Shared interaction behavior, palette, and caller state styles. | Figma supplies pressed, disabled, loading, or focus treatments for those variants. |
| Focus outline | Existing semantic focus color with an accessible outline. | Figma supplies the button focus specification. |
| Generic icon arrangements and extra numeric sizes | Explicit consumer props and style overrides; no additional default preset is claimed. | Figma supplies an applicable reusable icon or size variant. |

For each relevant new drawing, update default tokens/styles and actual consumers together, preserve the customization API, compare against the supplied node, and update this record and its verification evidence. Provider typography already has a drawing and uses `typography.providerButton`; it is not a provisional Manrope fallback.

The minimum height allows labels to grow with system text scaling; hard-coded consumer heights or line limits can reintroduce clipping. Loading content can change width when the layout does not constrain it; reserve width through `style` when required. Native hover, focus outlines, refs, screen readers, and touch targets depend on the runtime. Device behavior and custom color contrast require verification; documentation and component tests do not certify VoiceOver, TalkBack, or native keyboard behavior.
