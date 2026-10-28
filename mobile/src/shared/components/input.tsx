import { Ref, useId, useState } from 'react';
import {
  StyleSheet,
  Platform,
  Text,
  TextInput,
  type TextInputInstance,
  TextInputProps,
  TextProps,
  TextStyle,
  View,
  ViewStyle,
} from 'react-native';
import { ControlAppearance, ControlColors, controlColors, theme } from '@/shared/theme';
import { Accessory, renderAccessory, resolveStyle, StatefulStyle } from './component-styles';

export interface InputState {
  focused: boolean;
  disabled: boolean;
  readOnly: boolean;
  invalid: boolean;
}

export interface InputProps extends Omit<TextInputProps, 'style' | 'children'> {
  ref?: Ref<TextInputInstance>;
  label?: string;
  helperText?: string;
  errorMessage?: string;
  invalid?: boolean;
  disabled?: boolean;
  appearance?: ControlAppearance;
  colors?: Partial<ControlColors>;
  size?: number;
  iconSize?: number;
  leadingAccessory?: Accessory<InputState>;
  trailingAccessory?: Accessory<InputState>;
  style?: StatefulStyle<InputState, ViewStyle>;
  controlStyle?: StatefulStyle<InputState, ViewStyle>;
  inputStyle?: StatefulStyle<InputState, TextStyle>;
  labelStyle?: StatefulStyle<InputState, TextStyle>;
  helperTextStyle?: StatefulStyle<InputState, TextStyle>;
  labelProps?: Omit<TextProps, 'children' | 'style'>;
  helperTextProps?: Omit<TextProps, 'children' | 'style'>;
  'aria-describedby'?: string;
  'aria-invalid'?: boolean;
  'aria-readonly'?: boolean;
}

export function Input({
  ref,
  label,
  helperText,
  errorMessage,
  invalid = false,
  disabled = false,
  readOnly = false,
  editable = true,
  appearance = 'light',
  colors,
  size = theme.spacing.actionButtonHeight,
  iconSize = 20,
  leadingAccessory,
  trailingAccessory,
  style,
  controlStyle,
  inputStyle,
  labelStyle,
  helperTextStyle,
  labelProps,
  helperTextProps,
  accessibilityLabel,
  accessibilityHint,
  accessibilityState,
  onFocus,
  onBlur,
  'aria-describedby': describedBy,
  'aria-invalid': ariaInvalid,
  'aria-readonly': ariaReadOnly,
  ...nativeProps
}: InputProps) {
  const [focused, setFocused] = useState(false);
  const id = useId();
  const palette = { ...controlColors[appearance], ...colors };
  const blocked = Boolean(disabled || accessibilityState?.disabled || nativeProps['aria-disabled']);
  const hasError = invalid || Boolean(errorMessage) || Boolean(ariaInvalid);
  const effectiveReadOnly = readOnly || !editable || Boolean(ariaReadOnly);
  const state: InputState = { focused: focused && !blocked, disabled: blocked, readOnly: effectiveReadOnly, invalid: hasError };
  const message = hasError && errorMessage ? errorMessage : helperText;
  const foreground = blocked ? palette.disabledContent : palette.text;
  const accessoryState = { ...state, color: foreground, size: iconSize };
  const leading = renderAccessory(leadingAccessory, accessoryState);
  const trailing = renderAccessory(trailingAccessory, accessoryState);
  const messageId = helperTextProps?.id ?? helperTextProps?.nativeID ?? `${id}-message`;
  const webProps = Platform.OS === 'web' ? {
    'aria-invalid': hasError,
    'aria-readonly': effectiveReadOnly,
    'aria-describedby': [describedBy, message != null ? messageId : undefined].filter(Boolean).join(' ') || undefined,
  } : {};

  return (
    <View style={[styles.root, resolveStyle(style, state)]}>
      {label != null && (
        <Text {...labelProps} nativeID={labelProps?.nativeID ?? `${id}-label`} style={[styles.label, { color: palette.text }, resolveStyle(labelStyle, state)]}>
          {label}
        </Text>
      )}
      <View style={[
        styles.control,
        {
          minHeight: size,
          backgroundColor: blocked ? palette.disabledBackground : palette.surface,
          borderColor: hasError ? palette.error : state.focused ? palette.focus : palette.border,
          borderWidth: state.focused && !hasError ? 2 : 1,
        },
        resolveStyle(controlStyle, state),
      ]}>
        {leading}
        <TextInput
          placeholderTextColor={palette.secondary}
          selectionColor={palette.focus}
          underlineColorAndroid="transparent"
          {...nativeProps}
          {...webProps}
          ref={ref}
          editable={!blocked && !effectiveReadOnly}
          readOnly={blocked || effectiveReadOnly}
          accessibilityLabel={accessibilityLabel ?? label}
          accessibilityHint={[accessibilityHint, message].filter(Boolean).join('. ') || undefined}
          accessibilityState={{ ...accessibilityState, disabled: blocked }}
          aria-disabled={blocked}
          onFocus={(event) => { setFocused(true); onFocus?.(event); }}
          onBlur={(event) => { setFocused(false); onBlur?.(event); }}
          style={[styles.input, { color: foreground }, resolveStyle(inputStyle, state)]}
        />
        {trailing}
      </View>
      {message != null && (
        <Text
          accessibilityLiveRegion={hasError ? 'polite' : 'none'}
          {...helperTextProps}
          nativeID={messageId}
          style={[styles.helperText, { color: hasError ? palette.error : palette.secondary }, resolveStyle(helperTextStyle, state)]}
        >
          {message}
        </Text>
      )}
    </View>
  );
}

const styles = StyleSheet.create({
  root: { gap: theme.spacing.sm },
  label: { ...theme.typography.label },
  control: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: theme.spacing.sm,
    borderRadius: theme.radii.button,
    paddingHorizontal: theme.spacing.base,
    paddingVertical: theme.spacing.md,
  },
  input: {
    ...theme.typography.body,
    flex: 1,
    minWidth: 0,
    padding: 0,
  },
  helperText: { ...theme.typography.bodySmall },
});
