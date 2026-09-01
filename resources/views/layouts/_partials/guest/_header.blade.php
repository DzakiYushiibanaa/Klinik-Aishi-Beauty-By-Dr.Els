<header x-data="{ open: false }" class="sticky top-0 z-50 w-full bg-white shadow-sm transition-all">
        <div class="mx-auto flex h-20 w-full max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">

            {{-- 1. LOGO (Left) --}}
            <div class="flex items-center shrink-0">
                <a href="{{ route('landing') }}" class="flex flex-col items-center leading-none">
                    <span class="font-serif text-2xl tracking-widest text-[#1A1A1A]">AISHI BEAUTY</span>
                    <span class="text-[8px] font-medium text-[#666] tracking-[0.3em] mt-1">BY DR. ELS</span>
                </a>
            </div>

            {{-- 2. DESKTOP MENU (Centered) --}}
            <nav class="hidden lg:flex items-center gap-10">
                @if (\Illuminate\Support\Facades\Route::has('treatment.index'))
                    <a href="{{ route('treatment.index') }}"
                        class="group relative text-xs font-semibold tracking-widest uppercase text-[#1A1A1A] hover:text-[#8C7A63] transition-colors {{ request()->routeIs('treatment.*') ? 'text-[#8C7A63]' : '' }}">
                        Treatment
                        <span
                            class="absolute -bottom-1.5 left-1/2 h-[2px] w-0 -translate-x-1/2 bg-[#8C7A63] transition-all duration-300 ease-out group-hover:w-full {{ request()->routeIs('treatment.*') ? 'w-full' : '' }}"></span>
                    </a>
                @endif

                @if (\Illuminate\Support\Facades\Route::has('shop.index'))
                    <a href="{{ route('shop.index') }}"
                        class="group relative text-xs font-semibold tracking-widest uppercase text-[#1A1A1A] hover:text-[#8C7A63] transition-colors {{ request()->routeIs('shop.*') ? 'text-[#8C7A63]' : '' }}">
                        Produk
                        <span
                            class="absolute -bottom-1.5 left-1/2 h-[2px] w-0 -translate-x-1/2 bg-[#8C7A63] transition-all duration-300 ease-out group-hover:w-full {{ request()->routeIs('shop.*') ? 'w-full' : '' }}"></span>
                    </a>
                @endif

                @if (\Illuminate\Support\Facades\Route::has('forum.index'))
                    <a href="{{ route('forum.index') }}"
                        class="group relative text-xs font-semibold tracking-widest uppercase text-[#1A1A1A] hover:text-[#8C7A63] transition-colors {{ request()->routeIs('forum.*') ? 'text-[#8C7A63]' : '' }}">
                        Forum
                        <span
                            class="absolute -bottom-1.5 left-1/2 h-[2px] w-0 -translate-x-1/2 bg-[#8C7A63] transition-all duration-300 ease-out group-hover:w-full {{ request()->routeIs('forum.*') ? 'w-full' : '' }}"></span>
                    </a>
                @endif

                <a href="{{ route('pra.create') }}"
                    class="group relative text-xs font-semibold tracking-widest uppercase text-[#1A1A1A] hover:text-[#8C7A63] transition-colors {{ request()->routeIs('pra.*') ? 'text-[#8C7A63]' : '' }}">
                    Pra Konsultasi
                    <span
                        class="absolute -bottom-1.5 left-1/2 h-[2px] w-0 -translate-x-1/2 bg-[#8C7A63] transition-all duration-300 ease-out group-hover:w-full {{ request()->routeIs('pra.*') ? 'w-full' : '' }}"></span>
                </a>
            </nav>

            {{-- 3. DESKTOP ICONS & CTA (Right) --}}
            <div class="hidden lg:flex items-center gap-6">

                {{-- NOTIFICATION ICON --}}
                <x-nav-notification />

                {{-- CART ICON --}}
                @if (\Illuminate\Support\Facades\Route::has('cart.index'))
                    <a href="{{ route('cart.index') }}"
                        class="relative group text-[#1A1A1A] hover:text-[#8C7A63] transition-colors">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                        </svg>
                        <span data-cart-count
                            class="absolute -right-2 -top-2 flex h-4 min-w-4 px-1 items-center justify-center rounded-full bg-[#8C7A63] text-[10px] font-bold text-white shadow-sm"
                            @if($cartCount === 0) style="display:none" @endif>
                            {{ $cartCount > 99 ? '99+' : $cartCount }}
                        </span>
                    </a>
                @endif

                {{-- Vertical Line Separator --}}
                <div class="h-5 w-px bg-[#E8E0D8]"></div>

                @auth
                    <a href="{{ url('/dashboard') }}"
                        class="rounded-full bg-[#1A1A1A] px-6 py-2.5 text-xs font-bold uppercase tracking-wider text-white transition hover:bg-[#333]">
                        DASHBOARD
                    </a>

                    {{-- Avatar Dropdown --}}
                    <div x-data="{ dropdownOpen: false }" class="relative ml-2">
                        <button @click="dropdownOpen = !dropdownOpen" @click.away="dropdownOpen = false"
                            class="flex items-center gap-2 focus:outline-none group">
                            @if(auth()->user()->pasienProfile?->foto_profil_path)
                                <img src="{{ Storage::url(auth()->user()->pasienProfile->foto_profil_path) }}" alt="Avatar"
                                    class="w-10 h-10 rounded-full object-cover border border-[#E8E0D8] group-hover:border-[#CFAF53] transition-colors shadow-sm">
                            @else
                                <div
                                    class="w-10 h-10 rounded-full bg-[#FAF8F5] border border-[#E8E0D8] group-hover:border-[#CFAF53] transition-colors flex items-center justify-center overflow-hidden shadow-sm">
                                    <span
                                        class="text-sm font-bold text-[#8C7A63] uppercase">{{ substr(auth()->user()->name, 0, 1) }}</span>
                                </div>
                            @endif
                            <span
                                class="text-sm font-bold text-[#1A1A1A] group-hover:text-[#CFAF53] transition-colors max-w-[120px] truncate">{{ auth()->user()->name }}</span>
                            <svg class="w-4 h-4 text-slate-400 group-hover:text-[#CFAF53] transition-colors" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>

                        {{-- Dropdown Menu --}}
                        <div x-show="dropdownOpen" x-transition:enter="transition ease-out duration-200"
                            x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                            x-transition:leave="transition ease-in duration-75"
                            x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
                            class="absolute right-0 mt-3 w-48 bg-white rounded-xl shadow-lg border border-[#E8E0D8]/60 py-2 z-50"
                            x-cloak>
                            <div class="px-4 py-2 border-b border-[#E8E0D8]/60 mb-1 block lg:hidden">
                                <p class="text-sm font-bold text-[#1A1A1A] truncate">{{ auth()->user()->name }}</p>
                                <p class="text-xs text-slate-500 truncate">{{ auth()->user()->email }}</p>
                            </div>
                            <a href="{{ url('/dashboard?tab=profile') }}"
                                class="block px-4 py-2.5 text-sm font-semibold text-[#1A1A1A] hover:bg-[#FAF8F5] hover:text-[#CFAF53] transition-colors">Akun
                                Saya</a>
                            <a href="{{ url('/dashboard?tab=orders') }}"
                                class="block px-4 py-2.5 text-sm font-semibold text-[#1A1A1A] hover:bg-[#FAF8F5] hover:text-[#CFAF53] transition-colors">Pesanan
                                Saya</a>
                            <div class="border-t border-[#E8E0D8]/60 mt-1 pt-1">
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit"
                                        class="w-full text-left px-4 py-2.5 text-sm font-bold text-red-600 hover:bg-red-50 transition-colors">
                                        Log Out
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @else
                    <a href="{{ route('login') }}"
                        class="text-xs font-bold tracking-widest uppercase text-[#1A1A1A] hover:text-[#8C7A63] transition-colors">
                        Masuk
                    </a>
                    @if (\Illuminate\Support\Facades\Route::has('register'))
                        <a href="{{ route('register') }}"
                            class="rounded-full bg-[#1A1A1A] px-6 py-2.5 text-xs font-bold uppercase tracking-wider text-white transition hover:bg-[#333]">
                            Daftar
                        </a>
                    @endif
                @endauth
            </div>

            {{-- 4. MOBILE HAMBURGER --}}
            <div class="flex items-center gap-4 lg:hidden">
                {{-- NOTIFICATION ICON --}}
                <x-nav-notification />

                @if (\Illuminate\Support\Facades\Route::has('cart.index'))
                    <a href="{{ route('cart.index') }}" class="relative text-[#1A1A1A]">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                        </svg>
                        <span data-cart-count
                            class="absolute -right-2 -top-2 flex h-4 min-w-4 px-1 items-center justify-center rounded-full bg-[#8C7A63] text-[10px] font-bold text-white shadow-sm"
                            @if($cartCount === 0) style="display:none"
                            @endif>{{ $cartCount > 99 ? '99+' : $cartCount }}</span>
                    </a>
                @endif

                <button @click="open = !open" class="text-[#1A1A1A] p-2 focus:outline-none">
                    <svg x-show="!open" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                    <svg x-cloak x-show="open" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>

        {{-- 5. MOBILE MENU DROPDOWN --}}
        <div x-show="open" x-transition class="absolute top-full left-0 w-full bg-white shadow-lg lg:hidden">
            <div class="flex flex-col space-y-4 px-6 py-6">
                @if (\Illuminate\Support\Facades\Route::has('treatment.index'))
                    <a href="{{ route('treatment.index') }}"
                        class="text-sm font-semibold uppercase tracking-wider text-[#1A1A1A]">Treatment</a>
                @endif
                @if (\Illuminate\Support\Facades\Route::has('shop.index'))
                    <a href="{{ route('shop.index') }}"
                        class="text-sm font-semibold uppercase tracking-wider text-[#1A1A1A]">Produk</a>
                @endif
                @if (\Illuminate\Support\Facades\Route::has('forum.index'))
                    <a href="{{ route('forum.index') }}"
                        class="text-sm font-semibold uppercase tracking-wider text-[#1A1A1A]">Forum</a>
                @endif
                <a href="{{ route('pra.create') }}"
                    class="text-sm font-semibold uppercase tracking-wider text-[#1A1A1A]">Pra Konsultasi</a>
                <hr class="border-gray-200">
                @auth
                    <div class="flex items-center gap-3 mb-2 px-2">
                        @if(auth()->user()->pasienProfile?->foto_profil_path)
                            <img src="{{ Storage::url(auth()->user()->pasienProfile->foto_profil_path) }}" alt="Avatar"
                                class="w-10 h-10 rounded-full object-cover border border-[#E8E0D8]">
                        @else
                            <div
                                class="w-10 h-10 rounded-full bg-[#FAF8F5] border border-[#E8E0D8] flex items-center justify-center">
                                <span
                                    class="text-sm font-bold text-[#8C7A63] uppercase">{{ substr(auth()->user()->name, 0, 1) }}</span>
                            </div>
                        @endif
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-bold text-[#1A1A1A] truncate">{{ auth()->user()->name }}</p>
                            <p class="text-xs text-slate-500 truncate">{{ auth()->user()->email }}</p>
                        </div>
                    </div>
                    <a href="{{ url('/dashboard') }}"
                        class="text-sm font-bold text-[#1A1A1A] px-2 py-2 hover:text-[#CFAF53]">DASHBOARD</a>
                    <a href="{{ url('/dashboard?tab=profile') }}"
                        class="text-sm font-semibold text-[#1A1A1A] px-2 py-2 hover:text-[#CFAF53]">Akun Saya</a>
                    <a href="{{ url('/dashboard?tab=orders') }}"
                        class="text-sm font-semibold text-[#1A1A1A] px-2 py-2 hover:text-[#CFAF53]">Pesanan Saya</a>
                    <hr class="border-gray-200 my-2">
                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <button type="submit"
                            class="w-full text-left text-sm font-bold text-red-600 px-2 py-2 hover:bg-red-50 rounded-lg">Log
                            Out</button>
                    </form>
                @else
                    <a href="{{ route('login') }}"
                        class="inline-block text-center rounded-xl border border-[#1A1A1A] px-6 py-3 text-sm font-bold uppercase tracking-wider text-[#1A1A1A]">Masuk</a>
                    @if (\Illuminate\Support\Facades\Route::has('register'))
                        <a href="{{ route('register') }}"
                            class="inline-block text-center rounded-full bg-[#1A1A1A] px-6 py-3 text-sm font-bold uppercase tracking-wider text-white">Daftar</a>
                    @endif
                @endauth
            </div>
        </div>
    </header>