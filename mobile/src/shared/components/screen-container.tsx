import { ReactNode, Ref } from 'react';
import {
  KeyboardAvoidingView,
  KeyboardAvoidingViewProps,
  Platform,
  ScrollView,
  ScrollViewProps,
  StyleProp,
  StyleSheet,
  View,
  ViewProps,
  ViewStyle,
} from 'react-native';
import { useSafeAreaInsets } from 'react-native-safe-area-context';
import { theme } from '@/shared/theme';

export interface ScreenContainerProps extends Omit<ViewProps, 'children' | 'style'> {
  children: ReactNode;
  ref?: Ref<View>;
  scrollable?: boolean;
  style?: StyleProp<ViewStyle>;
  contentContainerStyle?: StyleProp<ViewStyle>;
  contentContainerProps?: Omit<ViewProps, 'children' | 'style'>;
  scrollStyle?: StyleProp<ViewStyle>;
  scrollViewProps?: Omit<ScrollViewProps, 'children' | 'contentContainerStyle' | 'style'>;
  keyboardStyle?: StyleProp<ViewStyle>;
  keyboardAvoidingViewProps?: Omit<KeyboardAvoidingViewProps, 'children' | 'style'>;
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
  contentContainerProps,
  scrollStyle,
  scrollViewProps,
  keyboardStyle,
  keyboardAvoidingViewProps,
  includeTopInset = true,
  includeBottomInset = true,
  maxWidth = 480,
  backgroundColor = theme.colors.background,
  ...viewProps
}: ScreenContainerProps) {
  const insets = useSafeAreaInsets();

  const containerPadding = {
    paddingTop: includeTopInset ? insets.top : 0,
    paddingBottom: includeBottomInset ? insets.bottom : 0,
    paddingLeft: insets.left,
    paddingRight: insets.right,
  };

  const innerContent = (
    <View {...contentContainerProps} style={[styles.centerWrapper, { maxWidth }, contentContainerStyle]}>
      {children}
    </View>
  );

  return (
    <View {...viewProps} style={[styles.root, { backgroundColor }, containerPadding, style]}>
      <KeyboardAvoidingView
        behavior={Platform.OS === 'ios' ? 'padding' : undefined}
        {...keyboardAvoidingViewProps}
        style={[styles.keyboardAvoid, keyboardStyle]}
      >
        {scrollable ? (
          <ScrollView
            keyboardShouldPersistTaps="handled"
            showsVerticalScrollIndicator={false}
            {...scrollViewProps}
            style={[styles.scroll, scrollStyle]}
            contentContainerStyle={styles.scrollContent}
          >
            {innerContent}
          </ScrollView>
        ) : (
          <View style={styles.staticContent}>
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
    flexGrow: 1,
  },
});
