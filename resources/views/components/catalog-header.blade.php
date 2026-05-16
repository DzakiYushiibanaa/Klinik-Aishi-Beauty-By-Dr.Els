@props([
    'title',
    'subtitle' => null,
    'breadcrumbCurrent',
    'totalCount'    => null,
    'firstItem'     => null,
    'lastItem'      => null,
    'searchRoute',
    'searchPlaceholder' => 'Cari...',
    'extraSearchParams' => [],   // assoc array of name => value to keep in search form
    'sortOptions'   => [],       // assoc array of value => label, optional
    'currentSort'   => null,
    'mobileFilterToggle' => true,
])

{{--
    Catalog header — slim, modern, single-row toolbar yang menggabungkan:
    - Title + breadcrumb compact
    - Hitungan hasil ("Menampilkan 1-12 dari 14")
    - Search inline pill
    - Sort dropdown (opsional)
    - Tombol Filter mobile

    Konsisten dipakai di shop, treatment, dan bundle index. Mengganti banner
    besar yang sebelumnya banyak makan tempat tanpa info berguna.
--}}

<section class="mb-8">

    {{-- Breadcrumb + Title baris atas --}}
    <div class="flex flex-col gap-3 mb-6 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <nav class="flex items-center gap-1.5 text-xs font-semibold text-slate-400 uppercase tracking-widest mb-2" aria-label="Breadcrumb">
                <a href="{{ route('landing') }}" class="hover:text-[#CFAF53] transition-colors">Home</a>
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
                <span class="text-[#1A1A1A]" aria-current="page">{{ $breadcrumbCurrent }}</span>
            </nav>

            <h1 class="text-3xl sm:text-4xl font-black tracking-tight text-[#1A1A1A] leading-tight">
                {{ $title }}
            </h1>

            @if($subtitle)
                <p class="mt-2 text-sm text-slate-500 max-w-xl leading-relaxed">{{ $subtitle }}</p>
            @endif
        </div>

        @if(!is_null($totalCount))
            <p class="text-xs font-semibold tracking-wide text-slate-500 sm:text-right whitespace-nowrap">
                <span class="text-[#CFAF53] font-bold">{{ $firstItem ?? 0 }}–{{ $lastItem ?? 0 }}</span>
                <span class="text-slate-400">dari</span>
                <span class="font-bold text-[#1A1A1A]">{{ $totalCount }}</span>
                hasil
            </p>
        @endif
    </div>

    {{-- Toolbar: search + sort + mobile filter --}}
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center">

        {{-- Search inline --}}
        <form action="{{ $searchRoute }}" method="GET" class="relative flex-1 sm:max-w-md">
            @foreach($extraSearchParams as $name => $value)
                @if(is_array($value))
                    @foreach($value as $v)
                        <input type="hidden" name="{{ $name }}[]" value="{{ $v }}">
                    @endforeach
                @else
                    <input type="hidden" name="{{ $name }}" value="{{ $value }}">
                @endif
            @endforeach

            <input type="text"
                   name="search"
                   value="{{ request('search') }}"
                   placeholder="{{ $searchPlaceholder }}"
                   aria-label="Cari"
                   class="w-full rounded-full border border-[#E8E0D8] bg-white pl-12 pr-12 py-3 text-sm text-[#1A1A1A] placeholder:text-slate-400 shadow-sm focus:border-[#CFAF53] focus:ring-2 focus:ring-[#CFAF53]/20 transition-all">

            <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400 pointer-events-none">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </span>

            @if(request('search'))
                <a href="{{ $searchRoute . '?' . http_build_query(request()->except(['search', 'page'])) }}"
                   class="absolute inset-y-0 right-3 flex items-center text-slate-400 hover:text-rose-500 transition-colors"
                   aria-label="Hapus pencarian">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </a>
            @endif
        </form>

        <div class="flex items-center gap-3">
            {{-- Sort dropdown (opsional) --}}
            @if(!empty($sortOptions))
                <div class="relative" x-data="{ openSort: false }" @click.away="openSort = false">
                    <button type="button" @click="openSort = !openSort"
                            class="inline-flex items-center gap-2 rounded-full border border-[#E8E0D8] bg-white px-4 py-2.5 text-sm font-bold text-[#1A1A1A] hover:border-[#CFAF53] hover:bg-[#CFAF53]/5 transition-colors cursor-pointer">
                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4h13M3 8h9m-9 4h6m4 0l4-4m0 0l4 4m-4-4v12"/>
                        </svg>
                        <span class="hidden sm:inline">Urutkan:</span>
                        <span>{{ $sortOptions[$currentSort] ?? reset($sortOptions) }}</span>
                        <svg class="w-4 h-4 text-slate-400 transition-transform" :class="openSort && 'rotate-180'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>

                    <div x-show="openSort"
                         x-transition:enter="transition ease-out duration-150"
                         x-transition:enter-start="opacity-0 scale-95"
                         x-transition:enter-end="opacity-100 scale-100"
                         x-cloak
                         class="absolute right-0 mt-2 w-56 origin-top-right rounded-2xl bg-white border border-[#E8E0D8] shadow-lg overflow-hidden z-30">
                        @foreach($sortOptions as $value => $label)
                            <a href="{{ $searchRoute . '?' . http_build_query(array_merge(request()->except('page'), ['sort' => $value])) }}"
                               class="flex items-center justify-between px-4 py-3 text-sm font-medium transition-colors {{ $currentSort === $value ? 'bg-[#CFAF53]/10 text-[#1A1A1A] font-bold' : 'text-slate-600 hover:bg-[#FAF8F5] hover:text-[#1A1A1A]' }}">
                                {{ $label }}
                                @if($currentSort === $value)
                                    <svg class="w-4 h-4 text-[#CFAF53]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                    </svg>
                                @endif
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Tombol Filter Mobile --}}
            @if($mobileFilterToggle)
                <button type="button"
                        @click="mobileFilterOpen = !mobileFilterOpen"
                        class="lg:hidden inline-flex items-center gap-2 rounded-full border border-[#E8E0D8] bg-white px-4 py-2.5 text-sm font-bold text-[#1A1A1A] hover:border-[#CFAF53] hover:bg-[#CFAF53]/5 transition-colors cursor-pointer">
                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                    </svg>
                    <span x-text="mobileFilterOpen ? 'Tutup' : 'Filter'"></span>
                </button>
            @endif
        </div>
    </div>
</section>
