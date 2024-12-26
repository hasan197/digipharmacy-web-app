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

interface Prescription {
    id: number
    patientName: string
    doctor: string
    date: string
    medicines: Array<{
        name: string
        dosage: string
        quantity: number
    }>
}

export default function Prescriptions() {
    const [prescriptions, setPrescriptions] = useState<Prescription[]>([
        {
            id: 1,
            patientName: "John Doe",
            doctor: "Dr. Smith",
            date: "2024-03-20",
            medicines: [
                {
                    name: "Paracetamol",
                    dosage: "500mg",
                    quantity: 10
                },
                {
                    name: "Amoxicillin",
                    dosage: "250mg",
                    quantity: 15
                }
            ]
        }
    ])

    return (
        <div className="container mx-auto p-6">
            <div className="flex justify-between items-center mb-6">
                <h2 className="text-3xl font-bold bg-gradient-to-r from-purple-600 to-pink-600 bg-clip-text text-transparent">
                    Resep Obat
                </h2>
                <Button>Tambah Resep Baru</Button>
            </div>

            <div className="grid gap-6 md:grid-cols-2">
                {/* Panel Pencarian */}
                <Card>
                    <CardHeader>
                        <CardTitle>Cari Resep</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div className="flex gap-4">
                            <Input 
                                placeholder="Cari nama pasien..." 
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
                            />
                            <Button variant="secondary">
                                Terapkan Filter
                            </Button>
                        </div>
                    </CardContent>
                </Card>
            </div>

            {/* Tabel Resep */}
            <Card className="mt-6">
                <CardHeader>
                    <CardTitle>Daftar Resep</CardTitle>
                </CardHeader>
                <CardContent>
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>ID</TableHead>
                                <TableHead>Nama Pasien</TableHead>
                                <TableHead>Dokter</TableHead>
                                <TableHead>Tanggal</TableHead>
                                <TableHead>Aksi</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            {prescriptions.map((prescription) => (
                                <TableRow key={prescription.id}>
                                    <TableCell>{prescription.id}</TableCell>
                                    <TableCell>{prescription.patientName}</TableCell>
                                    <TableCell>{prescription.doctor}</TableCell>
                                    <TableCell>{prescription.date}</TableCell>
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