import React from 'react';
import Sidebar from './Sidebar';
import '@res/css/MainLayout.css';
import ProtectedRoute from '../ProtectedRoute';
import { useAuth } from '../../hooks/useAuth';

interface MainLayoutProps {
    children: React.ReactNode;
}

const MainLayout: React.FC<MainLayoutProps> = ({ children }) => {
    const { user } = useAuth();

    const role = user?.rol === '0' ? 'admin' : 'docente';
    const fullName = user ? `${user.nombre.toUpperCase()} ${user.apellidos.toUpperCase()}` : '';

    return (
        <ProtectedRoute>
            <div className="main-layout">
                <Sidebar user={{ fullName, role: role as 'admin' | 'docente' }} />
                <main className="content-area">
                    {children}
                </main>
            </div>
        </ProtectedRoute>
    );
};

export default MainLayout;