import { useState, useEffect } from 'react';
import clienteApi from '../api/clienteApi';

interface ApiError {
    response?: {
        data?: {
            message?: string;
        };
    };
    message?: string;
}

interface Usuario {
    idUsuario: number;
    nombre: string;
    apellidos: string;
    correo: string;
    rol: string;
    estado: string;
    created_at: string;
}

export const useUsuarios = () => {
    const [usuarios, setUsuarios] = useState<Usuario[]>([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState<string | null>(null);

    const fetchUsuarios = async () => {
        try {
            setLoading(true);
            setError(null);
            const response = await clienteApi.get('/admin/usuarios');
            setUsuarios(response.data || []);
        } catch (err: unknown) {
            const error = err as ApiError;
            setError(error.response?.data?.message || 'Error al cargar usuarios');
        } finally {
            setLoading(false);
        }
    };

    const aprobarUsuario = async (idUsuario: number) => {
        try {
            await clienteApi.patch(`/admin/usuarios/${idUsuario}/approve`);
            await fetchUsuarios();
            return { success: true };
        } catch (err: unknown) {
            const error = err as ApiError;
            return {
                success: false,
                error: error.response?.data?.message || 'Error al aprobar usuario'
            };
        }
    };

    const suspenderUsuario = async (idUsuario: number) => {
        try {
            await clienteApi.patch(`/admin/usuarios/${idUsuario}/suspend`);
            await fetchUsuarios();
            return { success: true };
        } catch (err: unknown) {
            const error = err as ApiError;
            return {
                success: false,
                error: error.response?.data?.message || 'Error al suspender usuario'
            };
        }
    };

    const eliminarUsuario = async (idUsuario: number) => {
        try {
            await clienteApi.delete(`/admin/usuarios/${idUsuario}`);
            await fetchUsuarios();
            return { success: true };
        } catch (err: unknown) {
            const error = err as ApiError;
            return {
                success: false,
                error: error.response?.data?.message || 'Error al eliminar usuario'
            };
        }
    };

    const crearUsuario = async (userData: Omit<Usuario, 'idUsuario' | 'created_at'> & { password: string }) => {
        try {
            await clienteApi.post('/admin/usuarios', userData);
            await fetchUsuarios();
            return { success: true };
        } catch (err: unknown) {
            const error = err as ApiError;
            return {
                success: false,
                error: error.response?.data?.message || 'Error al crear usuario'
            };
        }
    };

    const actualizarUsuario = async (idUsuario: number, userData: Partial<Usuario & { password?: string }>) => {
        try {
            const updateData = { ...userData };
            if (!updateData.password) {
                delete updateData.password;
            }

            await clienteApi.put(`/admin/usuarios/${idUsuario}`, updateData);
            await fetchUsuarios();
            return { success: true };
        } catch (err: unknown) {
            const error = err as ApiError;
            return {
                success: false,
                error: error.response?.data?.message || 'Error al actualizar usuario'
            };
        }
    };

    useEffect(() => {
        fetchUsuarios();
    }, []);

    return {
        usuarios,
        loading,
        error,
        fetchUsuarios,
        aprobarUsuario,
        suspenderUsuario,
        eliminarUsuario,
        crearUsuario,
        actualizarUsuario
    };
};
