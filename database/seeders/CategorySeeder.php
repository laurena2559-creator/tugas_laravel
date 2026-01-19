<?php

namespace Database\Seeders;

use App\Models\Category; // Import Category
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Category::insert([
            ['name' => 'Mouse'],
            ['name' => 'Keyboard'],
            ['name' => 'Kabel Jaringan'],
            ['name' => 'Monitor'],
            ['name' => 'Proyektor'],
        ]);
    }
}
