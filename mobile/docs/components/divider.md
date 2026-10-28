# Divider

`Divider` renders a decorative horizontal rule with optional content between two lines. It uses the divider in [Welcome, node 88:360](https://www.figma.com/design/QYrBUue2AdyrjvJLiMu7F2/blubby?node-id=88-360). It has no business logic or additional behavioral pattern.

## Public props

| Prop | Type | Default and contract |
| --- | --- | --- |
| `children` | `ReactNode` | Unset. Strings and numbers use the built-in label. Custom nodes render directly. Absent, boolean, null, or empty-string content produces one uninterrupted line. |
| `ref` | `Ref<ViewInstance>` | Unset. Forwarded to the root native view. |
| `color` | `ColorValue` | `theme.colors.border`. Sets line color. |
| `thickness` | `number` | `1`. Line height in native layout units. |
| `gap` | `number` | `12`. Space between lines and content. |
| `style` | `StyleProp<ViewStyle>` | Unset. Root layout override, including width, minimum height, and spacing. |
| `lineStyle` | `StyleProp<ViewStyle>` | Unset. Final override for each line. |
| `labelStyle` | `StyleProp<TextStyle>` | Unset. Final override for built-in label typography and color. |
| `labelProps` | `Omit<TextProps, 'children' \| 'style'>` | Unset. Native props for the built-in label. |
| Other native view props | `Omit<ViewProps, 'children'>` | Forwarded to root, including accessibility, `testID`, and layout events. |

The root defaults to a horizontal, vertically centered row with minimum height `24`. Lines grow to fill remaining width; labels can shrink and wrap. Native style arrays are supported. Consumer part styles apply after defaults and numeric/color props. Root styling does not recolor the label or lines. Lines are hidden from native and web accessibility trees; label content remains available. Custom content owns its own styling and accessible name.

```tsx
import { Divider } from '@/shared/components';

<Divider>ou continue com</Divider>
<Divider color="#64748B" thickness={2} style={{ marginVertical: 24 }} />
<Divider gap={16} labelStyle={{ fontSize: 16 }}>Outras opções</Divider>
```

The rule does not manage vertical screen spacing, theme subscriptions, or navigation. The caller owns contrast and layout after visual overrides.
