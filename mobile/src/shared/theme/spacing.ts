export const spacing = {
  none: 0,
  xxs: 2,
  xs: 4,
  sm: 8,
  md: 12,
  base: 16,
  lg: 20,
  xl: 24,
  xxl: 32,
  xxxl: 40,
  huge: 48,

  screenPaddingHorizontal: 24,
  screenPaddingVertical: 16,
  cardPadding: 16,
  actionButtonHeight: 52,
} as const;

export type SpacingToken = keyof typeof spacing;
