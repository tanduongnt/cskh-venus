<x-filament-panels::page>
        @for ($i = 0; $i < (1440/$block); $i++)
        {{ $i }}
        @endfor
</x-filament-panels::page>
