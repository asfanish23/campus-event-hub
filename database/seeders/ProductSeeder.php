<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = [
            [
                'name' => 'Tech Club T-Shirt',
                'price' => 45.00,
                'stock' => 25,
                'category' => 'Apparel',
                'description' => 'Comfortable and stylish tech club merchandise T-shirt. Available in multiple sizes.',
            ],
            [
                'name' => 'Club Hoodie',
                'price' => 85.00,
                'stock' => 10,
                'category' => 'Apparel',
                'description' => 'Premium quality hoodie with club emblem. Perfect for cold weather events.',
            ],
            [
                'name' => 'Laptop Sticker Pack',
                'price' => 15.00,
                'stock' => 50,
                'category' => 'Accessories',
                'description' => 'Set of 10 vibrant stickers featuring club logos and tech themes.',
            ],
            [
                'name' => 'Water Bottle',
                'price' => 25.00,
                'stock' => 30,
                'category' => 'Accessories',
                'description' => 'Eco-friendly water bottle with club branding. 500ml capacity.',
            ],
        ];

        foreach ($products as $product) {
            Product::create($product);
        }
    }
}
