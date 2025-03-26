<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductLargeSeeder extends Seeder
{
    public function run()
    {
        // Ensure we have categories
        $categories = Category::all();
        if ($categories->isEmpty()) {
            $this->command->info('No categories found. Please run CategorySeeder first.');
            return;
        }

        $categoryIds = $categories->pluck('id')->toArray();
        $units = ['tablet', 'strip', 'bottle', 'box', 'ampule', 'vial', 'tube', 'sachet', 'capsule'];
        $statusOptions = ['active', 'inactive', 'discontinued'];
        
        // Common medication names and formats
        $medicationNames = [
            'Amoxicillin', 'Ciprofloxacin', 'Paracetamol', 'Ibuprofen', 'Omeprazole', 
            'Cetirizine', 'Loratadine', 'Metformin', 'Atorvastatin', 'Simvastatin',
            'Amlodipine', 'Lisinopril', 'Losartan', 'Metoprolol', 'Aspirin',
            'Diclofenac', 'Mefenamic Acid', 'Cefixime', 'Azithromycin', 'Doxycycline',
            'Vitamin C', 'Vitamin D3', 'Vitamin B Complex', 'Calcium', 'Iron',
            'Zinc', 'Folic Acid', 'Biotin', 'Magnesium', 'Potassium',
            'Ranitidine', 'Famotidine', 'Lansoprazole', 'Pantoprazole', 'Antacid',
            'Loperamide', 'Domperidone', 'Ondansetron', 'Metoclopramide', 'Bisacodyl',
            'Lactulose', 'Glycerin', 'Salbutamol', 'Montelukast', 'Fluticasone',
            'Budesonide', 'Ipratropium', 'Theophylline', 'Bromhexine', 'Ambroxol'
        ];
        
        $dosages = ['100mg', '250mg', '500mg', '1000mg', '5mg', '10mg', '20mg', '25mg', '50mg', '100mg', '200mg', '400mg', '600mg', '800mg', '1g', '2g'];
        $formulations = ['Tablet', 'Capsule', 'Syrup', 'Suspension', 'Injection', 'Cream', 'Ointment', 'Gel', 'Drops', 'Inhaler', 'Spray', 'Lotion', 'Solution'];
        
        $products = [];
        
        // Generate 100 products
        for ($i = 1; $i <= 100; $i++) {
            $medicationName = $medicationNames[array_rand($medicationNames)];
            $dosage = $dosages[array_rand($dosages)];
            $formulation = $formulations[array_rand($formulations)];
            $name = "$medicationName $dosage $formulation";
            
            // Ensure unique names by adding a suffix if needed
            $existingNames = array_column($products, 'name');
            if (in_array($name, $existingNames)) {
                $name .= ' ' . Str::random(3);
            }
            
            $categoryId = $categoryIds[array_rand($categoryIds)];
            $price = rand(5000, 500000);
            $stock = rand(10, 500);
            $unit = $units[array_rand($units)];
            $status = $statusOptions[array_rand($statusOptions)];
            
            // Generate a random expiry date between 6 months and 3 years from now
            $expiryMonths = rand(6, 36);
            $expiryDate = now()->addMonths($expiryMonths)->format('Y-m-d');
            
            // Determine if it requires prescription (more likely for certain categories)
            $requiresPrescription = (rand(1, 10) > 7) ? true : false;
            
            // Generate SKU and barcode
            $sku = 'SKU-' . strtoupper(Str::random(6));
            $barcode = rand(1000000000000, 9999999999999);
            
            // Generate cost price (always less than selling price)
            $costPrice = round($price * (rand(50, 80) / 100));
            
            $product = [
                'name' => $name,
                'category_id' => $categoryId,
                'description' => "A pharmaceutical product used for " . strtolower(substr($medicationName, 0, 1)) . substr($medicationName, 1),
                'price' => $price,
                'stock' => $stock,
                'unit' => $unit,
                'expiry_date' => $expiryDate,
                'requires_prescription' => $requiresPrescription,
                'status' => $status,
                'sku' => $sku,
                'barcode' => (string)$barcode,
                'cost_price' => $costPrice,
            ];
            
            $products[] = $product;
        }
        
        // Insert all products
        foreach ($products as $product) {
            Product::create($product);
        }
        
        $this->command->info('100 products seeded successfully.');
    }
}
