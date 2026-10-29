import { useState } from 'react';
import { Alert, StyleSheet, Text, View } from 'react-native';
import { AuthApiError } from '../auth-api';
import { useAuth } from '../auth-context';
import { Button, Input, ScreenContainer } from '@/shared/components';
import { controlColors, theme } from '@/shared/theme';

interface LoginScreenProps {
  onBack: () => void;
  onRegister: () => void;
}

export function LoginScreen({ onBack, onRegister }: LoginScreenProps) {
  const { signIn } = useAuth();
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [errors, setErrors] = useState<Record<string, string[]>>({});
  const [message, setMessage] = useState('');
  const [submitting, setSubmitting] = useState(false);

  async function submit() {
    if (submitting) return;
    setErrors({});
    setMessage('');
    setSubmitting(true);
    try {
      await signIn(email, password);
    } catch (error) {
      if (error instanceof AuthApiError) {
        setErrors(error.errors);
        setMessage(error.message);
      } else {
        setMessage('Não foi possível concluir o acesso. Tente novamente.');
      }
    } finally {
      setSubmitting(false);
    }
  }

  return (
    <ScreenContainer contentContainerStyle={styles.content}>
      <Button variant="text" accessibilityLabel="Voltar" style={styles.back} labelStyle={styles.backLabel} onPress={onBack}>←</Button>
      <Text style={styles.title}>Acessar minha conta</Text>
      <Text style={styles.subtitle}>Entre com os dados da sua conta Blubby.</Text>

      <View style={styles.fields}>
        <Input
          label="Email"
          placeholder="seu@email.com"
          value={email}
          onChangeText={setEmail}
          keyboardType="email-address"
          autoCapitalize="none"
          autoComplete="email"
          textContentType="emailAddress"
          returnKeyType="next"
          errorMessage={errors.email?.[0]}
        />
        <Input
          label="Senha"
          placeholder="Sua senha"
          value={password}
          onChangeText={setPassword}
          secureTextEntry
          autoComplete="current-password"
          textContentType="password"
          returnKeyType="done"
          onSubmitEditing={() => { void submit(); }}
          errorMessage={errors.password?.[0]}
        />
      </View>

      {message !== '' && <Text accessibilityRole="alert" style={styles.error}>{message}</Text>}
      <View style={styles.actions}>
        <Button loading={submitting} onPress={() => { void submit(); }}>Entrar</Button>
        <Button variant="text" style={styles.forgot} labelStyle={styles.forgotLabel} onPress={() => Alert.alert('Recuperação de senha', 'Esse recurso ainda não está disponível.')}>Esqueci minha senha</Button>
      </View>

      <View style={styles.footer}>
        <Text style={styles.footerText}>Ainda não tem conta?</Text>
        <Button variant="text" style={styles.footerLink} onPress={onRegister}>Criar conta</Button>
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
  fields: { gap: theme.spacing.huge, marginTop: theme.spacing.xl },
  actions: { gap: theme.spacing.base, marginTop: theme.spacing.xl + theme.spacing.xxl },
  forgot: { alignSelf: 'flex-end', minHeight: 32, paddingHorizontal: 0, paddingVertical: 0 },
  forgotLabel: { fontFamily: theme.fontFamilies.regular, fontSize: 14, lineHeight: 20, fontWeight: '400' },
  error: { ...theme.typography.bodySmall, color: controlColors.light.error, marginTop: theme.spacing.base },
  footer: { flexDirection: 'row', alignItems: 'center', justifyContent: 'center', marginTop: 'auto', paddingTop: theme.spacing.xl },
  footerText: { ...theme.typography.bodySmall, color: theme.colors.textSecondary },
  footerLink: { minHeight: 32, paddingHorizontal: theme.spacing.xs, paddingVertical: 0 },
});
