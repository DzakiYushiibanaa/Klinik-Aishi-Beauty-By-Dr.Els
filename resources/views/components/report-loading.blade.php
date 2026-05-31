@props([
    'target' => '',
    'text' => 'Memuat data laporan...',
])

{{--
  Reusable Livewire-aware loading overlay untuk halaman laporan.

  Pakai di dalam container yang sudah position:relative.

  Contoh:
    <div class="psr-card" style="position:relative;">
        <x-report-loading
            target="filter_month,filter_year,filter_week,filter_search,sortBy,gotoPage,nextPage,previousPage,per_page"
            text="Memuat tabel laporan..." />
        ... isi card ...
    </div>
--}}

<div wire:loading.flex
     wire:target="{{ $target }}"
     class="report-loading-overlay"
     role="status"
     aria-live="polite">
    <div class="report-loading-content">
        <div class="report-spinner" aria-hidden="true"></div>
        <span>{{ $text }}</span>
    </div>
</div>
