import React from 'react';
import { Navigate } from 'react-router-dom';
import { auth } from '@/lib/auth';

interface AdminRouteProps {
    children: React.ReactNode;
}

const AdminRoute: React.FC<AdminRouteProps> = ({ children }) => {
    const user = auth.getUser();
    const isAdmin = user?.roles?.some(role => role.name === 'admin') || false;

    if (!isAdmin) {
        return <Navigate to="/dashboard" replace />;
    }

    return <>{children}</>;
};

export default AdminRoute;
