<x-guest-layout title="Paket Hemat & Bundling">

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-10"
         x-data="{ mobileFilterOpen: false }">

        <x-catalog-header
            title="Paket Hemat & Bundling"
            subtitle="Kombinasi treatment dan produk pilihan dengan harga lebih hemat. Lebih banyak hasil, lebih sedikit pengeluaran."
            breadcrumb-current="Paket Hemat"
            :search-route="route('bundle.index')"
            search-placeholder="Cari paket..."
            :total-count="$bundles->total()"
            :first-item="$bundles->firstItem()"
            :last-item="$bundles->lastItem()"
            :extra-search-params="[
                'jenis' => (array) request('jenis'),
                'sort'  => request('sort', 'latest'),
            ]"
            :sort-options="[
                'latest'     => 'Terbaru',
                'price_low'  => 'Harga Termurah',
                'price_high' => 'Harga Tertinggi',
                'discount'   => 'Diskon Terbesar',
            ]"
            :current-sort="request('sort', 'latest')"
        />

        <div class="lg:flex lg:gap-8">

            {{-- SIDEBAR FILTER --}}
            <aside :class="mobileFilterOpen ? 'block' : 'hidden'"
                   class="w-full lg:block lg:w-[260px] shrink-0 mb-6 lg:mb-0">
                <div class="sticky top-24 bg-white rounded-2xl border border-[#E8E0D8]/60 shadow-sm p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-xs font-extrabold uppercase tracking-[0.2em] text-[#8C7A63]">Filter</h3>
                        @if(request('jenis'))
                            <a href="{{ route('bundle.index', ['search' => request('search'), 'sort' => request('sort')]) }}"
                               class="text-xs font-bold text-rose-500 hover:text-rose-600 transition-colors">
                                Reset
                            </a>
                        @endif
                    </div>

                    <form id="filter-form" action="{{ route('bundle.index') }}" method="GET" class="space-y-6">
                        @if(request('search'))
                            <input type="hidden" name="search" value="{{ request('search') }}">
                        @endif
                        @if(request('sort'))
                            <input type="hidden" name="sort" value="{{ request('sort') }}">
                        @endif

                        <div>
                            <h4 class="text-sm font-bold text-[#1A1A1A] mb-3">Jenis Paket</h4>
                            <div class="space-y-2.5">
                                @foreach([
                                    ['product',   'Produk'],
                                    ['treatment', 'Treatment'],
                                    ['mixed',     'Kombinasi'],
                                ] as [$val, $lbl])
                                    @php $checked = in_array($val, (array)request('jenis')); @endphp
                                    <label class="flex items-center cursor-pointer group">
                                        <input type="checkbox" name="jenis[]" value="{{ $val }}"
                                               onchange="document.getElementById('filter-form').submit()"
                                               {{ $checked ? 'checked' : '' }}
                                               class="h-4 w-4 rounded border-slate-300 text-[#CFAF53] focus:ring-[#CFAF53]/40 cursor-pointer">
                                        <span class="ml-3 text-sm transition-colors {{ $checked ? 'font-bold text-[#1A1A1A]' : 'text-slate-600 group-hover:text-[#1A1A1A]' }}">
                                            {{ $lbl }}
                                        </span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    </form>
                </div>
            </aside>

            {{-- MAIN --}}
            <div class="flex-1 min-w-0">

                @if(request('jenis') || request('search'))
                    <div class="flex flex-wrap items-center gap-2 mb-6">
                        <span class="text-xs font-bold uppercase tracking-widest text-slate-400">Aktif:</span>

                        @if(request('search'))
                            <span class="inline-flex items-center gap-1.5 rounded-full bg-[#1A1A1A] pl-3 pr-2 py-1 text-xs font-bold text-white">
                                "{{ request('search') }}"
                                <a href="{{ route('bundle.index', request()->except(['search', 'page'])) }}" class="rounded-full hover:bg-white/10 p-0.5 transition-colors" aria-label="Hapus pencarian">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                                </a>
                            </span>
                        @endif

                        @foreach((array)request('jenis') as $j)
                            @php $label = match($j) { 'product' => 'Produk', 'treatment' => 'Treatment', 'mixed' => 'Kombinasi', default => $j }; @endphp
                            <span class="inline-flex items-center gap-1.5 rounded-full bg-[#CFAF53]/15 text-[#1A1A1A] border border-[#CFAF53]/30 pl-3 pr-2 py-1 text-xs font-bold">
                                {{ $label }}
                                <a href="{{ route('bundle.index', array_merge(request()->except('page'), ['jenis' => array_diff((array)request('jenis'), [$j])])) }}" class="rounded-full hover:bg-[#CFAF53]/20 p-0.5 transition-colors" aria-label="Hapus filter">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                                </a>
                            </span>
                        @endforeach
                    </div>
                @endif

                {{-- GRID BUNDLES --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5 sm:gap-6">
                    @forelse($bundles as $bundle)
                        @php
                            $hasPromo = $bundle->harga_normal > $bundle->harga_promo && $bundle->harga_promo > 0;
                            $persen   = $hasPromo ? round((($bundle->harga_normal - $bundle->harga_promo) / $bundle->harga_normal) * 100) : 0;
                            $jenisLabel = match($bundle->jenis) {
                                'product'   => 'Produk',
                                'treatment' => 'Treatment',
                                'mixed'     => 'Kombinasi',
                                default     => 'Paket',
                            };
                            $totalItems   = $bundle->productItems->count() + $bundle->treatmentItems->count();
                            $displayItems = $bundle->productItems->take(2)->merge($bundle->treatmentItems->take(2));
                        @endphp

                        <article class="group/card relative flex flex-col rounded-2xl bg-white border border-[#E8E0D8]/60 overflow-hidden transition-all duration-300 hover:-translate-y-1 hover:shadow-xl hover:shadow-[#CFAF53]/10 hover:border-[#CFAF53]/50">

                            <div class="relative aspect-square overflow-hidden bg-gradient-to-br from-[#FAF8F5] to-[#F2EFE9]">
                                <a href="{{ route('bundle.show', $bundle) }}" class="absolute inset-0 z-0 block" aria-label="{{ $bundle->nama }}">
                                    @if($bundle->foto_path)
                                        <img src="{{ asset('storage/' . $bundle->foto_path) }}"
                                             alt="{{ $bundle->nama }}"
                                             loading="lazy"
                                             class="h-full w-full object-cover transition duration-500 group-hover/card:scale-105">
                                    @else
                                        <div class="flex h-full w-full items-center justify-center">
                                            <svg class="h-16 w-16 text-slate-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                            </svg>
                                        </div>
                                    @endif
                                </a>

                                {{-- Diskon ribbon --}}
                                @if($hasPromo)
                                    <span class="absolute left-0 top-3 z-10 rounded-r-full bg-rose-500 pl-3 pr-4 py-1 text-[10px] font-extrabold tracking-widest text-white uppercase shadow-md pointer-events-none">
                                        Hemat {{ $persen }}%
                                    </span>
                                @endif

                                {{-- Hover CTA --}}
                                <div class="absolute inset-x-3 bottom-3 z-20 opacity-0 translate-y-3 group-hover/card:opacity-100 group-hover/card:translate-y-0 transition-all duration-300">
                                    <a href="{{ route('bundle.show', $bundle) }}"
                                       class="w-full inline-flex items-center justify-center gap-1.5 rounded-full bg-[#1A1A1A] hover:bg-[#CFAF53] text-white text-[10px] sm:text-[11px] font-bold uppercase tracking-wider py-2.5 transition-colors cursor-pointer shadow-lg whitespace-nowrap">
                                        Lihat Detail
                                        <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                                        </svg>
                                    </a>
                                </div>
                            </div>

                            <div class="flex flex-col p-4 border-t border-[#E8E0D8]/60">
                                {{-- Jenis paket --}}
                                <span class="text-[11px] font-semibold tracking-wide text-[#8C7A63] mb-1.5">
                                    {{ $jenisLabel }}
                                </span>

                                <h3 class="text-sm sm:text-base font-bold text-[#1A1A1A] line-clamp-2 leading-snug min-h-[2.5rem]">
                                    <a href="{{ route('bundle.show', $bundle) }}" class="hover:text-[#8C7A63] transition-colors">{{ $bundle->nama }}</a>
                                </h3>

                                <div class="mt-2 flex items-baseline gap-2 flex-wrap">
                                    <span class="text-base sm:text-lg font-extrabold text-[#CFAF53]">
                                        Rp {{ number_format($hasPromo ? $bundle->harga_promo : $bundle->harga_normal, 0, ',', '.') }}
                                    </span>
                                    @if($hasPromo)
                                        <span class="text-xs text-slate-400 line-through">
                                            Rp {{ number_format($bundle->harga_normal, 0, ',', '.') }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </article>
                    @empty
                        <div class="col-span-full">
                            <div class="rounded-2xl border border-dashed border-[#E8E0D8] bg-white p-10 sm:p-14 text-center">
                                <div class="mx-auto mb-4 inline-flex h-16 w-16 items-center justify-center rounded-2xl bg-[#FAF8F5] text-[#CFAF53]">
                                    <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                    </svg>
                                </div>
                                <h3 class="text-lg font-extrabold text-[#1A1A1A]">Paket tidak ditemukan</h3>
                                <p class="mt-2 text-sm text-slate-500 max-w-sm mx-auto leading-relaxed">
                                    Belum ada paket yang cocok dengan filter saat ini. Coba kata kunci lain atau hapus filter.
                                </p>
                                <a href="{{ route('bundle.index') }}"
                                   class="mt-6 inline-flex items-center gap-2 rounded-full bg-[#CFAF53] hover:bg-[#b09444] text-white text-sm font-bold px-5 py-2.5 transition-colors cursor-pointer">
                                    Lihat Semua Paket
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                                </a>
                            </div>
                        </div>
                    @endforelse
                </div>

                @if($bundles->hasPages())
                    <div class="mt-10 pt-8 border-t border-[#E8E0D8]/60 flex justify-center">
                        {{ $bundles->links('vendor.pagination.aishi') }}
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
         class="fixed bottom-24 right-6 z-40">
        <button @click="window.scrollTo({ top: 0, behavior: 'smooth' })"
                aria-label="Kembali ke atas"
                class="flex h-11 w-11 items-center justify-center rounded-full bg-[#1A1A1A] text-white shadow-lg hover:bg-[#CFAF53] transition-colors cursor-pointer">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/>
            </svg>
        </button>
    </div>

</x-guest-layout>
