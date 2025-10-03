import React from 'react';
import MainLayout from '../../components/layout/MainLayout';
import { BarChart, Bar, XAxis, YAxis, CartesianGrid, Tooltip, Legend, ResponsiveContainer } from 'recharts';

const data = [
  { name: 'Nuevos Usuarios', value: 12 },
  { name: 'Exámenes Activos', value: 5 },
  { name: 'Exámenes Completados', value: 78 },
];

const AdminDashboard: React.FC = () => {
    return (
        <MainLayout>
            <h1>Dashboard</h1>
            <p>Resumen del sistema.</p>
            <div style={{ width: '100%', height: 300 }}>
                <ResponsiveContainer>
                    <BarChart data={data}>
                        <CartesianGrid strokeDasharray="3 3" />
                        <XAxis dataKey="name" />
                        <YAxis />
                        <Tooltip />
                        <Legend />
                        <Bar dataKey="value" fill="#1a1a2e" />
                    </BarChart>
                </ResponsiveContainer>
            </div>
        </MainLayout>
    );
};

export default AdminDashboard;