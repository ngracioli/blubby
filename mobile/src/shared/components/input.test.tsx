import { fireEvent, render } from '@testing-library/react-native';
import { Input } from '@/shared/components';

describe('Input', () => {
  it('forwards editing and focus events while preserving controlled values', () => {
    const onChangeText = jest.fn();
    const onFocus = jest.fn();
    const onBlur = jest.fn();
    const event = { nativeEvent: {} };
    const { getByLabelText, rerender } = render(
      <Input label="Email" value="first@example.com" onChangeText={onChangeText} onFocus={onFocus} onBlur={onBlur} />,
    );
    const input = getByLabelText('Email');

    fireEvent.changeText(input, 'next@example.com');
    fireEvent(input, 'focus', event);
    fireEvent(input, 'blur', event);

    expect(onChangeText).toHaveBeenCalledWith('next@example.com');
    expect(onFocus).toHaveBeenCalledWith(event);
    expect(onBlur).toHaveBeenCalledWith(event);
    expect(input).toHaveProp('value', 'first@example.com');

    rerender(<Input label="Email" value="next@example.com" onChangeText={onChangeText} />);
    expect(getByLabelText('Email')).toHaveProp('value', 'next@example.com');
  });

  it.each(['disabled', 'readOnly'] as const)('keeps %s authoritative over editable and accessibility overrides', (property) => {
    const { getByLabelText } = render(
      <Input label="Nome" {...{ [property]: true }} editable aria-disabled={false} accessibilityState={{ disabled: false }} />,
    );
    const input = getByLabelText('Nome');

    expect(input).toHaveProp('editable', false);
    expect(input).toHaveProp('readOnly', true);
    expect(input).toHaveProp('accessibilityState', expect.objectContaining({ disabled: property === 'disabled' }));
  });

  it('announces errors instead of helper text and preserves invalid state during focus customization', () => {
    const inputStyle = jest.fn(() => ({ fontSize: 20 }));
    const { getByLabelText, getByText, queryByText } = render(
      <Input label="Email" accessibilityHint="Obrigatório" helperText="Use email pessoal" errorMessage="Email inválido" inputStyle={inputStyle} />,
    );
    const input = getByLabelText('Email');

    expect(input).toHaveProp('accessibilityHint', 'Obrigatório. Email inválido');
    expect(getByText('Email inválido')).toHaveProp('accessibilityLiveRegion', 'polite');
    expect(queryByText('Use email pessoal')).toBeNull();
    expect(input).toHaveStyle({ fontSize: 20 });

    fireEvent(input, 'focus', { nativeEvent: {} });
    expect(inputStyle).toHaveBeenLastCalledWith(expect.objectContaining({ focused: true, invalid: true }));
    fireEvent(input, 'blur', { nativeEvent: {} });
    expect(inputStyle).toHaveBeenLastCalledWith(expect.objectContaining({ focused: false, invalid: true }));
  });
});
