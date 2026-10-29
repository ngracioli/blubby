import { Alert } from 'react-native';
import { Redirect } from 'expo-router';
import { HomeScreen, useAuth } from '@/features/auth';

export default function HomeRoute() {
  const { status, user, signOut } = useAuth();
  if (status !== 'authenticated' || !user) return <Redirect href="/" />;
  return <HomeScreen user={user} onSignOut={async () => {
    try {
      await signOut();
    } catch {
      Alert.alert('Sessão encerrada', 'O acesso foi removido deste dispositivo, mas o servidor não confirmou a revogação.');
    }
  }} />;
}
