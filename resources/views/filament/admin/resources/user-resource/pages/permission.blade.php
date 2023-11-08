<x-filament-panels::page>
    <div>
        @foreach ($roleList as $role)
            @if ($role->permissions && $role->permissions->count() > 0)
                <div class="bg-white border border-gray-200 rounded-lg shadow mt-2">
                    <div class="border-b border-gray-200 px-4 py-4">
                        <h3 class="text-lg font-medium leading-6 text-gray-900">
                            {{ $role->display_name }}
                        </h3>
                    </div>
                    <div class="flex flex-wrap gap-4 p-4">
                        @foreach ($role->permissions as $permission)
                        <label>
                            <x-filament::input.checkbox wire:key="{{ $permission->id }}" wire:model="permissions" value="{{ $permission->id }}" />
                            <span class="ps-2">
                                {{ $permission->display_name }}
                            </span>
                        </label>
                        @endforeach
                    </div>
                </div>
            @else
                <div class=" bg-white border border-gray-200 rounded-lg shadow mt-2">
                    <div class="border-b border-gray-200 px-4 py-5">
                        <label>
                            <x-filament::input.checkbox wire:model="roles" value="{{ $role->id }}"/>

                            <span class="ps-2">
                                {{ $role->display_name }}
                            </span>
                        </label>
                    </div>
                </div>
            @endif
        @endforeach
        <div>
            <x-filament::button class="mt-2" type="button" wire:click="grant">
                Hoàn tất
            </x-filament::button>
        </div>
    </div>
</x-filament-panels::page>
