/**
 * Main application component for DigiPharmacy
 * Handles routing, authentication, and layout management
 */
import React, { useEffect, useState } from 'react';
import { BrowserRouter as Router, Routes, Route, Navigate, useNavigate, useLocation } from 'react-router-dom';
import Login from './auth/Login';
import POS from './pos/POS';
import DefaultLayout from './layouts/DefaultLayout';
import Dashboard from './dashboard/Dashboard';
import Prescriptions from './prescriptions/Prescriptions';
import Customers from './customers/Customers';
import Inventory from './inventory/Inventory';
import { auth } from '@/lib/auth';

/**
 * Custom hook to handle authentication state
 * Uses auth service to track login status
 * @returns {boolean} Current authentication status
 */
const useAuth = () => {
    const [isLoggedIn, setIsLoggedIn] = useState(() => {
        return auth.isAuthenticated();
    });

    useEffect(() => {
        // Check auth status when component mounts
        setIsLoggedIn(auth.isAuthenticated());

        // Listen for storage changes (for multi-tab support)
        const handleStorageChange = (e: StorageEvent) => {
            if (e.key === 'auth') {
                setIsLoggedIn(auth.isAuthenticated());
            }
        };

        window.addEventListener('storage', handleStorageChange);
        return () => window.removeEventListener('storage', handleStorageChange);
    }, []);

    return isLoggedIn;
};

/**
 * Protected Route Component
 * Ensures routes are only accessible to authenticated users
 * Redirects unauthenticated users to login page
 * Uses default layout by default for authenticated users
 * 
 * @param {Object} props - Component props
 * @param {React.ReactNode} props.children - Child components to render
 * @param {boolean} [props.useDefaultLayout=true] - Whether to use default layout
 */
const ProtectedRoute = ({ children, useDefaultLayout = true }: { children: React.ReactNode, useDefaultLayout?: boolean }) => {
    const isLoggedIn = useAuth();
    const location = useLocation();

    if (!isLoggedIn) {
        // Redirect to login page but save the attempted location
        return <Navigate to="/login" state={{ from: location }} replace />;
    }

    if (!useDefaultLayout) {
        // return <>{children}</>;
        return <DefaultLayout>{children}</DefaultLayout>;
    }

    return <DefaultLayout>{children}</DefaultLayout>;
};

/**
 * Public Route Component
 * Handles routes that should only be accessible to unauthenticated users
 * Redirects authenticated users to dashboard or their previous location
 * 
 * @param {Object} props - Component props
 * @param {React.ReactNode} props.children - Child components to render
 */
const PublicRoute = ({ children }: { children: React.ReactNode }) => {
    const isLoggedIn = useAuth();
    const location = useLocation();

    if (isLoggedIn) {
        // Redirect to the page they came from or dashboard
        return <Navigate to={(location.state as any)?.from?.pathname || '/dashboard'} replace />;
    }

    return <>{children}</>;
};

/**
 * Main layout component
 * Provides consistent styling and structure across the application
 * Conditionally renders navbar based on authentication and route
 * 
 * @param {Object} props - Component props
 * @param {React.ReactNode} props.children - Child components to render
 */
const AppLayout = ({ children }: { children: React.ReactNode }) => {
    const location = useLocation();
    const isLoggedIn = useAuth();
    const isPOSRoute = location.pathname.startsWith('/pos');

    return (
        <div className="min-h-screen bg-white dark:bg-slate-900">
            {isLoggedIn && !isPOSRoute}
            <main className={`flex-1 w-full min-h-screen ${
                !isPOSRoute && 'bg-gradient-to-b from-blue-50 to-white dark:from-slate-900 dark:to-slate-800'
            }`}>
                {children}
            </main>
        </div>
    );
};

/**
 * Root Application Component
 * Sets up routing structure and handles initial authentication check
 * Provides layout wrapper for all routes
 */
const App: React.FC = () => {
    const [isLoading, setIsLoading] = useState(true);

    useEffect(() => {
        // Check initial auth status
        setIsLoading(false);
    }, []);

    if (isLoading) {
        return <div>Loading...</div>;
    }

    return (
        <Router>
            <AppLayout>
                <Routes>
                    {/* Public routes */}
                    <Route path="/login" element={
                        <PublicRoute>
                            <Login />
                        </PublicRoute>
                    } />

                    {/* Protected routes */}
                    <Route path="/dashboard" element={
                        <ProtectedRoute>
                            <Dashboard />
                        </ProtectedRoute>
                    } />
                    
                    <Route path="/pos/*" element={
                        <ProtectedRoute>
                            <POS />
                        </ProtectedRoute>
                    } />

                    <Route path="/prescriptions" element={
                        <ProtectedRoute>
                            <Prescriptions />
                        </ProtectedRoute>
                    } />

                    <Route path="/customers" element={
                        <ProtectedRoute>
                            <Customers />
                        </ProtectedRoute>
                    } />

                    <Route path="/inventory" element={
                        <ProtectedRoute>
                            <Inventory />
                        </ProtectedRoute>
                    } />

                    {/* Default route */}
                    <Route path="/" element={
                        <ProtectedRoute>
                            <Navigate to="/dashboard" replace />
                        </ProtectedRoute>
                    } />

                    {/* Catch all route */}
                    <Route path="*" element={
                        <ProtectedRoute>
                            <Navigate to="/dashboard" replace />
                        </ProtectedRoute>
                    } />
                </Routes>
            </AppLayout>
        </Router>
    );
};

export default App;
