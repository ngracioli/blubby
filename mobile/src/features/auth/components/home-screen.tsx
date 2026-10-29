import { useState } from 'react';
import { StyleSheet, Text, View } from 'react-native';
import { AuthUser } from '../auth-api';
import { Button, ScreenContainer } from '@/shared/components';
import { theme } from '@/shared/theme';

interface HomeScreenProps {
  user: AuthUser;
  onSignOut: () => Promise<void>;
}

export function HomeScreen({ user, onSignOut }: HomeScreenProps) {
  const [signingOut, setSigningOut] = useState(false);

  async function signOut() {
    setSigningOut(true);
    try {
      await onSignOut();
    } finally {
      setSigningOut(false);
    }
  }

  return (
    <ScreenContainer contentContainerStyle={styles.content}>
      <View style={styles.copy}>
        <Text style={styles.title}>Olá, {user.name}</Text>
        <Text style={styles.email}>{user.email}</Text>
        <Text style={styles.message}>Sua conta está conectada. Sua área inicial será adicionada em breve.</Text>
      </View>
      <Button loading={signingOut} variant="outline" onPress={() => { void signOut(); }}>Sair</Button>
    </ScreenContainer>
  );
}

const styles = StyleSheet.create({
  content: { flex: 1, justifyContent: 'space-between', padding: theme.spacing.screenPaddingHorizontal, paddingVertical: theme.spacing.xl },
  copy: { flex: 1, justifyContent: 'center', gap: theme.spacing.sm },
  title: { ...theme.typography.heading1, color: theme.colors.textPrimary },
  email: { ...theme.typography.bodyStrong, color: theme.colors.textSecondary },
  message: { ...theme.typography.body, color: theme.colors.textSecondary, marginTop: theme.spacing.base },
});
