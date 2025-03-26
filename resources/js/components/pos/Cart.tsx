import React, { useState, useEffect } from 'react';
import { formatPrice } from '../ui/utils';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Button } from '@/components/ui/button';
import { Alert, AlertDescription } from '@/components/ui/alert';
import CustomerSearch from './CustomerSearch';
import CartPending from './CartPending';
import PaymentMethodForm, { PaymentData } from './PaymentMethodForm';
import { api } from '@/lib/auth';
import { Tabs, TabsList, TabsTrigger, TabsContent } from '@/components/ui/tabs';

// Define the structure of a cart item
interface CartItem {
    id: string;
    name: string;
    quantity: number;
    price: number;
    requires_prescription?: boolean;
}

// Define the props for the Cart component
interface CartProps {
    customerId?: number | null;
    setCustomerId: (id: number | null) => void;    
    items: CartItem[];
    onUpdateQuantity: (id: string, quantity: number) => void;
    onRemoveItem: (id: string) => void;
    onCheckout: () => void;
    onPendingSale?: () => void;
    isProcessing: boolean;
    paymentMethod: string;
    onPaymentMethodChange: (method: string) => void;
    onPaymentDataChange?: (data: PaymentData) => void;
    error?: string | null;
    successMessage?: string | null;
    customerName: string;
    setCustomerName: (name: string) => void;
    customerPhone: string;
    setCustomerPhone: (phone: string) => void;
    pendingSales?: Array<{
        id: number;
        cartItems: CartItem[];
        customerName: string;
        customerPhone: string;
        paymentMethod: string;
        timestamp: string;
        total: number;
    }>;
    onResumeSale?: (saleId: number) => void;
    onDeletePendingSale?: (saleId: number) => void;
}

