<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run()
    {
        $products = [
            // Antibiotics
            [
                'name' => 'Amoxicillin 500mg',
                'category_id' => 1,
                'description' => 'Broad-spectrum antibiotic',
                'price' => 50000,
                'stock' => 100,
                'unit' => 'strip',
                'expiry_date' => '2025-12-31'
            ],
            [
                'name' => 'Ciprofloxacin 500mg',
                'category_id' => 1,
                'description' => 'Fluoroquinolone antibiotic',
                'price' => 75000,
                'stock' => 80,
                'unit' => 'strip',
                'expiry_date' => '2025-12-31'
            ],
            // Analgesics
            [
                'name' => 'Paracetamol 500mg',
                'category_id' => 2,
                'description' => 'Pain reliever and fever reducer',
                'price' => 15000,
                'stock' => 200,
                'unit' => 'strip',
                'expiry_date' => '2025-12-31'
            ],
            [
                'name' => 'Ibuprofen 400mg',
                'category_id' => 2,
                'description' => 'NSAID pain reliever',
                'price' => 25000,
                'stock' => 150,
                'unit' => 'strip',
                'expiry_date' => '2025-12-31'
            ],
            // Antacids
            [
                'name' => 'Omeprazole 20mg',
                'category_id' => 3,
                'description' => 'Proton pump inhibitor',
                'price' => 45000,
                'stock' => 120,
                'unit' => 'strip',
                'expiry_date' => '2025-12-31'
            ],
            // Antihistamines
            [
                'name' => 'Cetirizine 10mg',
                'category_id' => 4,
                'description' => 'Allergy relief',
                'price' => 20000,
                'stock' => 180,
                'unit' => 'strip',
                'expiry_date' => '2025-12-31'
            ],
            // Vitamins
            [
                'name' => 'Vitamin C 1000mg',
                'category_id' => 5,
                'description' => 'Immune system support',
                'price' => 35000,
                'stock' => 250,
                'unit' => 'bottle',
                'expiry_date' => '2025-12-31'
            ],
            [
                'name' => 'Vitamin D3 1000IU',
                'category_id' => 5,
                'description' => 'Bone health support',
                'price' => 40000,
                'stock' => 200,
                'unit' => 'bottle',
                'expiry_date' => '2025-12-31'
            ],
            // Supplements
            [
                'name' => 'Calcium + D3',
                'category_id' => 6,
                'description' => 'Bone strength supplement',
                'price' => 55000,
                'stock' => 150,
                'unit' => 'bottle',
                'expiry_date' => '2025-12-31'
            ],
            // First Aid
            [
                'name' => 'Antiseptic Solution',
                'category_id' => 7,
                'description' => 'Wound cleaning solution',
                'price' => 30000,
                'stock' => 100,
                'unit' => 'bottle',
                'expiry_date' => '2025-12-31'
            ],
            // Personal Care
            [
                'name' => 'Hand Sanitizer',
                'category_id' => 8,
                'description' => 'Alcohol-based hand sanitizer',
                'price' => 25000,
                'stock' => 300,
                'unit' => 'bottle',
                'expiry_date' => '2025-12-31'
            ]
        ];

        foreach ($products as $product) {
            Product::create($product);
        }
    }
}
