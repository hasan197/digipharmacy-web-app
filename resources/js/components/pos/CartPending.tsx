import React from 'react';
import { Button } from '@/components/ui/button';
import { formatPrice } from '@/components/ui/utils';

interface PendingSale {
    id: number;
    cartItems: Array<{
        id: string;
        name: string;
        price: number;
        quantity: number;
    }>;
    customerName: string;
    customerPhone: string;
    paymentMethod: string;
    timestamp: string;
    total: number;
}

interface CartPendingProps {
    pendingSales?: PendingSale[];
    onResumeSale?: (saleId: number) => void;
    onDeletePendingSale?: (saleId: number) => void;
    onBackToCart: () => void;
}

export default function CartPending({
    pendingSales = [],
    onResumeSale,
    onDeletePendingSale,
    onBackToCart
}: CartPendingProps) {
    return (
        <div className="space-y-4">
            <div className="flex items-center justify-between">
                <h3 className="font-medium">Transaksi Tertunda</h3>
                <Button 
                    variant="ghost" 
                    size="sm" 
                    onClick={onBackToCart}
                    className="text-xs"
                >
                    Kembali ke Keranjang
                </Button>
            </div>
            
            {pendingSales && pendingSales.length > 0 ? (
                <div className="space-y-3 pr-2">
                    {[...pendingSales].sort((a, b) => new Date(b.timestamp).getTime() - new Date(a.timestamp).getTime()).map((sale) => (
                        <div key={sale.id} 
                            className="p-4 bg-purple-50 hover:bg-purple-100 transition-colors rounded-lg border border-purple-100"
                        >
                            <div className="flex justify-between items-start mb-2">
                                <div>
                                    <div className="font-medium text-lg">{formatPrice(sale.total)}</div>
                                    <div className="text-sm text-gray-500">
                                        {new Date(sale.timestamp).toLocaleString()}
                                    </div>
                                </div>
                                <div className="px-2 py-1 bg-purple-100 text-purple-800 rounded text-xs">
                                    {sale.paymentMethod === 'cash' ? 'Tunai' : 
                                     sale.paymentMethod === 'debit' ? 'Kartu Debit' : 
                                     sale.paymentMethod === 'credit' ? 'Kartu Kredit' : 'QRIS'}
                                </div>
                            </div>
                            
                            {sale.customerName && (
                                <div className="mb-3">
                                    <div className="text-sm font-medium">{sale.customerName}</div>
                                    {sale.customerPhone && (
                                        <div className="text-xs text-gray-500">{sale.customerPhone}</div>
                                    )}
                                </div>
                            )}
                            
                            <div className="mb-3">
                                <div className="text-xs font-medium text-gray-500 mb-1">Item ({sale.cartItems.length})</div>
                                <div className="space-y-1">
                                    {sale.cartItems.slice(0, 3).map((item) => (
                                        <div key={item.id} className="flex justify-between text-sm">
                                            <div className="truncate flex-1">{item.name}</div>
                                            <div className="text-gray-500 ml-2">{item.quantity}x</div>
                                        </div>
                                    ))}
                                    {sale.cartItems.length > 3 && (
                                        <div className="text-xs text-gray-500">+{sale.cartItems.length - 3} item lainnya</div>
                                    )}
                                </div>
                            </div>
                            
                            <div className="flex gap-2">
                                <Button 
                                    onClick={() => {
                                        onResumeSale?.(sale.id);
                                        onBackToCart();
                                    }}
                                    className="flex-1"
                                >
                                    Lanjutkan Transaksi
                                </Button>
                                <Button 
                                    variant="destructive"
                                    size="icon"
                                    onClick={() => onDeletePendingSale?.(sale.id)}
                                    title="Hapus Transaksi"
                                >
                                    <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </Button>
                            </div>
                        </div>
                    ))}
                </div>
            ) : (
                <div className="text-center py-8">
                    <div className="w-16 h-16 mx-auto mb-4 rounded-full bg-gray-100 flex items-center justify-center">
                        <svg className="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <p className="text-gray-500">Tidak ada transaksi tertunda</p>
                </div>
            )}
        </div>
    );
}
