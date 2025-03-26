import React, { useState } from 'react';
import { FiPlus, FiTrash2, FiCheck, FiX } from 'react-icons/fi';
import axios from 'axios';

interface Medicine {
    id: number;
    name: string;
    price: number;
    stock: number;
    unit: string;
}

interface PrescriptionItem {
    medicineId: number;
    medicineName: string;
    quantity: number;
    dosage: string;
    price: number;
}

interface PrescriptionFormProps {
    onSubmit: (data: any) => void;
    onCancel: () => void;
}

const PrescriptionForm: React.FC<PrescriptionFormProps> = ({ onSubmit, onCancel }) => {
    const [patientName, setPatientName] = useState('');
    const [doctorName, setDoctorName] = useState('');
    const [items, setItems] = useState<PrescriptionItem[]>([]);
    const [searchQuery, setSearchQuery] = useState('');
    const [searchResults, setSearchResults] = useState<Medicine[]>([]);
    const [notes, setNotes] = useState('');
    const [total, setTotal] = useState(0);
    const [discount, setDiscount] = useState(0);
    const [additionalFee, setAdditionalFee] = useState(0);

    // Search medicines
    const handleSearch = async (query: string) => {
        setSearchQuery(query);
        if (query.trim()) {
            try {
                const response = await axios.get(`/api/medicines/search?query=${query}`);
                setSearchResults(response.data);
            } catch (error) {
                console.error('Error searching medicines:', error);
            }
        } else {
            setSearchResults([]);
        }
    };

    // Add medicine to prescription
    const handleAddMedicine = (medicine: Medicine) => {
        const newItem: PrescriptionItem = {
            medicineId: medicine.id,
            medicineName: medicine.name,
            quantity: 1,
            dosage: '',
            price: medicine.price
        };
        setItems([...items, newItem]);
        setSearchQuery('');
        setSearchResults([]);
        calculateTotal();
    };

    // Remove medicine from prescription
    const handleRemoveMedicine = (index: number) => {
        const newItems = items.filter((_, i) => i !== index);
        setItems(newItems);
        calculateTotal();
    };

    // Update quantity or dosage
    const handleUpdateItem = (index: number, field: string, value: string | number) => {
        const newItems = [...items];
        newItems[index] = {
            ...newItems[index],
            [field]: value
        };
        setItems(newItems);
        calculateTotal();
    };

    // Calculate total price
    const calculateTotal = () => {
        const subtotal = items.reduce((sum, item) => sum + (item.price * item.quantity), 0);
        const discountAmount = (discount / 100) * subtotal;
        const finalTotal = subtotal - discountAmount + additionalFee;
        setTotal(finalTotal);
    };

    // Handle form submission
    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        const prescriptionData = {
            patientName,
            doctorName,
            items,
            notes,
            subtotal: total,
            discount,
            additionalFee,
            total: total,
            status: 'pending' // pending validation
        };
        onSubmit(prescriptionData);
    };

    return (
        <form onSubmit={handleSubmit} className="bg-white dark:bg-slate-800 rounded-lg shadow p-6">
            <div className="space-y-6">
                {/* Patient and Doctor Information */}
                <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label className="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Nama Pasien
                        </label>
                        <input
                            type="text"
                            value={patientName}
                            onChange={(e) => setPatientName(e.target.value)}
                            className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-slate-700 dark:border-slate-600"
                            required
                        />
                    </div>
                    <div>
                        <label className="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Nama Dokter
                        </label>
                        <input
                            type="text"
                            value={doctorName}
                            onChange={(e) => setDoctorName(e.target.value)}
                            className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-slate-700 dark:border-slate-600"
                            required
                        />
                    </div>
                </div>

                {/* Medicine Search */}
                <div>
                    <label className="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Tambah Obat
                    </label>
                    <div className="mt-1 relative">
                        <input
                            type="text"
                            value={searchQuery}
                            onChange={(e) => handleSearch(e.target.value)}
                            className="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-slate-700 dark:border-slate-600"
                            placeholder="Ketik nama obat atau scan barcode..."
                        />
                        {searchResults.length > 0 && (
                            <div className="absolute z-10 w-full mt-1 bg-white dark:bg-slate-700 rounded-md shadow-lg">
                                {searchResults.map((medicine) => (
                                    <div
                                        key={medicine.id}
                                        className="px-4 py-2 hover:bg-gray-100 dark:hover:bg-slate-600 cursor-pointer"
                                        onClick={() => handleAddMedicine(medicine)}
                                    >
                                        <div className="flex justify-between">
                                            <span>{medicine.name}</span>
                                            <span className="text-gray-500 dark:text-gray-400">
                                                Stock: {medicine.stock} {medicine.unit}
                                            </span>
                                        </div>
                                    </div>
                                ))}
                            </div>
                        )}
                    </div>
                </div>

                {/* Medicine List */}
                <div>
                    <h3 className="text-lg font-medium text-gray-900 dark:text-gray-100">Daftar Obat</h3>
                    <div className="mt-4 space-y-4">
                        {items.map((item, index) => (
                            <div key={index} className="flex items-center space-x-4">
                                <div className="flex-1">
                                    <p className="font-medium">{item.medicineName}</p>
                                </div>
                                <input
                                    type="number"
                                    value={item.quantity}
                                    onChange={(e) => handleUpdateItem(index, 'quantity', parseInt(e.target.value))}
                                    className="w-20 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-slate-700 dark:border-slate-600"
                                    min="1"
                                />
                                <input
                                    type="text"
                                    value={item.dosage}
                                    onChange={(e) => handleUpdateItem(index, 'dosage', e.target.value)}
                                    className="w-40 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-slate-700 dark:border-slate-600"
                                    placeholder="Aturan pakai"
                                />
                                <button
                                    type="button"
                                    onClick={() => handleRemoveMedicine(index)}
                                    className="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300"
                                >
                                    <FiTrash2 className="w-5 h-5" />
                                </button>
                            </div>
                        ))}
                    </div>
                </div>

                {/* Notes */}
                <div>
                    <label className="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Catatan
                    </label>
                    <textarea
                        value={notes}
                        onChange={(e) => setNotes(e.target.value)}
                        rows={3}
                        className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-slate-700 dark:border-slate-600"
                    />
                </div>

                {/* Pricing */}
                <div className="space-y-4">
                    <div className="flex justify-between items-center">
                        <span className="text-sm font-medium text-gray-700 dark:text-gray-300">Diskon (%)</span>
                        <input
                            type="number"
                            value={discount}
                            onChange={(e) => {
                                setDiscount(parseFloat(e.target.value));
                                calculateTotal();
                            }}
                            className="w-32 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-slate-700 dark:border-slate-600"
                            min="0"
                            max="100"
                        />
                    </div>
                    <div className="flex justify-between items-center">
                        <span className="text-sm font-medium text-gray-700 dark:text-gray-300">Biaya Tambahan</span>
                        <input
                            type="number"
                            value={additionalFee}
                            onChange={(e) => {
                                setAdditionalFee(parseFloat(e.target.value));
                                calculateTotal();
                            }}
                            className="w-32 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-slate-700 dark:border-slate-600"
                            min="0"
                        />
                    </div>
                    <div className="flex justify-between items-center text-lg font-bold">
                        <span className="text-gray-900 dark:text-gray-100">Total</span>
                        <span className="text-gray-900 dark:text-gray-100">
                            Rp {total.toLocaleString('id-ID')}
                        </span>
                    </div>
                </div>

                {/* Submit Buttons */}
                <div className="flex justify-end space-x-4">
                    <button
                        type="button"
                        onClick={onCancel}
                        className="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 dark:bg-slate-700 dark:border-slate-600 dark:text-gray-300 dark:hover:bg-slate-600"
                    >
                        Batal
                    </button>
                    <button
                        type="submit"
                        className="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 dark:hover:bg-blue-800"
                    >
                        Simpan Resep
                    </button>
                </div>
            </div>
        </form>
    );
};

export default PrescriptionForm;
