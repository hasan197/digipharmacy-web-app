import React, { useState, useEffect } from 'react';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { formatPrice } from '@/components/ui/utils';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';

interface PaymentMethodFormProps {
    paymentMethod: string;
    total: number;
    onPaymentDataChange: (data: PaymentData) => void;
    disabled?: boolean;
}

export interface PaymentData {
    method: string;
    amountPaid?: number;
    change?: number;
    cardLast4?: string;
    cardType?: string;
    approvalCode?: string;
    transactionId?: string;
    isValid: boolean;
}

export default function PaymentMethodForm({
    paymentMethod,
    total,
    onPaymentDataChange,
    disabled = false
}: PaymentMethodFormProps) {
    const [amountPaid, setAmountPaid] = useState<number | undefined>(undefined);
    const [change, setChange] = useState<number>(0);
    const [cardLast4, setCardLast4] = useState('');
    const [approvalCode, setApprovalCode] = useState('');
    const [cardType, setCardType] = useState('');
    const [transactionId, setTransactionId] = useState('');
    const [isValid, setIsValid] = useState(false);

    // Reset form when payment method changes
    useEffect(() => {
        setAmountPaid(undefined);
        setChange(0);
        setCardLast4('');
        setApprovalCode('');
        setCardType('');
        setTransactionId('');
        setIsValid(false);
    }, [paymentMethod]);

    // Validate and update parent component
    useEffect(() => {
        let valid = false;
        
        if (paymentMethod === 'cash') {
            valid = amountPaid !== undefined && amountPaid >= total;
        } else if (paymentMethod === 'debit' || paymentMethod === 'credit') {
            valid = cardLast4.length === 4 && approvalCode.trim().length > 0 && cardType;
        } else if (paymentMethod === 'qris') {
            valid = transactionId.trim().length > 0;
        }
        
        setIsValid(valid);
        
        // Pastikan semua data pembayaran valid dan lengkap
        const paymentData = {
            method: paymentMethod,
            // Pastikan nilai numerik
            amountPaid: Number(amountPaid || 0),
            change: Number(change || 0),
            // Pastikan nilai string sesuai dengan metode pembayaran
            cardLast4: (paymentMethod === 'debit' || paymentMethod === 'credit') ? (cardLast4 || '') : null,
            cardType: (paymentMethod === 'debit' || paymentMethod === 'credit') ? (cardType || '') : null,
            approvalCode: (paymentMethod === 'debit' || paymentMethod === 'credit') ? (approvalCode || '') : null,
            transactionId: paymentMethod === 'qris' ? (transactionId || '') : null,
            // Pastikan isValid selalu boolean
            isValid: Boolean(valid)
        };
        
        // Log data untuk debugging
        console.log('Payment data being sent:', paymentData);
        
        onPaymentDataChange(paymentData);
    }, [paymentMethod, amountPaid, change, cardLast4, approvalCode, cardType, transactionId, total]);

    // Calculate change when amount paid changes
    useEffect(() => {
        if (amountPaid !== undefined && amountPaid >= total) {
            setChange(amountPaid - total);
        } else {
            setChange(0);
        }
    }, [amountPaid, total]);

    // Format and validate 4 last digits
    const formatCardLast4 = (value: string) => {
        return value.replace(/[^0-9]/g, '').slice(0, 4);
    };
    
    const handleCardLast4Change = (e: React.ChangeEvent<HTMLInputElement>) => {
        const formattedValue = formatCardLast4(e.target.value);
        setCardLast4(formattedValue);
    };
    
    // Format approval code (alphanumeric)
    const formatApprovalCode = (value: string) => {
        return value.replace(/[^A-Za-z0-9]/g, '').slice(0, 6).toUpperCase();
    };
    
    const handleApprovalCodeChange = (e: React.ChangeEvent<HTMLInputElement>) => {
        const formattedValue = formatApprovalCode(e.target.value);
        setApprovalCode(formattedValue);
    };

    if (!paymentMethod) {
        return null;
    }

    return (
        <div className="space-y-4 mt-4">
            {paymentMethod === 'cash' && (
                <>
                    <div>
                        <Label htmlFor="amountPaid">Jumlah Dibayar</Label>
                        <Input
                            id="amountPaid"
                            type="number"
                            min={total}
                            value={amountPaid || ''}
                            onChange={(e) => setAmountPaid(parseFloat(e.target.value) || undefined)}
                            placeholder="Masukkan jumlah yang dibayarkan"
                            className="mt-1"
                            disabled={disabled}
                        />
                    </div>
                    
                    {change > 0 && (
                        <div className="bg-green-50 p-3 rounded-md border border-green-100">
                            <Label className="text-green-800">Kembalian</Label>
                            <div className="text-xl font-bold text-green-700 mt-1">
                                {formatPrice(change)}
                            </div>
                        </div>
                    )}
                </>
            )}
            
            {(paymentMethod === 'debit' || paymentMethod === 'credit') && (
                <>
                    <div className="bg-yellow-50 p-3 rounded-md border border-yellow-100 mb-4">
                        <p className="text-sm text-yellow-800">
                            Proses pembayaran melalui terminal EDC terlebih dahulu, kemudian masukkan informasi transaksi di bawah ini.
                        </p>
                    </div>
                    
                    <div>
                        <Label htmlFor="cardType">Jenis Kartu</Label>
                        <Select
                            value={cardType}
                            onValueChange={setCardType}
                            disabled={disabled}
                            required
                        >
                            <SelectTrigger className="w-full mt-1">
                                <SelectValue placeholder="Pilih jenis kartu" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="visa">Visa</SelectItem>
                                <SelectItem value="mastercard">MasterCard</SelectItem>
                                <SelectItem value="amex">American Express</SelectItem>
                                <SelectItem value="other">Lainnya</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                    
                    <div>
                        <Label htmlFor="cardLast4">4 Digit Terakhir Kartu</Label>
                        <Input
                            id="cardLast4"
                            value={cardLast4}
                            onChange={handleCardLast4Change}
                            placeholder="Contoh: 1234"
                            className="mt-1"
                            maxLength={4}
                            disabled={disabled}
                        />
                    </div>
                    
                    <div>
                        <Label htmlFor="approvalCode">Kode Persetujuan</Label>
                        <Input
                            id="approvalCode"
                            value={approvalCode}
                            onChange={handleApprovalCodeChange}
                            placeholder="Contoh: ABC123"
                            className="mt-1"
                            maxLength={6}
                            disabled={disabled}
                        />
                        <div className="text-sm text-gray-500 mt-1">
                            Kode yang tercetak pada struk EDC
                        </div>
                    </div>
                </>
            )}
            
            {paymentMethod === 'qris' && (
                <>
                    <div className="bg-yellow-50 p-3 rounded-md border border-yellow-100 mb-4">
                        <p className="text-sm text-yellow-800">
                            Tampilkan QR Code kepada pelanggan dan tunggu konfirmasi pembayaran dari sistem QRIS.
                        </p>
                    </div>
                    
                    <div>
                        <Label htmlFor="transactionId">ID Transaksi Merchant</Label>
                        <Input
                            id="transactionId"
                            value={transactionId}
                            onChange={(e) => setTransactionId(e.target.value.toUpperCase())}
                            placeholder="Contoh: INV/2025/03/001"
                            className="mt-1"
                            maxLength={20}
                            disabled={disabled}
                        />
                        <div className="text-sm text-gray-500 mt-1">
                            Masukkan ID transaksi atau nomor invoice sebagai referensi internal
                        </div>
                    </div>
                </>
            )}
        </div>
    );
}
