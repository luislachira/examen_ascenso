import { defineConfig, loadEnv, UserConfig } from 'vite';
import tailwindcss from '@tailwindcss/vite';
import react from '@vitejs/plugin-react';
import laravel from 'laravel-vite-plugin';
import path from 'path';

// La configuración ahora es una función de TypeScript que devuelve un objeto UserConfig
export default defineConfig(({ command, mode }): UserConfig => {
    const env = loadEnv(mode, process.cwd(), '');
    const serverConfig: UserConfig['server'] = {};

    if (command === 'serve') {
        // Configurar el host explícitamente para evitar problemas con IPv6
        // Usar 'localhost' en lugar de '0.0.0.0' para evitar problemas de CORS
        serverConfig.host = 'localhost';
        serverConfig.strictPort = false;

        // Usar HTTP para desarrollo (Apache maneja HTTPS)
        // Esto evita problemas de CORS y certificados cuando se accede a través del proxy de Apache
        // Apache hace proxy de las solicitudes a Vite, por lo que no necesitamos HTTPS en Vite
        // No definir 'https' significa que Vite usará HTTP
        console.log('✅ Vite usando HTTP (Apache maneja HTTPS)');

        // Configurar CORS en el servidor para permitir conexiones desde Apache
        // Permitir todos los orígenes en desarrollo para evitar problemas de CORS
        serverConfig.cors = {
            origin: true, // Permitir todos los orígenes en desarrollo
            credentials: true,
            methods: ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'],
            allowedHeaders: ['Content-Type', 'Authorization', 'X-Requested-With'],
        };

        // HMR deshabilitado (sin WebSocket)
        serverConfig.hmr = false;

        // Configurar proxy para API
        // Si usas Apache/WAMP con HTTPS, usa: https://examen_ascenso.com
        // Si usas php artisan serve, usa: http://localhost:8000
        const backendUrl = env.VITE_APP_URL || 'https://examen_ascenso.com';
        serverConfig.proxy = {
            '/api': {
                target: backendUrl,
                changeOrigin: true,
                secure: false, // Permitir certificados autofirmados en el backend
                // Si el backend usa HTTPS con certificado autofirmado, secure debe ser false
            },
        };
    }

    return {
        plugins: [
            laravel({
                // Ajusta la ruta de entrada para el CSS si es necesario
                input: ['resources/css/app.css', 'resources/js/app.tsx'],
                // SSR deshabilitado - el proyecto usa React Router, no Inertia
                // ssr: 'resources/js/ssr.tsx',
                refresh: true,
                // Deshabilitar detección automática de Herd/Valet (no disponible en Windows/WAMP)
                // Configurar manualmente el servidor de desarrollo
                detectTls: false,
            }),
            react(),
            tailwindcss(),
        ],

        // Configuración de build para optimización
        build: {
            sourcemap: false, // Sin sourcemaps en dev
            rollupOptions: {
                output: {
                    manualChunks: (id) => {
                        // Separar node_modules en chunks por librería
                        if (id.includes('node_modules')) {
                            // ESTRATEGIA CONSERVADORA: Agrupar React y TODAS sus posibles dependencias
                            // Si hay alguna duda, mejor incluirla en vendor-react para evitar errores

                            // Primero, identificar librerías que DEFINITIVAMENTE no dependen de React
                            const nonReactLibs = [
                                'tailwindcss',
                                'clsx',
                                'class-variance-authority',
                                'tailwind-merge',
                                'axios',
                                'guzzle',
                                'date-fns',
                                'moment',
                                'dayjs'
                            ];

                            // Si es una librería que definitivamente NO depende de React
                            const isNonReactLib = nonReactLibs.some(lib => id.includes(lib));

                            if (isNonReactLib) {
                                // Separar por tipo
                                if (id.includes('tailwindcss') || id.includes('clsx') || id.includes('class-variance-authority') || id.includes('tailwind-merge')) {
                                    return 'vendor-ui';
                                }
                                if (id.includes('axios') || id.includes('guzzle')) {
                                    return 'vendor-http';
                                }
                                if (id.includes('date-fns') || id.includes('moment') || id.includes('dayjs')) {
                                    return 'vendor-date';
                                }
                            }

                            // TODO LO DEMÁS va a vendor-react (incluyendo React y todas sus dependencias)
                            // Esto asegura que React esté disponible para todas las librerías que lo necesiten
                            return 'vendor-react';
                        }

                        // Separar código de la aplicación por secciones
                        if (id.includes('resources/js/pages/admin')) {
                            return 'admin';
                        }
                        if (id.includes('resources/js/pages/docente')) {
                            return 'docente';
                        }
                        if (id.includes('resources/js/pages/auth')) {
                            return 'auth';
                        }
                        if (id.includes('resources/js/pages/banco-preguntas')) {
                            return 'banco-preguntas';
                        }
                        if (id.includes('resources/js/pages/profile')) {
                            return 'profile';
                        }
                        // Componentes compartidos
                        if (id.includes('resources/js/components')) {
                            return 'components';
                        }
                        // Hooks y servicios
                        if (id.includes('resources/js/hooks') || id.includes('resources/js/services')) {
                            return 'utils';
                        }
                    },
                },
            },
            // Límite de advertencia de tamaño (en KB)
            chunkSizeWarningLimit: 500,
        },

        resolve: {
            extensions: ['.js', '.jsx', '.ts', '.tsx'],
            alias: {
                '@': path.resolve(__dirname, './resources/js'),
                '~': path.resolve(__dirname, './resources'),
                '@css': path.resolve(__dirname, './resources/css'),
                '@img' : path.resolve(__dirname, './resources/js/assets'),
            },
        },
        // --- CONFIGURACIÓN DEL SERVIDOR DE DESARROLLO ---
        server: serverConfig, // Aplicamos la configuración del servidor
    };
});
