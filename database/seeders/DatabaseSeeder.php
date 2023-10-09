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
            'name' => 'Tấn',
            'email' => 'tan@venuscorp.vn',
            'phone' => '012345678',
            'password' => bcrypt('12345678'),
        ]);

        \App\Models\Customer::factory(100)->create();

        $building = \App\Models\Building::create([
            'name' => 'Pertroland Tower',
            'floor' => '30',
            'apartment' => '61',
            'active' => true,
        ]);

        $apartment = \App\Models\Apartment::create([
            'building_id' => $building->id,
            'name' => 'Căn hộ 1',
            'code'  => 'CH1',
            'active' => true,
        ]);

        $utilityType = \App\Models\UtilityType::create([
            'name' => 'Phòng sinh hoạt chung',
        ]);

        $utility = \App\Models\Utility::create([
            'building_id' => $building->id,
            'utility_type_id' => $utilityType->id,
            'name' => 'Phòng họp',
            'start_time'  => '07:00:00',
            'end_time'  => '19:00:00',
            'block'  => '60',
            'active' => true,
        ]);
    }
}
