<x-filament-panels::page>
    <form wire:submit="save">
        {{ $this->form }}

        <div class="mt-6 flex gap-4">
            <x-filament::button type="submit">
                Simpan Perubahan
            </x-filament::button>
            <x-filament::button type="button" color="gray" tag="a" href="{{ url()->previous() }}">
                Kembali
            </x-filament::button>
        </div>
    </form>
</x-filament-panels::page>
