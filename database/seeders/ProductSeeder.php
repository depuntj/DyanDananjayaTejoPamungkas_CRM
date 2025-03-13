<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            [
                'name' => 'Residential Basic',
                'description' => 'Basic internet package for residential customers',
                'price' => 299000,
                'speed' => '20 Mbps',
                'type' => 'Residential',
            ],
            [
                'name' => 'Residential Plus',
                'description' => 'Medium-tier internet package for residential customers',
                'price' => 499000,
                'speed' => '50 Mbps',
                'type' => 'Residential',
            ],
            [
                'name' => 'Residential Premium',
                'description' => 'High-end internet package for residential customers',
                'price' => 799000,
                'speed' => '100 Mbps',
                'type' => 'Residential',
            ],
            [
                'name' => 'Business Basic',
                'description' => 'Basic internet package for small businesses',
                'price' => 999000,
                'speed' => '50 Mbps',
                'type' => 'Business',
            ],
            [
                'name' => 'Business Plus',
                'description' => 'Medium-tier internet package for businesses',
                'price' => 1499000,
                'speed' => '100 Mbps',
                'type' => 'Business',
            ],
            [
                'name' => 'Business Premium',
                'description' => 'High-end internet package for businesses with dedicated support',
                'price' => 2999000,
                'speed' => '500 Mbps',
                'type' => 'Business',
            ],
            [
                'name' => 'Enterprise',
                'description' => 'Enterprise-grade internet solution with SLA guarantees',
                'price' => 9999000,
                'speed' => '1 Gbps',
                'type' => 'Enterprise',
            ],
        ];

        foreach ($products as $product) {
            Product::create($product);
        }
    }
}
