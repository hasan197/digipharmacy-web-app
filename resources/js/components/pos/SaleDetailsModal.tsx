import React from 'react';
import { formatPrice } from '../../lib/utils';
import {
    Sheet,
    SheetContent,
    SheetHeader,
    SheetTitle,
    SheetFooter,
} from "@/components/ui/sheet";
import { Button } from "@/components/ui/button";

interface SaleDetailsModalProps {
    isOpen: boolean;
    onClose: () => void;
    sale: any;
}

const SaleDetailsModal: React.FC<SaleDetailsModalProps> = ({ isOpen, onClose, sale }) => {
    if (!sale) return null;

    return (
        <Sheet open={isOpen} onOpenChange={onClose}>
            <SheetContent side="right" className="w-full max-w-[90%] sm:max-w-[600px] md:max-w-[700px] lg:max-w-[800px] overflow-y-auto">
                <SheetHeader className="space-y-2">
                    <SheetTitle className="text-xl font-semibold text-gray-900 dark:text-white">
                        Detail Transaksi
                    </SheetTitle>
                    <div className="flex flex-col">
                        <div className="text-sm text-purple-600 dark:text-purple-400 font-medium">{sale.invoice_number}</div>
                        {sale.customer_name && (
                            <div className="text-sm text-gray-600 dark:text-gray-400">{sale.customer_name}</div>
                        )}
                    </div>
                </SheetHeader>

                <div className="mt-6">
                        {/* Customer Info */}
                        {(sale.customer || sale.customer_name || sale.customer_phone) && (
                            <div className="mb-6">
                                <h3 className="font-semibold text-gray-900 dark:text-white mb-2">Informasi Pelanggan</h3>
                                <div className="space-y-1 text-gray-600 dark:text-gray-300">
                                    {sale.customer_name && <p>Nama: {sale.customer_name}</p>}
                                    {sale.customer_phone && <p>Telepon: {sale.customer_phone}</p>}
                                    {sale.customer?.email && <p>Email: {sale.customer.email}</p>}
                                    {sale.customer?.address && <p>Alamat: {sale.customer.address}</p>}
                                </div>
                            </div>
                        )}

                        <hr className="my-6 border-gray-200 dark:border-slate-700" />

                        {/* Items Table */}
                        <div className="overflow-x-auto">
                            <table className="min-w-full divide-y divide-gray-200 dark:divide-slate-700">
                                <thead>
                                    <tr>
                                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Product</th>
                                        <th className="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Quantity</th>
                                        <th className="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Price</th>
                                        <th className="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-gray-200 dark:divide-slate-700">
                                    {sale.details?.map((item: any) => (
                                        <tr key={item.id}>
                                            <td className="px-6 py-4">
                                                {item.product ? (
                                                    <>
                                                        <div className="text-sm text-gray-900 dark:text-white">{item.product.name}</div>
                                                        <div className="text-sm text-gray-500 dark:text-gray-400">{item.product.unit}</div>
                                                    </>
                                                ) : (
                                                    <div className="text-sm text-gray-900 dark:text-white">Product ID: {item.product_id}</div>
                                                )}
                                            </td>
                                            <td className="px-6 py-4 text-right text-sm text-gray-500 dark:text-gray-400">{item.quantity}</td>
                                            <td className="px-6 py-4 text-right text-sm text-gray-500 dark:text-gray-400">{formatPrice(item.price)}</td>
                                            <td className="px-6 py-4 text-right text-sm text-gray-500 dark:text-gray-400">{formatPrice(item.subtotal)}</td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>

                        <hr className="my-6 border-gray-200 dark:border-slate-700" />

                        {/* Summary */}
                        <div className="space-y-2">
                            <div className="flex justify-between">
                                <span className="text-gray-600 dark:text-gray-300">Subtotal:</span>
                                <span className="text-gray-900 dark:text-white">{formatPrice(sale.total)}</span>
                            </div>
                            {sale.discount > 0 && (
                                <div className="flex justify-between">
                                    <span className="text-gray-600 dark:text-gray-300">Discount:</span>
                                    <span className="text-green-600 dark:text-green-400">-{formatPrice(sale.discount)}</span>
                                </div>
                            )}
                            {sale.additional_fee > 0 && (
                                <div className="flex justify-between">
                                    <span className="text-gray-600 dark:text-gray-300">Additional Fee:</span>
                                    <span className="text-gray-900 dark:text-white">{formatPrice(sale.additional_fee)}</span>
                                </div>
                            )}
                            <div className="flex justify-between font-semibold">
                                <span className="text-gray-900 dark:text-white">Grand Total:</span>
                                <span className="text-gray-900 dark:text-white">{formatPrice(sale.grand_total)}</span>
                            </div>
                        </div>

                        <hr className="my-6 border-gray-200 dark:border-slate-700" />

                        {/* Footer Info */}
                        <div className="space-y-2">
                            <div className="flex items-center gap-2">
                                <span className="text-gray-600 dark:text-gray-300">Payment Method:</span>
                                <span className="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                    {sale.payment_method}
                                </span>
                            </div>
                            
                            {/* Payment Details */}
                            {sale.payment_details && (
                                <div className="mt-2">
                                    <h3 className="font-semibold text-gray-900 dark:text-white mb-2">Detail Pembayaran</h3>
                                    <div className="space-y-1 text-gray-600 dark:text-gray-300">
                                        {sale.payment_details.method && (
                                            <div className="flex items-center gap-2">
                                                <span>Metode:</span>
                                                <span className="text-sm capitalize">{sale.payment_details.method}</span>
                                            </div>
                                        )}
                                        
                                        <div className="flex items-center gap-2">
                                            <span>Status Pembayaran:</span>
                                            <span className="text-sm">{sale.payment_details.isValid ? 'Valid' : 'Invalid'}</span>
                                        </div>
                                        
                                        {/* Kartu Debit/Kredit */}
                                        {(sale.payment_method === 'debit' || sale.payment_method === 'credit') && (
                                            <>
                                                {(sale.payment_details.cardType || sale.payment_details.additionalData?.cardType) && (
                                                    <div className="flex items-center gap-2">
                                                        <span>Tipe Kartu:</span>
                                                        <span className="text-sm capitalize">{sale.payment_details.cardType || sale.payment_details.additionalData?.cardType}</span>
                                                    </div>
                                                )}
                                                {(sale.payment_details.cardLast4 || sale.payment_details.additionalData?.cardLast4) && (
                                                    <div className="flex items-center gap-2">
                                                        <span>Nomor Kartu:</span>
                                                        <span className="text-sm">XXXX-XXXX-XXXX-{sale.payment_details.cardLast4 || sale.payment_details.additionalData?.cardLast4}</span>
                                                    </div>
                                                )}
                                                {(sale.payment_details.approvalCode || sale.payment_details.additionalData?.approvalCode) && (
                                                    <div className="flex items-center gap-2">
                                                        <span>Kode Approval:</span>
                                                        <span className="text-sm">{sale.payment_details.approvalCode || sale.payment_details.additionalData?.approvalCode}</span>
                                                    </div>
                                                )}
                                                {(sale.payment_details.cardHolderName || sale.payment_details.additionalData?.cardHolderName) && (
                                                    <div className="flex items-center gap-2">
                                                        <span>Nama Pemegang Kartu:</span>
                                                        <span className="text-sm">{sale.payment_details.cardHolderName || sale.payment_details.additionalData?.cardHolderName}</span>
                                                    </div>
                                                )}
                                            </>
                                        )}
                                        
                                        {/* QRIS */}
                                        {sale.payment_method === 'qris' && (
                                            <>
                                                {(sale.payment_details.transactionId || sale.payment_details.additionalData?.transactionId) && (
                                                    <div className="flex items-center gap-2">
                                                        <span>ID Transaksi:</span>
                                                        <span className="text-sm">{sale.payment_details.transactionId || sale.payment_details.additionalData?.transactionId}</span>
                                                    </div>
                                                )}
                                            </>
                                        )}
                                        
                                        {/* Cash */}
                                        {sale.payment_method === 'cash' && (sale.payment_details.change !== undefined || sale.payment_details.additionalData?.change !== undefined) && (
                                            <div className="flex items-center gap-2">
                                                <span>Kembalian:</span>
                                                <span className="text-sm">{formatPrice(Number(sale.payment_details.change || sale.payment_details.additionalData?.change))}</span>
                                            </div>
                                        )}
                                        {sale.payment_method === 'cash' && (sale.payment_details.amountPaid !== undefined || sale.payment_details.additionalData?.amountPaid !== undefined) && (
                                            <div className="flex items-center gap-2">
                                                <span>Jumlah Dibayar:</span>
                                                <span className="text-sm">{formatPrice(Number(sale.payment_details.amountPaid || sale.payment_details.additionalData?.amountPaid))}</span>
                                            </div>
                                        )}
                                    </div>
                                </div>
                            )}
                            <div className="flex items-center gap-2">
                                <span className="text-gray-600 dark:text-gray-300">Status:</span>
                                <span className={`px-2 py-1 text-xs rounded-full ${
                                    sale.status === 'completed'
                                        ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200'
                                        : 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200'
                                }`}>
                                    {sale.status}
                                </span>
                            </div>
                            {sale.notes && (
                                <div className="space-y-1">
                                    <span className="text-gray-600 dark:text-gray-300">Notes:</span>
                                    <p className="text-sm text-gray-500 dark:text-gray-400">{sale.notes}</p>
                                </div>
                            )}
                        </div>
                </div>
                <SheetFooter className="mt-6">
                    <Button variant="outline" onClick={onClose}>
                        Close
                    </Button>
                </SheetFooter>
            </SheetContent>
        </Sheet>
    );
};

export default SaleDetailsModal;
