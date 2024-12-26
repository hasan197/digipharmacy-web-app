import React, { useEffect, useState, useCallback } from 'react';
import axios from 'axios';
import SaleCard from './SaleCard';
import InventoryCard from './InventoryCard';
import Cart from './Cart';
import debounce from 'lodash/debounce';

interface Medicine {
    id: number;
    name: string;
    price: number;
    stock: number;
    unit: string;
    category: {
        id: number;
        name: string;
    };
}

interface CartItem {
    id: string;
    name: string;
    price: number;
    stock: number;
    quantity?: number;
}

export default function POS() {
    const [selectedTab, setSelectedTab] = useState('all');
    const [cartItems, setCartItems] = useState<CartItem[]>([]);
    const [medicines, setMedicines] = useState<Medicine[]>([]);
    const [searchQuery, setSearchQuery] = useState('');
    const [isLoading, setIsLoading] = useState(true);
    const [inventory, setInventory] = useState([]);
    
    // Tambahkan debounce untuk mencegah terlalu banyak request
    const debouncedSearch = useCallback(
        debounce(async (query: string) => {
            try {
                const response = await axios.get('/api/medicines/search', {
                    params: { query }
                });
                setMedicines(response.data);
            } catch (error) {
                console.error('Error searching medicines:', error);
            } finally {
                setIsLoading(false);
            }
        }, 300),
        []
    );

    // Update useEffect untuk memanggil API search ketika query berubah
    useEffect(() => {
        if (searchQuery.trim() === '') {
            // Jika search kosong, tampilkan semua medicines
            const fetchMedicines = async () => {
                setIsLoading(true);
                try {
                    const response = await axios.get('/api/medicines');
                    setMedicines(response.data);
                } catch (error) {
                    console.error('Error fetching medicines:', error);
                } finally {
                    setIsLoading(false);
                }
            };
            fetchMedicines();
        } else {
            setIsLoading(true);
            debouncedSearch(searchQuery);
        }
    }, [searchQuery]);

    // Handler untuk input search
    const handleSearch = (e: React.ChangeEvent<HTMLInputElement>) => {
        setSearchQuery(e.target.value);
    };

    const handleAddToCart = (item: CartItem) => {
        const existingItem = cartItems.find(cartItem => cartItem.id === item.id);
        
        if (existingItem) {
            handleUpdateQuantity(item.id, (existingItem.quantity || 1) + 1);
        } else {
            setCartItems([...cartItems, { ...item, quantity: 1 }]);
        }
    };

    const handleUpdateQuantity = (itemId: string, newQuantity: number) => {
        setCartItems(cartItems.map(item => 
            item.id === itemId 
                ? { ...item, quantity: newQuantity }
                : item
        ));
    };

    const handleRemoveItem = (itemId: string) => {
        setCartItems(cartItems.filter(item => item.id !== itemId));
    };

    const handleCheckout = () => {
        console.log('Checkout items:', cartItems);
    };

    useEffect(() => {
        const fetchInventory = async () => {
            setIsLoading(true);
            try {
                // Simulasi delay network
                await new Promise(resolve => setTimeout(resolve, 1000));
                
                // Fetch data inventory
                const response = await fetch('/api/inventory');
                const data = await response.json();
                setInventory(data);
            } catch (error) {
                console.error('Error fetching inventory:', error);
            } finally {
                setIsLoading(false);
            }
        };

        fetchInventory();
    }, []);

    return (
        <div className="min-h-screen bg-gradient-to-br from-gray-50 to-white p-4 sm:p-6 lg:p-8">
            <div className="flex flex-col lg:flex-row gap-8">
                {/* Left Section - Sales and Inventory */}
                <div className="flex-1 w-full">
                    {/* Sales Section tetap sama */}
                    {/* ... */}

                    {/* Inventory Section - diupdate untuk menggunakan data medicines */}
                    <div>
                        <div className="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-8 gap-4">
                            <h2 className="text-3xl font-bold bg-gradient-to-r from-purple-600 to-pink-600 bg-clip-text text-transparent">
                                Kasir / POS
                            </h2>
                            <div className="relative w-full sm:w-80">
                                <input
                                    type="text"
                                    value={searchQuery}
                                    onChange={handleSearch}
                                    placeholder="Search inventory..."
                                    className="w-full px-4 py-3 bg-white shadow-lg shadow-gray-200/50 border border-gray-100 rounded-2xl pl-10"
                                />
                                <svg
                                    className="w-5 h-5 text-gray-400 absolute left-3 top-1/2 transform -translate-y-1/2"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        strokeLinecap="round"
                                        strokeLinejoin="round"
                                        strokeWidth="2"
                                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
                                    />
                                </svg>
                            </div>
                        </div>

                        {/* Inventory Cards */}
                        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                            {isLoading ? (
                                // Render multiple skeleton cards
                                [...Array(6)].map((_, index) => (
                                    <InventoryCard key={index} isLoading={true} />
                                ))
                            ) : (
                                medicines.map(medicine => (
                                    <InventoryCard
                                        key={medicine.id}
                                        name={medicine.name}
                                        image="https://picsum.photos/400/300"
                                        category={medicine.category.name}
                                        price={medicine.price}
                                        stock={medicine.stock}
                                        onAddToCart={() => handleAddToCart({
                                            id: medicine.id.toString(),
                                            name: medicine.name,
                                            price: medicine.price,
                                            stock: medicine.stock
                                        })}
                                        isLoading={false}
                                    />
                                ))
                            )}
                        </div>
                    </div>
                </div>

                {/* Right Section - Cart tetap sama */}
                <Cart
                    items={cartItems}
                    onUpdateQuantity={handleUpdateQuantity}
                    onRemoveItem={handleRemoveItem}
                    onCheckout={handleCheckout}
                />
            </div>
        </div>
    );
}
