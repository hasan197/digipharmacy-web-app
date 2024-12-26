const SkeletonCard = () => {
    return (
        <div className="bg-white rounded-2xl shadow-lg shadow-gray-200/50 border border-gray-100 overflow-hidden hover:shadow-xl transition-all w-full animate-pulse">
            <div className="h-48 bg-gray-200"></div>
            
            <div className="p-6">
                <div className="mb-4">
                    <div className="h-6 bg-gray-200 rounded-lg w-3/4 mb-2"></div>
                    <div className="flex items-center gap-2">
                        <div className="w-5 h-5 bg-gray-200 rounded-full"></div>
                        <div className="h-4 bg-gray-200 rounded w-1/3"></div>
                    </div>
                </div>

                <div className="flex items-center justify-between">
                    <div className="h-8 bg-gray-200 rounded w-1/3"></div>
                    <div className="h-10 bg-gray-200 rounded-xl w-1/3"></div>
                </div>
            </div>
        </div>
    );
};

interface InventoryCardProps {
    name: string;
    image: string;
    category: string;
    price: number;
    stock: number;
    onAddToCart: () => void;
    isLoading?: boolean;
}

export default function InventoryCard({ isLoading = false, ...props }: InventoryCardProps) {
    if (isLoading) {
        return <SkeletonCard />;
    }

    return (
        <div className="bg-white rounded-2xl shadow-lg shadow-gray-200/50 border border-gray-100 overflow-hidden hover:shadow-xl transition-all w-full">
            <div className="relative">
                <img
                    src={props.image}
                    alt={props.name}
                    className="w-full h-48 object-cover"
                />
                <span className="absolute top-4 right-4 px-3 py-1 bg-white/90 backdrop-blur-sm text-gray-700 text-xs font-medium rounded-full">
                    {props.category}
                </span>
            </div>
            
            <div className="p-6">
                <div className="mb-4">
                    <h3 className="text-lg font-semibold text-gray-900 mb-1">{props.name}</h3>
                    <div className="flex items-center gap-2">
                        <svg className="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                        </svg>
                        <span className="text-sm text-gray-600">{props.stock} in stock</span>
                    </div>
                </div>

                <div className="flex items-center justify-between">
                    <span className="text-2xl font-bold bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent">
                        Rp {props.price.toLocaleString()}
                    </span>
                    <button
                        onClick={props.onAddToCart}
                        className="px-4 py-2 bg-gradient-to-r from-blue-500 to-purple-500 text-white rounded-xl flex items-center gap-2 text-sm font-medium shadow-lg shadow-blue-500/30 hover:shadow-blue-500/40 transition-all"
                    >
                        Add to Cart
                        <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    );
}
