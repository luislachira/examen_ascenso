import { useSyncExternalStore, useMemo } from 'react';
import { authStore, apiLogin, apiLogout, apiRegister, UsuarioDTO } from '../store/authStore';

export function useAuth() {
    const state = useSyncExternalStore(authStore.subscribe, authStore.getState, authStore.getState);

    const api = useMemo(() => ({
        login: apiLogin,
        logout: apiLogout,
        register: apiRegister
    }), []);

    return {
        user: state.user as UsuarioDTO | null,
        token: state.token,
        ...api,
        isAdmin: state.user?.rol === '0',
        isDocente: state.user?.rol === '1',
        isAuthenticated: Boolean(state.token),
    };
}
