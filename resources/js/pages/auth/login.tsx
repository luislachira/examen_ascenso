import React, { useState } from 'react';

import '@res/css/Login.css';
import logo from '../assets/logo_leonor_cerna 2.png';
import { useAuth } from '../../hooks/useAuth';

// --- Iconos SVG para los botones de OAuth ---
const GoogleIcon = () => (
    <svg className="w-5 h-5 mr-3" viewBox="0 0 48 48">
        <path fill="#FFC107" d="M43.611 20.083H42V20H24v8h11.303c-1.649 4.657-6.08 8-11.303 8c-6.627 0-12-5.373-12-12s5.373-12 12-12c3.059 0 5.842 1.154 7.961 3.039L38.802 9.92C34.553 6.08 29.625 4 24 4C12.955 4 4 12.955 4 24s8.955 20 20 20s20-8.955 20-20c0-1.341-.138-2.65-.389-3.917z"></path>
        <path fill="#FF3D00" d="m6.306 14.691l6.571 4.819C14.655 15.108 18.961 12 24 12c3.059 0 5.842 1.154 7.961 3.039l4.841-4.841C34.553 6.08 29.625 4 24 4C16.318 4 9.656 8.337 6.306 14.691z"></path>
        <path fill="#4CAF50" d="m24 44c5.166 0 9.86-1.977 13.409-5.192l-6.19-5.238A11.91 11.91 0 0 1 24 36c-5.228 0-9.652-3.512-11.289-8.223l-6.522 5.025C9.505 39.556 16.227 44 24 44z"></path>
        <path fill="#1976D2" d="M43.611 20.083H42V20H24v8h11.303c-.792 2.237-2.231 4.166-4.087 5.571l6.19 5.238C42.012 35.337 44 30.022 44 24c0-1.341-.138-2.65-.389-3.917z"></path>
    </svg>
);

const MicrosoftIcon = () => (
    <svg className="w-5 h-5 mr-3" viewBox="0 0 21 21">
        <path fill="#f25022" d="M1 1h9v9H1z"/>
        <path fill="#00a4ef" d="M1 11h9v9H1z"/>
        <path fill="#7fba00" d="M11 1h9v9h-9z"/>
        <path fill="#ffb900" d="M11 11h9v9h-9z"/>
    </svg>
);

interface ApiError {
    response?: {
        data?: {
            message?: string;
        };
    };
}

const Login: React.FC = () => {
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
                            <GoogleIcon />
                            Sign in with Google
                        </button>
                        <button type="button" className="oauth-btn microsoft" onClick={() => oauthLogin('microsoft')}>
                            <MicrosoftIcon />
                            Sign in with Microsoft
                        </button>
                    </div>
                </div>
            </div>
        </div>
    );
};

export default Login;