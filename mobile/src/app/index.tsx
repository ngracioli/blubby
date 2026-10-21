import { Image, StyleSheet, Text, View } from 'react-native';
import { theme } from '@/shared/theme';
import { images } from '@/assets';
import { ScreenContainer } from '@/shared/components';

export default function Index() {
  return (
    <ScreenContainer contentContainerStyle={styles.container}>
      <View style={styles.content}>
        <Image source={images.logo.symbol} style={styles.logo} resizeMode="contain" />
        <Text style={styles.title}>Blubby</Text>
        <Text style={styles.subtitle}>Seu mês organizado. Seu futuro mais claro.</Text>
      </View>
    </ScreenContainer>
  );
}

const styles = StyleSheet.create({
  container: {
    justifyContent: 'center',
    paddingHorizontal: theme.spacing.screenPaddingHorizontal,
  },
  content: {
    alignItems: 'center',
    justifyContent: 'center',
  },
  logo: {
    width: 64,
    height: 64,
    marginBottom: theme.spacing.base,
  },
  title: {
    ...theme.typography.heading1,
    color: theme.colors.textPrimary,
    marginBottom: theme.spacing.sm,
    textAlign: 'center',
  },
  subtitle: {
    ...theme.typography.body,
    color: theme.colors.textSecondary,
    textAlign: 'center',
  },
});
