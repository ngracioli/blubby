import { render, screen } from '@testing-library/react-native';
import { Text } from 'react-native';
import { ScreenContainer } from '@/shared/components';

jest.mock('react-native-safe-area-context', () => ({
  useSafeAreaInsets: () => ({ top: 44, bottom: 34, left: 4, right: 6 }),
}));

it.each([true, false])('applies root and content overrides when scrollable=%s', (scrollable) => {
  render(
    <ScreenContainer
      testID="screen"
      scrollable={scrollable}
      style={{ backgroundColor: '#123456', paddingTop: 12 }}
      contentContainerProps={{ testID: 'content' }}
      contentContainerStyle={{ padding: 19, maxWidth: 360 }}
    >
      <Text>Conteúdo</Text>
    </ScreenContainer>,
  );

  expect(screen.getByTestId('screen')).toHaveStyle({ backgroundColor: '#123456', paddingTop: 12, paddingBottom: 34, paddingLeft: 4, paddingRight: 6 });
  expect(screen.getByTestId('content')).toHaveStyle({ padding: 19, maxWidth: 360 });
  expect(screen.getByText('Conteúdo')).toBeOnTheScreen();
});
