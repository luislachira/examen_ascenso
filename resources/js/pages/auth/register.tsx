import React, { useState } from 'react';
import '@res/css/LoginPage.css';
import logo from '../../assets/logo_leonor_cerna 2.png';
import { useAuth } from '../../hooks/useAuth';
import { RegisterData } from '../../store/authStore';

const Register: React.FC = () => {
    const { register } = useAuth();
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState<string | null>(null);
    const [formData, setFormData] = useState<RegisterData>({
        nombre: '',
        apellidos: '',
        dni: '',
        correo: '',
        password: '',
        password_confirmation: ''
    });

    const handleChange = (e: React.ChangeEvent<HTMLInputElement>) => {
        setFormData({
            ...formData,
            [e.target.name]: e.target.value
        });
    };

    const onSubmit = async (e: React.FormEvent) => {
        e.preventDefault();
        setError(null);
        setLoading(true);

        try {
            await register(formData);
            alert('Registro exitoso, espere hasta que el administrador acepte su solicitud');
            window.location.href = '/login';
        } catch (err) {
            const error = err as { response?: { data?: { message?: string } } };
            setError(error?.response?.data?.message || 'Error al registrar usuario');
        } finally {
            setLoading(false);
        }
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
                        <label htmlFor="nombre">Nombres</label>
                        <input
                            type="text"
                            id="nombre"
                            name="nombre"
                            required
                            value={formData.nombre}
                            onChange={handleChange}
                        />
                    </div>
                    <div className="input-group">
                        <label htmlFor="apellidos">Apellidos</label>
                        <input
                            type="text"
                            id="apellidos"
                            name="apellidos"
                            required
                            value={formData.apellidos}
                            onChange={handleChange}
                        />
                    </div>
                    <div className="input-group">
                        <label htmlFor="dni">DNI</label>
                        <input
                            type="text"
                            id="dni"
                            name="dni"
                            required
                            pattern="[0-9]{8}"
                            title="DNI debe tener 8 dígitos"
                            value={formData.dni}
                            onChange={handleChange}
                        />
                    </div>
                    <div className="input-group">
                        <label htmlFor="correo">Correo Electrónico</label>
                        <input
                            type="email"
                            id="correo"
                            name="correo"
                            required
                            value={formData.correo}
                            onChange={handleChange}
                        />
                    </div>
                    <div className="input-group">
                        <label htmlFor="password">Contraseña</label>
                        <input
                            type="password"
                            id="password"
                            name="password"
                            required
                            minLength={8}
                            value={formData.password}
                            onChange={handleChange}
                        />
                    </div>
                    <div className="input-group">
                        <label htmlFor="password_confirmation">Confirmar Contraseña</label>
                        <input
                            type="password"
                            id="password_confirmation"
                            name="password_confirmation"
                            required
                            minLength={8}
                            value={formData.password_confirmation}
                            onChange={handleChange}
                        />
                    </div>
                    {error && <div className="error-message">{error}</div>}
                    <button type="submit" className="btn btn-primary" disabled={loading}>
                        {loading ? 'Registrando...' : 'Registrar'}
                    </button>
                    <button 
                        type="button" 
                        className="btn btn-secondary" 
                        onClick={() => window.location.href = '/login'}
                    >
                        Volver al Login
                    </button>
                </form>
            </div>
        </div>
    );
};

export default Register;
