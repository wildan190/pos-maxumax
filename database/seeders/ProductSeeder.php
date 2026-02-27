<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Optional: truncate table if desired, uncomment if needed.
        // \App\Models\Product::truncate();

        $categories = [
            'Pro Jersey' => ['1/2 yrs', '3/4 yrs', '5/6 yrs', '7/8 yrs', '9/11 yrs', 'XS', 'S', 'M', 'L', 'XL', '2XL', '3XL'],
            'FABD Jersey' => ['XS', 'S', 'M', 'L', 'XL', '2XL', '3XL', '4XL', '5XL', '6XL', '7XL', '8XL'],
            'Outdoor Shirt' => ['S', 'M', 'L', 'XL', '2XL', '3XL'],
            'Accessories' => []
        ];

        foreach ($categories as $category => $sizes) {
            for ($i = 1; $i <= 7; $i++) {

                $stock = [];
                // If category has sizes, assign random stock for each
                if (count($sizes) > 0) {
                    // Pick a random subset of sizes to have stock
                    $availableSizes = fake()->randomElements($sizes, rand(2, count($sizes)));
                    foreach ($availableSizes as $size) {
                        $stock[] = [
                            'size_key' => $size,
                            'size_value' => fake()->numberBetween(0, 50)
                        ];
                    }
                } else {
                    // Accessories might just have single numerical entry like 'One Size' or no sizes
                    $stock[] = [
                        'size_key' => 'One Size',
                        'size_value' => fake()->numberBetween(0, 50)
                    ];
                }

                \App\Models\Product::create([
                    'item_code' => 'DUMMY-' . strtoupper(\Illuminate\Support\Str::random(6)),
                    'name' => fake()->words(3, true) . ' ' . $category,
                    'price' => fake()->randomFloat(2, 10, 150),
                    'type' => 'standard',
                    'category' => $category,
                    'stock' => $stock
                ]);
            }
        }
    }
}
