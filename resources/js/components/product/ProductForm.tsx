import React, { useState, useEffect } from "react"
import { Button } from "@/components/ui/button"
import { Input } from "@/components/ui/input"
import { Label } from "@/components/ui/label"
import { Checkbox } from "@/components/ui/checkbox"
import { Textarea } from "../../components/ui/textarea"
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from "@/components/ui/select"
import { DialogFooter } from "@/components/ui/dialog"
import { productService, type Product, type ProductCreateRequest } from '@/services/product.service';

interface ProductFormProps {
    product?: Product;
    onSaved: () => void;
}

export default function ProductForm({ product, onSaved }: ProductFormProps) {
    const [formData, setFormData] = useState<ProductCreateRequest>({
        name: '',
        category_id: 1,
        price: 0,
        stock: 0,
        unit: 'pcs',
        requires_prescription: false,
        description: '',
        status: 'active',
        sku: '',
        barcode: '',
        cost_price: 0,
        expiry_date: ''
    });

    const [loading, setLoading] = useState(false);
    const [error, setError] = useState<string | null>(null);

    useEffect(() => {
        if (product) {
            setFormData({
                name: product.name,
                category_id: product.category_id,
                price: product.price,
                stock: product.stock,
                unit: product.unit,
                requires_prescription: product.requires_prescription,
                description: product.description || '',
                status: product.status,
                sku: product.sku || '',
                barcode: product.barcode || '',
                cost_price: product.cost_price || 0,
                expiry_date: product.expiry_date || ''
            });
        }
    }, [product]);

    const handleChange = (e: React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement>) => {
        const { name, value, type } = e.target;
        
        setFormData(prev => ({
            ...prev,
            [name]: type === 'number' ? parseFloat(value) : value
        }));
    };

    const handleSelectChange = (name: string, value: string) => {
        setFormData(prev => ({
            ...prev,
            [name]: name === 'category_id' ? parseInt(value) : value
        }));
    };

    const handleCheckboxChange = (name: string, checked: boolean) => {
        setFormData(prev => ({
            ...prev,
            [name]: checked
        }));
    };

    const handleSubmit = async (e: React.FormEvent) => {
        e.preventDefault();
        setLoading(true);
        setError(null);

        try {
            if (product) {
                // Update existing product
                await productService.updateProduct(product.id, formData);
            } else {
                // Create new product
                await productService.createProduct(formData);
            }
            onSaved();
        } catch (err) {
            console.error('Error saving product:', err);
            setError('Failed to save product. Please check your inputs and try again.');
        } finally {
            setLoading(false);
        }
    };

    return (
        <form onSubmit={handleSubmit}>
            {error && (
                <div className="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    {error}
                </div>
            )}
            
            <div className="grid grid-cols-2 gap-4 mb-4">
                <div className="space-y-2">
                    <Label htmlFor="name">Nama Produk</Label>
                    <Input
                        id="name"
                        name="name"
                        value={formData.name}
                        onChange={handleChange}
                        required
                    />
                </div>

                <div className="space-y-2">
                    <Label htmlFor="category_id">Kategori</Label>
                    <Select 
                        value={formData.category_id.toString()} 
                        onValueChange={(value) => handleSelectChange('category_id', value)}
                    >
                        <SelectTrigger>
                            <SelectValue placeholder="Pilih Kategori" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="1">Obat Bebas</SelectItem>
                            <SelectItem value="2">Obat Keras</SelectItem>
                            <SelectItem value="3">Obat Bebas Terbatas</SelectItem>
                        </SelectContent>
                    </Select>
                </div>

                <div className="space-y-2">
                    <Label htmlFor="price">Harga Jual</Label>
                    <Input
                        id="price"
                        name="price"
                        type="number"
                        value={formData.price}
                        onChange={handleChange}
                        required
                    />
                </div>

                <div className="space-y-2">
                    <Label htmlFor="cost_price">Harga Beli</Label>
                    <Input
                        id="cost_price"
                        name="cost_price"
                        type="number"
                        value={formData.cost_price || 0}
                        onChange={handleChange}
                    />
                </div>

                <div className="space-y-2">
                    <Label htmlFor="stock">Stok</Label>
                    <Input
                        id="stock"
                        name="stock"
                        type="number"
                        value={formData.stock}
                        onChange={handleChange}
                        required
                    />
                </div>

                <div className="space-y-2">
                    <Label htmlFor="unit">Satuan</Label>
                    <Input
                        id="unit"
                        name="unit"
                        value={formData.unit}
                        onChange={handleChange}
                        required
                    />
                </div>

                <div className="space-y-2">
                    <Label htmlFor="sku">SKU</Label>
                    <Input
                        id="sku"
                        name="sku"
                        value={formData.sku || ''}
                        onChange={handleChange}
                    />
                </div>

                <div className="space-y-2">
                    <Label htmlFor="barcode">Barcode</Label>
                    <Input
                        id="barcode"
                        name="barcode"
                        value={formData.barcode || ''}
                        onChange={handleChange}
                    />
                </div>

                <div className="space-y-2">
                    <Label htmlFor="expiry_date">Tanggal Kadaluarsa</Label>
                    <Input
                        id="expiry_date"
                        name="expiry_date"
                        type="date"
                        value={formData.expiry_date || ''}
                        onChange={handleChange}
                    />
                </div>

                <div className="space-y-2">
                    <Label htmlFor="status">Status</Label>
                    <Select 
                        value={formData.status || 'active'} 
                        onValueChange={(value) => handleSelectChange('status', value)}
                    >
                        <SelectTrigger>
                            <SelectValue placeholder="Pilih Status" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="active">Aktif</SelectItem>
                            <SelectItem value="inactive">Tidak Aktif</SelectItem>
                        </SelectContent>
                    </Select>
                </div>
            </div>

            <div className="space-y-2 mb-4">
                <Label htmlFor="description">Deskripsi</Label>
                <Textarea
                    id="description"
                    name="description"
                    value={formData.description || ''}
                    onChange={handleChange}
                    rows={3}
                />
            </div>

            <div className="flex items-center space-x-2 mb-4">
                <Checkbox 
                    id="requires_prescription" 
                    checked={formData.requires_prescription}
                    onChange={(e) => 
                        handleCheckboxChange('requires_prescription', e.target.checked)
                    }
                />
                <Label htmlFor="requires_prescription">Memerlukan Resep</Label>
            </div>

            <DialogFooter>
                <Button type="submit" disabled={loading}>
                    {loading ? 'Menyimpan...' : product ? 'Update Produk' : 'Simpan Produk'}
                </Button>
            </DialogFooter>
        </form>
    );
}
