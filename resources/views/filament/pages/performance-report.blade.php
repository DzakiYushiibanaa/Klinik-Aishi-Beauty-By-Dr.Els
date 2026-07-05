<x-filament-panels::page>
    <div>
        {{ $this->form }}
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        {{-- Widget Tabel Dokter --}}
        @livewire('doctor-leaderboard', [
            'startDate' => $this->startDate,
            'endDate'   => $this->endDate,
        ])

        {{-- Widget Tabel Terapis --}}
        @livewire('therapist-leaderboard', [
            'startDate' => $this->startDate,
            'endDate'   => $this->endDate,
        ])
    </div>
</x-filament-panels::page>