// Cart component to manage and display the shopping cart
export default function Cart({
    items,
    onUpdateQuantity,
    onRemoveItem,
    onCheckout,
    onPendingSale,
    isProcessing,
    paymentMethod,
    onPaymentMethodChange,
    onPaymentDataChange,
    error,
    successMessage,
    customerName,
    setCustomerName,
    customerPhone,
    setCustomerPhone,
    customerId,
    setCustomerId,
    pendingSales,
    onResumeSale,
    onDeletePendingSale
}: CartProps) {
    // Calculate subtotal, tax, and total amounts
    const subtotal = items.reduce((total, item) => total + (item.price * item.quantity), 0);
    const tax = subtotal * 0.1; // 10% tax
    const total = subtotal + tax;

    // Payment methods available
    const paymentMethods = [
        { value: 'cash', label: 'Tunai' },
        { value: 'debit', label: 'Kartu Debit' },
        { value: 'credit', label: 'Kartu Kredit' },
        { value: 'qris', label: 'QRIS' },
    ];
    
    // State for payment data validation
    const [paymentData, setPaymentData] = useState<PaymentData | null>(null);
    
    // Reset payment data when payment method changes
    useEffect(() => {
        setPaymentData(null);
    }, [paymentMethod]);
    
    // Update parent component when payment data changes
    useEffect(() => {
        if (onPaymentDataChange) {
            onPaymentDataChange(paymentData);
        }
    }, [paymentData, onPaymentDataChange]);

    // Notification components
    const Notification = () => {
        if (successMessage) {
            return (
                <Alert className="mb-4 bg-green-50 border-green-200">
                    <AlertDescription className="text-green-800">
                        {successMessage}
                    </AlertDescription>
                </Alert>
            );
        }
        if (error) {
            return (
                <Alert className="mb-4 bg-red-50 border-red-200">
                    <AlertDescription className="text-red-800">
                        {error}
                    </AlertDescription>
                </Alert>
            );
        }
        return null;
    };


    // Render the Cart component
    const [activeTab, setActiveTab] = useState("cart");
    
    return (
        <div className="w-full lg:w-96 bg-white rounded-2xl shadow-lg shadow-gray-200/50 border border-gray-100 p-6 lg:sticky lg:top-8">
            <Notification />
            <div className="flex items-center justify-between mb-6">
                <h2 className="text-2xl font-bold bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent">
                    Cart
                </h2>
                <span className="px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-xs font-medium">
                    {items.length} items
                </span>
            </div>
            
            <Tabs defaultValue="cart" value={activeTab} onValueChange={setActiveTab} className="mb-6">
                <TabsList className="grid w-full grid-cols-2">
                    <TabsTrigger value="cart">Keranjang</TabsTrigger>
                    <TabsTrigger value="pending" className="relative">
                        Tertunda
                        {pendingSales && pendingSales.length > 0 && (
                            <span className="absolute -top-1 -right-1 w-5 h-5 bg-purple-600 text-white text-xs rounded-full flex items-center justify-center">
                                {pendingSales.length}
                            </span>
                        )}
                    </TabsTrigger>
                </TabsList>
                
                <TabsContent value="cart">
                    {items.length === 0 ? (
                    <div className="text-center py-8">
                        <div className="w-16 h-16 mx-auto mb-4 rounded-full bg-gray-100 flex items-center justify-center">
                            <svg className="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                            </svg>
                        </div>
                        <p className="text-gray-500 mb-4">Keranjang kosong</p>
                        {pendingSales && pendingSales.length > 0 && (
                            <Button 
                                variant="outline" 
                                onClick={() => setActiveTab("pending")}
                                className="mx-auto"
                            >
                                Lihat {pendingSales.length} Transaksi Tertunda
                            </Button>
                        )}
                    </div>
                    ) : (
                        <>
                        {/* Customer Information */}
                        <div className="mb-8">
                            <CustomerSearch
                                customerName={customerName}
                                setCustomerName={setCustomerName}
                                customerPhone={customerPhone}
                                setCustomerPhone={setCustomerPhone}
                                onSelectCustomer={(customer) => {
                                    if (customer) {
                                        setCustomerName(customer.name);
                                        setCustomerPhone(customer.phone);
                                        setCustomerId(customer.id);
                                    } else {
                                        setCustomerId(null);
                                    }
                                }}
                                onNewCustomer={async (name, phone) => {
                                    try {
                                        const response = await api.post('/customers', {
                                            name: name.trim(),
                                            phone: phone.trim()
                                        });
                                        
                                        if (!response.data) {
                                            throw new Error('Gagal menambahkan pelanggan: Response kosong');
                                        }
                                        
                                        const newCustomer = response.data;
                                        
                                        if (!newCustomer.id) {
                                            throw new Error('Gagal menambahkan pelanggan: ID tidak valid');
                                        }
                                        
                                        setCustomerName(newCustomer.name);
                                        setCustomerPhone(newCustomer.phone);
                                        setCustomerId(newCustomer.id);
                                        return newCustomer;
                                    } catch (error: any) {
                                        console.error('Error creating customer:', error);
                                        const errorMessage = error.response?.data?.message || error.message || 'Gagal menambahkan pelanggan';
                                        throw new Error(errorMessage);
                                    }
                                }}
                            />
                        </div>

                        {/* Cart Items */}
                        <div className="space-y-4 mb-8">
                            {items.map((item) => (
                                <div key={item.id} className="flex items-center gap-4 p-4 bg-gray-50 rounded-xl">
                                    <div className="flex-1 min-w-0">
                                        <div className="flex items-center gap-2">
                                            <h3 className="text-sm font-medium text-gray-900 truncate">{item.name}</h3>
                                            {item.requires_prescription && (
                                                <span className="inline-flex items-center rounded-md bg-red-50 px-2 py-1 text-xs font-medium text-red-700 ring-1 ring-inset ring-red-600/20">
                                                    Rx
                                                </span>
                                            )}
                                        </div>
                                        <p className="text-sm text-gray-500">{formatPrice(item.price)}</p>
                                    </div>
                                    <div className="flex items-center gap-2">
                                        <button
                                            onClick={() => onUpdateQuantity(item.id, item.quantity - 1)}
                                            className="p-1 rounded-lg hover:bg-gray-200 transition-colors"
                                            disabled={item.quantity <= 1}
                                        >
                                            <svg className="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M20 12H4" />
                                            </svg>
                                        </button>
                                        <span className="w-8 text-center text-sm font-medium">{item.quantity}</span>
                                        <button
                                            onClick={() => onUpdateQuantity(item.id, item.quantity + 1)}
                                            className="p-1 rounded-lg hover:bg-gray-200 transition-colors"
                                        >
                                            <svg className="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                            </svg>
                                        </button>
                                        <button
                                            onClick={() => onRemoveItem(item.id)}
                                            className="p-1 rounded-lg hover:bg-gray-200 transition-colors ml-2"
                                        >
                                            <svg className="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            ))}
                        </div>

                        <div className="space-y-4 mb-8">
                            <div className="flex justify-between text-sm">
                                <span className="text-gray-500">Subtotal</span>
                                <span className="font-medium">{formatPrice(subtotal)}</span>
                            </div>
                            <div className="flex justify-between text-sm">
                                <span className="text-gray-500">Tax (10%)</span>
                                <span className="font-medium">{formatPrice(tax)}</span>
                            </div>
                            <div className="pt-4 border-t border-gray-200">
                                <div className="flex justify-between">
                                    <span className="text-lg font-medium text-gray-900">Total</span>
                                    <span className="text-lg font-bold bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent">{formatPrice(total)}</span>
                                </div>
                            </div>
                        </div>

                        {/* Payment Method Selection */}
                        <div className="mb-6">
                            <label className="block text-sm font-medium text-gray-700 mb-2">
                                Metode Pembayaran
                            </label>
                            <Select
                                value={paymentMethod || undefined}
                                onValueChange={onPaymentMethodChange}
                                disabled={isProcessing}
                            >
                                <SelectTrigger className="w-full">
                                    <SelectValue placeholder="Pilih metode pembayaran" />
                                </SelectTrigger>
                                <SelectContent>
                                    {paymentMethods.map((method) => (
                                        <SelectItem key={method.value} value={method.value}>
                                            {method.label}
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                            
                            {paymentMethod && (
                                <PaymentMethodForm
                                    paymentMethod={paymentMethod}
                                    total={total}
                                    onPaymentDataChange={setPaymentData}
                                    disabled={isProcessing}
                                />
                            )}
                        </div>

                        {/* Button to switch to Pending tab if there are pending sales */}
                        {pendingSales && pendingSales.length > 0 && (
                            <Button 
                                variant="outline" 
                                onClick={() => setActiveTab("pending")}
                                className="w-full mb-4"
                                size="sm"
                            >
                                Lihat {pendingSales.length} Transaksi Tertunda
                            </Button>
                        )}

                        {/* Action Buttons */}
                        <div className="flex gap-2">
                            <Button
                                onClick={onCheckout}
                                disabled={isProcessing || !paymentMethod || !paymentData || !paymentData.isValid}
                                className="flex-1"
                            >
                                {isProcessing ? 'Memproses...' : 'Checkout'}
                            </Button>
                            {items?.length > 0 && onPendingSale && (
                                <Button
                                    onClick={onPendingSale}
                                    variant="outline"
                                >
                                    Tunda
                                </Button>
                            )}
                        </div>

                        {/* Error Message */}
                        {error && (
                            <Alert variant="destructive" className="mt-4">
                                <AlertDescription>{error}</AlertDescription>
                            </Alert>
                        )}

                        {/* Success Message */}
                        {successMessage && (
                            <Alert className="mt-4 bg-green-50 text-green-800 border-green-200">
                                <AlertDescription>{successMessage}</AlertDescription>
                            </Alert>
                        )}
                    </>
                    )}
                </TabsContent>
                
                <TabsContent value="pending">
                    <CartPending 
                        pendingSales={pendingSales}
                        onResumeSale={onResumeSale}
                        onDeletePendingSale={onDeletePendingSale}
                        onBackToCart={() => setActiveTab("cart")}
                    />
                </TabsContent>
            </Tabs>
        </div>
    );
}
