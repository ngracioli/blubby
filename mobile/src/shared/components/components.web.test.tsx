import { act, createRef } from 'react';
import { createRoot, Root } from 'react-dom/client';
import { Text, TextInput } from 'react-native';
import { Button, Input } from '@/shared/components';

let container: HTMLDivElement;
let root: Root;

beforeAll(() => {
  (globalThis as { IS_REACT_ACT_ENVIRONMENT?: boolean }).IS_REACT_ACT_ENVIRONMENT = true;
});

beforeEach(() => {
  container = document.createElement('div');
  document.body.appendChild(container);
  root = createRoot(container);
});

afterEach(() => {
  act(() => root.unmount());
  container.remove();
});

it('hides decorative icon labels from the web accessibility tree', () => {
  act(() => root.render(<Button leadingIcon={<Text accessibilityLabel="Ícone decorativo">+</Text>}>Salvar</Button>));
  const icon = container.querySelector('[aria-label="Ícone decorativo"]');
  expect(Boolean(icon?.closest('[aria-hidden="true"]'))).toBe(true);
});

it('connects input errors through the native id alias and supports ref-driven focus', () => {
  const ref = createRef<TextInput>();
  act(() => root.render(
    <Input
      ref={ref}
      label="Email"
      value="inválido"
      errorMessage="Email inválido"
      aria-describedby="external-hint"
      helperTextProps={{ id: 'email-error', nativeID: 'ignored-id' }}
    />,
  ));
  const input = container.querySelector('input') as HTMLInputElement;
  expect(input.getAttribute('aria-label')).toBe('Email');
  expect(input.getAttribute('aria-invalid')).toBe('true');
  expect(input.getAttribute('aria-describedby')).toBe('external-hint email-error');
  expect(document.getElementById('email-error')?.textContent).toBe('Email inválido');
  act(() => ref.current?.focus());
  expect(document.activeElement === input).toBe(true);
  expect(input.getAttribute('aria-invalid')).toBe('true');
  act(() => ref.current?.blur());
  expect(document.activeElement === input).toBe(false);
});

it('distinguishes read-only and disabled native DOM inputs', () => {
  act(() => root.render(<Input label="Identificador" readOnly />));
  let input = container.querySelector('input') as HTMLInputElement;
  expect(input.readOnly).toBe(true);
  expect(input.getAttribute('aria-readonly')).toBe('true');
  expect(input.getAttribute('aria-disabled')).not.toBe('true');
  expect(input.disabled).toBe(false);

  act(() => root.render(<Input label="Identificador" disabled editable aria-disabled={false} />));
  input = container.querySelector('input') as HTMLInputElement;
  expect(input.readOnly).toBe(true);
  expect(input.getAttribute('aria-disabled')).toBe('true');
});
