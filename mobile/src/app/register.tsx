import { Redirect, useRouter } from 'expo-router';
import { RegistrationScreen, useAuth } from '@/features/auth';

export default function RegisterRoute() {
  const router = useRouter();
  const { status } = useAuth();
  if (status === 'authenticated') return <Redirect href="/home" />;
  if (status !== 'anonymous') return <Redirect href="/" />;
  return (
    <RegistrationScreen
      onBack={() => router.replace('/')}
      onSignIn={() => router.replace('/login')}
    />
  );
}
