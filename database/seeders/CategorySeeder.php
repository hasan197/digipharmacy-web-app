<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run()
    {
        $categories = [
            ['name' => 'Antibiotics'],
            ['name' => 'Analgesics'],
            ['name' => 'Antacids'],
            ['name' => 'Antihistamines'],
            ['name' => 'Vitamins'],
            ['name' => 'Supplements'],
            ['name' => 'First Aid'],
            ['name' => 'Personal Care'],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}
