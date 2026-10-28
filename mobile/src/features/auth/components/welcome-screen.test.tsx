import { fireEvent, render, screen } from '@testing-library/react-native';
import { WelcomeScreen } from './welcome-screen';

jest.mock('react-native-safe-area-context', () => ({
  useSafeAreaInsets: () => ({ top: 44, bottom: 34, left: 0, right: 0 }),
}));

it('exposes native shared buttons and forwards feature callbacks', () => {
  const onCreateAccount = jest.fn();
  const onSignIn = jest.fn();
  const onGoogleSignIn = jest.fn();
  const onAppleSignIn = jest.fn();
  render(<WelcomeScreen {...{ onCreateAccount, onSignIn, onGoogleSignIn, onAppleSignIn }} />);

  const actions = [
    ['Criar conta', onCreateAccount],
    ['Acessar minha conta', onSignIn],
    ['Continuar com Google', onGoogleSignIn],
    ['Continuar com a Apple', onAppleSignIn],
  ] as const;
  for (const [label, expectedCallback] of actions) {
    for (const [, callback] of actions) callback.mockClear();
    fireEvent.press(screen.getByRole('button', { name: label }));
    for (const [, callback] of actions) expect(callback).toHaveBeenCalledTimes(callback === expectedCallback ? 1 : 0);
  }
  for (const label of ['Continuar com Google', 'Continuar com a Apple']) {
    expect(screen.getByText(label)).toHaveStyle({ fontFamily: 'GoogleSansFlex_500Medium' });
  }
});
