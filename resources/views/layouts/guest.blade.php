@props(['title' => 'Aishi Beauty By Dr.Els'])

<!doctype html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>{{ $title }} - Aishi Beauty By Dr.Els</title>
    
    {{-- Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,600;0,700;1,400;1,600&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .font-serif { font-family: 'Playfair Display', serif; }
        [x-cloak] { display: none !important; }
        .animate-fade-in-down { animation: fadeInDown 0.5s ease-out; }
        @keyframes fadeInDown {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>

<body class="min-h-screen bg-[#FAF8F5] text-slate-900 flex flex-col font-sans antialiased">

    {{-- 🔥 LOGIC HITUNG JUMLAH CART 🔥 --}}
    @php
        $cartSession = session('cart', []);
        $cartCount = collect($cartSession)->sum('qty'); 
    @endphp

    {{-- HEADER --}}
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
                        <span class="absolute -bottom-1.5 left-1/2 h-[2px] w-0 -translate-x-1/2 bg-[#8C7A63] transition-all duration-300 ease-out group-hover:w-full {{ request()->routeIs('treatment.*') ? 'w-full' : '' }}"></span>
                    </a>
                @endif
                
                @if (\Illuminate\Support\Facades\Route::has('shop.index'))
                    <a href="{{ route('shop.index') }}" 
                       class="group relative text-xs font-semibold tracking-widest uppercase text-[#1A1A1A] hover:text-[#8C7A63] transition-colors {{ request()->routeIs('shop.*') ? 'text-[#8C7A63]' : '' }}">
                        Produk
                        <span class="absolute -bottom-1.5 left-1/2 h-[2px] w-0 -translate-x-1/2 bg-[#8C7A63] transition-all duration-300 ease-out group-hover:w-full {{ request()->routeIs('shop.*') ? 'w-full' : '' }}"></span>
                    </a>
                @endif

                @if (\Illuminate\Support\Facades\Route::has('forum.index'))
                    <a href="{{ route('forum.index') }}" 
                       class="group relative text-xs font-semibold tracking-widest uppercase text-[#1A1A1A] hover:text-[#8C7A63] transition-colors {{ request()->routeIs('forum.*') ? 'text-[#8C7A63]' : '' }}">
                        Forum
                        <span class="absolute -bottom-1.5 left-1/2 h-[2px] w-0 -translate-x-1/2 bg-[#8C7A63] transition-all duration-300 ease-out group-hover:w-full {{ request()->routeIs('forum.*') ? 'w-full' : '' }}"></span>
                    </a>
                @endif

                <a href="{{ route('pra.create') }}" 
                   class="group relative text-xs font-semibold tracking-widest uppercase text-[#1A1A1A] hover:text-[#8C7A63] transition-colors {{ request()->routeIs('pra.*') ? 'text-[#8C7A63]' : '' }}">
                    Pra Konsultasi
                    <span class="absolute -bottom-1.5 left-1/2 h-[2px] w-0 -translate-x-1/2 bg-[#8C7A63] transition-all duration-300 ease-out group-hover:w-full {{ request()->routeIs('pra.*') ? 'w-full' : '' }}"></span>
                </a>
            </nav>

            {{-- 3. DESKTOP ICONS & CTA (Right) --}}
            <div class="hidden lg:flex items-center gap-6">
                {{-- Search Icon --}}
                <button class="text-[#1A1A1A] hover:text-[#8C7A63] transition-colors">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </button>

                {{-- CART ICON --}}
                @if (\Illuminate\Support\Facades\Route::has('cart.index'))
                    <a href="{{ route('cart.index') }}" class="relative group text-[#1A1A1A] hover:text-[#8C7A63] transition-colors">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                        </svg>
                        @if($cartCount > 0)
                            <span class="absolute -right-2 -top-2 flex h-4 w-4 items-center justify-center rounded-full bg-[#8C7A63] text-[10px] font-bold text-white shadow-sm">
                                {{ $cartCount > 99 ? '99+' : $cartCount }}
                            </span>
                        @endif
                    </a>
                @endif

                @auth
                    <a href="{{ url('/dashboard') }}" class="rounded-full bg-[#1A1A1A] px-6 py-2.5 text-xs font-bold uppercase tracking-wider text-white transition hover:bg-[#333]">
                        Dashboard
                    </a>
                @else
                    @if (\Illuminate\Support\Facades\Route::has('appointment.create'))
                        <a href="{{ route('appointment.create') }}" class="rounded-full bg-[#1A1A1A] px-6 py-2.5 text-xs font-bold uppercase tracking-wider text-white transition hover:bg-[#333]">
                            Buat Janji Temu
                        </a>
                    @endif
                @endauth
            </div>

            {{-- 4. MOBILE HAMBURGER --}}
            <div class="flex items-center gap-4 lg:hidden">
                @if (\Illuminate\Support\Facades\Route::has('cart.index'))
                    <a href="{{ route('cart.index') }}" class="relative text-[#1A1A1A]">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" /></svg>
                        @if($cartCount > 0)
                            <span class="absolute -right-2 -top-2 flex h-4 w-4 items-center justify-center rounded-full bg-[#8C7A63] text-[10px] font-bold text-white shadow-sm">{{ $cartCount > 99 ? '99+' : $cartCount }}</span>
                        @endif
                    </a>
                @endif

                <button @click="open = !open" class="text-[#1A1A1A] p-2 focus:outline-none">
                    <svg x-show="!open" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" /></svg>
                    <svg x-cloak x-show="open" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>
        </div>

        {{-- 5. MOBILE MENU DROPDOWN --}}
        <div x-show="open" x-transition class="absolute top-full left-0 w-full bg-white shadow-lg lg:hidden">
            <div class="flex flex-col space-y-4 px-6 py-6">
                @if (\Illuminate\Support\Facades\Route::has('treatment.index'))
                    <a href="{{ route('treatment.index') }}" class="text-sm font-semibold uppercase tracking-wider text-[#1A1A1A]">Treatment</a>
                @endif
                @if (\Illuminate\Support\Facades\Route::has('shop.index'))
                    <a href="{{ route('shop.index') }}" class="text-sm font-semibold uppercase tracking-wider text-[#1A1A1A]">Produk</a>
                @endif
                <a href="{{ route('pra.index') }}" class="text-sm font-semibold uppercase tracking-wider text-[#1A1A1A]">Pra Konsultasi</a>
                <hr class="border-gray-200">
                @auth
                    <a href="{{ url('/dashboard') }}" class="inline-block text-center rounded-full bg-[#1A1A1A] px-6 py-3 text-sm font-bold uppercase tracking-wider text-white">Dashboard</a>
                @else
                    @if (\Illuminate\Support\Facades\Route::has('appointment.create'))
                        <a href="{{ route('appointment.create') }}" class="inline-block text-center rounded-full bg-[#1A1A1A] px-6 py-3 text-sm font-bold uppercase tracking-wider text-white">Buat Janji Temu</a>
                    @endif
                @endauth
            </div>
        </div>
    </header>

    {{-- MAIN CONTENT --}}
    <main class="flex-1 w-full flex flex-col">
        <div class="w-full">
            {{-- Flash Messages --}}

            {{ $slot }}
        </div>
    </main>

    {{-- DARK FOOTER --}}
    <footer class="bg-[#1C1A18] text-[#9CA3AF] py-16">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 grid grid-cols-1 md:grid-cols-4 gap-10">
            <div class="md:col-span-1">
                <a href="{{ route('landing') }}" class="flex flex-col mb-6">
                    <span class="font-serif text-2xl tracking-widest text-white">AISHI BEAUTY</span>
                    <span class="text-[8px] font-medium text-[#8C7A63] tracking-[0.3em] mt-1">BY DR. ELS</span>
                </a>
                <p class="text-sm leading-relaxed mb-6">
                    Klinik estetika terpercaya dengan pelayanan dokter dan terapis profesional untuk hasil kulit sehat maksimal.
                </p>
                <div class="flex gap-4">
                    {{-- Instagram --}}
                    <a href="https://www.instagram.com/aishibeauty_bydr.els" target="_blank" class="h-8 w-8 rounded-full border border-gray-700 flex items-center justify-center hover:border-[#8C7A63] hover:text-[#8C7A63] transition-colors"><svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg></a>
                    {{-- TikTok --}}
                    <a href="https://www.tiktok.com/@aishibeauty.id_official" target="_blank" class="h-8 w-8 rounded-full border border-gray-700 flex items-center justify-center hover:border-[#8C7A63] hover:text-[#8C7A63] transition-colors"><svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M19.59 6.69a4.83 4.83 0 01-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 01-2.88 2.5 2.89 2.89 0 01-2.89-2.89 2.89 2.89 0 012.89-2.89c.28 0 .54.04.79.1v-3.5a6.37 6.37 0 00-.79-.05A6.34 6.34 0 003.15 15.2a6.34 6.34 0 0010.86 4.48 6.3 6.3 0 001.86-4.49V8.74a8.18 8.18 0 004.72 1.5v-3.4a4.85 4.85 0 01-1-.15z"/></svg></a>
                </div>
            </div>
            <div>
                <h4 class="text-white font-bold mb-4 uppercase tracking-wider text-xs">Navigasi</h4>
                <ul class="space-y-3 text-sm">
                    <li><a href="{{ route('appointment.create') }}" class="hover:text-white transition">Buat Janji Temu</a></li>
                    <li><a href="{{ route('treatment.index') }}" class="hover:text-white transition">Treatment</a></li>
                    <li><a href="{{ route('shop.index') }}" class="hover:text-white transition">Produk Skincare</a></li>
                    <li><a href="{{ route('pra.index') }}" class="hover:text-white transition">Konsultasi Online</a></li>
                </ul>
            </div>
            <div>
                <h4 class="text-white font-bold mb-4 uppercase tracking-wider text-xs">Layanan</h4>
                <ul class="space-y-3 text-sm">
                    <li><a href="#" class="hover:text-white transition">Facial Treatment</a></li>
                    <li><a href="#" class="hover:text-white transition">Laser & Light</a></li>
                    <li><a href="#" class="hover:text-white transition">Anti Aging</a></li>
                    <li><a href="#" class="hover:text-white transition">Acne Solution</a></li>
                </ul>
            </div>
            <div>
                <h4 class="text-white font-bold mb-4 uppercase tracking-wider text-xs">Informasi</h4>
                <ul class="space-y-3 text-sm">
                    <li><a href="#" class="hover:text-white transition">Kebijakan Privasi</a></li>
                    <li><a href="#" class="hover:text-white transition">Syarat & Ketentuan</a></li>
                    <li><a href="#" class="hover:text-white transition">FAQ</a></li>
                </ul>
            </div>
        </div>
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 mt-12 pt-8 border-t border-gray-800 text-xs text-center md:text-left flex flex-col md:flex-row justify-between items-center">
            <p>&copy; {{ date('Y') }} Aishi Beauty By Dr.Els. All rights reserved.</p>
        </div>
    </footer>

    {{-- Floating WhatsApp Chat Widget --}}
    <div id="wa-widget" x-data="{ open: false }" style="position:fixed; bottom:24px; right:24px; z-index:2147483647;">
        
        {{-- Chat Popup --}}
        <div x-show="open" x-cloak
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-4 scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0 scale-100"
             x-transition:leave-end="opacity-0 translate-y-4 scale-95"
             @click.away="open = false"
             style="position:absolute; bottom:72px; right:0; width:340px; border-radius:16px; overflow:hidden; background:#fff; box-shadow: 0 20px 60px rgba(0,0,0,0.3), 0 0 0 1px rgba(0,0,0,0.05); z-index:2147483647;">

            {{-- Header --}}
            <div style="background:#075E54; padding:16px 20px; display:flex; align-items:center; justify-content:space-between;">
                <div style="display:flex; align-items:center; gap:12px;">
                    <div style="width:42px; height:42px; border-radius:50%; background:rgba(255,255,255,0.2); display:flex; align-items:center; justify-content:center;">
                        <svg style="width:22px; height:22px; color:#fff;" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/></svg>
                    </div>
                    <div>
                        <p style="color:#fff; font-weight:700; font-size:14px; margin:0;">Aishi Beauty</p>
                        <p style="color:#a7f3d0; font-size:11px; margin:2px 0 0 0;">Biasanya membalas dalam 1 jam</p>
                    </div>
                </div>
                <button @click="open = false" style="color:rgba(255,255,255,0.7); background:none; border:none; cursor:pointer; padding:4px;">
                    <svg style="width:20px; height:20px;" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            {{-- Chat Body --}}
            <div style="background:#ECE5DD; padding:24px 20px; min-height:120px;">
                <div style="background:#fff; border-radius:0 12px 12px 12px; padding:12px 16px; max-width:85%; box-shadow:0 1px 2px rgba(0,0,0,0.08);">
                    <p style="font-size:13px; color:#1f2937; font-weight:600; margin:0 0 4px 0;">Aishi Beauty</p>
                    <p style="font-size:13px; color:#4b5563; line-height:1.6; margin:0;">Halo! 👋<br>Ada yang bisa kami bantu? Silakan chat untuk konsultasi atau informasi lebih lanjut.</p>
                    <p style="font-size:10px; color:#9ca3af; text-align:right; margin:6px 0 0 0;">{{ now()->format('H:i') }}</p>
                </div>
            </div>

            {{-- Action --}}
            <div style="background:#fff; padding:16px 20px; border-top:1px solid #f3f4f6;">
                <a href="https://wa.me/+6285780050007?text=Halo%20Aishi%20Beauty%2C%20saya%20ingin%20bertanya%20tentang%20layanan%20klinik." 
                   target="_blank"
                   style="display:flex; align-items:center; justify-content:center; gap:8px; background:#25D366; color:#fff; font-weight:700; font-size:14px; padding:12px; border-radius:12px; text-decoration:none;"
                   onmouseover="this.style.background='#1EBE5B'" onmouseout="this.style.background='#25D366'">
                    <svg style="width:20px; height:20px;" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                    Mulai Chat
                </a>
                <p style="font-size:10px; color:#9ca3af; text-align:center; margin:8px 0 0 0;">Powered by WhatsApp</p>
            </div>
        </div>

        {{-- Floating Green Button --}}
        <button @click="open = !open" 
                style="width:56px; height:56px; border-radius:50%; background:#25D366; border:none; cursor:pointer; display:flex; align-items:center; justify-content:center; box-shadow:0 4px 15px rgba(37,211,102,0.4); transition:all 0.3s;"
                onmouseover="this.style.background='#1EBE5B'; this.style.transform='scale(1.1)'" 
                onmouseout="this.style.background='#25D366'; this.style.transform='scale(1)'"
                aria-label="Chat via WhatsApp">
            <template x-if="!open">
                <svg style="width:28px; height:28px; color:#fff;" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
            </template>
            <template x-if="open">
                <svg style="width:24px; height:24px; color:#fff;" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </template>
        </button>
    </div>
    
    <x-toast />

</body>
</html>