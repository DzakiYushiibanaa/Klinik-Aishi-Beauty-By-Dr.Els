<x-guest-layout title="Daftar Treatment">

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-10"
         x-data="{ mobileFilterOpen: false }">

        <x-catalog-header
            title="Pilihan Treatment"
            breadcrumb-current="Treatment"
            :search-route="route('treatment.index')"
            search-placeholder="Cari treatment..."
            :total-count="$treatments->total()"
            :first-item="$treatments->firstItem()"
            :last-item="$treatments->lastItem()"
            :extra-search-params="[
                'category' => (array) request('category'),
                'sort'     => $sort ?? 'price_low',
            ]"
            :sort-options="[
                'price_low'  => 'Harga Termurah',
                'price_high' => 'Harga Tertinggi',
                'newest'     => 'Terbaru',
                'name'       => 'Nama A-Z',
            ]"
            :current-sort="$sort ?? 'price_low'"
        />

        <div class="lg:flex lg:gap-8">

            {{-- SIDEBAR FILTER --}}
                @include('treatment._partials._sidebar_filter')
            {{-- MAIN --}}
            <div class="flex-1 min-w-0">

                @include('treatment._partials._active_filter_chips')
                {{-- GRID TREATMENT --}}
                <div class="grid grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-3 sm:gap-5 lg:gap-6">
                    @forelse($treatments as $item)
                        @include('treatment._partials._treatment_card')
                    @empty
                        @include('treatment._partials._empty_state')
                    @endforelse
                </div>

                @if($treatments->hasPages())
                    <div class="mt-10 pt-8 border-t border-[#E8E0D8]/60 flex justify-center">
                        {{ $treatments->links('vendor.pagination.aishi') }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div x-data="{ show: false }"
         @scroll.window="show = window.pageYOffset > 400"
         x-show="show"
         x-cloak
         x-transition.opacity
         class="fixed bottom-40 lg:bottom-24 right-6 z-40">
        <button @click="window.scrollTo({ top: 0, behavior: 'smooth' })"
                aria-label="Kembali ke atas"
                class="flex h-11 w-11 items-center justify-center rounded-full bg-[#1A1A1A] text-white shadow-lg hover:bg-[#CFAF53] transition-colors cursor-pointer">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/>
            </svg>
        </button>
    </div>

</x-guest-layout>
