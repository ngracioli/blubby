import { Image, Platform, StyleSheet, Text, View } from 'react-native';
import { images } from '@/assets';
import { Button, ButtonProps, Divider, ScreenContainer } from '@/shared/components';
import { theme } from '@/shared/theme';

export interface WelcomeScreenProps {
  onCreateAccount?: ButtonProps['onPress'];
  onSignIn?: ButtonProps['onPress'];
  onGoogleSignIn?: ButtonProps['onPress'];
  onAppleSignIn?: ButtonProps['onPress'];
}

export function WelcomeScreen({ onCreateAccount, onSignIn, onGoogleSignIn, onAppleSignIn }: WelcomeScreenProps) {
  return (
    <ScreenContainer
      backgroundColor={theme.colors.surface}
      contentContainerStyle={styles.scrollContent}
    >
      <View style={styles.content}>
        <View style={styles.introduction}>
          <View style={styles.brandSlot}>
            <View style={styles.logoCrop}>
              <Image source={images.logo.horizontal} style={styles.logo} />
            </View>
          </View>
          <View style={styles.copy}>
            <Text style={styles.title}>Seu mês organizado.{'\n'}Seu futuro mais claro.</Text>
            <Text style={styles.subtitle}>
              Organize suas finanças de hoje{'\n'}e enxergue os próximos meses.
            </Text>
          </View>
        </View>

        <View style={styles.spacer} />

        <View style={styles.accessOptions}>
          <Button onPress={onCreateAccount}>Criar conta</Button>
          <View style={styles.actionGap} />
          <Button variant="text" onPress={onSignIn}>Acessar minha conta</Button>
          <View style={styles.dividerTopSpace} />
          <Divider>ou continue com</Divider>
          <View style={styles.dividerBottomSpace} />
          <Button
            variant="outline"
            onPress={onGoogleSignIn}
            style={styles.providerButton}
            labelStyle={styles.providerText}
            contentStyle={styles.providerContent}
            leadingIcon={<Image source={images.auth.google} style={styles.providerIcon} />}
          >
            Continuar com Google
          </Button>
          {Platform.OS === 'ios' && (
            <>
              <View style={styles.providerGap} />
              <Button
                variant="outline"
                onPress={onAppleSignIn}
                style={styles.providerButton}
                labelStyle={styles.providerText}
                contentStyle={styles.providerContent}
                leadingIcon={<View style={styles.appleIconCrop}><Image source={images.auth.apple} style={styles.appleIcon} /></View>}
              >
                Continuar com a Apple
              </Button>
            </>
          )}
        </View>
      </View>
    </ScreenContainer>
  );
}

const styles = StyleSheet.create({
  scrollContent: {
    flexGrow: 1,
  },
  content: {
    flexGrow: 1,
    paddingHorizontal: theme.spacing.screenPaddingHorizontal,
    paddingTop: theme.spacing.huge,
    paddingBottom: theme.spacing.xl,
  },
  introduction: {
    minHeight: 244,
    alignItems: 'center',
  },
  brandSlot: {
    height: 112,
    width: '100%',
    alignItems: 'center',
  },
  logoCrop: {
    width: 260,
    height: 80,
    overflow: 'hidden',
  },
  logo: {
    position: 'absolute',
    width: 377.75,
    height: 188.875,
    left: -59.83,
    top: -53.45,
  },
  copy: {
    width: '100%',
    alignItems: 'center',
    gap: theme.spacing.md,
  },
  title: {
    ...theme.typography.heading1,
    color: theme.colors.textPrimary,
    textAlign: 'center',
  },
  subtitle: {
    ...theme.typography.body,
    color: theme.colors.textSecondary,
    textAlign: 'center',
  },
  spacer: {
    flex: 1,
    minHeight: theme.spacing.none,
  },
  accessOptions: {
    width: '100%',
  },
  actionGap: {
    height: theme.spacing.sm,
  },
  dividerTopSpace: {
    height: theme.spacing.base,
  },
  dividerBottomSpace: {
    height: theme.spacing.base,
  },
  providerButton: {
    borderColor: theme.colors.providerBorder,
    backgroundColor: theme.colors.surface,
  },
  providerContent: {
    gap: theme.spacing.md,
  },
  providerIcon: {
    width: 20,
    height: 20,
  },
  providerText: {
    ...theme.typography.providerButton,
    color: theme.colors.providerText,
  },
  providerGap: {
    height: theme.spacing.md,
  },
  appleIconCrop: {
    width: 20,
    height: 20,
    overflow: 'hidden',
  },
  appleIcon: {
    position: 'absolute',
    width: 345,
    height: 52,
    left: -47,
    top: -16,
  },
});
