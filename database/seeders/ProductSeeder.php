<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Product::create([
            'name' => 'Design',
            'type' => 'service',
            'qty' => 4.00,
            'unit_price' => 2000,
            'total_price' => 8000,
        ]);
        Product::create([
            'name' => 'Development',
            'type' => 'service',
            'qty' => 5.00,
            'unit_price' => 6000,
            'total_price' => 30000,
        ]);
        Product::create([
            'name' => 'Subs Add',
            'type' => 'subscription',
            'qty' => 5.00,
            'unit_price' => 6000,
            'total_price' => 30000,
        ]);
        Product::create([
            'name' => 'Subs On',
            'type' => 'subscription',
            'qty' => 5.00,
            'unit_price' => 6000,
            'total_price' => 30000,
        ]);
        Product::create([
            'name' => 'Printer',
            'type' => 'hardware',
            'qty' => 4.00,
            'unit_price' => 2000,
            'total_price' => 8000,
        ]);
        Product::create([
            'name' => 'Monitor',
            'type' => 'hardware',
            'qty' => 4.00,
            'unit_price' => 2000,
            'total_price' => 8000,
        ]);
    }
}
