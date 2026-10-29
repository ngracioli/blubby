import { Redirect, useRouter } from 'expo-router';
import { LoginScreen, useAuth } from '@/features/auth';

export default function LoginRoute() {
  const router = useRouter();
  const { status } = useAuth();
  if (status === 'authenticated') return <Redirect href="/home" />;
  if (status !== 'anonymous') return <Redirect href="/" />;
  return (
    <LoginScreen
      onBack={() => router.replace('/')}
      onRegister={() => router.replace('/register')}
    />
  );
}
