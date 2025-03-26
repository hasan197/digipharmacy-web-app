import React, { useState } from 'react';
import axios from 'axios';
import { FiClock, FiPackage, FiEye } from 'react-icons/fi';
import SaleDetailsModal from './SaleDetailsModal';
import { formatPrice } from '../../lib/utils';

interface SaleCardProps {
    id: number;
    invoice_number: string;
    type: string;
    customer: string;
    status: 'completed' | 'pending';
    time: string | null;
    itemCount: number;
    amount: number;
}

const SaleCard: React.FC<SaleCardProps> = ({ id, invoice_number, type, customer, status, time, itemCount, amount }) => {
    const [isModalOpen, setIsModalOpen] = useState(false);
    const [saleDetails, setSaleDetails] = useState(null);
    const handleViewItems = async () => {
        try {
            const response = await axios.get(`/api/sales/${id}`);
            setSaleDetails(response.data);
            setIsModalOpen(true);
        } catch (error) {
            console.error('Error fetching sale details:', error);
            alert('Failed to fetch sale details');
        }
    };
    const formatDate = (dateString: string | null) => {
        if (!dateString) return '-';
        
        try {
            const date = new Date(dateString);
            // Check if date is valid
            if (isNaN(date.getTime())) return '-';
            
            return new Intl.DateTimeFormat('id-ID', {
                day: '2-digit',
                month: 'short',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            }).format(date);
        } catch (error) {
            console.error('Error formatting date:', error);
            return '-';
        }
    };



    return (
        <div className="bg-white dark:bg-slate-800 rounded-lg shadow-sm border border-gray-200 dark:border-slate-700 overflow-hidden">
            {/* Header */}
            <div className="p-4 border-b border-gray-200 dark:border-slate-700">
                <div className="flex items-center justify-between mb-2">
                    <div className="flex flex-col">
                        <span className="text-sm font-medium text-purple-600 dark:text-purple-400">{invoice_number}</span>
                        <span className="text-xs text-gray-500 dark:text-gray-400">#{id}</span>
                    </div>
                    <span className={`px-2 py-1 text-xs rounded-full ${
                        status === 'completed' 
                            ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' 
                            : 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200'
                    }`}>
                        {status}
                    </span>
                </div>
                <h3 className="text-lg font-semibold text-gray-900 dark:text-white">{customer}</h3>
            </div>

            {/* Body */}
            <div className="p-4">
                <div className="grid grid-cols-2 md:grid-cols-4 gap-4">
                    {/* Time */}
                    <div className="flex items-center gap-2 text-gray-600 dark:text-gray-300">
                        <FiClock className="w-4 h-4" />
                        <span className="text-sm">{formatDate(time)}</span>
                    </div>

                    {/* Items */}
                    <div className="flex items-center gap-2 text-gray-600 dark:text-gray-300">
                        <FiPackage className="w-4 h-4" />
                        <span className="text-sm">{itemCount} items</span>
                    </div>

                    {/* Payment Type */}
                    <div className="flex items-center gap-2 text-gray-600 dark:text-gray-300">
                        <span className="text-sm capitalize">{type}</span>
                    </div>

                    {/* Amount */}
                    <div className="flex items-center gap-2 text-gray-900 dark:text-white font-medium">
                        <span>{formatPrice(amount)}</span>
                    </div>
                </div>
            </div>

            {/* Actions */}
            <div className="p-4 bg-gray-50 dark:bg-slate-900 border-t border-gray-200 dark:border-slate-700">
                <div className="flex gap-2 justify-end">
                    <button
                        onClick={handleViewItems}
                        className="flex items-center gap-2 px-3 py-1.5 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-slate-800 rounded-lg"
                    >
                        <FiEye className="w-4 h-4" />
                        <span className="hidden md:inline">View Items</span>
                    </button>
                    <SaleDetailsModal
                        isOpen={isModalOpen}
                        onClose={() => setIsModalOpen(false)}
                        sale={saleDetails}
                    />

                </div>
            </div>
        </div>
    );
};

export default SaleCard;
