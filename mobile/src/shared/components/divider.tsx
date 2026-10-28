import { ReactNode, Ref } from 'react';
import { ColorValue, StyleProp, StyleSheet, Text, TextProps, TextStyle, View, ViewProps, ViewStyle } from 'react-native';
import { theme } from '@/shared/theme';

export interface DividerProps extends Omit<ViewProps, 'children'> {
  ref?: Ref<View>;
  children?: ReactNode;
  color?: ColorValue;
  thickness?: number;
  gap?: number;
  lineStyle?: StyleProp<ViewStyle>;
  labelStyle?: StyleProp<TextStyle>;
  labelProps?: Omit<TextProps, 'children' | 'style'>;
}

export function Divider({
  children,
  color = theme.colors.border,
  thickness = 1,
  gap = theme.spacing.md,
  style,
  lineStyle,
  labelStyle,
  labelProps,
  ...viewProps
}: DividerProps) {
  const line = <View aria-hidden accessibilityElementsHidden importantForAccessibility="no-hide-descendants" style={[styles.line, { backgroundColor: color, height: thickness }, lineStyle]} />;
  const hasLabel = children != null && typeof children !== 'boolean' && children !== '';

  return (
    <View {...viewProps} style={[styles.root, { gap }, style]}>
      {line}
      {hasLabel && (typeof children === 'string' || typeof children === 'number'
        ? <Text {...labelProps} style={[styles.label, labelStyle]}>{children}</Text>
        : children)}
      {hasLabel && line}
    </View>
  );
}

const styles = StyleSheet.create({
  root: { flexDirection: 'row', alignItems: 'center', minHeight: 24 },
  line: { flex: 1 },
  label: { ...theme.typography.bodySmall, color: theme.colors.textSecondary, flexShrink: 1, textAlign: 'center' },
});
