export const radii = {
  none: 0,
  xs: 4,
  sm: 8,
  md: 12,
  lg: 16,
  xl: 24,
  full: 9999,

  button: 12,
  card: 16,
  container: 24,
  badge: 8,
} as const;

export type RadiusToken = keyof typeof radii;
