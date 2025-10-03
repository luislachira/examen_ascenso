import React from 'react';
import { useAuth } from '../hooks/useAuth';

interface ProtectedRouteProps {
    children: React.ReactNode;
}

const ProtectedRoute: React.FC<ProtectedRouteProps> = ({ children }) => {
    const { isAuthenticated } = useAuth();

    if (!isAuthenticated) {
        window.location.href = '/login';
        return null;
    }

    return <>{children}</>;
};

export default ProtectedRoute;
