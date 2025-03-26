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
    Card,
    CardContent,
    CardHeader,
    CardTitle,
} from "@/components/ui/card"
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from "@/components/ui/dialog"
import { Label } from "@/components/ui/label"
import { Alert, AlertTitle, AlertDescription } from "@/components/ui/alert"

import { customersService, type Customer, type CustomerCreateRequest } from '@/services/customers.service';

export default function Customers() {
    const [customers, setCustomers] = useState<Customer[]>([]);
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState<string | null>(null);
    const [successMessage, setSuccessMessage] = useState<string | null>(null);
    const [errorMessage, setErrorMessage] = useState<string | null>(null);
    const [searchQuery, setSearchQuery] = useState('');
    const [joinDate, setJoinDate] = useState('');
    
    // State for customer form
    const [isAddDialogOpen, setIsAddDialogOpen] = useState(false);
    const [isEditDialogOpen, setIsEditDialogOpen] = useState(false);
    const [isDeleteDialogOpen, setIsDeleteDialogOpen] = useState(false);
    const [selectedCustomer, setSelectedCustomer] = useState<Customer | null>(null);
    const [formData, setFormData] = useState<CustomerCreateRequest>({
        name: '',
        email: '',
        phone: '',
        address: ''
    });
    const [formSubmitting, setFormSubmitting] = useState(false);

    useEffect(() => {
        fetchCustomers();
    }, []);

    const fetchCustomers = async () => {
        setLoading(true);
        try {
            const customersData = await customersService.getCustomers({
                query: searchQuery,
                joinDateStart: joinDate
            });
            
            // Ensure we're working with an array
            if (Array.isArray(customersData)) {
                setCustomers(customersData);
            } else {
                // Fallback to empty array if response is not as expected
                console.error('Unexpected API response format:', customersData);
                setCustomers([]);
            }
            
            setError(null);
        } catch (err) {
            setError('Failed to fetch customers');
            console.error('Error fetching customers:', err);
            setCustomers([]); // Ensure customers is an array even on error
        } finally {
            setLoading(false);
        }
    };

    const handleSearch = () => {
        fetchCustomers();
    };

    const handleDateFilter = () => {
        fetchCustomers();
    };
    
    // Reset form data
    const resetFormData = () => {
        setFormData({
            name: '',
            email: '',
            phone: '',
            address: ''
        });
    };
    
    // Open add dialog
    const openAddDialog = () => {
        resetFormData();
        setIsAddDialogOpen(true);
    };
    
    // Open edit dialog
    const openEditDialog = (customer: Customer) => {
        setSelectedCustomer(customer);
        setFormData({
            name: customer.name,
            email: customer.email,
            phone: customer.phone,
            address: customer.address
        });
        setIsEditDialogOpen(true);
    };
    
    // Open delete dialog
    const openDeleteDialog = (customer: Customer) => {
        setSelectedCustomer(customer);
        setIsDeleteDialogOpen(true);
    };
    
    // Handle form input change
    const handleInputChange = (e: React.ChangeEvent<HTMLInputElement>) => {
        const { name, value } = e.target;
        setFormData(prev => ({
            ...prev,
            [name]: value
        }));
    };
    
    // Handle add customer
    const handleAddCustomer = async () => {
        setFormSubmitting(true);
        try {
            await customersService.createCustomer(formData);
            setIsAddDialogOpen(false);
            // Show success message
            setSuccessMessage("Customer added successfully");
            setTimeout(() => setSuccessMessage(null), 3000);
            fetchCustomers(); // Refresh the list
        } catch (err) {
            console.error('Error adding customer:', err);
            // Show error message
            setErrorMessage("Failed to add customer");
            setTimeout(() => setErrorMessage(null), 3000);
        } finally {
            setFormSubmitting(false);
        }
    };
    
    // Handle edit customer
    const handleEditCustomer = async () => {
        if (!selectedCustomer) return;
        
        setFormSubmitting(true);
        try {
            await customersService.updateCustomer(selectedCustomer.id, formData);
            setIsEditDialogOpen(false);
            // Show success message
            setSuccessMessage("Customer updated successfully");
            setTimeout(() => setSuccessMessage(null), 3000);
            fetchCustomers(); // Refresh the list
        } catch (err) {
            console.error('Error updating customer:', err);
            // Show error message
            setErrorMessage("Failed to update customer");
            setTimeout(() => setErrorMessage(null), 3000);
        } finally {
            setFormSubmitting(false);
        }
    };
    
    // Handle delete customer
    const handleDeleteCustomer = async () => {
        if (!selectedCustomer) return;
        
        setFormSubmitting(true);
        try {
            await customersService.deleteCustomer(selectedCustomer.id);
            setIsDeleteDialogOpen(false);
            // Show success message
            setSuccessMessage("Customer deleted successfully");
            setTimeout(() => setSuccessMessage(null), 3000);
            fetchCustomers(); // Refresh the list
        } catch (err) {
            console.error('Error deleting customer:', err);
            // Show error message
            setErrorMessage("Failed to delete customer. Customer may have transactions.");
            setTimeout(() => setErrorMessage(null), 3000);
        } finally {
            setFormSubmitting(false);
        }
    };

    return (
        <div className="container mx-auto p-6">
            {/* Success Message */}
            {successMessage && (
                <Alert className="mb-4" variant="default">
                    <AlertTitle>Success</AlertTitle>
                    <AlertDescription>{successMessage}</AlertDescription>
                </Alert>
            )}
            
            {/* Error Message */}
            {errorMessage && (
                <Alert className="mb-4" variant="destructive">
                    <AlertTitle>Error</AlertTitle>
                    <AlertDescription>{errorMessage}</AlertDescription>
                </Alert>
            )}
            
            <div className="flex justify-between items-center mb-6">
                <h2 className="text-3xl font-bold bg-gradient-to-r from-purple-600 to-pink-600 bg-clip-text text-transparent">
                    Pelanggan
                </h2>
                <Button onClick={openAddDialog}>Tambah Pelanggan Baru</Button>
            </div>

            <div className="grid gap-6 md:grid-cols-2">
                {/* Panel Pencarian */}
                <Card>
                    <CardHeader>
                        <CardTitle>Cari Pelanggan</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div className="flex gap-4">
                            <Input 
                                placeholder="Cari nama atau email..." 
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
                            <Input 
                                type="date" 
                                className="flex-1"
                                placeholder="Filter berdasarkan tanggal bergabung"
                                value={joinDate}
                                onChange={(e) => setJoinDate(e.target.value)}
                            />
                            <Button 
                                variant="secondary"
                                onClick={handleDateFilter}
                                disabled={loading}
                            >
                                {loading ? 'Menerapkan...' : 'Terapkan Filter'}
                            </Button>
                        </div>
                    </CardContent>
                </Card>
            </div>

            {/* Tabel Pelanggan */}
            <Card className="mt-6">
                <CardHeader>
                    <CardTitle>Daftar Pelanggan</CardTitle>
                </CardHeader>
                <CardContent>
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>ID</TableHead>
                                <TableHead>Nama</TableHead>
                                <TableHead>Email</TableHead>
                                <TableHead>No. Telepon</TableHead>
                                <TableHead>Alamat</TableHead>
                                <TableHead>Tanggal Bergabung</TableHead>
                                <TableHead>Aksi</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            {Array.isArray(customers) && customers.length > 0 ? customers.map((customer) => (
                                <TableRow key={customer.id}>
                                    <TableCell>{customer.id}</TableCell>
                                    <TableCell>{customer.name}</TableCell>
                                    <TableCell>{customer.email}</TableCell>
                                    <TableCell>{customer.phone}</TableCell>
                                    <TableCell>{customer.address}</TableCell>
                                    <TableCell>{customer.joinDate}</TableCell>
                                    <TableCell>
                                        <div className="flex gap-2">
                                            <Button 
                                                variant="outline" 
                                                size="sm"
                                                onClick={() => openEditDialog(customer)}
                                            >
                                                Edit
                                            </Button>
                                            <Button 
                                                variant="destructive" 
                                                size="sm"
                                                onClick={() => openDeleteDialog(customer)}
                                            >
                                                Hapus
                                            </Button>
                                        </div>
                                    </TableCell>
                                </TableRow>
                            )) : (
                                <TableRow>
                                    <TableCell colSpan={7} className="text-center py-6">
                                        {loading ? 'Loading customers...' : error ? error : 'No customers found'}
                                    </TableCell>
                                </TableRow>
                            )}
                        </TableBody>
                    </Table>
                </CardContent>
            </Card>
            
            {/* Add Customer Dialog */}
            <Dialog open={isAddDialogOpen} onOpenChange={setIsAddDialogOpen}>
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>Tambah Pelanggan Baru</DialogTitle>
                        <DialogDescription>
                            Masukkan informasi pelanggan baru di bawah ini.
                        </DialogDescription>
                    </DialogHeader>
                    <div className="grid gap-4 py-4">
                        <div className="grid grid-cols-4 items-center gap-4">
                            <Label htmlFor="name" className="text-right">
                                Nama
                            </Label>
                            <Input
                                id="name"
                                name="name"
                                value={formData.name}
                                onChange={handleInputChange}
                                className="col-span-3"
                            />
                        </div>
                        <div className="grid grid-cols-4 items-center gap-4">
                            <Label htmlFor="email" className="text-right">
                                Email
                            </Label>
                            <Input
                                id="email"
                                name="email"
                                type="email"
                                value={formData.email}
                                onChange={handleInputChange}
                                className="col-span-3"
                            />
                        </div>
                        <div className="grid grid-cols-4 items-center gap-4">
                            <Label htmlFor="phone" className="text-right">
                                Telepon
                            </Label>
                            <Input
                                id="phone"
                                name="phone"
                                value={formData.phone}
                                onChange={handleInputChange}
                                className="col-span-3"
                            />
                        </div>
                        <div className="grid grid-cols-4 items-center gap-4">
                            <Label htmlFor="address" className="text-right">
                                Alamat
                            </Label>
                            <Input
                                id="address"
                                name="address"
                                value={formData.address}
                                onChange={handleInputChange}
                                className="col-span-3"
                            />
                        </div>
                    </div>
                    <DialogFooter>
                        <Button 
                            variant="outline" 
                            onClick={() => setIsAddDialogOpen(false)}
                            disabled={formSubmitting}
                        >
                            Batal
                        </Button>
                        <Button 
                            onClick={handleAddCustomer}
                            disabled={formSubmitting}
                        >
                            {formSubmitting ? 'Menyimpan...' : 'Simpan'}
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>
            
            {/* Edit Customer Dialog */}
            <Dialog open={isEditDialogOpen} onOpenChange={setIsEditDialogOpen}>
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>Edit Pelanggan</DialogTitle>
                        <DialogDescription>
                            Edit informasi pelanggan di bawah ini.
                        </DialogDescription>
                    </DialogHeader>
                    <div className="grid gap-4 py-4">
                        <div className="grid grid-cols-4 items-center gap-4">
                            <Label htmlFor="edit-name" className="text-right">
                                Nama
                            </Label>
                            <Input
                                id="edit-name"
                                name="name"
                                value={formData.name}
                                onChange={handleInputChange}
                                className="col-span-3"
                            />
                        </div>
                        <div className="grid grid-cols-4 items-center gap-4">
                            <Label htmlFor="edit-email" className="text-right">
                                Email
                            </Label>
                            <Input
                                id="edit-email"
                                name="email"
                                type="email"
                                value={formData.email}
                                onChange={handleInputChange}
                                className="col-span-3"
                            />
                        </div>
                        <div className="grid grid-cols-4 items-center gap-4">
                            <Label htmlFor="edit-phone" className="text-right">
                                Telepon
                            </Label>
                            <Input
                                id="edit-phone"
                                name="phone"
                                value={formData.phone}
                                onChange={handleInputChange}
                                className="col-span-3"
                            />
                        </div>
                        <div className="grid grid-cols-4 items-center gap-4">
                            <Label htmlFor="edit-address" className="text-right">
                                Alamat
                            </Label>
                            <Input
                                id="edit-address"
                                name="address"
                                value={formData.address}
                                onChange={handleInputChange}
                                className="col-span-3"
                            />
                        </div>
                    </div>
                    <DialogFooter>
                        <Button 
                            variant="outline" 
                            onClick={() => setIsEditDialogOpen(false)}
                            disabled={formSubmitting}
                        >
                            Batal
                        </Button>
                        <Button 
                            onClick={handleEditCustomer}
                            disabled={formSubmitting}
                        >
                            {formSubmitting ? 'Menyimpan...' : 'Simpan'}
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>
            
            {/* Delete Customer Dialog */}
            <Dialog open={isDeleteDialogOpen} onOpenChange={setIsDeleteDialogOpen}>
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>Hapus Pelanggan</DialogTitle>
                        <DialogDescription>
                            Apakah Anda yakin ingin menghapus pelanggan ini? Tindakan ini tidak dapat dibatalkan.
                            {selectedCustomer && (
                                <div className="mt-2 font-medium">
                                    {selectedCustomer.name} ({selectedCustomer.email || selectedCustomer.phone})
                                </div>
                            )}
                        </DialogDescription>
                    </DialogHeader>
                    <DialogFooter>
                        <Button 
                            variant="outline" 
                            onClick={() => setIsDeleteDialogOpen(false)}
                            disabled={formSubmitting}
                        >
                            Batal
                        </Button>
                        <Button 
                            onClick={handleDeleteCustomer}
                            disabled={formSubmitting}
                            variant="destructive"
                        >
                            {formSubmitting ? 'Menghapus...' : 'Hapus'}
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>
        </div>
    )
}
