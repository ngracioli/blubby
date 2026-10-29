import * as Crypto from 'expo-crypto';
import * as SecureStore from 'expo-secure-store';
import { Platform } from 'react-native';

const tokenKey = 'blubby.session-token';
const installationKey = 'blubby.installation-id';
let webToken: string | null = null;
let webInstallationId: string | null = null;

export interface AuthUser {
  id: number;
  name: string;
  email: string;
  email_verified_at: string | null;
  created_at: string;
  updated_at: string;
}

interface SessionResponse {
  data: { user: AuthUser; token: string; token_type: 'Bearer' };
}

export class AuthApiError extends Error {
  constructor(
    message: string,
    readonly status: number,
    readonly errors: Record<string, string[]> = {},
  ) {
    super(message);
  }
}

function apiUrl() {
  const value = process.env.EXPO_PUBLIC_API_URL?.replace(/\/+$/, '');
  if (!value) throw new AuthApiError('Configure EXPO_PUBLIC_API_URL para conectar ao servidor.', 0);
  return value;
}

async function request<T>(path: string, init: RequestInit = {}, token?: string): Promise<T> {
  const url = apiUrl();
  let response: Response;
  const controller = new AbortController();
  const timeout = setTimeout(() => controller.abort(), 10_000);
  try {
    response = await fetch(`${url}${path}`, {
      ...init,
      signal: controller.signal,
      headers: {
        Accept: 'application/json',
        ...(init.body ? { 'Content-Type': 'application/json' } : {}),
        ...(token ? { Authorization: `Bearer ${token}` } : {}),
        ...init.headers,
      },
    });
  } catch {
    throw new AuthApiError('Não foi possível conectar ao servidor. Confira sua conexão.', 0);
  } finally {
    clearTimeout(timeout);
  }

  const body = response.status === 204 ? undefined : await response.json().catch(() => undefined);
  if (!response.ok) {
    const payload = body as { message?: string; errors?: Record<string, string[]> } | undefined;
    throw new AuthApiError(
      payload?.message ?? 'Não foi possível concluir a solicitação.',
      response.status,
      payload?.errors ?? {},
    );
  }
  return body as T;
}

async function installationId() {
  if (Platform.OS === 'web') {
    webInstallationId ??= Crypto.randomUUID();
    return webInstallationId;
  }
  const existing = await SecureStore.getItemAsync(installationKey);
  if (existing) return existing;
  const created = Crypto.randomUUID();
  await SecureStore.setItemAsync(installationKey, created);
  return created;
}

async function deviceFields() {
  return { device_id: await installationId(), device_name: 'Blubby Mobile' };
}

export async function register(name: string, email: string, password: string, passwordConfirmation: string) {
  const body = await request<SessionResponse>('/api/register', {
    method: 'POST',
    body: JSON.stringify({ name, email, password, password_confirmation: passwordConfirmation, ...await deviceFields() }),
  });
  return body.data;
}

export async function login(email: string, password: string) {
  const body = await request<SessionResponse>('/api/login', {
    method: 'POST',
    body: JSON.stringify({ email, password, ...await deviceFields() }),
  });
  return body.data;
}

export function getCurrentUser(token: string) {
  return request<AuthUser>('/api/user', {}, token);
}

export async function revokeSession(token: string) {
  await request<void>('/api/logout', { method: 'POST' }, token);
}

export const sessionStorage = {
  getToken: () => Platform.OS === 'web' ? Promise.resolve(webToken) : SecureStore.getItemAsync(tokenKey),
  setToken: (token: string) => {
    if (Platform.OS === 'web') { webToken = token; return Promise.resolve(); }
    return SecureStore.setItemAsync(tokenKey, token);
  },
  removeToken: () => {
    if (Platform.OS === 'web') { webToken = null; return Promise.resolve(); }
    return SecureStore.deleteItemAsync(tokenKey);
  },
};
