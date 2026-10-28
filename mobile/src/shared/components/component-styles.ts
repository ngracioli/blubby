import { ReactNode } from 'react';
import { ColorValue, StyleProp, TextStyle, ViewStyle } from 'react-native';

export type StatefulStyle<State, Style extends ViewStyle | TextStyle> = StyleProp<Style> | ((state: State) => StyleProp<Style>);

export function resolveStyle<State, Style extends ViewStyle | TextStyle>(style: StatefulStyle<State, Style> | undefined, state: State) {
  return typeof style === 'function' ? style(state) : style;
}

export type Accessory<State> = ReactNode | ((state: State & { color: ColorValue; size: number }) => ReactNode);

export function renderAccessory<State>(accessory: Accessory<State>, state: State & { color: ColorValue; size: number }) {
  const content = typeof accessory === 'function' ? accessory(state) : accessory;
  return typeof content === 'boolean' || content === '' ? null : content;
}
