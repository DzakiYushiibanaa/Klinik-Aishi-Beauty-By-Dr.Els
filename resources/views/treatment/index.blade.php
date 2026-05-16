<x-guest-layout title="Daftar Treatment">

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-10"
         x-data="{ mobileFilterOpen: false }">

        <x-catalog-header
            title="Pilihan Treatment"
            subtitle="Layanan perawatan kulit & kecantikan langsung dari dokter dan terapis berpengalaman."
            breadcrumb-current="Treatment"
            :search-route="route('treatment.index')"
            search-placeholder="Cari treatment..."
            :total-count="$treatments->total()"
            :first-item="$treatments->firstItem()"
            :last-item="$treatments->lastItem()"
            :extra-search-params="[
                'category' => (array) request('category'),
            ]"
        />

        <div class="lg:flex lg:gap-8">

            {{-- SIDEBAR FILTER --}}
            <aside :class="mobileFilterOpen ? 'block' : 'hidden'"
                   class="w-full lg:block lg:w-[260px] shrink-0 mb-6 lg:mb-0">
                <div class="sticky top-24 bg-white rounded-2xl border border-[#E8E0D8]/60 shadow-sm p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-xs font-extrabold uppercase tracking-[0.2em] text-[#8C7A63]">Filter</h3>
                        @if(request('category'))
                            <a href="{{ route('treatment.index', ['search' => request('search')]) }}"
                               class="text-xs font-bold text-rose-500 hover:text-rose-600 transition-colors">
                                Reset
                            </a>
                        @endif
                    </div>

                    <form id="filter-form" action="{{ route('treatment.index') }}" method="GET" class="space-y-6">
                        @if(request('search'))
                            <input type="hidden" name="search" value="{{ request('search') }}">
                        @endif

                        <div>
                            <h4 class="text-sm font-bold text-[#1A1A1A] mb-3">Kategori</h4>
                            <div class="space-y-2.5 max-h-72 overflow-y-auto custom-scrollbar pr-1">
                                @if(isset($categories))
                                    @foreach($categories as $cat)
                                        @php $checked = in_array($cat->treatment_category_id, (array)request('category')); @endphp
                                        <label class="flex items-center cursor-pointer group">
                                            <input type="checkbox" name="category[]" value="{{ $cat->treatment_category_id }}"
                                                   onchange="document.getElementById('filter-form').submit()"
                                                   {{ $checked ? 'checked' : '' }}
                                                   class="h-4 w-4 rounded border-slate-300 text-[#CFAF53] focus:ring-[#CFAF53]/40 cursor-pointer">
                                            <span class="ml-3 text-sm transition-colors {{ $checked ? 'font-bold text-[#1A1A1A]' : 'text-slate-600 group-hover:text-[#1A1A1A]' }}">
                                                {{ $cat->nama }}
                                            </span>
                                        </label>
                                    @endforeach
                                @endif
                            </div>
                        </div>
                    </form>
                </div>
            </aside>

            {{-- MAIN --}}
            <div class="flex-1 min-w-0">

                @if(request('category') || request('search'))
                    <div class="flex flex-wrap items-center gap-2 mb-6">
                        <span class="text-xs font-bold uppercase tracking-widest text-slate-400">Aktif:</span>

                        @if(request('search'))
                            <span class="inline-flex items-center gap-1.5 rounded-full bg-[#1A1A1A] pl-3 pr-2 py-1 text-xs font-bold text-white">
                                "{{ request('search') }}"
                                <a href="{{ route('treatment.index', request()->except(['search', 'page'])) }}" class="rounded-full hover:bg-white/10 p-0.5 transition-colors" aria-label="Hapus pencarian">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                                </a>
                            </span>
                        @endif

                        @foreach((array)request('category') as $cat_id)
                            @php $c = $categories->where('treatment_category_id', $cat_id)->first(); @endphp
                            @if($c)
                                <span class="inline-flex items-center gap-1.5 rounded-full bg-[#CFAF53]/15 text-[#1A1A1A] border border-[#CFAF53]/30 pl-3 pr-2 py-1 text-xs font-bold">
                                    {{ $c->nama }}
                                    <a href="{{ route('treatment.index', array_merge(request()->except('page'), ['category' => array_diff((array)request('category'), [$cat_id])])) }}" class="rounded-full hover:bg-[#CFAF53]/20 p-0.5 transition-colors" aria-label="Hapus filter">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </a>
                                </span>
                            @endif
                        @endforeach
                    </div>
                @endif

                {{-- GRID TREATMENT --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5 sm:gap-6">
                    @forelse($treatments as $item)
                        <article class="group flex flex-col rounded-2xl bg-white border border-[#E8E0D8]/60 overflow-hidden transition-all duration-300 hover:-translate-y-1 hover:shadow-lg hover:shadow-[#CFAF53]/10 hover:border-[#CFAF53]/40">

                            <a href="{{ route('treatment.show', $item) }}" class="relative block overflow-hidden aspect-square bg-[#FAF8F5]">
                                @if($item->foto_path)
                                    <img src="{{ asset('storage/' . $item->foto_path) }}"
                                         alt="{{ $item->nama }}"
                                         loading="lazy"
                                         class="h-full w-full object-cover transition duration-700 group-hover:scale-105">
                                @else
                                    <div class="flex h-full w-full items-center justify-center">
                                        <svg class="h-16 w-16 text-slate-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                        </svg>
                                    </div>
                                @endif

                                <span class="absolute left-3 top-3 rounded-full bg-white/90 backdrop-blur-sm px-3 py-1 text-[10px] font-bold tracking-widest text-[#1A1A1A] uppercase shadow-sm">
                                    {{ $item->category->nama ?? 'Umum' }}
                                </span>

                                @if($item->durasi_menit)
                                    <span class="absolute right-3 top-3 inline-flex items-center gap-1 rounded-full bg-[#1A1A1A]/85 backdrop-blur-sm px-3 py-1 text-[10px] font-bold tracking-widest text-white uppercase shadow-sm">
                                        <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        {{ $item->durasi_menit }} mnt
                                    </span>
                                @endif

                                <div class="absolute inset-x-0 bottom-0 translate-y-full group-hover:translate-y-0 transition-transform duration-300 bg-gradient-to-t from-black/60 via-black/30 to-transparent p-4 flex justify-end">
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-white px-3 py-1.5 text-xs font-bold text-[#1A1A1A] shadow-lg">
                                        Lihat Detail
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                                        </svg>
                                    </span>
                                </div>
                            </a>

                            <div class="flex flex-1 flex-col p-5">
                                <a href="{{ route('treatment.show', $item) }}" class="group/link">
                                    <h3 class="text-base font-bold text-[#1A1A1A] line-clamp-2 leading-snug min-h-[2.75rem] group-hover/link:text-[#8C7A63] transition-colors">
                                        {{ $item->nama }}
                                    </h3>
                                </a>

                                <div class="mt-2 flex items-baseline gap-2">
                                    <span class="text-lg font-extrabold text-[#1A1A1A]">
                                        Rp {{ number_format((int)$item->harga, 0, ',', '.') }}
                                    </span>
                                </div>

                                <div class="mt-4 pt-4 border-t border-[#E8E0D8]/60">
                                    <a href="{{ route('appointment.create', ['treatment_id' => $item->treatment_id]) }}"
                                       class="w-full inline-flex items-center justify-center gap-2 rounded-xl bg-[#1A1A1A] hover:bg-[#CFAF53] text-white text-sm font-bold py-2.5 transition-colors cursor-pointer">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                        </svg>
                                        Buat Janji Temu
                                    </a>
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
                                <h3 class="text-lg font-extrabold text-[#1A1A1A]">Treatment tidak ditemukan</h3>
                                <p class="mt-2 text-sm text-slate-500 max-w-sm mx-auto leading-relaxed">
                                    Coba ubah kata kunci pencarian atau hapus beberapa filter agar hasil lebih banyak muncul.
                                </p>
                                <a href="{{ route('treatment.index') }}"
                                   class="mt-6 inline-flex items-center gap-2 rounded-full bg-[#CFAF53] hover:bg-[#b09444] text-white text-sm font-bold px-5 py-2.5 transition-colors cursor-pointer">
                                    Hapus Semua Filter
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                                </a>
                            </div>
                        </div>
                    @endforelse
                </div>

                @if($treatments->hasPages())
                    <div class="mt-10 border-t border-[#E8E0D8]/60 pt-8">
                        {{ $treatments->links() }}
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
