import { api } from '@/lib/auth';

export interface InventoryItem {
    id: number;
    name: string;
    category_id: number;
    description: string | null;
    price: number;
    stock: number;
    unit: string;
    expiry_date: string | null;
    requires_prescription: boolean;
    created_at: string | null;
    updated_at: string | null;
}

export interface InventoryResponse {
    success: boolean;
    data: InventoryItem[];
    message?: string;
}

export interface InventoryCreateRequest {
    name: string;
    category: string;
    stock: number;
    unit: string;
    supplier: string;
    expiryDate: string;
    price: number;
}

export interface InventorySearchParams {
    query?: string;
    category?: string;
    supplier?: string;
    expiryDateStart?: string;
    expiryDateEnd?: string;
    stockBelow?: number;
}

export const inventoryService = {
    // Get all inventory items
    async getInventory(params?: InventorySearchParams) {
        const response = await api.get<InventoryResponse>('/inventory', { params });
        return response.data.data;
    },

    // Get a single inventory item
    async getInventoryItem(id: number) {
        const response = await api.get<InventoryItem>(`/inventory/${id}`);
        return response.data;
    },

    // Create a new inventory item
    async createInventoryItem(data: InventoryCreateRequest) {
        const response = await api.post<InventoryItem>('/inventory', data);
        return response.data;
    },

    // Update an inventory item (will be converted to POST with _method: 'PUT')
    async updateInventoryItem(id: number, data: Partial<InventoryCreateRequest>) {
        const response = await api.put<InventoryItem>(`/inventory/${id}`, data);
        return response.data;
    },

    // Delete an inventory item (will be converted to POST with _method: 'DELETE')
    async deleteInventoryItem(id: number) {
        const response = await api.delete(`/inventory/${id}`);
        return response.data;
    },

    // Update stock level (will be converted to POST with _method: 'PATCH')
    async updateStock(id: number, quantity: number) {
        const response = await api.patch<InventoryItem>(`/inventory/${id}/stock`, {
            quantity
        });
        return response.data;
    },

    // Search inventory
    async searchInventory(query: string) {
        const response = await api.get<InventoryItem[]>('/inventory/search', {
            params: { query }
        });
        return response.data;
    },

    // Get low stock items
    async getLowStockItems(threshold: number = 10) {
        const response = await api.get<InventoryItem[]>('/inventory/low-stock', {
            params: { threshold }
        });
        return response.data;
    },

    // Get expiring items
    async getExpiringItems(daysThreshold: number = 90) {
        const response = await api.get<InventoryItem[]>('/inventory/expiring', {
            params: { days: daysThreshold }
        });
        return response.data;
    }
};
