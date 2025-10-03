import axios, { InternalAxiosRequestConfig, AxiosResponse, AxiosError } from 'axios';
import { authStore } from '../store/authStore';

// 1. CREACIÓN DE LA INSTANCIA DE AXIOS
// =====================================
// Aquí creamos una instancia de Axios con la configuración base.
const clienteApi = axios.create({
    // La URL base cuando se sirve desde WAMP bajo /laravel/examen_ascenso/public
    baseURL: '/api',
    headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
    }
});


// 2. INTERCEPTOR DE PETICIONES (REQUEST)
// ========================================
// Esto es una función que se ejecuta ANTES de que cada petición sea enviada.
// Su trabajo es "interceptar" la petición y modificarla si es necesario.
clienteApi.interceptors.request.use(
    (config: InternalAxiosRequestConfig) => {
        // Obtenemos el token del store de autenticación.
        const token = authStore.getState().token;
        
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
    (response: AxiosResponse) => {
        return response;
    },
    (error: AxiosError) => {
        if (error.response?.status === 401) {
            // Si el token no es válido/expiró, limpiamos sesión.
            authStore.clear();
            // Opcional: Redirigir al login.
            // window.location.href = '/laravel/examen_ascenso/public/login';
        }
        return Promise.reject(error);
    }
);


// 4. EXPORTACIÓN
// ==============
export default clienteApi;