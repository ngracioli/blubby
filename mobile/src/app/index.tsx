import { Redirect, useRouter } from 'expo-router';
import { StyleSheet, Text, View } from 'react-native';
import { WelcomeScreen } from '@/features/auth';
import { useAuth } from '@/features/auth';
import { Button, ScreenContainer } from '@/shared/components';
import { theme } from '@/shared/theme';

export default function Index() {
  const router = useRouter();
  const { status, restoreError, retryRestoreSession } = useAuth();

  if (status === 'loading') {
    return <AuthLoadingScreen message="Carregando sua sessão..." />;
  }
  if (status === 'unavailable') {
    return (
      <AuthLoadingScreen
        message={`${restoreError || 'Não foi possível verificar sua sessão.'} Confira sua conexão e tente novamente.`}
        onRetry={retryRestoreSession}
      />
    );
  }
  if (status === 'authenticated') {
    return <Redirect href="/home" />;
  }

  return (
    <WelcomeScreen
      onCreateAccount={() => router.push('/register')}
      onSignIn={() => router.push('/login')}
    />
  );
}

function AuthLoadingScreen({ message, onRetry }: { message: string; onRetry?: () => void }) {
  return (
    <ScreenContainer scrollable={false}>
      <View style={styles.loadingContent}>
        <Text style={styles.message}>{message}</Text>
        {onRetry && <Button onPress={onRetry}>Tentar novamente</Button>}
      </View>
    </ScreenContainer>
  );
}

const styles = StyleSheet.create({
  loadingContent: { flex: 1, justifyContent: 'center', gap: theme.spacing.base, padding: theme.spacing.screenPaddingHorizontal },
  message: { ...theme.typography.body, color: theme.colors.textSecondary, textAlign: 'center' },
});
