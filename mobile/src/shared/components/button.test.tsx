import { Text } from 'react-native';
import { fireEvent, render } from '@testing-library/react-native';
import { Button } from '@/shared/components';

describe('Button', () => {
  it('forwards enabled press and long press events', () => {
    const onPress = jest.fn();
    const onLongPress = jest.fn();
    const event = { nativeEvent: { timestamp: 10 } };
    const { getByRole } = render(<Button onPress={onPress} onLongPress={onLongPress}>Salvar</Button>);
    const button = getByRole('button', { name: 'Salvar' });

    fireEvent.press(button, event);
    fireEvent(button, 'longPress', event);

    expect(onPress).toHaveBeenCalledWith(event);
    expect(onLongPress).toHaveBeenCalledWith(event);
  });

  it.each(['disabled', 'loading'] as const)('blocks activation when %s despite visual and accessibility overrides', (property) => {
    const onPress = jest.fn();
    const onLongPress = jest.fn();
    const { getByRole } = render(
      <Button
        {...{ [property]: true }}
        aria-disabled={false}
        accessibilityState={{ disabled: false }}
        style={{ opacity: 1 }}
        onPress={onPress}
        onLongPress={onLongPress}
      >Salvar</Button>,
    );
    const button = getByRole('button');

    fireEvent.press(button);
    fireEvent(button, 'longPress');

    expect(onPress).not.toHaveBeenCalled();
    expect(onLongPress).not.toHaveBeenCalled();
    expect(button).toBeDisabled();
    expect(button).toHaveStyle({ opacity: 1 });
    if (property === 'loading') expect(button).toBeBusy();
  });

  it('customizes a labelled icon-only button from its pressed state', () => {
    const style = jest.fn(() => ({ minHeight: 72 }));
    const leadingIcon = jest.fn(() => <Text>+</Text>);
    const { getByRole, getByText } = render(
      <Button accessibilityLabel="Adicionar" testOnly_pressed iconSize={28} style={style} leadingIcon={leadingIcon} />,
    );

    expect(getByRole('button', { name: 'Adicionar' })).toHaveStyle({ minHeight: 72 });
    expect(style).toHaveBeenLastCalledWith(expect.objectContaining({ pressed: true, disabled: false }));
    expect(leadingIcon).toHaveBeenLastCalledWith(expect.objectContaining({ size: 28, pressed: true }));
    expect(getByText('+', { includeHiddenElements: true })).toBeOnTheScreen();
  });
});
