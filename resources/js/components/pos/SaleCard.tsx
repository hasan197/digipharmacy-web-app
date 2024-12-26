interface SaleCardProps {
    saleNumber: string;
    type: string;
    customer: string;
    status: 'completed' | 'pending' | 'cancelled';
    time: string;
    itemCount: number;
    onViewItems: () => void;
    onPrintReceipt: () => void;
}

export default function SaleCard({
    saleNumber,
    type,
    customer,
    status,
    time,
    itemCount,
    onViewItems,
    onPrintReceipt
}: SaleCardProps) {
    const getStatusColor = () => {
        switch (status) {
            case 'completed':
                return 'bg-green-100 text-green-800';
            case 'pending':
                return 'bg-yellow-100 text-yellow-800';
            case 'cancelled':
                return 'bg-red-100 text-red-800';
            default:
                return 'bg-gray-100 text-gray-800';
        }
    };

    return (
        <div className="bg-white rounded-2xl shadow-lg shadow-gray-200/50 border border-gray-100 p-6 hover:shadow-xl transition-all">
            <div className="flex justify-between items-start mb-4">
                <div>
                    <span className="text-sm text-gray-500">Sale #{saleNumber}</span>
                    <h3 className="text-lg font-semibold text-gray-900 mt-1">{customer}</h3>
                </div>
                <span className={`px-3 py-1 rounded-full text-xs font-medium capitalize ${getStatusColor()}`}>
                    {status}
                </span>
            </div>

            <div className="flex items-center gap-4 mb-6">
                <div className="flex items-center gap-2">
                    <svg className="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                    </svg>
                    <span className="text-sm text-gray-600">{type}</span>
                </div>
                <div className="flex items-center gap-2">
                    <svg className="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                    <span className="text-sm text-gray-600">{itemCount} items</span>
                </div>
                <div className="flex items-center gap-2">
                    <svg className="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span className="text-sm text-gray-600">{time}</span>
                </div>
            </div>

            <div className="flex gap-4">
                <button
                    onClick={onViewItems}
                    className="flex-1 px-4 py-2 bg-blue-50 text-blue-600 rounded-xl hover:bg-blue-100 transition-colors"
                >
                    View Items
                </button>
                <button
                    onClick={onPrintReceipt}
                    className="flex-1 px-4 py-2 bg-purple-50 text-purple-600 rounded-xl hover:bg-purple-100 transition-colors"
                >
                    Print Receipt
                </button>
            </div>
        </div>
    );
}
