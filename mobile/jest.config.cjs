const aliases = { '^@/(.*)$': '<rootDir>/src/$1' };

module.exports = {
  projects: [
    {
      displayName: 'native',
      preset: 'jest-expo',
      moduleNameMapper: aliases,
      testMatch: ['<rootDir>/src/**/*.test.ts?(x)'],
      testPathIgnorePatterns: ['\\.web\\.test\\.tsx$'],
    },
    {
      displayName: 'web',
      preset: 'jest-expo/web',
      moduleNameMapper: { '^react-native$': 'react-native-web', ...aliases },
      testMatch: ['<rootDir>/src/**/*.web.test.tsx'],
    },
  ],
};
