<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            PermissionSeeder::class,
        ]);
        // \App\Models\User::factory(10)->create();

        $admin = User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@venuscorp.vn',
            'password' => bcrypt('12345678'),
        ]);

        $admin->assignRole(Str::slug(env('IMPLICITLY_GRANT')));


        $customer = \App\Models\Customer::create([
            'ho_va_ten' => 'Tấn',
            'email' => 'tan@venuscorp.vn',
            'so_dien_thoai' => '012345678',
            'password' => bcrypt('12345678'),
        ]);

        \App\Models\Customer::factory(100)->create();
    }
}
