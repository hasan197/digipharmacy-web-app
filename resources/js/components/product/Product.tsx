import React, { useState, useEffect } from "react"
import { Button } from "@/components/ui/button"
import { Input } from "@/components/ui/input"
import { 
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow 
} from "@/components/ui/table"
import {
    Pagination,
    PaginationContent,
    PaginationEllipsis,
    PaginationItem,
    PaginationLink,
    PaginationNext,
    PaginationPrevious,
} from "@/components/ui/pagination"
import { 
    Card,
    CardContent,
    CardHeader,
    CardTitle 
} from "@/components/ui/card"
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from "@/components/ui/select"
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
} from "@/components/ui/dialog"

import { productService, type Product as ProductType } from '@/services/product.service';
import ProductForm from './ProductForm';

export default function Product() {
    const [products, setProducts] = useState<ProductType[]>([]);
    const [loading, setLoading] = useState(false);
    const [, setError] = useState<string | null>(null);
    const [searchQuery, setSearchQuery] = useState('');
    const [selectedCategory, setSelectedCategory] = useState('');
    const [isAddDialogOpen, setIsAddDialogOpen] = useState(false);
    const [isEditDialogOpen, setIsEditDialogOpen] = useState(false);
    const [currentProduct, setCurrentProduct] = useState<ProductType | null>(null);
    
    // Pagination state
    const [currentPage, setCurrentPage] = useState(1);
    const [totalPages, setTotalPages] = useState(1);
    const [itemsPerPage] = useState(10);
    const [allProducts, setAllProducts] = useState<ProductType[]>([]);

    useEffect(() => {
        fetchProducts();
    }, [currentPage]);

    const fetchProducts = async () => {
        setLoading(true);
        try {
            const productsData = await productService.getProducts({
                query: searchQuery,
                category_id: selectedCategory ? parseInt(selectedCategory) : undefined
            });
            
            if (Array.isArray(productsData)) {
                setAllProducts(productsData);
                
                // Calculate pagination
                const totalItems = productsData.length;
                setTotalPages(Math.ceil(totalItems / itemsPerPage));
                
                // Get current page items
                const startIndex = (currentPage - 1) * itemsPerPage;
                const endIndex = startIndex + itemsPerPage;
                const paginatedProducts = productsData.slice(startIndex, endIndex);
                setProducts(paginatedProducts);
            } else {
                console.error('Unexpected API response format:', productsData);
                setProducts([]);
                setAllProducts([]);
                setTotalPages(1);
            }
            
            setError(null);
        } catch (err) {
            setError('Failed to fetch products');
            console.error('Error fetching products:', err);
            setProducts([]);
            setAllProducts([]);
            setTotalPages(1);
        } finally {
            setLoading(false);
        }
    };

    const handleSearch = () => {
        setCurrentPage(1); // Reset to first page when searching
        fetchProducts();
    };

    const handleCategoryFilter = (category: string) => {
        setSelectedCategory(category);
        setCurrentPage(1); // Reset to first page when filtering
        fetchProducts();
    };

    const handleAddProduct = () => {
        setIsAddDialogOpen(true);
    };

    const handleEditProduct = (product: ProductType) => {
        setCurrentProduct(product);
        setIsEditDialogOpen(true);
    };

    const handleDeleteProduct = async (id: number) => {
        if (confirm('Are you sure you want to delete this product?')) {
            try {
                await productService.deleteProduct(id);
                fetchProducts();
            } catch (err) {
                console.error('Error deleting product:', err);
                setError('Failed to delete product');
            }
        }
    };

    const handleProductSaved = () => {
        setIsAddDialogOpen(false);
        setIsEditDialogOpen(false);
        setCurrentProduct(null);
        fetchProducts();
    };

    const formatCurrency = (amount: number) => {
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR'
        }).format(amount);
    };

    return (
        <div className="container mx-auto p-6">
            <div className="flex justify-between items-center mb-6">
                <h2 className="text-3xl font-bold bg-gradient-to-r from-purple-600 to-pink-600 bg-clip-text text-transparent">
                    Manajemen Produk
                </h2>
                <Button onClick={handleAddProduct}>Tambah Produk Baru</Button>
            </div>

            <div className="grid gap-6 md:grid-cols-2">
                {/* Panel Pencarian */}
                <Card>
                    <CardHeader>
                        <CardTitle>Cari Produk</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div className="flex gap-4">
                            <Input 
                                placeholder="Cari nama produk..." 
                                className="flex-1"
                                value={searchQuery}
                                onChange={(e) => setSearchQuery(e.target.value)}
                            />
                            <Button 
                                variant="secondary"
                                onClick={handleSearch}
                                disabled={loading}
                            >
                                {loading ? 'Mencari...' : 'Cari'}
                            </Button>
                        </div>
                    </CardContent>
                </Card>

                {/* Panel Filter */}
                <Card>
                    <CardHeader>
                        <CardTitle>Filter</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div className="flex gap-4">
                            <Select value={selectedCategory} onValueChange={handleCategoryFilter}>
                                <SelectTrigger className="flex-1">
                                    <SelectValue placeholder="Pilih Kategori" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="1">Obat Bebas</SelectItem>
                                    <SelectItem value="2">Obat Keras</SelectItem>
                                    <SelectItem value="3">Obat Bebas Terbatas</SelectItem>
                                </SelectContent>
                            </Select>
                            <Button variant="secondary">
                                Terapkan Filter
                            </Button>
                        </div>
                    </CardContent>
                </Card>
            </div>

            {/* Panel Statistik */}
            <div className="grid gap-6 md:grid-cols-3 mt-6">
                <Card>
                    <CardHeader>
                        <CardTitle>Total Produk</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <p className="text-3xl font-bold">{products.length}</p>
                    </CardContent>
                </Card>
                <Card>
                    <CardHeader>
                        <CardTitle>Stok Menipis</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <p className="text-3xl font-bold text-yellow-600">
                            {products.filter(item => item.stock < 100).length}
                        </p>
                    </CardContent>
                </Card>
                <Card>
                    <CardHeader>
                        <CardTitle>Produk Tidak Aktif</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <p className="text-3xl font-bold text-red-600">
                            {products.filter(item => item.status === 'inactive').length}
                        </p>
                    </CardContent>
                </Card>
            </div>

            {/* Tabel Produk */}
            <Card className="mt-6">
                <CardHeader>
                    <CardTitle>Daftar Produk</CardTitle>
                </CardHeader>
                <CardContent>
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>ID</TableHead>
                                <TableHead>Nama Produk</TableHead>
                                <TableHead>Kategori</TableHead>
                                <TableHead>Stok</TableHead>
                                <TableHead>Satuan</TableHead>
                                <TableHead>Harga Jual</TableHead>
                                <TableHead>Harga Beli</TableHead>
                                <TableHead>Status</TableHead>
                                <TableHead>Aksi</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            {products.length === 0 && (
                                <TableRow>
                                    <TableCell colSpan={9} className="text-center py-4">
                                        {loading ? 'Memuat data...' : 'Tidak ada data produk'}
                                    </TableCell>
                                </TableRow>
                            )}
                            {products.map((product) => (
                                <TableRow key={product.id}>
                                    <TableCell>{product.id}</TableCell>
                                    <TableCell>{product.name}</TableCell>
                                    <TableCell>{product.category_id}</TableCell>
                                    <TableCell className={product.stock < 100 ? "text-yellow-600 font-medium" : ""}>
                                        {product.stock}
                                    </TableCell>
                                    <TableCell>{product.unit}</TableCell>
                                    <TableCell>{formatCurrency(product.price)}</TableCell>
                                    <TableCell>{product.cost_price ? formatCurrency(product.cost_price) : '-'}</TableCell>
                                    <TableCell>
                                        <span className={`px-2 py-1 rounded-full text-xs font-medium ${
                                            product.status === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'
                                        }`}>
                                            {product.status === 'active' ? 'Aktif' : 'Tidak Aktif'}
                                        </span>
                                    </TableCell>
                                    <TableCell>
                                        <div className="flex gap-2">
                                            <Button 
                                                variant="outline" 
                                                size="sm"
                                                onClick={() => handleEditProduct(product)}
                                            >
                                                Edit
                                            </Button>
                                            <Button 
                                                variant="destructive" 
                                                size="sm"
                                                onClick={() => handleDeleteProduct(product.id)}
                                            >
                                                Hapus
                                            </Button>
                                        </div>
                                    </TableCell>
                                </TableRow>
                            ))}
                        </TableBody>
                    </Table>
                    
                    {/* Pagination */}
                    {totalPages > 1 && (
                        <div className="mt-4 flex justify-center">
                            <Pagination>
                                <PaginationContent>
                                    <PaginationItem>
                                        <PaginationPrevious 
                                            onClick={() => setCurrentPage(prev => Math.max(prev - 1, 1))} 
                                            disabled={currentPage === 1}
                                        />
                                    </PaginationItem>
                                    
                                    {/* First page */}
                                    {currentPage > 2 && (
                                        <PaginationItem>
                                            <PaginationLink onClick={() => setCurrentPage(1)}>1</PaginationLink>
                                        </PaginationItem>
                                    )}
                                    
                                    {/* Ellipsis if needed */}
                                    {currentPage > 3 && (
                                        <PaginationItem>
                                            <PaginationEllipsis />
                                        </PaginationItem>
                                    )}
                                    
                                    {/* Previous page if not first */}
                                    {currentPage > 1 && (
                                        <PaginationItem>
                                            <PaginationLink onClick={() => setCurrentPage(currentPage - 1)}>
                                                {currentPage - 1}
                                            </PaginationLink>
                                        </PaginationItem>
                                    )}
                                    
                                    {/* Current page */}
                                    <PaginationItem>
                                        <PaginationLink isActive>{currentPage}</PaginationLink>
                                    </PaginationItem>
                                    
                                    {/* Next page if not last */}
                                    {currentPage < totalPages && (
                                        <PaginationItem>
                                            <PaginationLink onClick={() => setCurrentPage(currentPage + 1)}>
                                                {currentPage + 1}
                                            </PaginationLink>
                                        </PaginationItem>
                                    )}
                                    
                                    {/* Ellipsis if needed */}
                                    {currentPage < totalPages - 2 && (
                                        <PaginationItem>
                                            <PaginationEllipsis />
                                        </PaginationItem>
                                    )}
                                    
                                    {/* Last page */}
                                    {currentPage < totalPages - 1 && (
                                        <PaginationItem>
                                            <PaginationLink onClick={() => setCurrentPage(totalPages)}>
                                                {totalPages}
                                            </PaginationLink>
                                        </PaginationItem>
                                    )}
                                    
                                    <PaginationItem>
                                        <PaginationNext 
                                            onClick={() => setCurrentPage(prev => Math.min(prev + 1, totalPages))} 
                                            disabled={currentPage === totalPages}
                                        />
                                    </PaginationItem>
                                </PaginationContent>
                            </Pagination>
                        </div>
                    )}
                </CardContent>
            </Card>

            {/* Add Product Dialog */}
            <Dialog open={isAddDialogOpen} onOpenChange={setIsAddDialogOpen}>
                <DialogContent className="max-w-2xl">
                    <DialogHeader>
                        <DialogTitle>Tambah Produk Baru</DialogTitle>
                        <DialogDescription>
                            Isi data produk baru dengan lengkap
                        </DialogDescription>
                    </DialogHeader>
                    <ProductForm onSaved={handleProductSaved} />
                </DialogContent>
            </Dialog>

            {/* Edit Product Dialog */}
            <Dialog open={isEditDialogOpen} onOpenChange={setIsEditDialogOpen}>
                <DialogContent className="max-w-2xl">
                    <DialogHeader>
                        <DialogTitle>Edit Produk</DialogTitle>
                        <DialogDescription>
                            Edit data produk
                        </DialogDescription>
                    </DialogHeader>
                    {currentProduct && (
                        <ProductForm product={currentProduct} onSaved={handleProductSaved} />
                    )}
                </DialogContent>
            </Dialog>
        </div>
    );
}
