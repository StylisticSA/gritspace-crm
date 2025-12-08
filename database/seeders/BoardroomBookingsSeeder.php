<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\BoardroomBooking;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class BoardroomBookingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        BoardroomBooking::factory()->count(1)->create();

    }
}
