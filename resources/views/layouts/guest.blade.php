@props(['title' => 'Aishi Beauty'])

<!doctype html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>{{ $title }} - Aishi Beauty</title>
    
    {{-- Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        [x-cloak] { display: none !important; }
        /* Animasi Custom */
        .animate-fade-in-down { animation: fadeInDown 0.5s ease-out; }
        @keyframes fadeInDown {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>

<body class="min-h-screen bg-slate-50 text-slate-900 flex flex-col font-sans antialiased">

    {{-- HEADER (Sticky & Responsive) --}}
    <header x-data="{ open: false }" class="sticky top-0 z-50 w-full border-b border-slate-200 bg-white/90 backdrop-blur-md transition-all">
        <div class="mx-auto flex max-w-6xl items-center justify-between px-4 py-4">
            
            {{-- 1. LOGO --}}
            <a href="{{ route('landing') }}" class="flex items-center gap-2 font-bold tracking-tight text-xl text-slate-900 group shrink-0">
                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-[#CFAF53] text-white transition group-hover:bg-[#b59b48]">
                    A
                </span>
                <span>Aishi<span class="text-[#CFAF53]">Beauty</span></span>
            </a>

            {{-- 2. DESKTOP MENU (Hidden on Mobile) --}}
            <nav class="hidden md:flex items-center gap-6">
                
                @if (\Illuminate\Support\Facades\Route::has('shop.index'))
                    <a href="{{ route('shop.index') }}" 
                       class="text-sm font-medium transition hover:text-[#CFAF53] {{ request()->routeIs('shop.*') ? 'text-[#CFAF53]' : 'text-slate-600' }}">
                        Produk
                    </a>
                @endif

                @if (\Illuminate\Support\Facades\Route::has('appointment.create'))
                    <a href="{{ route('appointment.create') }}" 
                       class="text-sm font-medium transition hover:text-[#CFAF53] {{ request()->routeIs('appointment.*') ? 'text-[#CFAF53]' : 'text-slate-600' }}">
                        Booking
                    </a>
                @endif

                {{-- NOTIFICATION BELL (NEW) --}}
                <x-nav-notification />

                @if (\Illuminate\Support\Facades\Route::has('cart.index'))
                    <a href="{{ route('cart.index') }}" class="relative group">
                        <svg class="h-6 w-6 text-slate-600 transition group-hover:text-[#CFAF53]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                        </svg>
                        @php $cartCount = count(session('cart', [])); @endphp
                        @if($cartCount > 0)
                            <span class="absolute -right-2 -top-2 flex h-4 w-4 items-center justify-center rounded-full bg-rose-500 text-[10px] font-bold text-white">
                                {{ $cartCount }}
                            </span>
                        @endif
                    </a>
                @endif

                <div class="h-6 w-px bg-slate-200"></div>

                @auth
                    <a href="{{ url('/dashboard') }}" 
                       class="rounded-full bg-slate-100 px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-[#CFAF53] hover:text-white">
                        Dashboard
                    </a>
                @else
                    @if (\Illuminate\Support\Facades\Route::has('login'))
                        <a href="{{ route('login') }}" 
                           class="rounded-full bg-[#CFAF53] px-5 py-2 text-sm font-bold text-slate-900 transition hover:bg-[#b59b48] hover:shadow-lg">
                            Login
                        </a>
                    @endif
                @endauth
            </nav>

            {{-- 3. MOBILE HAMBURGER BUTTON (Visible on Mobile Only) --}}
            <div class="flex items-center gap-4 md:hidden">
                {{-- Notifikasi di Mobile (Icon Saja di luar menu burger agar mudah diakses) --}}
                <x-nav-notification />

                <button @click="open = !open" class="rounded-lg p-2 text-slate-600 hover:bg-slate-100 focus:outline-none">
                    <svg x-show="!open" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                    <svg x-cloak x-show="open" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>

        {{-- 4. MOBILE MENU DROPDOWN --}}
        <div x-show="open" x-transition 
             class="border-t border-slate-100 bg-white shadow-lg md:hidden">
            <div class="space-y-1 px-4 pb-4 pt-2">
                
                @if (\Illuminate\Support\Facades\Route::has('shop.index'))
                    <a href="{{ route('shop.index') }}" class="block rounded-lg px-3 py-2 text-base font-medium text-slate-700 hover:bg-slate-50 hover:text-[#CFAF53]">
                        Produk Skincare
                    </a>
                @endif

                @if (\Illuminate\Support\Facades\Route::has('appointment.create'))
                    <a href="{{ route('appointment.create') }}" class="block rounded-lg px-3 py-2 text-base font-medium text-slate-700 hover:bg-slate-50 hover:text-[#CFAF53]">
                        Booking Online
                    </a>
                @endif

                @if (\Illuminate\Support\Facades\Route::has('cart.index'))
                    <a href="{{ route('cart.index') }}" class="flex items-center justify-between rounded-lg px-3 py-2 text-base font-medium text-slate-700 hover:bg-slate-50 hover:text-[#CFAF53]">
                        <span>Keranjang Belanja</span>
                        @if($cartCount > 0)
                            <span class="rounded-full bg-rose-500 px-2 py-0.5 text-xs font-bold text-white">{{ $cartCount }}</span>
                        @endif
                    </a>
                @endif

                <div class="my-2 border-t border-slate-100"></div>

                @auth
                    <a href="{{ url('/dashboard') }}" class="block w-full rounded-lg bg-slate-100 px-3 py-2 text-center text-base font-bold text-slate-700 hover:bg-[#CFAF53] hover:text-white">
                        Ke Dashboard
                    </a>
                @else
                    @if (\Illuminate\Support\Facades\Route::has('login'))
                        <a href="{{ route('login') }}" class="block w-full rounded-lg bg-[#CFAF53] px-3 py-2 text-center text-base font-bold text-slate-900 hover:bg-[#b59b48]">
                            Login
                        </a>
                    @endif
                @endauth
            </div>
        </div>
    </header>

    {{-- MAIN CONTENT --}}
    <main class="flex-1">
        <div class="mx-auto max-w-6xl px-4 py-8">
            
            {{-- Flash Message: Success --}}
            @if (session('success'))
                <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)" 
                     class="mb-6 flex items-center gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 shadow-sm animate-fade-in-down">
                    <svg class="h-5 w-5 text-emerald-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            {{-- Flash Message: Error --}}
            @if (session('error'))
                <div x-data="{ show: true }" x-show="show" 
                     class="mb-6 flex items-center gap-3 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800 shadow-sm animate-fade-in-down">
                    <svg class="h-5 w-5 text-rose-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            {{-- Slot Content --}}
            {{ $slot }}
        </div>
    </main>

    {{-- FOOTER --}}
    <footer class="border-t border-slate-200 bg-white mt-auto">
        <div class="mx-auto max-w-6xl px-4 py-8 flex flex-col items-center justify-between gap-4 text-center sm:flex-row sm:text-left">
            <div>
                <p class="font-bold text-slate-900 flex items-center justify-center sm:justify-start gap-2">
                    <span class="flex h-5 w-5 items-center justify-center rounded bg-[#CFAF53] text-[10px] text-white">A</span>
                    Aishi Beauty Clinic
                </p>
                <p class="text-sm text-slate-500 mt-1">&copy; {{ date('Y') }} All rights reserved.</p>
            </div>
            
            <div class="flex flex-wrap justify-center items-center gap-4 sm:gap-6 text-sm text-slate-500">
                <a href="#" class="hover:text-[#CFAF53]">Privacy Policy</a>
                <a href="#" class="hover:text-[#CFAF53]">Terms</a>
                <span class="hidden sm:inline text-slate-300">|</span>
                <p class="font-medium text-[#CFAF53]">09:00 - 18:30 WIB</p>
            </div>
        </div>
    </footer>

</body>
</html>