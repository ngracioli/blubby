export const colors = {
  background: '#F8FAFC',
  surface: '#FFFFFF',
  surfaceSubtle: '#F1F5F9',
  surfaceRaised: '#FFFFFF',

  textPrimary: '#0F172A',
  textSecondary: '#475569',
  textMuted: '#64748B',
  textInverse: '#FFFFFF',

  border: '#E2E8F0',
  borderSubtle: '#F1F5F9',

  primary: '#1E3A8A',
  primaryPressed: '#172554',
  primaryLight: '#DBEAFE',

  projection: '#0E7490',
  projectionLight: '#CFFAFE',

  income: '#047857',
  incomeLight: '#D1FAE5',

  expense: '#B91C1C',
  expenseLight: '#FEE2E2',

  warning: '#D97706',
  warningLight: '#FEF3C7',
} as const;

export type ColorToken = keyof typeof colors;
