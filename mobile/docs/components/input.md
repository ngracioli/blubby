# Input

`Input` composes native `TextInput` with a label, optional accessories, helper/error text, and typed visual customization. Features own values and validation decisions. The control does not validate, transform, or duplicate the caller's value in internal state. Plain React composition and native input behavior suffice; no additional behavioral pattern is required.

The visual source is [Field, node 24:119](https://www.figma.com/design/QYrBUue2AdyrjvJLiMu7F2/blubby?node-id=24-119), including Light and Dark states. Shared palette values are recorded in [control-colors.ts](../../src/shared/theme/control-colors.ts). Numeric sizing supports customization without inventing named Figma size presets.

## Public props

| Prop | Type | Default and contract |
| --- | --- | --- |
| `ref` | `Ref<TextInputInstance>` | Unset. Forwarded to native input for `focus`, `blur`, and supported native methods. |
| `label` | `string` | Unset. Visible label and fallback `accessibilityLabel`. A placeholder is not an accessible label substitute. |
| `helperText` | `string` | Unset. Supporting text beneath the control; included in the input accessibility hint. |
| `errorMessage` | `string` | Unset. A nonempty string makes the input invalid and replaces helper text with the error message. |
| `invalid` | `boolean` | `false`. Shows invalid styling even without an error message. Does not prevent editing or run validation. |
| `disabled` | `boolean` | `false`. Prevents editing, applies disabled colors, and exposes disabled accessibility state. |
| `readOnly` | Native `TextInputProps['readOnly']` | `false`. Prevents editing while retaining ordinary appearance and non-disabled accessibility state. |
| `editable` | Native `TextInputProps['editable']` | `true`. `false` also prevents editing and yields `readOnly: true` in style/accessory state. |
| `appearance` | `'light' \| 'dark'` | `'light'`. Selects an explicit palette; does not automatically follow system appearance. |
| `colors` | `Partial<ControlColors>` | No overrides. Shallow overrides the selected palette. |
| `size` | `number` | `52`, from `theme.spacing.actionButtonHeight`. Minimum control-row height in React Native layout units. |
| `iconSize` | `number` | `20`. Passed to accessory callbacks; supplied nodes own their size. |
| `leadingAccessory` | `Accessory<InputState>` | Unset. Optional content before the native input. |
| `trailingAccessory` | `Accessory<InputState>` | Unset. Optional content after the native input, including separately accessible actions. |
| `style` | `StatefulStyle<InputState, ViewStyle>` | Unset. Styles the root wrapper containing label, control, and message. |
| `controlStyle` | `StatefulStyle<InputState, ViewStyle>` | Unset. Styles the bordered row containing input and accessories. |
| `inputStyle` | `StatefulStyle<InputState, TextStyle>` | Unset. Styles the native `TextInput`, including text color and typography. |
| `labelStyle` | `StatefulStyle<InputState, TextStyle>` | Unset. Styles visible label text. |
| `helperTextStyle` | `StatefulStyle<InputState, TextStyle>` | Unset. Styles the currently rendered helper or error text. |
| `labelProps` | `Omit<TextProps, 'children' \| 'style'>` | Unset. Forwards props to label `Text`. A native ID is generated unless supplied. |
| `helperTextProps` | `Omit<TextProps, 'children' \| 'style'>` | Unset. Forwards props to message `Text`. Default live region is `polite` for invalid state and `none` otherwise; callers can override it. |
| `accessibilityLabel` | Native label type | Falls back to `label`. Supply explicitly when no visible label exists. |
| `accessibilityHint` | Native hint type | Unset. Combined with current helper/error message, separated by `. `. |
| `aria-describedby` | `string` | Unset. On web, existing description IDs are combined with the rendered message's native ID. |
| `aria-invalid` | `boolean` | Unset. `true` also sets invalid state. It cannot clear an error supplied through `invalid` or `errorMessage`. The resulting attribute is emitted on web. |
| `aria-readonly` | `boolean` | Unset. `true` also prevents editing and sets read-only state. The resulting attribute is emitted on web. |
| Other native props | `Omit<TextInputProps, 'style' \| 'children'>` | Forwarded to native input, including `value`, `defaultValue`, `onChangeText`, selection, `multiline`, secure entry, keyboard/autofill props, accessibility props, and `testID`. Focus and blur handlers are composed with internal tracking. |

## State and customization

`StatefulStyle` accepts native styles, registered styles, arrays, or `(state) => style`. `InputState` contains `focused`, `disabled`, `readOnly`, and `invalid`. Disabled reflects effective blocking from `disabled`, `accessibilityState.disabled`, or `aria-disabled`. Focused is false when disabled. Read-only reflects `readOnly || !editable || aria-readonly`; disabled is a separate state.

`Accessory<InputState>` accepts a React node or `(state) => ReactNode`. Callbacks receive all input state fields plus resolved foreground `color` and numeric `size`. No space is reserved for absent accessories. Accessories render directly, so consumers style their own wrappers and provide accessible names, touch targets, and disabled behavior for interactive accessories. A password visibility action remains a separate button with its own label.

Styles apply in this order: base theme typography, spacing and radius; size and palette defaults; active-state defaults; consumer part styles. Consumer styles win for their visible part. Each part is independent: root `style` does not set input text color, and `inputStyle` does not style the border. Native `placeholderTextColor`, `selectionColor`, and `underlineColorAndroid` can override palette defaults through native props.

The palette's `surface`, `text`, `secondary`, `border`, `focus`, `error`, `disabledBackground`, and `disabledContent` set the control surface, normal foreground, placeholder/helper foreground, ordinary border, focus/selection color, invalid border/message, disabled surface, and disabled input/accessory foreground respectively. `primary`, `onPrimary`, and `pressed` are shared button roles and do not independently affect input appearance. All roles accept React Native `ColorValue` through `colors`.

The invalid state is `invalid || Boolean(errorMessage) || aria-invalid`. A nonempty error message wins over helper text. With `invalid` alone, helper text remains visible and receives invalid styling/live-region defaults; supply a meaningful explanation because color alone does not communicate an error. Focus never replaces the invalid border: invalid uses a 1-point error border, while valid focused input uses a 2-point focus border. Disabled and invalid can coexist; invalid border/message remain while the control and input use disabled colors.

Explicit disabled, read-only, or non-editable props cannot be bypassed by forwarded native props or visual overrides. Read-only controls retain normal colors and are not marked disabled. On web, the input exposes derived `aria-invalid` and `aria-readonly`, and `aria-describedby` associates the current message with the input. Message IDs use `helperTextProps.id`, then `helperTextProps.nativeID`, then a generated ID, matching native ID alias precedence. On native platforms, helper/error text is included in the accessibility hint, with a message live region where supported. Verify actual announcements on supported screen readers.

## Usage

```tsx
import { Input } from '@/shared/components';

<Input
  label="E-mail"
  value={email}
  onChangeText={setEmail}
  keyboardType="email-address"
  autoCapitalize="none"
  autoComplete="email"
  errorMessage={emailError}
/>
```

```tsx
<Input
  label="Descrição"
  defaultValue="Compra do mês"
  helperText="Informe como identificar este lançamento."
  multiline
  size={80}
  appearance="dark"
  colors={{ focus: '#67E8F9', error: '#FCA5A5' }}
  style={{ width: '100%' }}
  controlStyle={({ invalid, focused }) => ({
    borderRadius: 12,
    borderColor: invalid ? '#FCA5A5' : focused ? '#67E8F9' : '#94A3B8',
  })}
  inputStyle={{ fontSize: 18, textAlignVertical: 'top' }}
  trailingAccessory={({ color, size }) => <NoteIcon color={color} size={size} />}
/>
```

```tsx
<Input label="Identificador" value={identifier} readOnly />
<Input label="Campo indisponível" disabled />
<Input ref={inputRef} accessibilityLabel="Buscar lançamentos" placeholder="Buscar" />
```

Features define the referenced state, handlers, refs, and icons. Use `value` with `onChangeText` for controlled input or `defaultValue` for uncontrolled input; do not switch modes during a mounted input's lifetime. Native events and refs keep native signatures.

## Limits and verification

Field `24:119` defines Light/Dark default, focus, error, and disabled visuals. Read-only presentation and accessory arrangements do not yet have dedicated drawings. Read-only currently retains ordinary field visuals; custom accessories use caller content and styling with the existing field spacing. Keep these capabilities provisional until Figma supplies matching nodes. When those drawings arrive in relevant component work, align tokens/styles, consumers, and this documentation while preserving native semantics and customization.

The native input keeps system font scaling enabled unless the caller overrides it. Minimum heights allow growth, but multiline layout, long labels, RTL, accessory bounds, and caller height constraints require checks with enlarged text. Screen-level safe areas and keyboard avoidance belong to `ScreenContainer`; the input does not own them. Native keyboard, autofill, selection, read-only behavior, VoiceOver, and TalkBack require device verification. Documentation and component tests do not certify those platform behaviors or accessibility after custom overrides.
