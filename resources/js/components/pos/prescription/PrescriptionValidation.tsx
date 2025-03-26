import React, { useState } from 'react';
import { FiCheck, FiX, FiEdit3 } from 'react-icons/fi';
import axios from 'axios';

interface PrescriptionItem {
    medicineId: number;
    medicineName: string;
    quantity: number;
    dosage: string;
    price: number;
}

interface Prescription {
    id: number;
    patientName: string;
    doctorName: string;
    items: PrescriptionItem[];
    notes: string;
    status: 'pending' | 'validated' | 'rejected';
    pharmacistNotes?: string;
    total: number;
    discount: number;
    additionalFee: number;
    createdAt: string;
}

interface PrescriptionValidationProps {
    prescription: Prescription;
    onValidate: (id: number, status: 'validated' | 'rejected', notes: string) => void;
}

const PrescriptionValidation: React.FC<PrescriptionValidationProps> = ({
    prescription,
    onValidate
}) => {
    const [pharmacistNotes, setPharmacistNotes] = useState(prescription.pharmacistNotes || '');
    const [isEditing, setIsEditing] = useState(false);

    const handleValidate = async (status: 'validated' | 'rejected') => {
        try {
            await onValidate(prescription.id, status, pharmacistNotes);
        } catch (error) {
            console.error('Error validating prescription:', error);
        }
    };

    return (
        <div className="bg-white dark:bg-slate-800 rounded-lg shadow p-6">
            {/* Header */}
            <div className="flex justify-between items-start mb-6">
                <div>
                    <h2 className="text-xl font-bold text-gray-900 dark:text-gray-100">
                        Validasi Resep #{prescription.id}
                    </h2>
                    <p className="text-sm text-gray-500 dark:text-gray-400">
                        {new Date(prescription.createdAt).toLocaleDateString('id-ID', {
                            day: 'numeric',
                            month: 'long',
                            year: 'numeric',
                            hour: '2-digit',
                            minute: '2-digit'
                        })}
                    </p>
                </div>
                <div className="flex items-center space-x-2">
                    {prescription.status === 'pending' ? (
                        <>
                            <button
                                onClick={() => handleValidate('validated')}
                                className="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 flex items-center space-x-2"
                            >
                                <FiCheck className="w-4 h-4" />
                                <span>Validasi</span>
                            </button>
                            <button
                                onClick={() => handleValidate('rejected')}
                                className="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 flex items-center space-x-2"
                            >
                                <FiX className="w-4 h-4" />
                                <span>Tolak</span>
                            </button>
                        </>
                    ) : (
                        <span className={`px-3 py-1 rounded-full text-sm font-medium
                            ${prescription.status === 'validated' 
                                ? 'bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100'
                                : 'bg-red-100 text-red-800 dark:bg-red-800 dark:text-red-100'
                            }`}
                        >
                            {prescription.status === 'validated' ? 'Tervalidasi' : 'Ditolak'}
                        </span>
                    )}
                </div>
            </div>

            {/* Patient & Doctor Info */}
            <div className="grid grid-cols-2 gap-6 mb-6">
                <div>
                    <h3 className="text-sm font-medium text-gray-500 dark:text-gray-400">Pasien</h3>
                    <p className="mt-1 text-lg font-medium text-gray-900 dark:text-gray-100">
                        {prescription.patientName}
                    </p>
                </div>
                <div>
                    <h3 className="text-sm font-medium text-gray-500 dark:text-gray-400">Dokter</h3>
                    <p className="mt-1 text-lg font-medium text-gray-900 dark:text-gray-100">
                        {prescription.doctorName}
                    </p>
                </div>
            </div>

            {/* Medicine List */}
            <div className="mb-6">
                <h3 className="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Daftar Obat</h3>
                <div className="space-y-4">
                    {prescription.items.map((item, index) => (
                        <div key={index} className="flex items-center justify-between p-4 bg-gray-50 dark:bg-slate-700 rounded-lg">
                            <div className="flex-1">
                                <p className="font-medium text-gray-900 dark:text-gray-100">{item.medicineName}</p>
                                <p className="text-sm text-gray-500 dark:text-gray-400">Aturan: {item.dosage}</p>
                            </div>
                            <div className="text-right">
                                <p className="font-medium text-gray-900 dark:text-gray-100">
                                    {item.quantity} x Rp {item.price.toLocaleString('id-ID')}
                                </p>
                                <p className="text-sm text-gray-500 dark:text-gray-400">
                                    Total: Rp {(item.quantity * item.price).toLocaleString('id-ID')}
                                </p>
                            </div>
                        </div>
                    ))}
                </div>
            </div>

            {/* Notes */}
            <div className="mb-6">
                <div className="flex justify-between items-center mb-2">
                    <h3 className="text-lg font-medium text-gray-900 dark:text-gray-100">Catatan Apoteker</h3>
                    {prescription.status === 'pending' && (
                        <button
                            onClick={() => setIsEditing(!isEditing)}
                            className="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300"
                        >
                            <FiEdit3 className="w-5 h-5" />
                        </button>
                    )}
                </div>
                {isEditing ? (
                    <textarea
                        value={pharmacistNotes}
                        onChange={(e) => setPharmacistNotes(e.target.value)}
                        className="w-full p-2 border border-gray-300 rounded-md dark:bg-slate-700 dark:border-slate-600"
                        rows={4}
                    />
                ) : (
                    <p className="text-gray-700 dark:text-gray-300">
                        {pharmacistNotes || 'Tidak ada catatan'}
                    </p>
                )}
            </div>

            {/* Pricing Summary */}
            <div className="border-t border-gray-200 dark:border-slate-700 pt-4">
                <div className="space-y-2">
                    <div className="flex justify-between text-sm">
                        <span className="text-gray-500 dark:text-gray-400">Subtotal</span>
                        <span className="text-gray-900 dark:text-gray-100">
                            Rp {prescription.total.toLocaleString('id-ID')}
                        </span>
                    </div>
                    <div className="flex justify-between text-sm">
                        <span className="text-gray-500 dark:text-gray-400">Diskon ({prescription.discount}%)</span>
                        <span className="text-gray-900 dark:text-gray-100">
                            - Rp {((prescription.discount / 100) * prescription.total).toLocaleString('id-ID')}
                        </span>
                    </div>
                    <div className="flex justify-between text-sm">
                        <span className="text-gray-500 dark:text-gray-400">Biaya Tambahan</span>
                        <span className="text-gray-900 dark:text-gray-100">
                            Rp {prescription.additionalFee.toLocaleString('id-ID')}
                        </span>
                    </div>
                    <div className="flex justify-between text-lg font-bold pt-2 border-t border-gray-200 dark:border-slate-700">
                        <span className="text-gray-900 dark:text-gray-100">Total</span>
                        <span className="text-gray-900 dark:text-gray-100">
                            Rp {(
                                prescription.total - 
                                ((prescription.discount / 100) * prescription.total) + 
                                prescription.additionalFee
                            ).toLocaleString('id-ID')}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    );
};

export default PrescriptionValidation;
