import React, { useState } from 'react';
import '@res/css/LoginPage.css';
import logo from '../assets/logo_leonor_cerna 2.png';
import { useAuth } from '../hooks/useAuth';

interface ApiError {
    response?: {
        data?: {
            message?: string;
        };
    };
}

const LoginPage: React.FC = () => {
    const { login } = useAuth();
    const [correo, setCorreo] = useState('');
    const [password, setPassword] = useState('');
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState<string | null>(null);

    const onSubmit = async (e: React.FormEvent) => {
        e.preventDefault();
        setError(null);
        setLoading(true);
        try {
            await login(correo, password);
            window.location.href = '/admin/dashboard';
        } catch (err: unknown) {
            const error = err as ApiError;
            setError(error?.response?.data?.message || 'Error al iniciar sesión');
        } finally {
            setLoading(false);
        }
    };

    const oauthLogin = (provider: 'google' | 'microsoft') => {
        window.location.href = `/oauth/redirect/${provider}`;
    };

    return (
        <div className="login-container">
            <div className="login-box">
                <div className="login-header">
                    <img src={logo} alt="Logo I.E. Leonor Cerna de Valdiviezo" />
                    <h2>I.E. LEONOR CERNA DE VALDIVIEZO</h2>
                </div>
                <form className="login-form" onSubmit={onSubmit}>
                    <div className="input-group">
                        <label htmlFor="correo">Correo</label>
                        <input 
                            type="email" 
                            id="correo" 
                            name="correo" 
                            required 
                            value={correo} 
                            onChange={(e)=>setCorreo(e.target.value)} 
                        />
                    </div>
                    <div className="input-group">
                        <label htmlFor="password">Contraseña</label>
                        <input 
                            type="password" 
                            id="password" 
                            name="password" 
                            required 
                            value={password} 
                            onChange={(e)=>setPassword(e.target.value)} 
                        />
                    </div>
                    {error && <div className="error-message">{error}</div>}
                    <button type="submit" className="btn btn-primary" disabled={loading}>
                        {loading ? 'Ingresando...' : 'Iniciar Sesión'}
                    </button>
                    <button 
                        type="button" 
                        className="btn btn-secondary"
                        onClick={() => window.location.href = '/register'}
                    >
                        Registrar
                    </button>
                </form>
                
                <div className="oauth-section">
                    <div className="oauth-divider">
                        <span>or</span>
                    </div>
                    <div className="oauth-buttons">
                        <button type="button" className="oauth-btn google" onClick={() => oauthLogin('google')}>
                            <svg className="oauth-logo" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
                                <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                                <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/>
                                <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
                            </svg>
                            Sign in with Google
                        </button>
                        <button type="button" className="oauth-btn microsoft" onClick={() => oauthLogin('microsoft')}>
                            <svg className="oauth-logo" viewBox="0 0 23 23" xmlns="http://www.w3.org/2000/svg">
                                <path fill="#f25022" d="M1 1h9v9H1z"/>
                                <path fill="#00a4ef" d="M1 12h9v9H1z"/>
                                <path fill="#7fba00" d="M12 1h9v9h-9z"/>
                                <path fill="#ffb900" d="M12 12h9v9h-9z"/>
                            </svg>
                            Sign in with Microsoft
                        </button>
                    </div>
                </div>
            </div>
        </div>
    );
};

export default LoginPage;