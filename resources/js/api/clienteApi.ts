import axios, { InternalAxiosRequestConfig, AxiosError } from 'axios';
import { authStore } from '../store/authStore';

// 1. CREACIÓN DE LA INSTANCIA DE AXIOS
// =====================================
// Aquí creamos una instancia de Axios con la configuración base.

// Determinar la URL base según el entorno
const getBaseURL = () => {
    // Si estamos en desarrollo local
    if (window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1') {
        return '/api/v1';
    }
    // Si estamos en producción, usar la URL completa
    return `${window.location.origin}/api/v1`;
};

const clienteApi = axios.create({
    // La URL base para las rutas de API
    baseURL: getBaseURL(),
    headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
    },
    timeout: 10000, // 10 segundos timeout
});


// 2. INTERCEPTOR DE PETICIONES (REQUEST)
// ========================================
// Esto es una función que se ejecuta ANTES de que cada petición sea enviada.
// Su trabajo es "interceptar" la petición y modificarla si es necesario.
clienteApi.interceptors.request.use(
    (config: InternalAxiosRequestConfig) => {
        // IMPORTANTE: Obtener el token SIEMPRE de manera fresca en cada petición
        const freshAuthState = authStore.getState();
        const token = freshAuthState.token;

        // Debug deshabilitado por defecto. Habilitar solo cuando sea necesario para diagnóstico
        // if (process.env.NODE_ENV === 'development') {
        //     console.log('🔎 Debug request interceptor:');
        //     console.log('- URL:', config.url);
        //     console.log('- Base URL:', config.baseURL);
        //     console.log('- Full URL:', `${config.baseURL}${config.url}`);
        //     console.log('- Token exists:', !!token);
        //     console.log('- Token preview:', token ? `${token.substring(0, 20)}...` : 'none');
        //     console.log('- Auth state:', {
        //         hasUser: !!authState.user,
        //         isInitialized: authState.isInitialized,
        //         userRole: authState.user?.rol
        //     });
        //     console.log('- Headers que se enviarán:', {
        //         'Authorization': token ? `Bearer ${token.substring(0, 20)}...` : 'none',
        //         'Content-Type': config.headers?.['Content-Type'],
        //         'Accept': config.headers?.['Accept']
        //     });
        // }

        // Si existe un token, lo añadimos a la cabecera 'Authorization'.
        if (token && config.headers) {
            config.headers['Authorization'] = `Bearer ${token}`;
        }

        // Devolvemos la configuración modificada para que la petición continúe.
        return config;
    },
    (error: AxiosError) => {
        return Promise.reject(error);
    }
);


// 3. INTERCEPTOR DE RESPUESTAS (RESPONSE) - Opcional pero recomendado
// ===================================================================
// Este se ejecuta DESPUÉS de recibir una respuesta del servidor.
// Es ideal para manejar errores de autenticación de forma global.
clienteApi.interceptors.response.use(
    (response) => {
        return response;
    },
    (error: AxiosError) => {
        // Si el servidor responde con un 401 (No autorizado), significa que el token
        // no es válido o ha expirado.
        if (error.response?.status === 401) {
            const errorData = error.response?.data as { expired?: boolean; message?: string; inactivity_timeout?: number } | undefined;

            // Verificar si es por inactividad
            if (errorData?.expired === true) {
                // Limpiamos el estado de autenticación del frontend.
                authStore.clear();
                // Mostrar mensaje al usuario
                alert(errorData.message || 'Su sesión ha expirado por inactividad. Por favor, inicie sesión nuevamente.');
                // Redirigir al login si no estamos ya en él.
                if (window.location.pathname !== '/login') {
                    window.location.href = '/login';
                }
            } else {
                // Limpiamos el estado de autenticación del frontend.
                authStore.clear();
                // Opcional: Redirigir al login si no estamos ya en él.
                if (window.location.pathname !== '/login') {
                    window.location.href = '/login';
                }
            }
        }
        
        // Silenciar errores 422 cuando el examen ya fue finalizado
        // Estos errores se manejan en los componentes y no necesitan aparecer en la consola
        if (error.response?.status === 422) {
            const errorData = error.response?.data as { ya_finalizado?: boolean } | undefined;
            if (errorData?.ya_finalizado === true) {
                // Retornar el error sin que aparezca en la consola
                // Los componentes lo manejarán apropiadamente
                return Promise.reject(error);
            }
        }
        
        return Promise.reject(error);
    }
);


// 4. EXPORTACIÓN
// // Exportamos la instancia configurada para ser utilizada en toda la aplicación.
export default clienteApi;
