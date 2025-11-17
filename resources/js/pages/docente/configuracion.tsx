import React, { useState, useEffect } from 'react';
import { Card } from '@/components/ui/card';
import { useAuth } from '../../hooks/useAuth';
import clienteApi from '../../api/clienteApi';

const ConfiguracionDocente: React.FC = () => {
  const { user, updateUser } = useAuth();
  const [loading, setLoading] = useState(false);
  const [message, setMessage] = useState<{ type: 'success' | 'error'; text: string } | null>(null);
  const [isEditingPerfil, setIsEditingPerfil] = useState(false);

  const [configuracion, setConfiguracion] = useState({
    notificaciones_email: true,
    notificaciones_sistema: true,
    mostrar_estadisticas: true,
    tema_oscuro: false,
  });

  const [perfilData, setPerfilData] = useState({
    nombre: user?.nombre || '',
    apellidos: user?.apellidos || '',
    correo: user?.correo || '',
    password: '',
    confirmPassword: '',
  });

  useEffect(() => {
    if (user) {
      setPerfilData({
        nombre: user.nombre || '',
        apellidos: user.apellidos || '',
        correo: user.correo || '',
        password: '',
        confirmPassword: '',
      });
    }
  }, [user]);

  const handleChange = (key: string, value: boolean) => {
    setConfiguracion(prev => ({
      ...prev,
      [key]: value,
    }));
  };

  const handlePerfilChange = (key: string, value: string) => {
    setPerfilData(prev => ({
      ...prev,
      [key]: value,
    }));
  };

  const handleSavePerfil = async () => {
    if (perfilData.password && perfilData.password !== perfilData.confirmPassword) {
      setMessage({ type: 'error', text: 'Las contraseñas no coinciden' });
      return;
    }

    setLoading(true);
    setMessage(null);

    try {
      const updateData: {
        nombre: string;
        apellidos: string;
        correo: string;
        password?: string;
        password_confirmation?: string;
      } = {
        nombre: perfilData.nombre,
        apellidos: perfilData.apellidos,
        correo: perfilData.correo,
      };

      if (perfilData.password) {
        updateData.password = perfilData.password;
        updateData.password_confirmation = perfilData.confirmPassword;
      }

      await clienteApi.put('/profile', updateData);

      if (updateUser) {
        updateUser({
          ...user,
          nombre: perfilData.nombre,
          apellidos: perfilData.apellidos,
          correo: perfilData.correo,
        });
      }

      setPerfilData(prev => ({ ...prev, password: '', confirmPassword: '' }));
      setIsEditingPerfil(false);
      setMessage({ type: 'success', text: 'Perfil actualizado exitosamente' });
    } catch (error: unknown) {
      const apiError = error as { response?: { data?: { message?: string } } };
      setMessage({
        type: 'error',
        text: apiError.response?.data?.message || 'Error al actualizar el perfil'
      });
    } finally {
      setLoading(false);
    }
  };

  const handleSave = async () => {
    setLoading(true);
    setMessage(null);

    try {
      // Aquí se guardaría la configuración en el backend
      // Por ahora solo simulamos el guardado
      await new Promise(resolve => setTimeout(resolve, 500));

      setMessage({ type: 'success', text: 'Configuración guardada exitosamente' });
    } catch {
      setMessage({ type: 'error', text: 'Error al guardar la configuración' });
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="min-h-screen bg-gray-50 p-6">
      <div className="max-w-4xl mx-auto">
        {/* Header */}
        <div className="mb-6">
          <h1 className="text-3xl font-bold text-gray-900">Configuración Personal</h1>
          <p className="text-gray-600 mt-2">Gestiona tus preferencias y opciones personales</p>
        </div>

        {message && (
          <div className={`mb-6 p-4 rounded-lg ${
            message.type === 'success'
              ? 'bg-green-50 border border-green-200 text-green-800'
              : 'bg-red-50 border border-red-200 text-red-800'
          }`}>
            {message.text}
          </div>
        )}

        {/* Preferencias de Visualización */}
        <Card className="p-6 mb-6">
          <h2 className="text-xl font-semibold text-gray-900 mb-4">Preferencias de Visualización</h2>
          <div className="space-y-4">
            <div className="flex items-center justify-between">
              <div>
                <label className="text-sm font-medium text-gray-700">Mostrar Estadísticas</label>
                <p className="text-xs text-gray-500 mt-1">Mostrar estadísticas personales en el dashboard</p>
              </div>
              <label className="relative inline-flex items-center cursor-pointer">
                <input
                  type="checkbox"
                  checked={configuracion.mostrar_estadisticas}
                  onChange={(e) => handleChange('mostrar_estadisticas', e.target.checked)}
                  className="sr-only peer"
                />
                <div className="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
              </label>
            </div>

            <div className="flex items-center justify-between">
              <div>
                <label className="text-sm font-medium text-gray-700">Tema Oscuro</label>
                <p className="text-xs text-gray-500 mt-1">Activar modo oscuro (próximamente)</p>
              </div>
              <label className="relative inline-flex items-center cursor-pointer">
                <input
                  type="checkbox"
                  checked={configuracion.tema_oscuro}
                  onChange={(e) => handleChange('tema_oscuro', e.target.checked)}
                  disabled
                  className="sr-only peer"
                />
                <div className="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600 opacity-50 cursor-not-allowed"></div>
              </label>
            </div>
          </div>
        </Card>

        {/* Notificaciones */}
        <Card className="p-6 mb-6">
          <h2 className="text-xl font-semibold text-gray-900 mb-4">Notificaciones</h2>
          <div className="space-y-4">
            <div className="flex items-center justify-between">
              <div>
                <label className="text-sm font-medium text-gray-700">Notificaciones por Email</label>
                <p className="text-xs text-gray-500 mt-1">Recibir notificaciones sobre tus exámenes por correo</p>
              </div>
              <label className="relative inline-flex items-center cursor-pointer">
                <input
                  type="checkbox"
                  checked={configuracion.notificaciones_email}
                  onChange={(e) => handleChange('notificaciones_email', e.target.checked)}
                  className="sr-only peer"
                />
                <div className="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
              </label>
            </div>

            <div className="flex items-center justify-between">
              <div>
                <label className="text-sm font-medium text-gray-700">Notificaciones del Sistema</label>
                <p className="text-xs text-gray-500 mt-1">Mostrar notificaciones en el panel</p>
              </div>
              <label className="relative inline-flex items-center cursor-pointer">
                <input
                  type="checkbox"
                  checked={configuracion.notificaciones_sistema}
                  onChange={(e) => handleChange('notificaciones_sistema', e.target.checked)}
                  className="sr-only peer"
                />
                <div className="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
              </label>
            </div>
          </div>
        </Card>

        {/* RF-D.1.1: Gestión de Perfil (Escala Actual) */}
        <Card className="p-6 mb-6">
          <div className="flex justify-between items-center mb-4">
            <h2 className="text-xl font-semibold text-gray-900">Mi Perfil</h2>
            {!isEditingPerfil ? (
              <button
                onClick={() => setIsEditingPerfil(true)}
                className="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700 transition-colors"
              >
                Editar Perfil
              </button>
            ) : (
              <div className="flex gap-2">
                <button
                  onClick={handleSavePerfil}
                  disabled={loading}
                  className="px-4 py-2 bg-green-600 text-white rounded-lg text-sm font-medium hover:bg-green-700 transition-colors disabled:opacity-50"
                >
                  {loading ? 'Guardando...' : 'Guardar'}
                </button>
                <button
                  onClick={() => {
                    setIsEditingPerfil(false);
                    setPerfilData({
                      nombre: user?.nombre || '',
                      apellidos: user?.apellidos || '',
                      correo: user?.correo || '',
                      password: '',
                      confirmPassword: '',
                    });
                    setMessage(null);
                  }}
                  disabled={loading}
                  className="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-300 transition-colors disabled:opacity-50"
                >
                  Cancelar
                </button>
              </div>
            )}
          </div>

          <div className="space-y-4">
            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-1">Nombre</label>
                {isEditingPerfil ? (
                  <input
                    type="text"
                    value={perfilData.nombre}
                    onChange={(e) => handlePerfilChange('nombre', e.target.value)}
                    className="w-full border border-gray-300 rounded-lg px-3 py-2"
                  />
                ) : (
                  <p className="text-gray-900">{user?.nombre}</p>
                )}
              </div>

              <div>
                <label className="block text-sm font-medium text-gray-700 mb-1">Apellidos</label>
                {isEditingPerfil ? (
                  <input
                    type="text"
                    value={perfilData.apellidos}
                    onChange={(e) => handlePerfilChange('apellidos', e.target.value)}
                    className="w-full border border-gray-300 rounded-lg px-3 py-2"
                  />
                ) : (
                  <p className="text-gray-900">{user?.apellidos}</p>
                )}
              </div>

              <div>
                <label className="block text-sm font-medium text-gray-700 mb-1">Correo Electrónico</label>
                {isEditingPerfil ? (
                  <input
                    type="email"
                    value={perfilData.correo}
                    onChange={(e) => handlePerfilChange('correo', e.target.value)}
                    className="w-full border border-gray-300 rounded-lg px-3 py-2"
                  />
                ) : (
                  <p className="text-gray-900">{user?.correo}</p>
                )}
              </div>


              {isEditingPerfil && (
                <>
                  <div>
                    <label className="block text-sm font-medium text-gray-700 mb-1">
                      Nueva Contraseña (opcional)
                    </label>
                    <input
                      type="password"
                      value={perfilData.password}
                      onChange={(e) => handlePerfilChange('password', e.target.value)}
                      className="w-full border border-gray-300 rounded-lg px-3 py-2"
                      placeholder="Dejar vacío para no cambiar"
                    />
                  </div>

                  <div>
                    <label className="block text-sm font-medium text-gray-700 mb-1">
                      Confirmar Contraseña
                    </label>
                    <input
                      type="password"
                      value={perfilData.confirmPassword}
                      onChange={(e) => handlePerfilChange('confirmPassword', e.target.value)}
                      className="w-full border border-gray-300 rounded-lg px-3 py-2"
                      placeholder="Confirmar nueva contraseña"
                    />
                  </div>
                </>
              )}
            </div>
          </div>
        </Card>

        {/* Botón Guardar */}
        <div className="flex justify-end">
          <button
            onClick={handleSave}
            disabled={loading}
            className="px-6 py-3 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
          >
            {loading ? 'Guardando...' : 'Guardar Configuración'}
          </button>
        </div>
      </div>
    </div>
  );
};

export default ConfiguracionDocente;

