import { colors } from './colors';
import { typography, fontFamilies } from './typography';
import { spacing } from './spacing';
import { radii } from './radii';
import { shadows } from './shadows';

export const theme = {
  colors,
  typography,
  fontFamilies,
  spacing,
  radii,
  shadows,
} as const;

export type Theme = typeof theme;

export * from './colors';
export * from './typography';
export * from './spacing';
export * from './radii';
export * from './shadows';
export * from './control-colors';
