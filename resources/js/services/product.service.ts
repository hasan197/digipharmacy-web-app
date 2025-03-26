import { api } from '@/lib/auth';

export interface Product {
    id: number;
    name: string;
    category_id: number;
    description: string | null;
    price: number;
    stock: number;
    unit: string;
    expiry_date: string | null;
    requires_prescription: boolean;
    status: string;
    sku: string | null;
    barcode: string | null;
    cost_price: number | null;
    created_at: string | null;
    updated_at: string | null;
}

export interface ProductResponse {
    success: boolean;
    data: Product[];
    message?: string;
}

export interface ProductCreateRequest {
    name: string;
    category_id: number;
    description?: string;
    price: number;
    stock: number;
    unit: string;
    expiry_date?: string;
    requires_prescription: boolean;
    status?: string;
    sku?: string;
    barcode?: string;
    cost_price?: number;
}

export interface ProductSearchParams {
    query?: string;
    category_id?: number;
    status?: string;
    stock_below?: number;
}

export const productService = {
    // Get all products
    async getProducts(params?: ProductSearchParams) {
        try {
            const response = await api.get<ProductResponse>('/products', { params });
            if (response.data && response.data.data) {
                return response.data.data;
            } else {
                console.error('Invalid API response format:', response.data);
                return [];
            }
        } catch (error) {
            console.error('Error fetching products:', error);
            return [];
        }
    },

    // Get a single product
    async getProduct(id: number) {
        const response = await api.get<Product>(`/products/${id}`);
        return response.data;
    },

    // Create a new product
    async createProduct(data: ProductCreateRequest) {
        const response = await api.post<Product>('/products', data);
        return response.data;
    },

    // Update a product
    async updateProduct(id: number, data: Partial<ProductCreateRequest>) {
        const response = await api.put<Product>(`/products/${id}`, data);
        return response.data;
    },

    // Delete a product
    async deleteProduct(id: number) {
        const response = await api.delete(`/products/${id}`);
        return response.data;
    },

    // Update product status
    async updateProductStatus(id: number, status: string) {
        const response = await api.patch<Product>(`/products/${id}/status`, {
            status
        });
        return response.data;
    },

    // Get products by category
    async getProductsByCategory(categoryId: number) {
        const response = await api.get<Product[]>(`/products/category/${categoryId}`);
        return response.data;
    },

    // Get low stock products
    async getLowStockProducts(threshold: number = 10) {
        const response = await api.get<Product[]>('/products/low-stock', {
            params: { threshold }
        });
        return response.data;
    }
};
