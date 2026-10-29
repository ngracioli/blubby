import { useState } from 'react';
import { StyleSheet, Text, View } from 'react-native';
import { AuthApiError } from '../auth-api';
import { useAuth } from '../auth-context';
import { Button, Input, ScreenContainer } from '@/shared/components';
import { controlColors, theme } from '@/shared/theme';

interface RegistrationScreenProps {
  onBack: () => void;
  onSignIn: () => void;
}

export function RegistrationScreen({ onBack, onSignIn }: RegistrationScreenProps) {
  const { signUp } = useAuth();
  const [name, setName] = useState('');
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [confirmation, setConfirmation] = useState('');
  const [errors, setErrors] = useState<Record<string, string[]>>({});
  const [message, setMessage] = useState('');
  const [submitting, setSubmitting] = useState(false);

  async function submit() {
    if (submitting) return;
    setErrors({});
    setMessage('');
    setSubmitting(true);
    try {
      await signUp(name, email, password, confirmation);
    } catch (error) {
      if (error instanceof AuthApiError) {
        setErrors(error.errors);
        setMessage(error.message);
      } else {
        setMessage('Não foi possível criar a conta. Tente novamente.');
      }
    } finally {
      setSubmitting(false);
    }
  }

  return (
    <ScreenContainer contentContainerStyle={styles.content}>
      <Button variant="text" accessibilityLabel="Voltar" style={styles.back} labelStyle={styles.backLabel} onPress={onBack}>←</Button>
      <Text style={styles.title}>Criar sua conta</Text>
      <Text style={styles.subtitle}>Crie sua conta para começar.</Text>

      <View style={styles.fields}>
        <Input label="Nome" placeholder="Seu nome" value={name} onChangeText={setName} autoComplete="name" textContentType="name" errorMessage={errors.name?.[0]} />
        <Input label="Email" placeholder="seu@email.com" value={email} onChangeText={setEmail} keyboardType="email-address" autoCapitalize="none" autoComplete="email" textContentType="emailAddress" errorMessage={errors.email?.[0]} />
        <Input label="Senha" placeholder="Crie uma senha" value={password} onChangeText={setPassword} secureTextEntry autoComplete="new-password" textContentType="newPassword" errorMessage={errors.password?.[0]} />
        <Input label="Confirmar senha" placeholder="Repita sua senha" value={confirmation} onChangeText={setConfirmation} secureTextEntry autoComplete="new-password" textContentType="newPassword" errorMessage={errors.password_confirmation?.[0]} />
      </View>

      {message !== '' && <Text accessibilityRole="alert" style={styles.error}>{message}</Text>}
      <Button loading={submitting} style={styles.submit} onPress={() => { void submit(); }}>Criar conta</Button>

      <View style={styles.footer}>
        <Text style={styles.footerText}>Já tem uma conta?</Text>
        <Button variant="text" style={styles.footerLink} onPress={onSignIn}>Entrar</Button>
      </View>
    </ScreenContainer>
  );
}

const styles = StyleSheet.create({
  content: { paddingHorizontal: theme.spacing.screenPaddingHorizontal, paddingTop: theme.spacing.sm, paddingBottom: theme.spacing.xl },
  back: { alignSelf: 'flex-start', width: 48, minHeight: 48, paddingHorizontal: 0, paddingVertical: 0 },
  backLabel: { fontSize: 24, lineHeight: 32, fontWeight: '400' },
  title: { ...theme.typography.heading1, color: theme.colors.textPrimary, marginTop: theme.spacing.xl },
  subtitle: { ...theme.typography.body, color: theme.colors.textSecondary, marginTop: theme.spacing.sm },
  fields: { gap: 36, marginTop: theme.spacing.xl },
  error: { ...theme.typography.bodySmall, color: controlColors.light.error, marginTop: theme.spacing.base },
  submit: { marginTop: theme.spacing.xl },
  footer: { flexDirection: 'row', alignItems: 'center', justifyContent: 'center', marginTop: theme.spacing.base },
  footerText: { ...theme.typography.bodySmall, color: theme.colors.textSecondary },
  footerLink: { minHeight: 32, paddingHorizontal: theme.spacing.xs, paddingVertical: 0 },
});
