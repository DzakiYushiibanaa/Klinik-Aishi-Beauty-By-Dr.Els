<x-guest-layout title="Login Pasien">
    
    {{-- Container Full Screen (Center Alignment) --}}
    <div class="flex min-h-[calc(100vh-100px)] items-center justify-center p-4">
        
        {{-- Card Utama (Grid 2 Kolom) --}}
        <div class="w-full max-w-5xl overflow-hidden rounded-3xl bg-white shadow-2xl transition-all grid grid-cols-1 md:grid-cols-2">
            
            {{-- KOLOM KIRI: Visual & Branding (Hidden di Mobile) --}}
            <div class="relative hidden md:flex flex-col justify-between bg-slate-900 p-10 text-white">
                
                {{-- Background Image (Ganti src dengan foto klinik kamu) --}}
                <div class="absolute inset-0 z-0 bg-gradient-to-br from-slate-800 to-[#CFAF53]/20">
                    {{-- Placeholder Image (Bisa diganti asset('img/login-bg.jpg')) --}}
                    <img src="https://images.unsplash.com/photo-1552693673-1bf958298935?q=80&w=2073&auto=format&fit=crop" 
                        alt="Background Register" 
                        class="h-full w-full object-cover opacity-50 grayscale transition duration-700 hover:grayscale-0 hover:opacity-70 hover:scale-105">

                    {{-- Overlay Gradient biar teks kebaca --}}
                    <div class="absolute inset-0 bg-gradient-to-t from-slate-900 via-slate-900/40 to-transparent"></div>
                </div>

                {{-- Konten di atas Gambar --}}
                <div class="relative z-10">
                    <a href="{{ route('landing') }}" class="flex items-center gap-2 font-bold text-2xl tracking-tight text-white">
                        <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-[#CFAF53] text-white">A</span>
                        <span>Aishi<span class="text-[#CFAF53]">Beauty</span></span>
                    </a>
                </div>

                <div class="relative z-10 mb-6">
                    <h2 class="text-3xl font-bold leading-tight">
                        Wujudkan Kulit Sehat <br>
                        <span class="text-[#CFAF53]">Impianmu Hari Ini.</span>
                    </h2>
                    <p class="mt-4 text-slate-300 text-sm leading-relaxed max-w-sm">
                        Masuk untuk mengatur jadwal konsultasi, melihat riwayat perawatan, dan memesan produk favoritmu dengan mudah.
                    </p>
                </div>

                <div class="relative z-10 text-xs text-slate-500">
                    &copy; {{ date('Y') }} Aishi Beauty Clinic. All rights reserved.
                </div>
            </div>

            {{-- KOLOM KANAN: Form Login --}}
            <div class="flex flex-col justify-center p-8 sm:p-12 lg:p-16 bg-white relative">
                
                {{-- Tombol Balik (Mobile Only) --}}
                <a href="{{ route('landing') }}" class="absolute top-6 right-6 md:hidden text-slate-400 hover:text-slate-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </a>

                <div class="w-full max-w-sm mx-auto">
                    {{-- Header Form --}}
                    <div class="mb-8">
                        <h3 class="text-2xl font-bold text-slate-900">Selamat Datang</h3>
                        <p class="text-slate-500 mt-1 text-sm">Silakan masukkan detail akun Anda.</p>
                    </div>

                    {{-- Session Status --}}
                    <x-auth-session-status class="mb-6" :status="session('status')" />

                    <form method="POST" action="{{ route('login') }}" class="space-y-5">
                        @csrf

                        <div>
                            <label for="email" class="block text-xs font-bold uppercase tracking-wide text-slate-500 mb-2">Email</label>
                            <input id="email" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" 
                                class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3.5 text-sm text-slate-900 placeholder-slate-400 focus:border-[#CFAF53] focus:bg-white focus:ring-2 focus:ring-[#CFAF53]/20 transition-all"
                                placeholder="nama@email.com">
                            <x-input-error :messages="$errors->get('email')" class="mt-2" />
                        </div>

                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <label for="password" class="block text-xs font-bold uppercase tracking-wide text-slate-500">Kata Sandi</label>
                                @if (Route::has('password.request'))
                                    <a href="{{ route('password.request') }}" class="text-xs font-medium text-[#CFAF53] hover:text-[#b59b48] hover:underline transition">
                                        Lupa sandi?
                                    </a>
                                @endif
                            </div>
                            <input id="password" type="password" name="password" required autocomplete="current-password"
                                class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3.5 text-sm text-slate-900 placeholder-slate-400 focus:border-[#CFAF53] focus:bg-white focus:ring-2 focus:ring-[#CFAF53]/20 transition-all"
                                placeholder="••••••••">
                            <x-input-error :messages="$errors->get('password')" class="mt-2" />
                        </div>

                        <div class="flex items-center justify-between">
                            <label for="remember_me" class="inline-flex items-center cursor-pointer group">
                                <input id="remember_me" type="checkbox" name="remember" 
                                    class="rounded border-slate-300 text-[#CFAF53] shadow-sm focus:ring-[#CFAF53]/40 transition cursor-pointer h-4 w-4">
                                <span class="ms-2 text-sm text-slate-600 group-hover:text-slate-900 transition">Ingat saya</span>
                            </label>
                        </div>

                        <button type="submit" class="w-full flex justify-center items-center gap-2 rounded-xl bg-[#CFAF53] px-4 py-4 text-sm font-bold text-slate-900 shadow-lg shadow-[#CFAF53]/20 transition-all hover:bg-[#CFAF53]/90 hover:-translate-y-0.5 focus:outline-none focus:ring-2 focus:ring-[#CFAF53]/50">
                            Masuk Sekarang
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                        </button>
                    </form>

                    {{-- Footer: Register Link --}}
                    <div class="mt-8 text-center">
                        <p class="text-sm text-slate-500">
                            Belum punya akun pasien? 
                            @if (Route::has('register'))
                                <a href="{{ route('register') }}" class="font-bold text-slate-900 hover:text-[#CFAF53] transition underline decoration-slate-300 hover:decoration-[#CFAF53] underline-offset-4">
                                    Daftar Gratis
                                </a>
                            @endif
                        </p>
                    </div>
                </div>
            </div>

        </div>
    </div>

</x-guest-layout>