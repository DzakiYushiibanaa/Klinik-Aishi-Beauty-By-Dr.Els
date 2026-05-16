<x-guest-layout title="Konfirmasi Password">

    <div class="flex min-h-[calc(100vh-100px)] items-center justify-center p-4">

        <div class="w-full max-w-5xl overflow-hidden rounded-3xl bg-white shadow-2xl transition-all grid grid-cols-1 md:grid-cols-2">

            {{-- KIRI: Branding --}}
            <div class="relative hidden md:flex flex-col justify-between bg-slate-900 p-10 text-white">
                <div class="absolute inset-0 z-0 bg-gradient-to-br from-slate-800 to-[#CFAF53]/20">
                    <img src="https://images.unsplash.com/photo-1552693673-1bf958298935?q=80&w=2073&auto=format&fit=crop"
                         alt="Background Konfirmasi Password"
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
                        Area Aman <br>
                        <span class="text-[#CFAF53]">Untuk Akun Anda.</span>
                    </h2>
                    <p class="mt-4 text-slate-300 text-sm leading-relaxed max-w-sm">
                        Konfirmasi ulang password Anda sebelum melanjutkan ke halaman ini. Langkah ini menjaga keamanan informasi sensitif.
                    </p>
                </div>

                <div class="relative z-10 text-xs text-slate-500">
                    &copy; {{ date('Y') }} Aishi Beauty By Dr.Els. All rights reserved.
                </div>
            </div>

            {{-- KANAN: Form --}}
            <div class="flex flex-col justify-center p-8 sm:p-12 lg:p-16 bg-white relative">

                <div class="w-full max-w-sm mx-auto">

                    <div class="mb-6 inline-flex items-center justify-center w-12 h-12 rounded-2xl bg-[#CFAF53]/10 text-[#CFAF53]">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                    </div>

                    <div class="mb-8">
                        <h3 class="text-2xl font-bold text-slate-900">Konfirmasi Password</h3>
                        <p class="text-slate-500 mt-2 text-sm leading-relaxed">
                            Halaman ini berisi data sensitif. Mohon konfirmasi password Anda terlebih dahulu sebelum melanjutkan.
                        </p>
                    </div>

                    <form method="POST" action="{{ route('password.confirm') }}" class="space-y-5">
                        @csrf

                        <div>
                            <label for="password" class="block text-xs font-bold uppercase tracking-wide text-slate-500 mb-2">Password Anda</label>
                            <x-password-input
                                id="password"
                                name="password"
                                autocomplete="current-password"
                                :autofocus="true"
                                placeholder="••••••••" />
                            <x-input-error :messages="$errors->get('password')" class="mt-2" />
                        </div>

                        <button type="submit" class="w-full flex justify-center items-center gap-2 rounded-xl bg-[#CFAF53] px-4 py-4 text-sm font-bold text-slate-900 shadow-lg shadow-[#CFAF53]/20 transition-all hover:bg-[#CFAF53]/90 hover:-translate-y-0.5 focus:outline-none focus:ring-2 focus:ring-[#CFAF53]/50 cursor-pointer">
                            Konfirmasi
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        </button>
                    </form>
                </div>
            </div>

        </div>
    </div>

</x-guest-layout>
