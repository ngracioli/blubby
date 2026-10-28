import { ReactNode, Ref, useState } from 'react';
import {
  ActivityIndicator,
  Pressable,
  PressableProps,
  StyleSheet,
  Text,
  TextProps,
  TextStyle,
  View,
  type ViewInstance,
  ViewStyle,
} from 'react-native';
import { ControlAppearance, ControlColors, controlColors, theme } from '@/shared/theme';
import { Accessory, renderAccessory, resolveStyle, StatefulStyle } from './component-styles';

export interface ButtonState {
  pressed: boolean;
  focused: boolean;
  hovered: boolean;
  disabled: boolean;
  loading: boolean;
}

type ButtonContent = {
  children: Exclude<ReactNode, undefined | null | boolean> | ((state: ButtonState) => ReactNode);
  accessibilityLabel?: string;
} | {
  children?: never;
  accessibilityLabel: string;
};

export type ButtonProps = Omit<PressableProps, 'children' | 'style'> & ButtonContent & {
  ref?: Ref<ViewInstance>;
  variant?: 'solid' | 'outline' | 'text';
  tone?: 'primary' | 'error';
  appearance?: ControlAppearance;
  colors?: Partial<ControlColors>;
  size?: number;
  iconSize?: number;
  loading?: boolean;
  loadingLabel?: string;
  leadingIcon?: Accessory<ButtonState>;
  trailingIcon?: Accessory<ButtonState>;
  loadingIndicator?: Accessory<ButtonState>;
  style?: StatefulStyle<ButtonState, ViewStyle>;
  contentStyle?: StatefulStyle<ButtonState, ViewStyle>;
  labelStyle?: StatefulStyle<ButtonState, TextStyle>;
  iconStyle?: StatefulStyle<ButtonState, ViewStyle>;
  labelProps?: Omit<TextProps, 'children' | 'style'>;
};

export function Button({
  ref,
  children,
  variant = 'solid',
  tone = 'primary',
  appearance = 'light',
  colors,
  size = theme.spacing.actionButtonHeight,
  iconSize = 20,
  disabled = false,
  loading = false,
  loadingLabel,
  leadingIcon,
  trailingIcon,
  loadingIndicator,
  style,
  contentStyle,
  labelStyle,
  iconStyle,
  labelProps,
  accessibilityState,
  onFocus,
  onBlur,
  onHoverIn,
  onHoverOut,
  onPress,
  onLongPress,
  ...nativeProps
}: ButtonProps) {
  const [focused, setFocused] = useState(false);
  const [hovered, setHovered] = useState(false);
  const palette = { ...controlColors[appearance], ...colors };
  const blocked = Boolean(disabled || loading || accessibilityState?.disabled || nativeProps['aria-disabled']);
  const accent = tone === 'error' ? palette.error : palette.primary;

  function getState(pressed: boolean): ButtonState {
    return { pressed: !blocked && pressed, focused: !blocked && focused, hovered: !blocked && hovered, disabled: blocked, loading };
  }

  function getForeground() {
    return blocked && !loading ? palette.disabledContent : variant === 'solid' ? palette.onPrimary : accent;
  }

  return (
    <Pressable
      accessibilityRole="button"
      {...nativeProps}
      ref={ref}
      disabled={blocked}
      accessibilityState={{ ...accessibilityState, disabled: blocked, busy: loading || accessibilityState?.busy }}
      aria-disabled={blocked}
      aria-busy={loading || nativeProps['aria-busy'] || accessibilityState?.busy}
      onPress={blocked ? undefined : onPress}
      onLongPress={blocked ? undefined : onLongPress}
      onFocus={(event) => { setFocused(true); onFocus?.(event); }}
      onBlur={(event) => { setFocused(false); onBlur?.(event); }}
      onHoverIn={(event) => { setHovered(true); onHoverIn?.(event); }}
      onHoverOut={(event) => { setHovered(false); onHoverOut?.(event); }}
      style={({ pressed }) => {
        const state = getState(pressed);
        const backgroundColor = blocked && !loading
          ? palette.disabledBackground
          : variant !== 'solid' ? 'transparent'
          : state.pressed && tone === 'primary' ? palette.pressed : accent;
        return [
          styles.root,
          {
            minHeight: size,
            backgroundColor,
            borderWidth: variant === 'outline' ? 1 : 0,
            borderColor: blocked && !loading ? palette.disabledContent : accent,
            outlineWidth: state.focused ? 2 : 0,
            outlineColor: palette.focus,
          },
          resolveStyle(style, state),
        ];
      }}
    >
      {({ pressed }) => {
        const state = getState(pressed);
        const foreground = getForeground();
        const accessoryState = { ...state, color: foreground, size: iconSize };
        const content = loading && loadingLabel ? loadingLabel
          : typeof children === 'function' ? children(state) : children;
        const leading = loading
          ? loadingIndicator === undefined
            ? loadingLabel ? null : <ActivityIndicator color={foreground} size="small" accessible={false} />
            : renderAccessory(loadingIndicator, accessoryState)
          : renderAccessory(leadingIcon, accessoryState);
        const trailing = renderAccessory(trailingIcon, accessoryState);

        return (
          <View style={[styles.content, resolveStyle(contentStyle, state)]}>
            {leading != null && <View aria-hidden accessibilityElementsHidden importantForAccessibility="no-hide-descendants" style={resolveStyle(iconStyle, state)}>{leading}</View>}
            {typeof content === 'string' || typeof content === 'number' ? (
              <Text {...labelProps} style={[styles.label, { color: foreground }, resolveStyle(labelStyle, state)]}>
                {content}
              </Text>
            ) : content}
            {trailing != null && <View aria-hidden accessibilityElementsHidden importantForAccessibility="no-hide-descendants" style={resolveStyle(iconStyle, state)}>{trailing}</View>}
          </View>
        );
      }}
    </Pressable>
  );
}

const styles = StyleSheet.create({
  root: {
    justifyContent: 'center',
    borderRadius: theme.radii.button,
    paddingHorizontal: theme.spacing.base,
    paddingVertical: theme.spacing.md,
    outlineStyle: 'solid',
    outlineOffset: 2,
  },
  content: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    gap: theme.spacing.sm,
  },
  label: {
    ...theme.typography.button,
    flexShrink: 1,
    textAlign: 'center',
  },
});
