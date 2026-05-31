<x-guest-layout title="Verifikasi Email">

    <div class="flex min-h-[calc(100vh-100px)] items-center justify-center p-4">

        <div class="w-full max-w-5xl overflow-hidden rounded-3xl bg-white shadow-2xl transition-all grid grid-cols-1 md:grid-cols-2">

            {{-- KIRI: Branding --}}
            <div class="relative hidden md:flex flex-col justify-between bg-slate-900 p-10 text-white">
                <div class="absolute inset-0 z-0 bg-gradient-to-br from-slate-800 to-[#CFAF53]/20">
                    <img src="https://images.unsplash.com/photo-1552693673-1bf958298935?q=80&w=2073&auto=format&fit=crop"
                         alt="Background Verifikasi Email"
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
                        Selangkah Lagi <br>
                        <span class="text-[#CFAF53]">Sebelum Memulai.</span>
                    </h2>
                    <p class="mt-4 text-slate-300 text-sm leading-relaxed max-w-sm">
                        Verifikasi email Anda untuk mengamankan akun dan mendapatkan akses penuh ke seluruh layanan Aishi Beauty.
                    </p>
                </div>

                <div class="relative z-10 text-xs text-slate-500">
                    &copy; {{ date('Y') }} Aishi Beauty By Dr.Els. All rights reserved.
                </div>
            </div>

            {{-- KANAN: Konten --}}
            <div class="flex flex-col justify-center p-8 sm:p-12 lg:p-16 bg-white relative">

                <div class="w-full max-w-sm mx-auto">

                    <div class="mb-6 inline-flex items-center justify-center w-12 h-12 rounded-2xl bg-[#CFAF53]/10 text-[#CFAF53]">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                    </div>

                    <div class="mb-6">
                        <h3 class="text-2xl font-bold text-slate-900">Verifikasi Email Anda</h3>
                        <p class="text-slate-500 mt-2 text-sm leading-relaxed">
                            Terima kasih sudah mendaftar. Kami telah mengirim tautan verifikasi ke email Anda. Silakan buka email Anda dan klik tautan tersebut untuk mengaktifkan akun.
                        </p>
                        <p class="text-slate-500 mt-3 text-sm leading-relaxed">
                            Belum menerima email? Cek folder spam atau kirim ulang tautan dengan tombol di bawah.
                        </p>
                    </div>

                    @if (session('status') == 'verification-link-sent')
                        <div class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 flex items-start gap-3">
                            <svg class="w-5 h-5 text-emerald-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <p class="text-sm text-emerald-700 leading-relaxed">
                                Tautan verifikasi baru telah dikirim ke email Anda. Mohon cek pesan masuk.
                            </p>
                        </div>
                    @endif

                    <div class="space-y-3">
                        <form method="POST" action="{{ route('verification.send') }}">
                            @csrf
                            <button type="submit" class="w-full flex justify-center items-center gap-2 rounded-xl bg-[#CFAF53] px-4 py-4 text-sm font-bold text-slate-900 shadow-lg shadow-[#CFAF53]/20 transition-all hover:bg-[#CFAF53]/90 hover:-translate-y-0.5 focus:outline-none focus:ring-2 focus:ring-[#CFAF53]/50 cursor-pointer">
                                Kirim Ulang Tautan Verifikasi
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                            </button>
                        </form>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="w-full flex justify-center items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-3.5 text-sm font-bold text-slate-700 hover:bg-slate-50 transition-colors cursor-pointer">
                                Keluar
                            </button>
                        </form>
                    </div>
                </div>
            </div>

        </div>
    </div>

</x-guest-layout>
