import { TextStyle } from 'react-native';

export const fontFamilies = {
  regular: 'Manrope_400Regular',
  medium: 'Manrope_500Medium',
  semiBold: 'Manrope_600SemiBold',
  bold: 'Manrope_700Bold',
} as const;

export const typography = {
  amount: {
    fontFamily: fontFamilies.bold,
    fontSize: 32,
    lineHeight: 40,
    fontWeight: '700',
    letterSpacing: -0.4,
  },
  amountCompact: {
    fontFamily: fontFamilies.bold,
    fontSize: 24,
    lineHeight: 32,
    fontWeight: '700',
    letterSpacing: -0.2,
  },
  amountRow: {
    fontFamily: fontFamilies.semiBold,
    fontSize: 16,
    lineHeight: 24,
    fontWeight: '600',
  },
  heading1: {
    fontFamily: fontFamilies.bold,
    fontSize: 28,
    lineHeight: 36,
    fontWeight: '700',
    letterSpacing: -0.3,
  },
  heading2: {
    fontFamily: fontFamilies.bold,
    fontSize: 22,
    lineHeight: 28,
    fontWeight: '700',
  },
  heading3: {
    fontFamily: fontFamilies.semiBold,
    fontSize: 20,
    lineHeight: 28,
    fontWeight: '600',
  },
  body: {
    fontFamily: fontFamilies.regular,
    fontSize: 16,
    lineHeight: 24,
    fontWeight: '400',
  },
  bodyStrong: {
    fontFamily: fontFamilies.semiBold,
    fontSize: 16,
    lineHeight: 24,
    fontWeight: '600',
  },
  bodySmall: {
    fontFamily: fontFamilies.regular,
    fontSize: 14,
    lineHeight: 20,
    fontWeight: '400',
  },
  label: {
    fontFamily: fontFamilies.semiBold,
    fontSize: 14,
    lineHeight: 20,
    fontWeight: '600',
  },
  button: {
    fontFamily: fontFamilies.semiBold,
    fontSize: 16,
    lineHeight: 24,
    fontWeight: '600',
  },
  caption: {
    fontFamily: fontFamilies.medium,
    fontSize: 12,
    lineHeight: 16,
    fontWeight: '500',
  },
} as const satisfies Record<string, TextStyle>;

export type TypographyToken = keyof typeof typography;
