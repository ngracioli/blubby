import React, { ReactNode } from 'react';
import {
  KeyboardAvoidingView,
  Platform,
  ScrollView,
  StyleProp,
  StyleSheet,
  View,
  ViewStyle,
} from 'react-native';
import { useSafeAreaInsets } from 'react-native-safe-area-context';
import { theme } from '../theme';

export interface ScreenContainerProps {
  children: ReactNode;
  scrollable?: boolean;
  style?: StyleProp<ViewStyle>;
  contentContainerStyle?: StyleProp<ViewStyle>;
  includeTopInset?: boolean;
  includeBottomInset?: boolean;
  maxWidth?: number;
  backgroundColor?: string;
}

export function ScreenContainer({
  children,
  scrollable = true,
  style,
  contentContainerStyle,
  includeTopInset = true,
  includeBottomInset = true,
  maxWidth = 480,
  backgroundColor = theme.colors.background,
}: ScreenContainerProps) {
  const insets = useSafeAreaInsets();

  const containerPadding = {
    paddingTop: includeTopInset ? insets.top : 0,
    paddingBottom: includeBottomInset ? insets.bottom : 0,
    paddingLeft: insets.left,
    paddingRight: insets.right,
  };

  const innerContent = (
    <View style={[styles.centerWrapper, { maxWidth }]}>
      {children}
    </View>
  );

  return (
    <View style={[styles.root, { backgroundColor }, containerPadding]}>
      <KeyboardAvoidingView
        style={styles.keyboardAvoid}
        behavior={Platform.OS === 'ios' ? 'padding' : undefined}
      >
        {scrollable ? (
          <ScrollView
            style={[styles.scroll, style]}
            contentContainerStyle={[styles.scrollContent, contentContainerStyle]}
            keyboardShouldPersistTaps="handled"
            showsVerticalScrollIndicator={false}
          >
            {innerContent}
          </ScrollView>
        ) : (
          <View style={[styles.staticContent, style]}>
            {innerContent}
          </View>
        )}
      </KeyboardAvoidingView>
    </View>
  );
}

const styles = StyleSheet.create({
  root: {
    flex: 1,
  },
  keyboardAvoid: {
    flex: 1,
  },
  scroll: {
    flex: 1,
  },
  scrollContent: {
    flexGrow: 1,
    alignItems: 'center',
  },
  staticContent: {
    flex: 1,
    alignItems: 'center',
  },
  centerWrapper: {
    width: '100%',
    flex: 1,
  },
});
