<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // \App\Models\User::factory(10)->create();

        \App\Models\User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@venuscorp.vn',
            'password' => bcrypt('12345678'),
        ]);


        $customer = \App\Models\Customer::create([
            'ho_va_ten' => 'Tấn',
            'email' => 'tan@venuscorp.vn',
            'so_dien_thoai' => '012345678',
            'password' => bcrypt('12345678'),
        ]);

        \App\Models\Customer::factory(100)->create();
    }
}
