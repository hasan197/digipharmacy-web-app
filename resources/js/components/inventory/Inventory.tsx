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
    CardTitle 
} from "@/components/ui/card"
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from "@/components/ui/select"
import { useState } from "react"
import DefaultLayout from "../layouts/DefaultLayout"

interface InventoryItem {
    id: number
    name: string
    category: string
    stock: number
    unit: string
    supplier: string
    expiryDate: string
    price: number
}

export default function Inventory() {
    const [inventory, setInventory] = useState<InventoryItem[]>([
        {
            id: 1,
            name: "Paracetamol 500mg",
            category: "Obat Bebas",
            stock: 500,
            unit: "Strip",
            supplier: "PT Kimia Farma",
            expiryDate: "2025-12-31",
            price: 12000
        },
        {
            id: 2,
            name: "Amoxicillin 500mg",
            category: "Obat Keras",
            stock: 200,
            unit: "Strip",
            supplier: "PT Kalbe Farma",
            expiryDate: "2025-06-30",
            price: 25000
        }
    ])

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
                            />
                            <Button variant="secondary">
                                Cari
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
                            <Select>
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
                                const expiryDate = new Date(item.expiryDate);
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
                                    <TableCell>{item.category}</TableCell>
                                    <TableCell className={item.stock < 100 ? "text-yellow-600 font-medium" : ""}>
                                        {item.stock}
                                    </TableCell>
                                    <TableCell>{item.unit}</TableCell>
                                    <TableCell>{item.supplier}</TableCell>
                                    <TableCell>{item.expiryDate}</TableCell>
                                    <TableCell>Rp {item.price.toLocaleString()}</TableCell>
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
                </CardContent>
            </Card>
        </div>
    )
}
