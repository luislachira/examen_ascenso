import clienteApi from '../api/clienteApi';

export type RolUsuario = '0' | '1'; // '0' admin, '1' docente

export interface UsuarioDTO {
    id: number;
    dni: string;
    nombre: string;
    apellidos: string;
    correo: string;
    rol: RolUsuario;
}

interface AuthState {
    token: string | null;
    user: UsuarioDTO | null;
}

// Suscripción estilo useSyncExternalStore
type Listener = () => void;

const STORAGE_KEY = 'auth_state_v1';

const authState: AuthState = loadState();
const listeners: Listener[] = [];

function loadState(): AuthState {
    try {
        const raw = localStorage.getItem(STORAGE_KEY);
        if (!raw) return { token: null, user: null };
        const parsed = JSON.parse(raw);
        return { token: parsed.token ?? null, user: parsed.user ?? null } as AuthState;
    } catch {
        return { token: null, user: null };
    }
}

function persist() {
    localStorage.setItem(STORAGE_KEY, JSON.stringify(authState));
}

function notify() {
    listeners.forEach((l) => l());
}

export const authStore = {
    getState(): AuthState {
        return authState;
    },
    subscribe(listener: Listener): () => void {
        listeners.push(listener);
        return () => {
            const idx = listeners.indexOf(listener);
            if (idx >= 0) listeners.splice(idx, 1);
        };
    },
    setTokenAndUser(token: string, user: UsuarioDTO) {
        authState.token = token;
        authState.user = user;
        persist();
        notify();
    },
    clear() {
        authState.token = null;
        authState.user = null;
        persist();
        notify();
    },
};

// Helpers de API
export interface LoginData {
    correo: string;
    password: string;
}

export interface RegisterData {
    nombre: string;
    apellidos: string;
    dni: string;
    correo: string;
    password: string;
    password_confirmation: string;
}

export async function apiLogin(data: LoginData) {
    const res = await clienteApi.post('/login', data);
    const { access_token, usuario } = res.data;
    authStore.setTokenAndUser(access_token, {
        id: usuario.id,
        dni: usuario.dni,
        nombre: usuario.nombre,
        apellidos: usuario.apellidos,
        correo: usuario.correo,
        rol: usuario.rol,
    });
}

export async function apiLogout() {
    try {
        await clienteApi.post('/logout');
    } catch {
        // Ignore logout errors
    }
    authStore.clear();
}

export async function apiRegister(data: RegisterData): Promise<void> {
    await clienteApi.post('/register', data);
}
