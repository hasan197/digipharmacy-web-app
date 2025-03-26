import React from 'react';
import '@testing-library/jest-dom';
import { render, screen, fireEvent } from '@testing-library/react';
import SaleDetailsModal from '../SaleDetailsModal';

describe('SaleDetailsModal', () => {
    const mockSale = {
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
                quantity: 2,
                price: 25000,
                subtotal: 50000
            }
        ],
        total: 50000,
        discount: 5000,
        additional_fee: 2000,
        grand_total: 47000,
        payment_method: 'cash',
        status: 'completed',
        notes: 'Test notes'
    };

    it('does not render when isOpen is false', () => {
        render(
            <SaleDetailsModal
                isOpen={false}
                onClose={() => {}}
                sale={mockSale}
            />
        );

        expect(screen.queryByText('Sale Details')).not.toBeInTheDocument();
    });

    it('renders all sale details correctly when open', () => {
        render(
            <SaleDetailsModal
                isOpen={true}
                onClose={() => {}}
                sale={mockSale}
            />
        );

        // Check header
        expect(screen.getByText('Sale Details')).toBeInTheDocument();

        // Check customer info
        expect(screen.getByText('Customer Information')).toBeInTheDocument();
        expect(screen.getByText('Name: John Doe')).toBeInTheDocument();
        expect(screen.getByText('Phone: 1234567890')).toBeInTheDocument();
        expect(screen.getByText('Email: john@example.com')).toBeInTheDocument();
        expect(screen.getByText('Address: 123 Street')).toBeInTheDocument();

        // Check items
        expect(screen.getByText('Product 1')).toBeInTheDocument();
        expect(screen.getByText('pcs')).toBeInTheDocument();
        expect(screen.getByText('2')).toBeInTheDocument();
        expect(screen.getByText('Rp 25.000')).toBeInTheDocument();
        expect(screen.getAllByText('Rp 50.000')).toHaveLength(2); // One in table, one in summary

        // Check summary
        expect(screen.getByText('Subtotal:')).toBeInTheDocument();
        expect(screen.getByText('Discount:')).toBeInTheDocument();
        expect(screen.getByText('Additional Fee:')).toBeInTheDocument();
        expect(screen.getByText('Grand Total:')).toBeInTheDocument();

        // Check payment info
        expect(screen.getByText('Payment Method:')).toBeInTheDocument();
        expect(screen.getByText('cash')).toBeInTheDocument();
        expect(screen.getByText('Status:')).toBeInTheDocument();
        expect(screen.getByText('completed')).toBeInTheDocument();
        expect(screen.getByText('Notes:')).toBeInTheDocument();
        expect(screen.getByText('Test notes')).toBeInTheDocument();
    });

    it('calls onClose when close button is clicked', () => {
        const mockOnClose = jest.fn();
        render(
            <SaleDetailsModal
                isOpen={true}
                onClose={mockOnClose}
                sale={mockSale}
            />
        );

        const closeButton = screen.getByRole('button', { name: /close/i });
        fireEvent.click(closeButton);

        expect(mockOnClose).toHaveBeenCalled();
    });

    it('handles missing optional data gracefully', () => {
        const saleWithoutOptionals = {
            ...mockSale,
            discount: 0,
            additional_fee: 0,
            notes: '',
            customer: null
        };

        render(
            <SaleDetailsModal
                isOpen={true}
                onClose={() => {}}
                sale={saleWithoutOptionals}
            />
        );

        // These should not be in the document
        expect(screen.queryByText('Customer Information')).not.toBeInTheDocument();
        expect(screen.queryByText('Discount:')).not.toBeInTheDocument();
        expect(screen.queryByText('Additional Fee:')).not.toBeInTheDocument();
        expect(screen.queryByText('Notes:')).not.toBeInTheDocument();

        // These should still be in the document
        expect(screen.getByText('Sale Details')).toBeInTheDocument();
        expect(screen.getByText('Product 1')).toBeInTheDocument();
    });
});
