<x-filament-panels::page>
    {{-- Form Filter --}}
    <div>
        {{ $this->form }}
    </div>

    {{-- Widget Statistik (Cards) --}}
    @livewire('financial-stats', [
        'startDate' => $this->startDate,
        'endDate'   => $this->endDate,
    ])

    {{-- Widget Chart (Grafik) --}}
    @livewire('financial-chart', [
        'startDate' => $this->startDate,
        'endDate'   => $this->endDate,
    ])
</x-filament-panels::page>
