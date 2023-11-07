<?php

namespace App\Filament\Admin\Resources\UserResource\Pages;

use App\Models\User;
use Filament\Resources\Pages\Page;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use App\Filament\Admin\Resources\UserResource;
use Filament\Forms\Concerns\InteractsWithForms;

class Permission extends Page
{
    use InteractsWithForms;

    protected static string $resource = UserResource::class;

    protected static string $view = 'filament.admin.resources.user-resource.pages.permission';

    public $user;
    public $roles = [];
    public $permissions = [];
    public ?Collection $roleList;

    public function mount(int | string $record)
    {
        $this->user = User::with(['roles', 'permissions'])->find($record);
        $this->roleList = Role::with(['permissions'])->get();
        $this->form->fill([
            'roles' => $this->user->roles->pluck('id'),
            'permissions' => $this->user->permissions->pluck('id'),
        ]);
    }

    public function grant()
    {
        try {
            DB::transaction(function () {
                $this->user->roles()->sync($this->roles);
                $this->user->permissions()->sync($this->permissions);
            });
            $this->redirect(UserResource::class);
        } catch (\Exception $ex) {
        }
    }
}
