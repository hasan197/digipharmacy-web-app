import React from 'react';
import { 
    ChartBarIcon, 
    CurrencyDollarIcon, 
    ShoppingCartIcon, 
    UsersIcon 
} from '@heroicons/react/24/outline';
import DefaultLayout from '../layouts/DefaultLayout';
import ReactApexChart from 'react-apexcharts';

export default function Dashboard() {
    // Data untuk grafik
    const chartOptions = {
        chart: {
            type: 'area',
            toolbar: {
                show: false
            }
        },
        dataLabels: {
            enabled: false
        },
        stroke: {
            curve: 'smooth',
            width: 2
        },
        xaxis: {
            categories: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
            axisBorder: {
                show: false
            }
        },
        yaxis: {
            labels: {
                formatter: function (value) {
                    return 'Rp ' + value.toLocaleString();
                }
            }
        },
        colors: ['#6366f1', '#a855f7'],
        fill: {
            type: 'gradient',
            gradient: {
                shadeIntensity: 1,
                opacityFrom: 0.4,
                opacityTo: 0.1,
                stops: [0, 100]
            }
        },
        grid: {
            borderColor: '#f1f1f1',
        },
        tooltip: {
            y: {
                formatter: function(value) {
                    return 'Rp ' + value.toLocaleString()
                }
            }
        }
    };

    const chartSeries = [
        {
            name: 'Penjualan',
            data: [30000, 40000, 35000, 50000, 49000, 60000, 70000, 91000, 85000, 89000, 95000, 100000]
        }
    ];

    return (
        <div className="p-6">
            {/* Statistik Cards */}
            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
                <StatCard
                    title="Total Penjualan"
                    value="Rp 12.500.000"
                    icon={<CurrencyDollarIcon className="w-6 h-6" />}
                    trend="+12.5%"
                    trendUp={true}
                />
                <StatCard
                    title="Transaksi"
                    value="156"
                    icon={<ShoppingCartIcon className="w-6 h-6" />}
                    trend="+8.2%"
                    trendUp={true}
                />
                <StatCard
                    title="Total Pelanggan"
                    value="1,245"
                    icon={<UsersIcon className="w-6 h-6" />}
                    trend="+2.3%"
                    trendUp={true}
                />
                <StatCard
                    title="Rata-rata Penjualan"
                    value="Rp 80.128"
                    icon={<ChartBarIcon className="w-6 h-6" />}
                    trend="-4.1%"
                    trendUp={false}
                />
            </div>

            <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
                {/* Grafik Penjualan */}
                <div className="lg:col-span-2 bg-white p-6 rounded-xl shadow-sm">
                    <h2 className="text-lg font-semibold mb-4">Grafik Penjualan</h2>
                    <div className="h-80">
                        <ReactApexChart
                            options={chartOptions}
                            series={chartSeries}
                            type="area"
                            height="100%"
                        />
                    </div>
                </div>

                {/* Produk Terlaris */}
                <div className="bg-white p-6 rounded-xl shadow-sm">
                    <h2 className="text-lg font-semibold mb-4">Produk Terlaris</h2>
                    <div className="space-y-4">
                        {topProducts.map((product, index) => (
                            <div key={index} className="flex items-center gap-4">
                                <div className="w-12 h-12 bg-gray-100 rounded-lg flex items-center justify-center">
                                    #{index + 1}
                                </div>
                                <div className="flex-1">
                                    <h3 className="font-medium">{product.name}</h3>
                                    <p className="text-sm text-gray-500">{product.sold} terjual</p>
                                </div>
                                <div className="text-right">
                                    <p className="font-medium">Rp {product.revenue.toLocaleString()}</p>
                                </div>
                            </div>
                        ))}
                    </div>
                </div>
            </div>

            {/* Transaksi Terbaru */}
            <div className="mt-6 bg-white rounded-xl shadow-sm">
                <div className="p-6 border-b">
                    <h2 className="text-lg font-semibold">Transaksi Terbaru</h2>
                </div>
                <div className="overflow-x-auto">
                    <table className="w-full">
                        <thead className="bg-gray-50">
                            <tr>
                                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    ID Transaksi
                                </th>
                                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Pelanggan
                                </th>
                                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Produk
                                </th>
                                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Total
                                </th>
                                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Status
                                </th>
                            </tr>
                        </thead>
                        <tbody className="bg-white divide-y divide-gray-200">
                            {recentTransactions.map((transaction, index) => (
                                <tr key={index}>
                                    <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        #{transaction.id}
                                    </td>
                                    <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {transaction.customer}
                                    </td>
                                    <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {transaction.products}
                                    </td>
                                    <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        Rp {transaction.total.toLocaleString()}
                                    </td>
                                    <td className="px-6 py-4 whitespace-nowrap">
                                        <span className={`px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${
                                            transaction.status === 'Selesai' 
                                                ? 'bg-green-100 text-green-800' 
                                                : 'bg-yellow-100 text-yellow-800'
                                        }`}>
                                            {transaction.status}
                                        </span>
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    );
}

// Komponen StatCard
function StatCard({ title, value, icon, trend, trendUp }) {
    return (
        <div className="bg-white p-6 rounded-xl shadow-sm">
            <div className="flex items-center justify-between mb-4">
                <div className="bg-blue-50 p-2 rounded-lg">
                    {icon}
                </div>
                <span className={`text-sm font-medium ${
                    trendUp ? 'text-green-600' : 'text-red-600'
                }`}>
                    {trend}
                </span>
            </div>
            <h3 className="text-gray-500 text-sm">{title}</h3>
            <p className="text-2xl font-semibold mt-1">{value}</p>
        </div>
    );
}

// Data dummy
const topProducts = [
    { name: 'Paracetamol', sold: 245, revenue: 2450000 },
    { name: 'Amoxicillin', sold: 189, revenue: 1890000 },
    { name: 'Vitamin C', sold: 165, revenue: 1650000 },
    { name: 'Antasida', sold: 142, revenue: 1420000 },
    { name: 'Aspirin', sold: 123, revenue: 1230000 },
];

const recentTransactions = [
    { id: '001', customer: 'John Doe', products: 'Paracetamol, Vitamin C', total: 75000, status: 'Selesai' },
    { id: '002', customer: 'Jane Smith', products: 'Amoxicillin', total: 120000, status: 'Proses' },
    { id: '003', customer: 'Bob Johnson', products: 'Aspirin, Antasida', total: 95000, status: 'Selesai' },
    { id: '004', customer: 'Alice Brown', products: 'Vitamin C', total: 50000, status: 'Selesai' },
    { id: '005', customer: 'Charlie Wilson', products: 'Paracetamol', total: 25000, status: 'Proses' },
]; 