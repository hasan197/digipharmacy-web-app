import React, { useState, useEffect, useRef } from 'react';
import { api } from '@/lib/auth';
import SaleCard from './SaleCard';
import { FiFilter, FiSearch } from 'react-icons/fi';
import { formatPrice } from '@/lib/utils';
import SaleDetailsModal from './SaleDetailsModal';

interface ApiSale {
    id: number;
    invoice_number: string;
    customer_name: string | null;
    status: 'completed' | 'pending';
    created_at: string | null;
    grand_total: number;
    payment_method: string;
    details: any[];
}

interface Sale {
    id: number;
    invoice_number: string;
    customer: string | null;
    status: 'completed' | 'pending';
    time: string;
    amount: number;
    type: string;
    itemCount: number;
}

interface ApiResponse {
    data: ApiSale[];
    pagination: {
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
    };
}

const SalesList = () => {
    const [viewMode, setViewMode] = useState<'card' | 'table'>(() => {
        const savedMode = localStorage.getItem('salesListViewMode');
        return (savedMode === 'card' || savedMode === 'table') ? savedMode : 'card';
    });
    const [sales, setSales] = useState<Sale[]>([]);
    const [isModalOpen, setIsModalOpen] = useState(false);
    const [saleDetails, setSaleDetails] = useState(null);
    // Pagination state with default values
    const [pagination, setPagination] = useState({
        currentPage: 1,
        lastPage: 1,
        perPage: 10,
        total: 0,
        from: 1,
        to: 10
    });
    const [searchTerm, setSearchTerm] = useState('');
    const [sortCriteria, setSortCriteria] = useState('date');
    const [statusFilter, setStatusFilter] = useState('all');
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState('');
    const [showFilters, setShowFilters] = useState(false);
    const requestSent = useRef(false);

    useEffect(() => {
        const auth = localStorage.getItem('auth');
        if (!auth) {
            window.location.href = '/login';
            return;
        }
        fetchSales(1);
    }, []);

    // Add useEffect for saving viewMode to localStorage
    useEffect(() => {
        localStorage.setItem('salesListViewMode', viewMode);
    }, [viewMode]);

    const handleViewSale = async (saleId: number) => {
        try {
            const response = await api.get(`/sales/${saleId}`);
            setSaleDetails(response.data);
            setIsModalOpen(true);
        } catch (error) {
            console.error('Error fetching sale details:', error);
            alert('Failed to fetch sale details');
        }
    };

    const fetchSales = async (page = 1) => {
        try {
            setLoading(true);
            const response = await api.get<ApiResponse>('/sales', {
                params: {
                    page,
                    per_page: 10
                }
            });
            
            const { data: salesData, pagination } = response.data;
            
            // Calculate from and to for display
            const from = ((pagination.current_page - 1) * pagination.per_page) + 1;
            const to = Math.min(pagination.current_page * pagination.per_page, pagination.total);
            
            // Map backend data to frontend format
            const formattedSales = salesData.map(sale => ({
                id: sale.id,
                invoice_number: sale.invoice_number,
                customer: sale.customer_name,
                status: sale.status,
                time: sale.created_at || new Date().toISOString(),
                amount: sale.grand_total,
                type: sale.payment_method,
                itemCount: sale.details?.length || 0
            }));
            
            setSales(formattedSales);
            setPagination({
                currentPage: pagination.current_page,
                lastPage: pagination.last_page,
                perPage: pagination.per_page,
                total: pagination.total,
                from,
                to
            });
            console.log('Sales data fetched successfully');
            setLoading(false);
        } catch (err) {
            console.error('Error fetching sales:', err);
            setError('Failed to load sales data');
            setLoading(false);
        }
    };

    const filteredSales = sales
        .filter(sale => {
            if (!searchTerm) return true;
            
            const customerMatch = sale.customer ? 
                sale.customer.toLowerCase().includes(searchTerm.toLowerCase()) : 
                false;
            const invoiceMatch = sale.invoice_number.toLowerCase().includes(searchTerm.toLowerCase());
            const idMatch = sale.id.toString().includes(searchTerm);
            
            return customerMatch || invoiceMatch || idMatch;
        })
        .filter(sale => statusFilter === 'all' || sale.status === statusFilter)
        .sort((a, b) => {
            if (sortCriteria === 'date') {
                return new Date(b.time).getTime() - new Date(a.time).getTime();
            } else if (sortCriteria === 'amount') {
                return b.amount - a.amount;
            }
            return 0;
        });

    const handlePageChange = async (newPage: number) => {
        if (newPage === pagination.currentPage) return;
        if (newPage < 1 || newPage > pagination.lastPage) return;
        
        try {
            await fetchSales(newPage);
        } catch (error) {
            console.error('Error changing page:', error);
        }
    };

    const handleExport = () => {
        const csvContent = 'data:text/csv;charset=utf-8,' +
            filteredSales.map(sale => `${sale.invoice_number},${sale.customer},${sale.status},${sale.time}`).join('\n');
        const encodedUri = encodeURI(csvContent);
        const link = document.createElement('a');
        link.setAttribute('href', encodedUri);
        link.setAttribute('download', 'sales_data.csv');
        document.body.appendChild(link);
        link.click();
    };

    if (loading) {
        return (
            <div className="p-4 flex items-center justify-center min-h-[200px]">
                <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500"></div>
            </div>
        );
    }

    if (error) {
        return (
            <div className="p-4 text-red-500 text-center">
                <p>{error}</p>
                <button 
                    onClick={() => fetchSales(pagination.currentPage)}
                    className="mt-2 px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600"
                >
                    Retry
                </button>
            </div>
        );
    }

    return (
        <div className="p-4 md:p-6 max-w-7xl mx-auto">
            {/* Header with View Toggle */}
            <div className="flex flex-col md:flex-row md:items-center justify-between mb-6">
                <h1 className="text-2xl font-bold mb-4 md:mb-0">Riwayat Transaksi</h1>
                <div className="flex items-center bg-gray-100 rounded-lg p-1">
                    <button
                        onClick={() => setViewMode('card')}
                        className={`px-3 py-1.5 rounded-md text-sm font-medium transition-all duration-200 ${
                            viewMode === 'card'
                            ? 'bg-white text-gray-900 shadow-sm'
                            : 'text-gray-600 hover:text-gray-900'
                        }`}
                    >
                        <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                        </svg>
                    </button>
                    <button
                        onClick={() => setViewMode('table')}
                        className={`px-3 py-1.5 rounded-md text-sm font-medium transition-all duration-200 ${
                            viewMode === 'table'
                            ? 'bg-white text-gray-900 shadow-sm'
                            : 'text-gray-600 hover:text-gray-900'
                        }`}
                    >
                        <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                        </svg>
                    </button>
                </div>
            </div>

            {/* Search and Filters */}
            <div className="flex flex-wrap items-center gap-4 mb-6">
                <div className="flex-1 min-w-[240px] relative">
                    <input
                        type="text"
                        placeholder="Cari invoice atau nama pelanggan..."
                        value={searchTerm}
                        onChange={(e) => setSearchTerm(e.target.value)}
                        className="w-full pl-10 pr-4 py-2 rounded-lg border border-gray-200 focus:border-purple-500 focus:ring-1 focus:ring-purple-500"
                    />
                    <FiSearch className="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" />
                </div>

                <div className="flex items-center gap-2 flex-wrap">
                    <select 
                        value={statusFilter} 
                        onChange={(e) => setStatusFilter(e.target.value)} 
                        className="px-3 py-2 rounded-lg border border-gray-200 text-sm focus:border-purple-500 focus:ring-1 focus:ring-purple-500"
                    >
                        <option value="all">Semua Status</option>
                        <option value="completed">Selesai</option>
                        <option value="pending">Pending</option>
                    </select>

                    <select 
                        value={sortCriteria} 
                        onChange={(e) => setSortCriteria(e.target.value)} 
                        className="px-3 py-2 rounded-lg border border-gray-200 text-sm focus:border-purple-500 focus:ring-1 focus:ring-purple-500"
                    >
                        <option value="date">Urutkan: Tanggal</option>
                        <option value="amount">Urutkan: Nominal</option>
                    </select>

                    <button
                        onClick={handleExport}
                        className="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 text-sm font-medium transition-colors"
                    >
                        Export CSV
                    </button>
                </div>
            </div>

            {/* Sales List */}
            {viewMode === 'card' ? (
                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    {filteredSales.map((sale) => (
                        <SaleCard
                            key={sale.id}
                            id={sale.id}
                            invoice_number={sale.invoice_number}
                            type={sale.type}
                            customer={sale.customer || '-'}
                            status={sale.status}
                            time={sale.time}
                            itemCount={sale.itemCount || 0}
                            amount={sale.amount}
                        />
                    ))}
                </div>
            ) : (
                <div className="overflow-x-auto rounded-lg border border-gray-200">
                    <table className="min-w-full divide-y divide-gray-200">
                        <thead className="bg-gray-50">
                            <tr>
                                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Invoice</th>
                                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
                                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Time</th>
                                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                <th className="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                            </tr>
                        </thead>
                        <tbody className="bg-white divide-y divide-gray-200">
                            {filteredSales.map(sale => (
                                <tr key={sale.id} className="hover:bg-gray-50">
                                    <td className="px-6 py-4 whitespace-nowrap">
                                        <div className="text-sm font-medium text-gray-900">{sale.invoice_number}</div>
                                        <div className="text-sm text-gray-500">#{sale.id}</div>
                                    </td>
                                    <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {sale.customer || '-'}
                                    </td>
                                    <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {sale.time ? new Date(sale.time).toLocaleString('id-ID') : '-'}
                                    </td>
                                    <td className="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        {formatPrice(sale.amount)}
                                    </td>
                                    <td className="px-6 py-4 whitespace-nowrap">
                                        <span className={`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${
                                            sale.status === 'completed' 
                                                ? 'bg-green-100 text-green-800' 
                                                : 'bg-yellow-100 text-yellow-800'
                                        }`}>
                                            {sale.status}
                                        </span>
                                    </td>
                                    <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {sale.type}
                                    </td>
                                    <td className="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <button
                                            onClick={() => handleViewSale(sale.id)}
                                            className="text-purple-600 hover:text-purple-900"
                                        >
                                            View
                                        </button>
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>
            )}
            {filteredSales.length === 0 && (
                <div className="text-center py-8 text-gray-500 dark:text-gray-400">
                    <p>No sales found</p>
                    {(searchTerm || statusFilter !== 'all') && (
                        <button
                            onClick={() => {
                                setSearchTerm('');
                                setStatusFilter('all');
                            }}
                            className="mt-2 text-blue-500 hover:text-blue-600"
                        >
                            Clear filters
                        </button>
                    )}
                </div>
            )}

            {/* Pagination and Total Records */}
            <div className="mt-6 flex flex-col items-center gap-4">
                <div className="flex items-center gap-2">
                    <button
                        onClick={() => handlePageChange(1)}
                        disabled={pagination.currentPage === 1}
                        className={`px-3 py-1.5 rounded-lg text-sm font-medium transition-colors ${pagination.currentPage === 1
                            ? 'bg-gray-100 text-gray-400 cursor-not-allowed'
                            : 'bg-gray-100 text-gray-600 hover:bg-gray-200'}`}
                    >
                        First
                    </button>

                    <button
                        onClick={() => handlePageChange(pagination.currentPage - 1)}
                        disabled={pagination.currentPage === 1}
                        className={`px-3 py-1.5 rounded-lg text-sm font-medium transition-colors ${pagination.currentPage === 1
                            ? 'bg-gray-100 text-gray-400 cursor-not-allowed'
                            : 'bg-gray-100 text-gray-600 hover:bg-gray-200'}`}
                    >
                        Previous
                    </button>

                    {Array.from({ length: pagination.lastPage }, (_, i) => i + 1)
                        .filter(page => {
                            const diff = Math.abs(page - pagination.currentPage);
                            return diff <= 1 || page === 1 || page === pagination.lastPage;
                        })
                        .map((page, index, array) => {
                            if (index > 0 && page - array[index - 1] > 1) {
                                return [
                                    <span key={`ellipsis-${page}`} className="px-2 text-gray-400">...</span>,
                                    <button
                                        key={page}
                                        onClick={() => handlePageChange(page)}
                                        className={`min-w-[2.5rem] px-3 py-1.5 rounded-lg text-sm font-medium transition-colors ${pagination.currentPage === page
                                            ? 'bg-purple-600 text-white hover:bg-purple-700'
                                            : 'bg-gray-100 text-gray-600 hover:bg-gray-200'}`}
                                    >
                                        {page}
                                    </button>
                                ];
                            }
                            return (
                                <button
                                    key={page}
                                    onClick={() => handlePageChange(page)}
                                    className={`min-w-[2.5rem] px-3 py-1.5 rounded-lg text-sm font-medium transition-colors ${pagination.currentPage === page
                                        ? 'bg-purple-600 text-white hover:bg-purple-700'
                                        : 'bg-gray-100 text-gray-600 hover:bg-gray-200'}`}
                                >
                                    {page}
                                </button>
                            );
                        })
                    }

                    <button
                        onClick={() => handlePageChange(pagination.currentPage + 1)}
                        disabled={pagination.currentPage === pagination.lastPage}
                        className={`px-3 py-1.5 rounded-lg text-sm font-medium transition-colors ${pagination.currentPage === pagination.lastPage
                            ? 'bg-gray-100 text-gray-400 cursor-not-allowed'
                            : 'bg-gray-100 text-gray-600 hover:bg-gray-200'}`}
                    >
                        Next
                    </button>

                    <button
                        onClick={() => handlePageChange(pagination.lastPage)}
                        disabled={pagination.currentPage === pagination.lastPage}
                        className={`px-3 py-1.5 rounded-lg text-sm font-medium transition-colors ${pagination.currentPage === pagination.lastPage
                            ? 'bg-gray-100 text-gray-400 cursor-not-allowed'
                            : 'bg-gray-100 text-gray-600 hover:bg-gray-200'}`}
                    >
                        Last
                    </button>
                </div>

                <div className="text-sm text-gray-600">
                    {pagination.total > 0 ? (
                        <>Menampilkan {pagination.from} - {pagination.to} dari {pagination.total} transaksi</>
                    ) : (
                        'Tidak ada transaksi'
                    )}
                </div>
            </div>
            <SaleDetailsModal
                isOpen={isModalOpen}
                onClose={() => setIsModalOpen(false)}
                sale={saleDetails}
            />
        </div>
    );
};

export default SalesList;
