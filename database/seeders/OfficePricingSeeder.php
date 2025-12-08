<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\OfficePricing;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class OfficePricingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $dedicatedDeskCategory = Category::where('name', 'Dedicated Desk')->first();
        $VirtualOfficeCategory = Category::where('name', 'Virtual Office')->first();

        // Insert pricing types for Dedicated Desk
        if ($dedicatedDeskCategory) {
            OfficePricing::create([
                'category_name' => $dedicatedDeskCategory->name,
                'pricing_type' => 'Premium',
                'rate' => 2000.00,
            ]);

            OfficePricing::create([
                'category_name' => $dedicatedDeskCategory->name,
                'pricing_type' => 'Standard',
                'rate' => 1500.00,
            ]);
        }

        // Insert pricing types for Closed Office
        if ($VirtualOfficeCategory) {
            OfficePricing::create([
                'category_name' => $VirtualOfficeCategory->name,
                'pricing_type' => 'Premium',
                'rate' => 1000.00,
            ]);

            OfficePricing::create([
                'category_name' => $VirtualOfficeCategory->name,
                'pricing_type' => 'Standard',
                'rate' => 100.00,
            ]);
        }
    }
}
