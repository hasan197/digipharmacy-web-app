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

import { inventoryService, type InventoryItem } from '@/services/inventory.service';

export default function Inventory() {
    const [inventory, setInventory] = useState<InventoryItem[]>([]);
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState<string | null>(null);
    const [searchQuery, setSearchQuery] = useState('');
    const [selectedCategory, setSelectedCategory] = useState('');
    
    // Pagination state
    const [currentPage, setCurrentPage] = useState(1);
    const [totalPages, setTotalPages] = useState(1);
    const [itemsPerPage] = useState(10);
    const [allInventoryItems, setAllInventoryItems] = useState<InventoryItem[]>([]);

    useEffect(() => {
        fetchInventory();
    }, [currentPage]);

    const fetchInventory = async () => {
        setLoading(true);
        try {
            const inventoryData = await inventoryService.getInventory({
                query: searchQuery,
                category: selectedCategory
            });
            
            // Ensure we're working with an array
            if (Array.isArray(inventoryData)) {
                setAllInventoryItems(inventoryData);
                
                // Calculate pagination
                const totalItems = inventoryData.length;
                setTotalPages(Math.ceil(totalItems / itemsPerPage));
                
                // Get current page items
                const startIndex = (currentPage - 1) * itemsPerPage;
                const endIndex = startIndex + itemsPerPage;
                const paginatedItems = inventoryData.slice(startIndex, endIndex);
                setInventory(paginatedItems);
            } else {
                // Fallback to empty array if response is not as expected
                console.error('Unexpected API response format:', inventoryData);
                setInventory([]);
                setAllInventoryItems([]);
                setTotalPages(1);
            }
            
            setError(null);
        } catch (err) {
            setError('Failed to fetch inventory');
            console.error('Error fetching inventory:', err);
            setInventory([]); // Ensure inventory is an array even on error
            setAllInventoryItems([]);
            setTotalPages(1);
        } finally {
            setLoading(false);
        }
    };

    const handleSearch = () => {
        setCurrentPage(1); // Reset to first page when searching
        fetchInventory();
    };

    const handleCategoryFilter = (category: string) => {
        setSelectedCategory(category);
        setCurrentPage(1); // Reset to first page when filtering
        fetchInventory();
    };

    return (
        <div className="container mx-auto p-6">
            <div className="flex justify-between items-center mb-6">
                <h2 className="text-3xl font-bold bg-gradient-to-r from-purple-600 to-pink-600 bg-clip-text text-transparent">
                    Stok Obat
                </h2>
                <Button>Tambah Item Baru</Button>
            </div>

            <div className="grid gap-6 md:grid-cols-2">
                {/* Panel Pencarian */}
                <Card>
                    <CardHeader>
                        <CardTitle>Cari Item</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div className="flex gap-4">
                            <Input 
                                placeholder="Cari nama obat..." 
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
                                    <SelectItem value="obat-bebas">Obat Bebas</SelectItem>
                                    <SelectItem value="obat-keras">Obat Keras</SelectItem>
                                    <SelectItem value="obat-bebas-terbatas">Obat Bebas Terbatas</SelectItem>
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
                        <CardTitle>Total Item</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <p className="text-3xl font-bold">{inventory.length}</p>
                    </CardContent>
                </Card>
                <Card>
                    <CardHeader>
                        <CardTitle>Stok Menipis</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <p className="text-3xl font-bold text-yellow-600">
                            {inventory.filter(item => item.stock < 100).length}
                        </p>
                    </CardContent>
                </Card>
                <Card>
                    <CardHeader>
                        <CardTitle>Hampir Kadaluarsa</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <p className="text-3xl font-bold text-red-600">
                            {inventory.filter(item => {
                                const expiryDate = new Date(item.expiry_date || '');
                                const threeMonthsFromNow = new Date();
                                threeMonthsFromNow.setMonth(threeMonthsFromNow.getMonth() + 3);
                                return expiryDate <= threeMonthsFromNow;
                            }).length}
                        </p>
                    </CardContent>
                </Card>
            </div>

            {/* Tabel Inventory */}
            <Card className="mt-6">
                <CardHeader>
                    <CardTitle>Daftar Stok</CardTitle>
                </CardHeader>
                <CardContent>
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>ID</TableHead>
                                <TableHead>Nama Obat</TableHead>
                                <TableHead>Kategori</TableHead>
                                <TableHead>Stok</TableHead>
                                <TableHead>Satuan</TableHead>
                                <TableHead>Supplier</TableHead>
                                <TableHead>Tanggal Kadaluarsa</TableHead>
                                <TableHead>Harga</TableHead>
                                <TableHead>Aksi</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            {inventory.map((item) => (
                                <TableRow key={item.id}>
                                    <TableCell>{item.id}</TableCell>
                                    <TableCell>{item.name}</TableCell>
                                    <TableCell>{item.category_id}</TableCell>
                                    <TableCell className={item.stock < 100 ? "text-yellow-600 font-medium" : ""}>
                                        {item.stock}
                                    </TableCell>
                                    <TableCell>{item.unit}</TableCell>
                                    <TableCell>-</TableCell>
                                    <TableCell>{item.expiry_date}</TableCell>
                                    <TableCell>Rp {item.price ? item.price.toLocaleString() : '0'}</TableCell>
                                    <TableCell>
                                        <div className="flex gap-2">
                                            <Button variant="outline" size="sm">
                                                Edit
                                            </Button>
                                            <Button variant="outline" size="sm">
                                                Stok +/-
                                            </Button>
                                            <Button variant="destructive" size="sm">
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
        </div>
    )
}
