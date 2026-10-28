# ScreenContainer

`ScreenContainer` owns screen safe areas, centered content, optional scrolling, and keyboard avoidance. It composes React Native primitives without an additional behavioral pattern. It requires a `SafeAreaProvider` above it.

The existing theme and screen layout remain the visual baseline. This contract repair does not introduce a new visual preset; screen references are listed in the [Figma reference map](../../../docs/design/figma-reference-map.md).

## Props

| Prop | Type | Default and behavior |
| --- | --- | --- |
| `children` | `ReactNode` | Required. Rendered inside the centered content view in both modes. |
| `ref` | `Ref<ViewInstance>` | Unset. Forwarded to the outer native view. |
| `scrollable` | `boolean` | `true`. Uses a `ScrollView`; `false` uses a static wrapper. |
| `style` | `StyleProp<ViewStyle>` | No override. Styles the outer root `View`, including its background and safe-area padding. |
| `contentContainerStyle` | `StyleProp<ViewStyle>` | No override. Styles the inner content `View` in both modes. Defaults are `flexGrow: 1`, `width: '100%'`, and the `maxWidth` prop. |
| `contentContainerProps` | `Omit<ViewProps, 'children' \| 'style'>` | Unset. Native props for the inner content view, including accessibility, `testID`, and `onLayout`. |
| `scrollStyle` | `StyleProp<ViewStyle>` | No override. Styles the `ScrollView` viewport; ignored in static mode. |
| `scrollViewProps` | `Omit<ScrollViewProps, 'children' \| 'contentContainerStyle' \| 'style'>` | No override. Forwards native scroll props. Defaults are `keyboardShouldPersistTaps="handled"` and `showsVerticalScrollIndicator={false}`; supplied values override these defaults. Ignored in static mode. |
| `keyboardStyle` | `StyleProp<ViewStyle>` | No override. Styles the `KeyboardAvoidingView`, whose default is `flex: 1`. |
| `keyboardAvoidingViewProps` | `Omit<KeyboardAvoidingViewProps, 'children' \| 'style'>` | No override. Forwards native keyboard avoidance props. Default `behavior` is `padding` on iOS and `undefined` elsewhere; supplied values override it. |
| `includeTopInset` | `boolean` | `true`. Applies the device's safe-area top inset to the root. |
| `includeBottomInset` | `boolean` | `true`. Applies the device's safe-area bottom inset to the root. |
| `maxWidth` | `number` | `480`. Limits the centered inner content width. |
| `backgroundColor` | `string` | `theme.colors.background`. Sets the root background. |
| Other native view props | `Omit<ViewProps, 'children' \| 'style'>` | Forwarded to the outer root, including `testID`, accessibility props, and `onLayout`. |

## Styling and layout

The root fills its parent and applies left/right safe-area padding unconditionally. Top and bottom padding follow the inset flags. `style` applies last, so callers can explicitly override any of these values.

The keyboard wrapper fills the root. In scroll mode, the scroll viewport fills that wrapper and its native content container uses `flexGrow: 1` and horizontal centering. In static mode, the static wrapper uses `flex: 1` and horizontal centering. The same inner content view receives `contentContainerStyle` in both modes. Its overrides apply after the default width, flex growth, and `maxWidth`. Inner content uses intrinsic height rather than a zero flex basis, allowing tall content to extend the scroll content.

Style destinations are separate: use `style` for the screen root, `contentContainerStyle` for child layout, `scrollStyle` for the scroll viewport, and `keyboardStyle` for keyboard wrapper layout. Native prop bags cannot replace managed children or styles through the public TypeScript API.

## Usage

```tsx
import { ScreenContainer } from '@/shared/components';

<ScreenContainer testID="profile-screen">
  <ProfileContent />
</ScreenContainer>
```

```tsx
<ScreenContainer
  scrollable={false}
  includeTopInset={false}
  maxWidth={640}
  style={{ backgroundColor: '#FFFFFF' }}
  contentContainerStyle={{ paddingHorizontal: 24, justifyContent: 'center' }}
  keyboardAvoidingViewProps={{ keyboardVerticalOffset: 64 }}
>
  <LoginForm />
</ScreenContainer>
```

```tsx
<ScreenContainer
  scrollStyle={{ marginHorizontal: 8 }}
  scrollViewProps={{ keyboardDismissMode: 'on-drag', showsVerticalScrollIndicator: true }}
  keyboardAvoidingViewProps={{ enabled: false }}
>
  <LongForm />
</ScreenContainer>
```

## Limitations and compatibility

- `style` now consistently targets the root. Callers that previously used it for the scroll viewport must use `scrollStyle`; callers styling static child layout must use `contentContainerStyle`.
- `contentContainerStyle` targets the centered inner view, not the native scroll content container. It now works in static mode as well. The managed scroll container keeps its fill and centering rules.
- Static mode does not provide overflow scrolling. Use scroll mode for content that can exceed the available height, including enlarged text.
- Keyboard avoidance depends on the platform, keyboard, and surrounding navigation. Screens configure offsets and dismissal through the native prop bags and must verify these on supported devices.
- Explicit style overrides can remove safe padding, centering, maximum width, or usable space. Consumers own the accessibility and layout consequences of such overrides.
- This component does not manage form state, validation, navigation, or input focus. Native screen-reader and keyboard behavior still require device verification.
