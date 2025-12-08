<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        if (Category::count() < 3) {
            Category::create(['name' => 'Dedicated Desks']);
            Category::create(['name' => 'Closed Offices']);
            Category::create(['name' => 'Virtual Offices']);
            Category::create(['name' => 'Boardrooms']);
            Category::create(['name' => 'Hot Desks']);


        }
    }
}
