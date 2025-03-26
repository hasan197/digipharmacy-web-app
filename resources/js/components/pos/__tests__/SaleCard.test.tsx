import React from 'react';
import '@testing-library/jest-dom';
import { render, screen, fireEvent, waitFor } from '@testing-library/react';
import axios from 'axios';
import SaleCard from '../SaleCard';

// Mock axios
jest.mock('axios');
const mockedAxios = axios as jest.Mocked<typeof axios>;

describe('SaleCard', () => {
    const mockSale = {
        id: 1,
        type: 'cash' as const,
        customer: 'John Doe',
        status: 'completed' as const,
        time: '2025-02-09T07:00:00.000Z',
        itemCount: 2,
        amount: 50000
    };

    beforeEach(() => {
        jest.clearAllMocks();
    });

    it('renders sale information correctly', () => {
        render(<SaleCard {...mockSale} />);

        expect(screen.getByText('#1')).toBeInTheDocument();
        expect(screen.getByText('John Doe')).toBeInTheDocument();
        expect(screen.getByText('completed')).toBeInTheDocument();
        expect(screen.getByText('2 items')).toBeInTheDocument();
        expect(screen.getByText('Rp 50.000')).toBeInTheDocument();
    });

    it('shows sale details modal when View Items is clicked', async () => {
        const mockSaleDetails = {
            id: 1,
            customer: {
                name: 'John Doe',
                phone: '1234567890',
                email: 'john@example.com',
                address: '123 Street'
            },
            items: [
                {
                    id: 1,
                    product: {
                        name: 'Product 1',
                        unit: 'pcs'
                    },
                    quantity: 1,
                    price: 25000,
                    subtotal: 25000
                }
            ],
            total: 50000,
            discount: 0,
            additional_fee: 0,
            grand_total: 50000,
            payment_method: 'cash',
            status: 'completed',
            notes: ''
        };

        mockedAxios.get.mockResolvedValueOnce({ data: mockSaleDetails });

        render(<SaleCard {...mockSale} />);

        const viewButton = screen.getByText(/View Items/i);
        fireEvent.click(viewButton);

        await waitFor(() => {
            expect(mockedAxios.get).toHaveBeenCalledWith('/api/sales/1');
            expect(screen.getByText('Sale Details')).toBeInTheDocument();
            expect(screen.getByText('Product 1')).toBeInTheDocument();
            expect(screen.getAllByText('Rp 25.000')).toHaveLength(2); // Price and subtotal
        });
    });

    it('shows error message when API call fails', async () => {
        // Mock console.error to prevent it from cluttering test output
        const mockConsoleError = jest.spyOn(console, 'error').mockImplementation(() => {});
        const mockError = new Error('API Error');
        mockedAxios.get.mockRejectedValueOnce(mockError);

        const mockAlert = jest.spyOn(window, 'alert').mockImplementation(() => {});

        render(<SaleCard {...mockSale} />);

        const viewButton = screen.getByText(/View Items/i);
        fireEvent.click(viewButton);

        await waitFor(() => {
            expect(mockedAxios.get).toHaveBeenCalledWith('/api/sales/1');
            expect(mockAlert).toHaveBeenCalledWith('Failed to fetch sale details');
            expect(mockConsoleError).toHaveBeenCalledWith('Error fetching sale details:', mockError);
        });

        mockAlert.mockRestore();
        mockConsoleError.mockRestore();
    });
});
