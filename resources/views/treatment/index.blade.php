<x-guest-layout title="Daftar Layanan & Treatment">

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-12">
        {{-- HERO SECTION --}}
        <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-white via-amber-50/50 to-[#CFAF53]/15 py-16 sm:py-24 text-center shadow-sm ring-1 ring-slate-100 mb-10">
            {{-- Subtle Dot Pattern Background --}}
            <div class="absolute inset-0 z-0 opacity-20" style="background-image: radial-gradient(#CFAF53 1.5px, transparent 1.5px); background-size: 32px 32px;"></div>
            
            <div class="relative z-10 flex flex-col items-center justify-center">
                <h1 class="mb-4 text-4xl sm:text-5xl font-extrabold text-slate-900 tracking-tight">
                    Pilihan Treatment
                </h1>
                <nav class="flex items-center gap-2 text-sm font-medium text-slate-500">
                    <a href="{{ route('landing') }}" class="hover:text-[#CFAF53] transition-colors">Home</a>
                    <span class="text-slate-300">/</span>
                    <span class="text-slate-900">Treatment</span>
                </nav>
            </div>
        </div>

        {{-- LAYOUT 2 KOLOM (SIDEBAR & KONTEN UTAMA) --}}
        <div class="mb-8 lg:flex lg:gap-8" x-data="{ mobileFilterOpen: false }">
            
            {{-- Tombol Filter Mobile --}}
            <div class="mb-6 lg:hidden">
                <button @click="mobileFilterOpen = !mobileFilterOpen" class="flex w-full items-center justify-center gap-2 rounded-xl bg-slate-900 py-3 text-sm font-semibold text-white shadow-sm hover:bg-[#CFAF53]">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
                    </svg>
                    <span x-text="mobileFilterOpen ? 'Tutup Filter' : 'Tampilkan Filter'"></span>
                </button>
            </div>

            {{-- LEFT SIDEBAR: PENGATURAN FILTER --}}
            <div :class="mobileFilterOpen ? 'block' : 'hidden'" class="w-full lg:block lg:w-1/4 mb-8 lg:mb-0">
                <h3 class="text-xl font-bold text-slate-900 mb-6 border-b border-slate-200 pb-4">Opsi Filter</h3>
                
                <form id="filter-form" action="{{ route('treatment.index') }}" method="GET">
                    <!-- Pertahankan pencarian jika ada -->
                    @if(request('search'))
                        <input type="hidden" name="search" value="{{ request('search') }}">
                    @endif
                    
                    {{-- Kategori Filter --}}
                    <div class="mb-8">
                        <h4 class="font-bold text-slate-900 mb-4">Berdasarkan Kategori</h4>
                        <div class="space-y-3">
                            @if(isset($categories))
                                @foreach($categories as $cat)
                                    <label class="flex items-center cursor-pointer group">
                                        <input type="checkbox" name="category[]" value="{{ $cat->treatment_category_id }}" 
                                               onchange="document.getElementById('filter-form').submit()"
                                               {{ in_array($cat->treatment_category_id, (array)request('category')) ? 'checked' : '' }}
                                               class="h-4 w-4 rounded border-slate-300 text-[#CFAF53] focus:ring-[#CFAF53]">
                                        <span class="ml-3 text-sm text-slate-600 transition-colors group-hover:text-slate-900">{{ $cat->nama }}</span>
                                    </label>
                                @endforeach
                            @endif
                        </div>
                    </div>
                </form>
            </div>

            {{-- MAIN CONTENT (RIGHT SIDE) --}}
            <div class="w-full lg:w-3/4">
                
                {{-- Bagian Atas: Info Hasil & Pencarian --}}
                <div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-100 pb-4">
                    <p class="text-sm text-slate-500 font-medium">
                        Menampilkan {{ $treatments->firstItem() ?? 0 }}-{{ $treatments->lastItem() ?? 0 }} dari {{ $treatments->total() }} hasil
                    </p>
                    
                    {{-- Form Pencarian --}}
                    <div class="flex items-center gap-4">
                        <form action="{{ route('treatment.index') }}" method="GET" class="relative w-full sm:w-64">
                            @foreach((array)request('category') as $cat_id)
                                <input type="hidden" name="category[]" value="{{ $cat_id }}">
                            @endforeach
                            <input type="text" 
                                   name="search" 
                                   value="{{ request('search') }}" 
                                   placeholder="Cari treatment..." 
                                   class="block w-full rounded-full border border-slate-300 bg-white py-2 pl-4 pr-10 text-sm text-slate-700 shadow-sm transition focus:border-[#CFAF53] focus:ring-1 focus:ring-[#CFAF53]">
                            
                            @if(request('search'))
                                <a href="{{ route('treatment.index', request()->except('search')) }}" 
                                   class="absolute inset-y-0 right-10 flex items-center pr-2 text-slate-400 hover:text-red-500 transition">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </a>
                            @endif
                            <button type="submit" class="absolute inset-y-0 right-0 flex items-center pr-4 text-slate-400 hover:text-[#CFAF53] transition">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </button>
                        </form>
                    </div>
                </div>

                {{-- Menampilkan Filter Aktif (Pills) --}}
                @if(request('category') || request('search'))
                    <div class="mb-6 flex flex-wrap items-center gap-2">
                        <span class="text-sm font-medium text-slate-900 mr-2">Filter Aktif:</span>
                        
                        @if(request('search'))
                            <span class="inline-flex items-center gap-1.5 rounded-full bg-slate-900 px-3 py-1 text-xs font-semibold text-white shadow-sm">
                                Pencarian: {{ request('search') }}
                            </span>
                        @endif

                        @foreach((array)request('category') as $cat_id)
                            @php $c = $categories->where('treatment_category_id', $cat_id)->first(); @endphp
                            @if($c)
                                <span class="inline-flex items-center gap-1.5 rounded-full bg-[#CFAF53] px-3 py-1 text-xs font-semibold text-white shadow-sm">
                                    {{ $c->nama }}
                                    <a href="{{ route('treatment.index', array_merge(request()->except('page'), ['category' => array_diff((array)request('category'), [$cat_id])])) }}" class="hover:text-red-200">
                                        <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                                    </a>
                                </span>
                            @endif
                        @endforeach

                        <a href="{{ route('treatment.index') }}" class="text-xs font-medium text-slate-500 hover:text-slate-900 underline ml-2 transition">Bersihkan Semua</a>
                    </div>
                @endif

                {{-- GRID TREATMENT CARDS --}}
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    @forelse($treatments as $item)
                        <div class="group relative flex flex-col overflow-hidden rounded-2xl bg-white border border-slate-100 transition-all duration-300 hover:shadow-[0_8px_30px_rgb(0,0,0,0.08)]">
                            
                            {{-- Image Container --}}
                            <a href="{{ route('treatment.show', $item) }}" class="relative block overflow-hidden aspect-square bg-[#F5F5F3]">
                                @if($item->foto_path)
                                    <img src="{{ asset('storage/' . $item->foto_path) }}" 
                                         alt="{{ $item->nama }}" 
                                         class="h-full w-full object-cover transition duration-700 group-hover:scale-105">
                                @else
                                    <div class="flex h-full w-full items-center justify-center bg-[#F5F5F3]">
                                        <svg class="h-16 w-16 text-slate-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                    </div>
                                @endif

                                {{-- Category Badge Area (Top Left) --}}
                                <span class="absolute left-3 top-3 rounded-md bg-slate-900/80 px-2.5 py-1 text-[10px] font-bold tracking-wider text-white backdrop-blur-sm uppercase">
                                    {{ $item->category->nama ?? 'Umum' }}
                                </span>

                                {{-- Duration Badge Area (Top Right) --}}
                                <span class="absolute right-3 top-3 flex items-center gap-1 rounded-md bg-white/95 px-2.5 py-1 text-[10px] font-bold text-slate-900 shadow-sm backdrop-blur-sm">
                                    <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    {{ $item->durasi_menit }}m
                                </span>
                            </a>

                            {{-- Content Info --}}
                            <div class="flex flex-1 flex-col p-5">
                                {{-- Category & Rating --}}
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-xs text-slate-400 font-medium">{{ $item->category->nama ?? 'Umum' }}</span>
                                </div>

                                <h3 class="mb-1.5 line-clamp-2 text-base font-bold text-slate-900 transition-colors group-hover:text-[#CFAF53]">
                                    <a href="{{ route('treatment.show', $item) }}">{{ $item->nama }}</a>
                                </h3>
                                
                                <p class="mb-4 text-lg font-bold text-[#CFAF53]">
                                    Rp {{ number_format((int)$item->harga, 0, ',', '.') }}
                                </p>

                                {{-- CTA Button --}}
                                <div class="mt-auto pt-4 border-t border-slate-100">
                                    <a href="{{ route('appointment.create', ['treatment_id' => $item->treatment_id]) }}" 
                                       class="flex w-full items-center justify-center gap-2 rounded-xl bg-slate-900 py-2.5 text-sm font-semibold text-white transition hover:bg-[#CFAF53] hover:shadow-lg">
                                        Book Sekarang
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                    </a>
                                </div>
                            </div>
                        </div>
                    @empty
                        {{-- Empty State --}}
                        <div class="col-span-full">
                            <div class="mx-auto max-w-md text-center py-16">
                                <div class="mb-6 inline-flex h-20 w-20 items-center justify-center rounded-full bg-[#f8f9fa]">
                                    <svg class="h-10 w-10 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                                    </svg>
                                </div>
                                <h3 class="mb-2 text-xl font-bold text-slate-900">Treatment Tidak Ditemukan</h3>
                                <p class="mb-6 text-slate-500 text-sm">
                                    Coba sesuaikan filter atau kata kunci pencarian Anda untuk menemukan apa yang Anda cari.
                                </p>
                                <a href="{{ route('treatment.index') }}" 
                                   class="inline-flex items-center justify-center rounded-full border border-slate-300 bg-white px-6 py-2.5 text-sm font-medium text-slate-700 shadow-sm transition hover:bg-slate-50">
                                    Hapus Semua Filter
                                </a>
                            </div>
                        </div>
                    @endforelse
                </div>

                {{-- Pagination --}}
                @if($treatments->hasPages())
                    <div class="mt-10 border-t border-slate-100 pt-8">
                        {{ $treatments->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Floating Action Button (Back to Top) --}}
    <div x-data="{ show: false }" 
         @scroll.window="show = window.pageYOffset > 400"
         x-show="show"
         x-transition
         class="fixed bottom-8 right-8 z-40">
        <button @click="window.scrollTo({ top: 0, behavior: 'smooth' })" 
                class="flex h-12 w-12 items-center justify-center rounded-full bg-slate-900 text-white shadow-lg transition hover:bg-[#CFAF53] hover:scale-110">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18" />
            </svg>
        </button>
    </div>

    {{-- CSS Animation Delay --}}
    <style>
        .animation-delay-2000 {
            animation-delay: 2s;
        }
    </style>

</x-guest-layout>