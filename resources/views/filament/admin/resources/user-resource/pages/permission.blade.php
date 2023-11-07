<x-filament-panels::page>
    <div>
        <form wire:submit="grant">
            @foreach ($roleList as $role)
                @if ($role->permissions && $role->permissions->count() > 0)
                    <div class="relative block bg-white border border-gray-200 rounded-lg shadow mt-2">

                        <div class="border-b border-gray-200 px-4 py-5">
                            <h3 class="text-lg font-medium leading-6 text-gray-900">
                                {{ $role->display_name }}
                            </h3>
                        </div>



                            <div class="grid grid-cols-4 gap-4 sm:grid-cols-2">
                                @foreach ($role->permissions as $permission)
                                <div class="relative flex items-center space-x-3 rounded-lg border border-gray-300 bg-white px-6 py-5 shadow-sm focus-within:ring-2 focus-within:ring-indigo-500 focus-within:ring-offset-2 hover:border-gray-400">
                                    <div class="flex-shrink-0">
                                        <label>
                                            <x-filament::input.checkbox wire:key="{{ $permission->id }}" wire:model="permissions" value="{{ $permission->id }}" />

                                            <span class="ps-2">
                                                {{ $permission->display_name }}
                                            </span>
                                        </label>
                                    </div>
                                </div>
                                @endforeach
                            </div>



                        {{--  <div class="p-4">
                            @foreach ($role->permissions as $permission)
                                <label>
                                    <x-filament::input.checkbox wire:key="{{ $permission->id }}" wire:model="permissions" value="{{ $permission->id }}" />

                                    <span class="ps-2">
                                        {{ $permission->display_name }}
                                    </span>
                                </label>
                            @endforeach
                        </div>  --}}
                    </div>
                @else
                    <div class="relative block bg-white border border-gray-200 rounded-lg shadow mt-2">
                        <div class="border-b border-gray-200 px-4 py-5">
                            <label>
                                <x-filament::input.checkbox wire:model="roles" />

                                <span class="ps-2">
                                    {{ $role->display_name }}
                                </span>
                            </label>
                        </div>
                    </div>
                @endif
            @endforeach
            <div>
                <x-filament::button class="mt-2" type="submit">
                    Hoàn tất
                </x-filament::button>
            </div>
        </form>

    </div>
</x-filament-panels::page>
