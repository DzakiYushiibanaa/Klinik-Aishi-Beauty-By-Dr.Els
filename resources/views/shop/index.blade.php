<x-guest-layout title="Produk Skincare">

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-10"
         x-data="{ mobileFilterOpen: false }">

        {{-- HEADER MODERN (banner besar dihapus, diganti toolbar slim) --}}
        <x-catalog-header
            title="Katalog Produk"
            subtitle="Skincare resmi dari Aishi Beauty, diformulasikan dokter untuk hasil yang terbukti."
            breadcrumb-current="Produk"
            :search-route="route('shop.index')"
            search-placeholder="Cari produk..."
            :total-count="$products->total()"
            :first-item="$products->firstItem()"
            :last-item="$products->lastItem()"
            :extra-search-params="[
                'category'     => (array) request('category'),
                'availability' => (array) request('availability'),
            ]"
        />

        {{-- LAYOUT 2 KOLOM (SIDEBAR & KONTEN UTAMA) --}}
        <div class="lg:flex lg:gap-8">

            {{-- LEFT SIDEBAR: PENGATURAN FILTER --}}
            <aside :class="mobileFilterOpen ? 'block' : 'hidden'"
                   class="w-full lg:block lg:w-[260px] shrink-0 mb-6 lg:mb-0">
                <div class="sticky top-24 bg-white rounded-2xl border border-[#E8E0D8]/60 shadow-sm p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-xs font-extrabold uppercase tracking-[0.2em] text-[#8C7A63]">Filter</h3>
                        @if(request('category') || request('availability'))
                            <a href="{{ route('shop.index', ['search' => request('search')]) }}"
                               class="text-xs font-bold text-rose-500 hover:text-rose-600 transition-colors">
                                Reset
                            </a>
                        @endif
                    </div>

                    <form id="filter-form" action="{{ route('shop.index') }}" method="GET" class="space-y-6">
                        @if(request('search'))
                            <input type="hidden" name="search" value="{{ request('search') }}">
                        @endif

                        {{-- Kategori --}}
                        <div>
                            <h4 class="text-sm font-bold text-[#1A1A1A] mb-3">Kategori</h4>
                            <div class="space-y-2.5 max-h-64 overflow-y-auto custom-scrollbar pr-1">
                                @foreach($categories as $cat)
                                    @php $checked = in_array($cat->product_category_id, (array)request('category')); @endphp
                                    <label class="flex items-center cursor-pointer group">
                                        <input type="checkbox" name="category[]" value="{{ $cat->product_category_id }}"
                                               onchange="document.getElementById('filter-form').submit()"
                                               {{ $checked ? 'checked' : '' }}
                                               class="h-4 w-4 rounded border-slate-300 text-[#CFAF53] focus:ring-[#CFAF53]/40 cursor-pointer">
                                        <span class="ml-3 text-sm transition-colors {{ $checked ? 'font-bold text-[#1A1A1A]' : 'text-slate-600 group-hover:text-[#1A1A1A]' }}">
                                            {{ $cat->nama }}
                                        </span>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        <div class="border-t border-[#E8E0D8]/60"></div>

                        {{-- Ketersediaan --}}
                        <div>
                            <h4 class="text-sm font-bold text-[#1A1A1A] mb-3">Ketersediaan</h4>
                            <div class="space-y-2.5">
                                @foreach([['in_stock', 'Tersedia'], ['out_of_stock', 'Stok Habis']] as [$val, $lbl])
                                    @php $checked = in_array($val, (array)request('availability')); @endphp
                                    <label class="flex items-center cursor-pointer group">
                                        <input type="checkbox" name="availability[]" value="{{ $val }}"
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

            {{-- MAIN CONTENT --}}
            <div class="flex-1 min-w-0">

                {{-- Active Filter Chips --}}
                @if(request('category') || request('availability') || request('search'))
                    <div class="flex flex-wrap items-center gap-2 mb-6">
                        <span class="text-xs font-bold uppercase tracking-widest text-slate-400">Aktif:</span>

                        @if(request('search'))
                            <span class="inline-flex items-center gap-1.5 rounded-full bg-[#1A1A1A] pl-3 pr-2 py-1 text-xs font-bold text-white">
                                "{{ request('search') }}"
                                <a href="{{ route('shop.index', request()->except(['search', 'page'])) }}" class="rounded-full hover:bg-white/10 p-0.5 transition-colors" aria-label="Hapus pencarian">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                                </a>
                            </span>
                        @endif

                        @foreach((array)request('category') as $cat_id)
                            @php $c = $categories->where('product_category_id', $cat_id)->first(); @endphp
                            @if($c)
                                <span class="inline-flex items-center gap-1.5 rounded-full bg-[#CFAF53]/15 text-[#1A1A1A] border border-[#CFAF53]/30 pl-3 pr-2 py-1 text-xs font-bold">
                                    {{ $c->nama }}
                                    <a href="{{ route('shop.index', array_merge(request()->except('page'), ['category' => array_diff((array)request('category'), [$cat_id])])) }}" class="rounded-full hover:bg-[#CFAF53]/20 p-0.5 transition-colors" aria-label="Hapus filter">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </a>
                                </span>
                            @endif
                        @endforeach

                        @foreach((array)request('availability') as $avail)
                            <span class="inline-flex items-center gap-1.5 rounded-full bg-[#CFAF53]/15 text-[#1A1A1A] border border-[#CFAF53]/30 pl-3 pr-2 py-1 text-xs font-bold">
                                {{ $avail === 'in_stock' ? 'Tersedia' : 'Stok Habis' }}
                                <a href="{{ route('shop.index', array_merge(request()->except('page'), ['availability' => array_diff((array)request('availability'), [$avail])])) }}" class="rounded-full hover:bg-[#CFAF53]/20 p-0.5 transition-colors" aria-label="Hapus filter">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                                </a>
                            </span>
                        @endforeach
                    </div>
                @endif

                {{-- GRID PRODUK --}}
                <div class="grid grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-3 sm:gap-5 lg:gap-6">
                    @forelse($products as $p)
                        @php
                            $inStock = (int) $p->stok > 0;
                            $isLow   = $inStock && (int) $p->stok <= 5;
                        @endphp
                        <article class="group/card relative flex flex-col rounded-2xl bg-white border border-[#E8E0D8]/60 overflow-hidden transition-all duration-300 hover:-translate-y-1 hover:shadow-xl hover:shadow-[#CFAF53]/10 hover:border-[#CFAF53]/50">

                            {{-- Image Area --}}
                            <div class="relative aspect-square overflow-hidden bg-gradient-to-br from-[#FAF8F5] to-[#F2EFE9]">
                                {{-- Link ke detail (overlay seluruh image) --}}
                                <a href="{{ route('shop.show', $p) }}" class="absolute inset-0 z-0 block" aria-label="{{ $p->nama }}">
                                    @if($p->foto_path)
                                        <img src="{{ asset('storage/' . $p->foto_path) }}"
                                             alt="{{ $p->nama }}"
                                             loading="lazy"
                                             class="h-full w-full object-contain p-6 sm:p-8 transition duration-500 group-hover/card:scale-105">
                                    @else
                                        <div class="flex h-full w-full items-center justify-center">
                                            <svg class="h-16 w-16 text-slate-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                            </svg>
                                        </div>
                                    @endif
                                </a>

                                {{-- Stok ribbon (di atas link image) --}}
                                @if(!$inStock)
                                    <span class="absolute left-0 top-3 z-10 rounded-r-full bg-rose-500 pl-3 pr-4 py-1 text-[10px] font-extrabold tracking-widest text-white uppercase shadow-md pointer-events-none">
                                        Habis
                                    </span>
                                @elseif($isLow)
                                    <span class="absolute left-0 top-3 z-10 rounded-r-full bg-amber-500 pl-3 pr-4 py-1 text-[10px] font-extrabold tracking-widest text-white uppercase shadow-md pointer-events-none">
                                        Sisa {{ $p->stok }}
                                    </span>
                                @endif

                                {{-- Hover CTA (sibling, bukan nested di link) — tablet+ saja --}}
                                @if($inStock)
                                    <div class="hidden sm:block absolute inset-x-3 bottom-3 z-20 opacity-0 translate-y-3 group-hover/card:opacity-100 group-hover/card:translate-y-0 transition-all duration-300">
                                        <form method="POST" action="{{ route('cart.add', $p) }}">
                                            @csrf
                                            <input type="hidden" name="qty" value="1">
                                            <button type="submit"
                                                    class="w-full inline-flex items-center justify-center gap-1.5 rounded-full bg-[#1A1A1A] hover:bg-[#CFAF53] text-white text-[10px] sm:text-[11px] font-bold uppercase tracking-wider py-2.5 transition-colors cursor-pointer shadow-lg whitespace-nowrap">
                                                <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                                                </svg>
                                                Tambah Keranjang
                                            </button>
                                        </form>
                                    </div>
                                @endif
                            </div>

                            {{-- Mobile-only CTA bar (selalu tampil di sm bawah) --}}
                            @if($inStock)
                                <form method="POST" action="{{ route('cart.add', $p) }}" class="sm:hidden px-3 pt-2 pb-3">
                                    @csrf
                                    <input type="hidden" name="qty" value="1">
                                    <button type="submit"
                                            class="w-full inline-flex items-center justify-center gap-1.5 rounded-full bg-[#1A1A1A] hover:bg-[#CFAF53] text-white text-[11px] font-bold uppercase tracking-wider py-2.5 transition-colors cursor-pointer">
                                        <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                                        </svg>
                                        Tambah
                                    </button>
                                </form>
                            @endif

                            {{-- Body --}}
                            <div class="flex flex-col p-3 sm:p-4 border-t border-[#E8E0D8]/60">
                                {{-- Kategori --}}
                                <span class="text-[10px] sm:text-[11px] font-semibold tracking-wide text-[#8C7A63] mb-1 sm:mb-1.5">
                                    {{ $p->category->nama ?? 'Skincare' }}
                                </span>

                                {{-- Nama produk --}}
                                <h3 class="text-[13px] sm:text-base font-bold text-[#1A1A1A] line-clamp-2 leading-snug min-h-[2.25rem] sm:min-h-[2.5rem]">
                                    <a href="{{ route('shop.show', $p) }}" class="hover:text-[#8C7A63] transition-colors">{{ $p->nama }}</a>
                                </h3>

                                {{-- Harga --}}
                                <div class="mt-1.5 sm:mt-2">
                                    <span class="text-sm sm:text-lg font-extrabold text-[#CFAF53]">
                                        Rp {{ number_format((int)$p->harga, 0, ',', '.') }}
                                    </span>
                                </div>
                            </div>
                        </article>
                    @empty
                        <div class="col-span-full">
                            <div class="rounded-2xl border border-dashed border-[#E8E0D8] bg-white p-10 sm:p-14 text-center">
                                <div class="mx-auto mb-4 inline-flex h-16 w-16 items-center justify-center rounded-2xl bg-[#FAF8F5] text-[#CFAF53]">
                                    <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                    </svg>
                                </div>
                                <h3 class="text-lg font-extrabold text-[#1A1A1A]">Produk tidak ditemukan</h3>
                                <p class="mt-2 text-sm text-slate-500 max-w-sm mx-auto leading-relaxed">
                                    Coba ubah kata kunci pencarian atau hapus beberapa filter agar hasil lebih banyak muncul.
                                </p>
                                <a href="{{ route('shop.index') }}"
                                   class="mt-6 inline-flex items-center gap-2 rounded-full bg-[#CFAF53] hover:bg-[#b09444] text-white text-sm font-bold px-5 py-2.5 transition-colors cursor-pointer">
                                    Hapus Semua Filter
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                                </a>
                            </div>
                        </div>
                    @endforelse
                </div>

                @if($products->hasPages())
                    <div class="mt-10 pt-8 border-t border-[#E8E0D8]/60 flex justify-center">
                        {{ $products->links('vendor.pagination.aishi') }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Floating Back to Top --}}
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
