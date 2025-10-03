import React, { useEffect } from 'react';
import { authStore, UsuarioDTO } from '../../store/authStore';

function parseHash(): Record<string, string> {
    const hash = window.location.hash.replace(/^#/, '');
    const params = new URLSearchParams(hash);
    const obj: Record<string, string> = {};
    params.forEach((v, k) => (obj[k] = v));
    return obj;
}

const OAuthSuccess: React.FC = () => {
    useEffect(() => {
        const p = parseHash();
        const token = p['access_token'];
        if (token) {
            const user: UsuarioDTO = {
                id: Number(p['id'] || 0),
                dni: String(p['dni'] || ''),
                nombre: decodeURIComponent(p['nombre'] || ''),
                apellidos: decodeURIComponent(p['apellidos'] || ''),
                correo: decodeURIComponent(p['correo'] || ''),
                rol: (p['rol'] as '0' | '1') || '1',
            };
            authStore.setTokenAndUser(token, user);
            const isAdmin = user.rol === '0';
            const base = `${window.location.origin}`;
            window.location.replace(`${base}${isAdmin ? '/admin/dashboard' : '/docente/examen'}`);
        }
    }, []);

    return (
        <div style={{ display:'flex', alignItems:'center', justifyContent:'center', minHeight:'100vh' }}>
            Procesando inicio de sesión...
        </div>
    );
};

export default OAuthSuccess; 