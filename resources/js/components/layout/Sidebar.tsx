import React from 'react';
import '@res/css/Sidebar.css';
import logo from '../../assets/logo_leonor_cerna 2.png';
import { FaTachometerAlt, FaUsers, FaBook, FaClipboardList, FaChartBar, FaCogs, FaSignOutAlt } from 'react-icons/fa';
import { useAuth } from '../../hooks/useAuth';

interface SidebarProps {
    user: {
        fullName: string;
        role: 'admin' | 'docente';
    };
}

const adminLinks = [
    { text: 'Dashboard', icon: <FaTachometerAlt />, href: '/admin/dashboard' },
    { text: 'Usuarios', icon: <FaUsers />, href: '/admin/usuarios' },
    { text: 'Banco de Preguntas', icon: <FaBook />, href: '/admin/banco' },
    { text: 'Gestión de Exámenes', icon: <FaClipboardList />, href: '/admin/examenes' },
    { text: 'Resultados', icon: <FaChartBar />, href: '/admin/resultados' },
];

const docenteLinks = [
    { text: 'Examen', icon: <FaClipboardList />, href: '/docente/examen' },
    { text: 'Resultados', icon: <FaChartBar />, href: '/docente/resultados' },
];

const Sidebar: React.FC<SidebarProps> = ({ user }) => {
    const { logout } = useAuth();
    const links = user.role === 'admin' ? adminLinks : docenteLinks;

    const handleLogout = async () => {
        await logout();
        window.location.href = '/login';
    };

    return (
        <aside className="sidebar">
            <div className="sidebar-header">
                <img src={logo} alt="Logo" className="sidebar-logo" />
                <span>LEONOR CERNA DE VALDIVIEZO</span>
            </div>
            <nav className="sidebar-nav">
                {links.map((link, index) => (
                    <a href={link.href} key={index} className={index === 0 ? 'active' : ''}>
                        {link.icon}
                        <span>{link.text}</span>
                    </a>
                ))}
            </nav>
            <div className="sidebar-footer">
                <a href="#">
                    <span>{user.fullName}</span>
                </a>
                <a href="/settings/profile">
                    <FaCogs />
                    <span>Configuración</span>
                </a>
                <a href="#" onClick={handleLogout}>
                    <FaSignOutAlt />
                    <span>Cerrar Sesión</span>
                </a>
            </div>
        </aside>
    );
};

export default Sidebar;