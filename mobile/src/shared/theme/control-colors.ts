import { ColorValue } from 'react-native';
import { colors } from './colors';

export interface ControlColors {
  surface: ColorValue;
  text: ColorValue;
  secondary: ColorValue;
  primary: ColorValue;
  onPrimary: ColorValue;
  pressed: ColorValue;
  border: ColorValue;
  focus: ColorValue;
  error: ColorValue;
  disabledBackground: ColorValue;
  disabledContent: ColorValue;
}

// Figma Button 24:26 e Field 24:119, incluindo os estados Light e Dark.
export const controlColors: Record<'light' | 'dark', ControlColors> = {
  light: {
    surface: colors.surface,
    text: colors.textPrimary,
    secondary: colors.textSecondary,
    primary: colors.primary,
    onPrimary: colors.textInverse,
    pressed: colors.primaryPressed,
    border: '#64748B',
    focus: '#0E7490',
    error: '#B91C1C',
    disabledBackground: '#E2E8F0',
    disabledContent: '#475569',
  },
  dark: {
    surface: '#1E293B',
    text: '#F8FAFC',
    secondary: '#CBD5E1',
    primary: '#67E8F9',
    onPrimary: '#0F172A',
    pressed: '#22D3EE',
    border: '#64748B',
    focus: '#67E8F9',
    error: '#FCA5A5',
    disabledBackground: '#334155',
    disabledContent: '#CBD5E1',
  },
};

export type ControlAppearance = keyof typeof controlColors;
