import React, { useState, useEffect } from 'react';
import { Link, useLocation, useNavigate } from 'react-router-dom';
import { auth } from '@/lib/auth';
import { LoadingBar } from '@/components/ui/loading-bar';

interface DefaultLayoutProps {
    children: React.ReactNode;
}

export default function DefaultLayout({ children }: DefaultLayoutProps) {
    const [isSidebarCollapsed, setIsSidebarCollapsed] = useState(true);
    const [loading, setLoading] = useState(false);
    const location = useLocation();
    const navigate = useNavigate();

    // Check if the current path matches the link path
    const isActivePath = (path: string) => {
        return location.pathname === path;
    };

    // Effect to add custom styles to body
    useEffect(() => {
        document.body.classList.add('bg-gray-50');
        return () => {
            document.body.classList.remove('bg-gray-50');
        };
    }, []);

    const handleLogout = async () => {
        setLoading(true);
        try {
            await auth.logout();
            navigate('/login', { replace: true });
        } catch (error) {
            console.error('Logout failed:', error);
            // Even if logout fails, we'll force logout on frontend
            navigate('/login', { replace: true });
        } finally {
            setLoading(false);
        }
    };

    return (
        <>
            <LoadingBar isLoading={loading} />
            <div className="flex min-h-screen">
                {/* Sidebar */}
                <aside 
                    className={`sidebar bg-white border-r transition-[width] duration-300 ease-in-out relative flex flex-col h-screen ${
                        isSidebarCollapsed ? 'w-12' : 'w-64'
                    }`}
                >
                    {/* Toggle Button */}
                    <button 
                        onClick={() => setIsSidebarCollapsed(!isSidebarCollapsed)}
                        className="absolute -right-3 top-16 bg-white border border-gray-200 rounded-full w-6 h-6 flex items-center justify-center cursor-pointer z-50 hover:bg-gray-50"
                    >
                        <svg 
                            className={`w-4 h-4 text-gray-600 transform transition-transform duration-300 ${
                                isSidebarCollapsed ? 'rotate-180' : ''
                            }`} 
                            fill="none" 
                            stroke="currentColor" 
                            viewBox="0 0 24 24"
                        >
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M15 19l-7-7 7-7" />
                        </svg>
                    </button>

                    {/* Sidebar Content */}
                    <div className="flex-1 flex flex-col">
                        {/* Logo Section */}
                        <div className={`p-4 ${isSidebarCollapsed ? 'p-2' : ''}`}>
                            <div className="flex items-center gap-3">
                                {/* App Logo */}
                                <div className="w-8 h-8 bg-gradient-to-r from-blue-500 to-purple-500 rounded-full flex items-center justify-center flex-shrink-0">
                                    <svg className="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                    </svg>
                                </div>
                                {/* App Title - Hidden when collapsed */}
                                {!isSidebarCollapsed && (
                                    <div>
                                        <div className="font-bold text-lg bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent">DigiPharmacy</div>
                                        <div className="text-sm text-gray-500">Pharmacy Management</div>
                                    </div>
                                )}
                            </div>
                        </div>

                        {/* Main Navigation */}
                        <nav className="flex-1">
                            <Link 
                                to="/dashboard"
                                className={`flex items-center px-4 py-2 transition-colors ${
                                    isActivePath('/dashboard')
                                        ? 'bg-gradient-to-r from-blue-500 to-purple-500 text-white'
                                        : 'text-gray-700 hover:bg-gray-50'
                                } ${isSidebarCollapsed ? 'justify-center px-2' : ''}`}
                            >
                                <svg className={`w-5 h-5 ${!isSidebarCollapsed && 'mr-3'}`} fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M4 6h16M4 12h16M4 18h16" />
                                </svg>
                                {!isSidebarCollapsed && <span>Dashboard</span>}
                            </Link>

                            <Link 
                                to="/pos"
                                className={`flex items-center px-4 py-2 transition-colors ${
                                    isActivePath('/pos')
                                        ? 'bg-gradient-to-r from-blue-500 to-purple-500 text-white'
                                        : 'text-gray-700 hover:bg-gray-50'
                                } ${isSidebarCollapsed ? 'justify-center px-2' : ''}`}
                            >
                                <svg className={`w-5 h-5 ${!isSidebarCollapsed && 'mr-3'}`} fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                                </svg>
                                {!isSidebarCollapsed && <span>Kasir/POS</span>}
                            </Link>

                            <Link 
                                to="/prescriptions"
                                className={`flex items-center px-4 py-2 transition-colors ${
                                    isActivePath('/prescriptions')
                                        ? 'bg-gradient-to-r from-blue-500 to-purple-500 text-white'
                                        : 'text-gray-700 hover:bg-gray-50'
                                } ${isSidebarCollapsed ? 'justify-center px-2' : ''}`}
                            >
                                <svg className={`w-5 h-5 ${!isSidebarCollapsed && 'mr-3'}`} fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                                </svg>
                                {!isSidebarCollapsed && <span>Resep</span>}
                            </Link>

                            <Link 
                                to="/customers"
                                className={`flex items-center px-4 py-2 transition-colors ${
                                    isActivePath('/customers')
                                        ? 'bg-gradient-to-r from-blue-500 to-purple-500 text-white'
                                        : 'text-gray-700 hover:bg-gray-50'
                                } ${isSidebarCollapsed ? 'justify-center px-2' : ''}`}
                            >
                                <svg className={`w-5 h-5 ${!isSidebarCollapsed && 'mr-3'}`} fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                                {!isSidebarCollapsed && <span>Pelanggan</span>}
                            </Link>

                            <Link 
                                to="/inventory"
                                className={`flex items-center px-4 py-2 transition-colors ${
                                    isActivePath('/inventory')
                                        ? 'bg-gradient-to-r from-blue-500 to-purple-500 text-white'
                                        : 'text-gray-700 hover:bg-gray-50'
                                } ${isSidebarCollapsed ? 'justify-center px-2' : ''}`}
                            >
                                <svg className={`w-5 h-5 ${!isSidebarCollapsed && 'mr-3'}`} fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                                </svg>
                                {!isSidebarCollapsed && <span>Stok Obat</span>}
                            </Link>                        
                        </nav>

                        {/* Logout Button */}
                        <div className="mt-auto border-t">
                            <button
                                onClick={handleLogout}
                                className={`w-full flex items-center px-4 py-3 text-red-600 hover:bg-red-50 transition-colors rounded-md ${
                                    isSidebarCollapsed ? 'justify-center px-2' : ''
                                }`}
                            >
                                <svg 
                                    className={`w-5 h-5 ${!isSidebarCollapsed && 'mr-3'}`} 
                                    fill="none" 
                                    stroke="currentColor" 
                                    viewBox="0 0 24 24"
                                >
                                    <path 
                                        strokeLinecap="round" 
                                        strokeLinejoin="round" 
                                        strokeWidth="2" 
                                        d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" 
                                    />
                                </svg>
                                {!isSidebarCollapsed && <span>Logout</span>}
                            </button>
                        </div>
                    </div>
                </aside>

                {/* Main Content */}
                <main className={`flex-1 transition-[margin] duration-300 ease-in-out ${isSidebarCollapsed ? 'ml-0' : 'ml-0'}`}>
                    {children}
                </main>
            </div>
        </>
    );
}
