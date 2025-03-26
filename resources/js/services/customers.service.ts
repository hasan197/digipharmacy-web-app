import { api } from '@/lib/auth';

export interface Customer {
    id: number;
    name: string;
    email: string;
    phone: string;
    address: string;
    joinDate: string;
}

export interface CustomerCreateRequest {
    name: string;
    email: string;
    phone: string;
    address: string;
}

export interface CustomerSearchParams {
    query?: string;
    joinDateStart?: string;
    joinDateEnd?: string;
}

export const customersService = {
    // Get all customers
    async getCustomers(params?: CustomerSearchParams) {
        const response = await api.get<Customer[]>('/customers', { params });
        return response.data;
    },

    // Get a single customer
    async getCustomer(id: number) {
        const response = await api.get<Customer>(`/customers/${id}`);
        return response.data;
    },

    // Create a new customer
    async createCustomer(data: CustomerCreateRequest) {
        const response = await api.post<Customer>('/customers', data);
        return response.data;
    },

    // Update a customer (will be converted to POST with _method: 'PUT')
    async updateCustomer(id: number, data: Partial<CustomerCreateRequest>) {
        const response = await api.put<Customer>(`/customers/${id}`, data);
        return response.data;
    },

    // Delete a customer (will be converted to POST with _method: 'DELETE')
    async deleteCustomer(id: number) {
        const response = await api.delete(`/customers/${id}`);
        return response.data;
    },

    // Search customers
    async searchCustomers(query: string) {
        const response = await api.get<Customer[]>('/customers/search', {
            params: { query }
        });
        return response.data;
    }
};
