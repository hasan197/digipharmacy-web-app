import { api } from '@/lib/auth';

export interface Product {
    id: number;
    name: string;
    price: number;
    stock: number;
    unit: string;
    requires_prescription: boolean;
    category: {
        id: number;
        name: string;
    };
}

export interface CartItem {
    id: string;
    name: string;
    price: number;
    stock: number;
    quantity: number;
    requires_prescription?: boolean;
}

export interface SalesOrderRequest {
    customer_id?: number;
    customer_name?: string;
    customer_phone?: string;
    items: {
        product_id: number;
        quantity: number;
    }[];
    payment_method: string;
    notes?: string;
    total_amount: number;
}

export interface SaleResponse {
    id: number;
    sale_number: string;
    total_amount: number;
    payment_method: string;
    status: string;
    created_at: string;
    items: {
        product_id: number;
        quantity: number;
        price: number;
        subtotal: number;
    }[];
}

export interface SalesSummary {
    total_sales: number;
    total_revenue: number;
    average_transaction: number;
    sales: SaleResponse[];
    top_selling_items: {
        product_id: number;
        name: string;
        quantity: number;
        revenue: number;
    }[];
}

export const posService = {
    // Get all products
    async getProducts() {
        const response = await api.get<{data: Product[]}>('/products');
        return response.data.data;
    },

    // Search products
    async searchProducts(query: string) {
        const response = await api.get<{success: boolean, data: Product[]}>('/products/search', {
            params: { query }
        });
        return response.data.data; // Mengembalikan array produk dari response.data.data
    },

    // Create a new sale
    async createSale(saleData: SalesOrderRequest) {
        const response = await api.post('/sales', saleData);
        return response.data;
    },

    // Get a specific sale by ID
    async getSale(saleId: number) {
        const response = await api.get<SaleResponse>(`/sales/${saleId}`);
        return response.data;
    },

    // Get all sales for today
    async getTodaySales() {
        const response = await api.get<SaleResponse[]>('/sales/today');
        return response.data;
    },

    // Get sales summary for a date range
    async getSalesSummary(startDate: string, endDate: string) {
        const response = await api.get<SalesSummary>('/sales/summary', {
            params: { start_date: startDate, end_date: endDate }
        });
        return response.data;
    },

    // Void a sale
    async voidSale(saleId: number, reason: string) {
        const response = await api.post(`/sales/${saleId}/void`, { reason });
        return response.data;
    },

    // Get low stock alerts
    async getLowStockAlerts() {
        const response = await api.get<Product[]>('/products/low-stock');
        return response.data;
    }
};
