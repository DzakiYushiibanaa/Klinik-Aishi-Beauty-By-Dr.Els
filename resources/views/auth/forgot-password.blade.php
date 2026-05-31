<x-guest-layout title="Lupa Password">

    {{-- Container Full Screen (Center Alignment) --}}
    <div class="flex min-h-[calc(100vh-100px)] items-center justify-center p-4">

        {{-- Card Utama (Grid 2 Kolom) --}}
        <div class="w-full max-w-5xl overflow-hidden rounded-3xl bg-white shadow-2xl transition-all grid grid-cols-1 md:grid-cols-2">

            {{-- KOLOM KIRI: Visual & Branding --}}
            <div class="relative hidden md:flex flex-col justify-between bg-slate-900 p-10 text-white">
                <div class="absolute inset-0 z-0 bg-gradient-to-br from-slate-800 to-[#CFAF53]/20">
                    <img src="https://images.unsplash.com/photo-1552693673-1bf958298935?q=80&w=2073&auto=format&fit=crop"
                         alt="Background Lupa Password"
                         class="h-full w-full object-cover opacity-50 grayscale transition duration-700 hover:grayscale-0 hover:opacity-70 hover:scale-105">
                    <div class="absolute inset-0 bg-gradient-to-t from-slate-900 via-slate-900/40 to-transparent"></div>
                </div>

                <div class="relative z-10">
                    <a href="{{ route('landing') }}" class="flex items-center gap-2 font-bold text-2xl tracking-tight text-white">
                        <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-[#CFAF53] text-white">A</span>
                        <span>Aishi<span class="text-[#CFAF53]">Beauty</span></span>
                    </a>
                </div>

                <div class="relative z-10 mb-6">
                    <h2 class="text-3xl font-bold leading-tight">
                        Tenang, Akun Anda <br>
                        <span class="text-[#CFAF53]">Aman Bersama Kami.</span>
                    </h2>
                    <p class="mt-4 text-slate-300 text-sm leading-relaxed max-w-sm">
                        Kami akan kirim tautan reset ke email Anda. Cukup buka pesan masuk dan ikuti petunjuknya untuk membuat password baru.
                    </p>
                </div>

                <div class="relative z-10 text-xs text-slate-500">
                    &copy; {{ date('Y') }} Aishi Beauty By Dr.Els. All rights reserved.
                </div>
            </div>

            {{-- KOLOM KANAN: Form --}}
            <div class="flex flex-col justify-center p-8 sm:p-12 lg:p-16 bg-white relative">

                {{-- Tombol Balik (Mobile Only) --}}
                <a href="{{ route('login') }}" class="absolute top-6 right-6 md:hidden text-slate-400 hover:text-slate-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </a>

                <div class="w-full max-w-sm mx-auto">

                    {{-- Icon --}}
                    <div class="mb-6 inline-flex items-center justify-center w-12 h-12 rounded-2xl bg-[#CFAF53]/10 text-[#CFAF53]">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"/>
                        </svg>
                    </div>

                    {{-- Header Form --}}
                    <div class="mb-8">
                        <h3 class="text-2xl font-bold text-slate-900">Lupa Password?</h3>
                        <p class="text-slate-500 mt-2 text-sm leading-relaxed">
                            Masukkan email yang terdaftar di akun Anda. Kami akan mengirim tautan untuk membuat password baru.
                        </p>
                    </div>

                    {{-- Session Status --}}
                    @if (session('status'))
                        <div class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 flex items-start gap-3">
                            <svg class="w-5 h-5 text-emerald-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <p class="text-sm text-emerald-700 leading-relaxed">{{ session('status') }}</p>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('password.email') }}" class="space-y-5">
                        @csrf

                        <div>
                            <label for="email" class="block text-xs font-bold uppercase tracking-wide text-slate-500 mb-2">Email</label>
                            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                                   class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3.5 text-sm text-slate-900 placeholder-slate-400 focus:border-[#CFAF53] focus:bg-white focus:ring-2 focus:ring-[#CFAF53]/20 transition-all"
                                   placeholder="nama@email.com">
                            <x-input-error :messages="$errors->get('email')" class="mt-2" />
                        </div>

                        <button type="submit" class="w-full flex justify-center items-center gap-2 rounded-xl bg-[#CFAF53] px-4 py-4 text-sm font-bold text-slate-900 shadow-lg shadow-[#CFAF53]/20 transition-all hover:bg-[#CFAF53]/90 hover:-translate-y-0.5 focus:outline-none focus:ring-2 focus:ring-[#CFAF53]/50 cursor-pointer">
                            Kirim Tautan Reset
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                        </button>
                    </form>

                    {{-- Footer: Back to Login --}}
                    <div class="mt-8 text-center">
                        <p class="text-sm text-slate-500">
                            Ingat password Anda?
                            <a href="{{ route('login') }}" class="font-bold text-slate-900 hover:text-[#CFAF53] transition underline decoration-slate-300 hover:decoration-[#CFAF53] underline-offset-4">
                                Kembali ke Login
                            </a>
                        </p>
                    </div>
                </div>
            </div>

        </div>
    </div>

</x-guest-layout>
