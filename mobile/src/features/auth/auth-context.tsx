import { createContext, ReactNode, useCallback, useContext, useEffect, useState } from 'react';
import { AuthApiError, AuthUser, getCurrentUser, login, register, revokeSession, sessionStorage } from './auth-api';

type AuthStatus = 'loading' | 'anonymous' | 'authenticated' | 'unavailable';

interface AuthContextValue {
  status: AuthStatus;
  user: AuthUser | null;
  restoreError: string;
  signIn: (email: string, password: string) => Promise<void>;
  signUp: (name: string, email: string, password: string, confirmation: string) => Promise<void>;
  signOut: () => Promise<void>;
  retryRestoreSession: () => Promise<void>;
}

const AuthContext = createContext<AuthContextValue | null>(null);

async function persistSession(session: { user: AuthUser; token: string }) {
  try {
    await sessionStorage.setToken(session.token);
  } catch {
    try { await revokeSession(session.token); } catch { /* Não esconder falha de armazenamento se revogação remota também falhar. */ }
    throw new Error('Não foi possível salvar sua sessão neste dispositivo.');
  }
}

export function AuthProvider({ children }: { children: ReactNode }) {
  const [status, setStatus] = useState<AuthStatus>('loading');
  const [user, setUser] = useState<AuthUser | null>(null);
  const [restoreError, setRestoreError] = useState('');

  const retryRestoreSession = useCallback(async () => {
    setStatus('loading');
    setRestoreError('');
    try {
      const token = await sessionStorage.getToken();
      if (!token) {
        setUser(null);
        setStatus('anonymous');
        return;
      }
      const currentUser = await getCurrentUser(token);
      setUser(currentUser);
      setStatus('authenticated');
    } catch (error) {
      if (error instanceof AuthApiError && error.status === 401) {
        try { await sessionStorage.removeToken(); } catch { /* Mesmo sem apagar o token, não abrir rota autenticada após 401. */ }
        setUser(null);
        setStatus('anonymous');
        return;
      }
      setRestoreError(error instanceof Error ? error.message : 'Não foi possível verificar sua sessão.');
      setStatus('unavailable');
    }
  }, []);

  useEffect(() => { void retryRestoreSession(); }, [retryRestoreSession]);

  const signIn = useCallback(async (email: string, password: string) => {
    const session = await login(email, password);
    await persistSession(session);
    setUser(session.user);
    setStatus('authenticated');
  }, []);

  const signUp = useCallback(async (name: string, email: string, password: string, confirmation: string) => {
    const session = await register(name, email, password, confirmation);
    await persistSession(session);
    setUser(session.user);
    setStatus('authenticated');
  }, []);

  const signOut = useCallback(async () => {
    try {
      const token = await sessionStorage.getToken();
      if (token) await revokeSession(token);
    } finally {
      try {
        await sessionStorage.removeToken();
      } finally {
        setUser(null);
        setStatus('anonymous');
      }
    }
  }, []);

  return (
    <AuthContext.Provider value={{ status, user, restoreError, signIn, signUp, signOut, retryRestoreSession }}>
      {children}
    </AuthContext.Provider>
  );
}

export function useAuth() {
  const value = useContext(AuthContext);
  if (!value) throw new Error('useAuth precisa estar dentro de AuthProvider.');
  return value;
}
