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
import { useState } from "react"
import DefaultLayout from "../layouts/DefaultLayout"

interface Customer {
    id: number
    name: string
    email: string
    phone: string
    address: string
    joinDate: string
}

export default function Customers() {
    const [customers, setCustomers] = useState<Customer[]>([
        {
            id: 1,
            name: "John Doe",
            email: "john.doe@example.com",
            phone: "0812-3456-7890",
            address: "Jl. Contoh No. 123, Jakarta",
            joinDate: "2024-01-15"
        },
        {
            id: 2,
            name: "Jane Smith",
            email: "jane.smith@example.com",
            phone: "0898-7654-3210",
            address: "Jl. Sample No. 456, Bandung",
            joinDate: "2024-02-20"
        }
    ])

    return (
        <div className="container mx-auto p-6">
            <div className="flex justify-between items-center mb-6">
                <h2 className="text-3xl font-bold bg-gradient-to-r from-purple-600 to-pink-600 bg-clip-text text-transparent">
                    Pelanggan
                </h2>
                <Button>Tambah Pelanggan Baru</Button>
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
                            <Input 
                                type="date" 
                                className="flex-1"
                                placeholder="Filter berdasarkan tanggal bergabung"
                            />
                            <Button variant="secondary">
                                Terapkan Filter
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
                            {customers.map((customer) => (
                                <TableRow key={customer.id}>
                                    <TableCell>{customer.id}</TableCell>
                                    <TableCell>{customer.name}</TableCell>
                                    <TableCell>{customer.email}</TableCell>
                                    <TableCell>{customer.phone}</TableCell>
                                    <TableCell>{customer.address}</TableCell>
                                    <TableCell>{customer.joinDate}</TableCell>
                                    <TableCell>
                                        <div className="flex gap-2">
                                            <Button variant="outline" size="sm">
                                                Lihat
                                            </Button>
                                            <Button variant="outline" size="sm">
                                                Edit
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
