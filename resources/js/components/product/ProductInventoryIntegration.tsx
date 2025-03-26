import React, { useState } from "react"
import { Tabs, TabsContent, TabsList, TabsTrigger } from "@/components/ui/tabs"

import Product from "./Product"
import Inventory from "../inventory/Inventory"

export default function ProductInventoryIntegration() {
    const [activeTab, setActiveTab] = useState("products")

    return (
        <div className="container mx-auto p-4">
            <div className="flex justify-between items-center mb-6">
                <h1 className="text-4xl font-bold bg-gradient-to-r from-purple-600 to-pink-600 bg-clip-text text-transparent">
                    Manajemen Produk & Stok
                </h1>
            </div>

            <Tabs defaultValue="products" value={activeTab} onValueChange={setActiveTab} className="w-full">
                <TabsList className="grid w-full grid-cols-2 mb-8">
                    <TabsTrigger value="products">Produk</TabsTrigger>
                    <TabsTrigger value="inventory">Stok & Inventaris</TabsTrigger>
                </TabsList>
                <TabsContent value="products">
                    <Product />
                </TabsContent>
                <TabsContent value="inventory">
                    <Inventory />
                </TabsContent>
            </Tabs>
        </div>
    )
}
