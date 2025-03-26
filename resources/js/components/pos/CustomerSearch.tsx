import React, { useState, useEffect, useRef } from 'react';
import { api } from '@/lib/auth';
import { Alert, AlertDescription } from '@/components/ui/alert';

interface Customer {
    id: number;
    name: string;
    phone: string;
    email?: string;
}

interface CustomerSearchProps {
    onSelectCustomer: (customer: Customer | null) => void;
    onNewCustomer: (name: string, phone: string) => void;
    customerName: string;
    setCustomerName: (name: string) => void;
    customerPhone: string;
    setCustomerPhone: (phone: string) => void;
}

export default function CustomerSearch({
    onSelectCustomer,
    onNewCustomer,
    customerName,
    setCustomerName,
    customerPhone,
    setCustomerPhone
}: CustomerSearchProps) {
    const [open, setOpen] = useState(false);
    const [customers, setCustomers] = useState<Customer[]>([]);
    const [loading, setLoading] = useState(false);
    const [searchTerm, setSearchTerm] = useState('');
    const [showNewCustomerForm, setShowNewCustomerForm] = useState(false);
    const [error, setError] = useState<string | null>(null);

    useEffect(() => {
        if (searchTerm.length >= 2) {
            searchCustomers(searchTerm);
        }
    }, [searchTerm]);

    const validatePhone = (phone: string): boolean => {
        // Format: +62xxx atau 08xxx, minimal 10 digit, maksimal 13 digit
        const phoneRegex = /^(\+62|08)\d{9,11}$/;
        return phoneRegex.test(phone);
    };

    const searchCustomers = async (query: string) => {
        setLoading(true);
        try {
            const response = await api.get(`/customers/search?q=${encodeURIComponent(query)}`);
            setCustomers(response.data);
            setError(null);
        } catch (error) {
            console.error('Error searching customers:', error);
            setError('Gagal mencari pelanggan');
        } finally {
            setLoading(false);
        }
    };

    const handleSelectCustomer = (customer: Customer) => {
        onSelectCustomer(customer);
        setCustomerName(customer.name);
        setCustomerPhone(customer.phone);
        setOpen(false);
        setShowNewCustomerForm(false);
        setError(null);
    };

    const handleInputChange = (field: 'name' | 'phone', value: string) => {
        if (field === 'name') {
            setCustomerName(value);
        } else {
            setCustomerPhone(value);
        }
        // Reset search when manually inputting
        setSearchTerm('');
        setOpen(false);
        onSelectCustomer(null);
        setError(null);
    };

    const handleNewCustomerClick = () => {
        setShowNewCustomerForm(true);
        setOpen(false);
        // Mengisi input fields dengan searchTerm jika sesuai format
        if (searchTerm.match(/^\d+$/)) { // Jika searchTerm hanya berisi angka
            setCustomerPhone(searchTerm);
        } else {
            setCustomerName(searchTerm);
        }
    };

    const handleAddCustomer = async () => {
        if (!customerName.trim()) {
            setError('Nama pelanggan harus diisi');
            return;
        }

        if (!customerPhone.trim()) {
            setError('Nomor telepon harus diisi');
            return;
        }

        if (!validatePhone(customerPhone)) {
            setError('Format nomor telepon tidak valid (gunakan format: 08xxx atau +62xxx)');
            return;
        }

        try {
            // Tambahkan customer baru
            const newCustomer = await onNewCustomer(customerName.trim(), customerPhone.trim());
            
            if (newCustomer && newCustomer.id) {
                // Set customer yang baru sebagai customer terpilih
                onSelectCustomer(newCustomer);
                setError(null);
                setShowNewCustomerForm(false);
                setSearchTerm('');
            } else {
                throw new Error('Data pelanggan tidak valid');
            }
        } catch (error: any) {
            console.error('Error adding new customer:', error);
            setError(error.message || 'Gagal menambahkan pelanggan baru');
            // Tidak menutup form jika terjadi error
        }
    };

    return (
        <div className="space-y-4">
            <h3 className="text-sm font-medium text-gray-700">Informasi Pelanggan</h3>
            
            {error && (
                <Alert className="bg-red-50 border-red-200">
                    <AlertDescription className="text-red-800">
                        {error}
                    </AlertDescription>
                </Alert>
            )}
            
            {showNewCustomerForm ? (
                // Form Add Customer
                <div className="space-y-3">
                    <div className="flex justify-between items-center">
                        <h4 className="text-sm font-medium text-gray-700">Tambah Pelanggan Baru</h4>
                        <button
                            onClick={() => {
                                setShowNewCustomerForm(false);
                                setCustomerName('');
                                setCustomerPhone('');
                                setError(null);
                            }}
                            className="text-sm text-purple-600 hover:text-purple-700"
                        >
                            Kembali ke Pencarian
                        </button>
                    </div>
                    <input
                        type="text"
                        placeholder="Nama Pelanggan"
                        value={customerName}
                        onChange={(e) => handleInputChange('name', e.target.value)}
                        className="w-full p-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                    />
                    <input
                        type="text"
                        placeholder="Nomor Telepon (contoh: 081234567890)"
                        value={customerPhone}
                        onChange={(e) => handleInputChange('phone', e.target.value)}
                        className="w-full p-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                    />
                    {customerName && customerPhone && (
                        <button
                            onClick={handleAddCustomer}
                            className="w-full px-4 py-2 text-sm font-medium text-white bg-purple-600 hover:bg-purple-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2"
                        >
                            Tambahkan Pelanggan
                        </button>
                    )}
                </div>
            ) : customerName && customerPhone ? (
                // Selected Customer Info
                <div className="p-4 bg-gray-50 rounded-lg space-y-2">
                    <div className="flex justify-between items-start">
                        <div>
                            <h4 className="font-medium text-gray-900">{customerName}</h4>
                            <p className="text-sm text-gray-500">{customerPhone}</p>
                        </div>
                        <button
                            onClick={() => {
                                setCustomerName('');
                                setCustomerPhone('');
                                onSelectCustomer(null);
                                setError(null);
                            }}
                            className="text-sm text-purple-600 hover:text-purple-700"
                        >
                            Ganti Pelanggan
                        </button>
                    </div>
                </div>
            ) : (
                // Search Customer Form
                <div>
                    <div className="relative">
                        <input
                            type="text"
                            placeholder="Cari nama atau nomor telepon pelanggan..."
                            value={searchTerm}
                            onChange={(e) => setSearchTerm(e.target.value)}
                            onFocus={() => setOpen(true)}
                            className="w-full p-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                        />
                    
                        {/* Search Results Dropdown */}
                        {open && (
                            <div className="absolute z-10 w-full mt-1 bg-white border border-gray-200 rounded-lg shadow-lg max-h-60 overflow-auto">
                                {loading ? (
                                    <div className="p-4 text-center text-sm text-gray-500">
                                        Mencari pelanggan...
                                    </div>
                                ) : customers.length > 0 ? (
                                    <div className="py-1">
                                        {customers.map((customer) => (
                                            <div
                                                key={customer.id}
                                                className="px-4 py-2 hover:bg-gray-100 cursor-pointer"
                                                onClick={() => handleSelectCustomer(customer)}
                                            >
                                                <div className="font-medium">{customer.name}</div>
                                                <div className="text-sm text-gray-500">{customer.phone}</div>
                                            </div>
                                        ))}
                                    </div>
                                ) : searchTerm ? (
                                    <div className="p-4 text-center">
                                        <p className="text-sm text-gray-500 mb-2">Pelanggan tidak ditemukan</p>
                                        <button
                                            onClick={handleNewCustomerClick}
                                            className="w-full px-4 py-2 text-sm font-medium text-white bg-purple-600 hover:bg-purple-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2"
                                        >
                                            Gunakan Data Pelanggan Ini
                                        </button>
                                    </div>
                                ) : null}
                            </div>
                        )}
                    </div>
                </div>
            )}
        </div>
    );
}
