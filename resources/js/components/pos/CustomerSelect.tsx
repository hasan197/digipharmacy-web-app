import React, { useEffect, useState } from 'react';
import { api } from '@/lib/auth';

interface Customer {
    id: number;
    name: string;
    phone: string;
    email: string;
}

interface CustomerSelectProps {
    onSelect: (customerId: number) => void;
    selectedCustomerId: number | null;
}

export default function CustomerSelect({ onSelect, selectedCustomerId }: CustomerSelectProps) {
    const [customers, setCustomers] = useState<Customer[]>([]);
    const [isLoading, setIsLoading] = useState(true);
    const [error, setError] = useState<string | null>(null);

    useEffect(() => {
        fetchCustomers();
    }, []);

    const fetchCustomers = async () => {
        try {
            const response = await api.get('/customers');
            setCustomers(response.data);
        } catch (error) {
            console.error('Error fetching customers:', error);
            setError('Failed to load customers');
        } finally {
            setIsLoading(false);
        }
    };

    if (isLoading) {
        return (
            <div className="w-full p-4 bg-white rounded-lg shadow">
                <div className="animate-pulse flex space-x-4">
                    <div className="flex-1 space-y-4 py-1">
                        <div className="h-4 bg-gray-200 rounded w-3/4"></div>
                    </div>
                </div>
            </div>
        );
    }

    if (error) {
        return (
            <div className="w-full p-4 bg-red-50 text-red-600 rounded-lg">
                {error}
            </div>
        );
    }

    return (
        <div className="w-full">
            <select
                className="w-full p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                value={selectedCustomerId || ''}
                onChange={(e) => onSelect(Number(e.target.value))}
            >
                <option value="">Select Customer</option>
                {customers.map((customer) => (
                    <option key={customer.id} value={customer.id}>
                        {customer.name} - {customer.phone}
                    </option>
                ))}
            </select>
        </div>
    );
}
