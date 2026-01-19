<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([

            'name' => 'Admin',

            'email' => 'admin@smkn2-mjk.sch.id',

            'password' => Hash::make('admin'), // Enkripsi password

            'role' => 'Admin',

        ]);



        User::create([

            'name' => 'Siswa Contoh',

            'email' => 'siswa@smkn2-mjk.sch.id',

            'password' => Hash::make('siswa'),

            'role' => 'Siswa',

        ]);

    }
}
