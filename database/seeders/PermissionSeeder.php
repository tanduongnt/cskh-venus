<?php

namespace Database\Seeders;

use Illuminate\Support\Str;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class PermissionSeeder extends Seeder
{
    public function run()
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        Role::updateOrCreate(['name' => Str::slug(env('IMPLICITLY_GRANT'))], ['display_name' => env('IMPLICITLY_GRANT'), 'description' => 'Được truy cập toàn bộ hệ thống']);

        // create permissions
        Permission::updateOrCreate(['name' => 'user.view'], ['display_name' => 'Xem', 'description' => 'Xem nhân viên']);
        Permission::updateOrCreate(['name' => 'user.create'], ['display_name' => 'Tạo mới', 'description' => 'Thêm mới nhân viên']);
        Permission::updateOrCreate(['name' => 'user.edit'], ['display_name' => 'Chỉnh sửa', 'description' => 'Chỉnh sửa thông tin nhân viên']);
        Permission::updateOrCreate(['name' => 'user.delete'], ['display_name' => 'Xóa', 'description' => 'Xóa nhân viên']);
        Permission::updateOrCreate(['name' => 'user.permission'], ['display_name' => 'Phân quyền', 'description' => 'Phân quyền']);
        // create roles and assign created permissions
        $roleUser = Role::updateOrCreate(['name' => 'user'], ['display_name' => 'Nhân viên', 'description' => 'Quản lý nhân viên']);
        $roleUser->syncPermissions(Permission::where('name', 'LIKE', 'user.%')->pluck('id'));

        Permission::updateOrCreate(['name' => 'customer.view'], ['display_name' => 'Xem', 'description' => 'Xem khách hàng']);
        Permission::updateOrCreate(['name' => 'customer.create'], ['display_name' => 'Tạo mới', 'description' => 'Thêm mới khách hàng']);
        Permission::updateOrCreate(['name' => 'customer.edit'], ['display_name' => 'Chỉnh sửa', 'description' => 'Chỉnh sửa khách hàng']);
        Permission::updateOrCreate(['name' => 'customer.delete'], ['display_name' => 'Xóa', 'description' => 'Xóa khách hàng']);
        // create roles and assign created permissions
        $roleCustomer = Role::updateOrCreate(['name' => 'customer'], ['display_name' => 'Khách hàng', 'description' => 'Quản lý khách hàng']);
        $roleCustomer->syncPermissions(Permission::where('name', 'LIKE', 'customer.%')->pluck('id'));

        Permission::updateOrCreate(['name' => 'building.view'], ['display_name' => 'Xem', 'description' => 'Xem tòa nhà']);
        Permission::updateOrCreate(['name' => 'building.create'], ['display_name' => 'Tạo mới', 'description' => 'Thêm mới toà nhà']);
        Permission::updateOrCreate(['name' => 'building.edit'], ['display_name' => 'Chỉnh sửa', 'description' => 'Chỉnh sửa toà nhà']);
        Permission::updateOrCreate(['name' => 'building.delete'], ['display_name' => 'Xóa', 'description' => 'Xóa toà nhà']);
        // create roles and assign created permissions
        $roleBuilding = Role::updateOrCreate(['name' => 'building'], ['display_name' => 'Tòa nhà', 'description' => 'Quản lý toà nhà']);
        $roleBuilding->syncPermissions(Permission::where('name', 'LIKE', 'building.%')->pluck('id'));

        Permission::updateOrCreate(['name' => 'apartment.view'], ['display_name' => 'Xem', 'description' => 'Xem căn hộ']);
        Permission::updateOrCreate(['name' => 'apartment.create'], ['display_name' => 'Tạo mới', 'description' => 'Thêm mới căn hộ']);
        Permission::updateOrCreate(['name' => 'apartment.edit'], ['display_name' => 'Chỉnh sửa', 'description' => 'Chỉnh sửa căn hộ']);
        Permission::updateOrCreate(['name' => 'apartment.delete'], ['display_name' => 'Xóa', 'description' => 'Xóa căn hộ']);
        // create roles and assign created permissions
        $roleApartment = Role::updateOrCreate(['name' => 'apartment'], ['display_name' => 'Căn hộ', 'description' => 'Quản lý căn hộ']);
        $roleApartment->syncPermissions(Permission::where('name', 'LIKE', 'apartment.%')->pluck('id'));

        Permission::updateOrCreate(['name' => 'utility_type.view'], ['display_name' => 'Xem', 'description' => 'Xem loại tiện ích']);
        Permission::updateOrCreate(['name' => 'utility_type.create'], ['display_name' => 'Tạo mới', 'description' => 'Thêm mới loại tiện ích']);
        Permission::updateOrCreate(['name' => 'utility_type.edit'], ['display_name' => 'Chỉnh sửa', 'description' => 'Chỉnh sửa loại tiện ích']);
        Permission::updateOrCreate(['name' => 'utility_type.delete'], ['display_name' => 'Xóa', 'description' => 'Xóa loại tiện ích']);
        // create roles and assign created permissions
        $roleUtilityType = Role::updateOrCreate(['name' => 'utility_type'], ['display_name' => 'Loại tiện ích', 'description' => 'Quản lý loại tiện ích']);
        $roleUtilityType->syncPermissions(Permission::where('name', 'LIKE', 'utility_type.%')->pluck('id'));

        Permission::updateOrCreate(['name' => 'utility.view'], ['display_name' => 'Xem', 'description' => 'Xem tiện ích']);
        Permission::updateOrCreate(['name' => 'utility.create'], ['display_name' => 'Tạo mới', 'description' => 'Thêm mới tiện ích']);
        Permission::updateOrCreate(['name' => 'utility.edit'], ['display_name' => 'Chỉnh sửa', 'description' => 'Chỉnh sửa tiện ích']);
        Permission::updateOrCreate(['name' => 'utility.delete'], ['display_name' => 'Xóa', 'description' => 'Xóa tiện ích']);
        // create roles and assign created permissions
        $roleUtility = Role::updateOrCreate(['name' => 'utility'], ['display_name' => 'Tiện ích', 'description' => 'Quản lý tiện ích']);
        $roleUtility->syncPermissions(Permission::where('name', 'LIKE', 'utility.%')->pluck('id'));


        $permissions = Permission::all();
        foreach ($permissions as $permission) {
            Permission::updateOrCreate([
                'name' => "{$permission->name}",
                'guard_name' => 'api',
            ], [
                'display_name' => $permission->display_name,
                'description' => $permission->description,
            ]);
        }
    }
}
