<x-guest-layout title="Buat Akun">

    {{-- Container Full Screen --}}
    <div class="flex min-h-[calc(100vh-100px)] items-center justify-center p-4">
        
        {{-- Card Utama (Grid 2 Kolom) --}}
        <div class="w-full max-w-5xl overflow-hidden rounded-3xl bg-white shadow-2xl transition-all grid grid-cols-1 md:grid-cols-2">
            
            {{-- KOLOM KIRI: Visual & Branding (Gradient Luxury) --}}
            <x-auth-brand-panel
                titleLine1="Mulai Perjalanan"
                titleHighlight="Kulit Sehatmu."
                description="Buat akun untuk akses mudah ke jadwal booking, konsultasi dokter, dan riwayat perawatan Anda di klinik kami."
            />

            {{-- KOLOM KANAN: Form Register --}}
            <div class="flex flex-col justify-center p-8 sm:p-12 lg:p-16 bg-white relative">
                
                {{-- Tombol Balik (Mobile Only) --}}
                <a href="{{ route('landing') }}" class="absolute top-6 right-6 md:hidden text-slate-400 hover:text-slate-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </a>

                <div class="w-full max-w-sm mx-auto">
                    {{-- Header Form --}}
                    <div class="mb-8">
                        <h3 class="text-2xl font-bold text-slate-900">Registrasi Akun</h3>
                        <p class="text-slate-500 mt-1 text-sm">Isi data diri di bawah ini untuk mendaftar.</p>
                    </div>

                    <form method="POST" action="{{ route('register') }}" class="space-y-5"
                          x-data="{
                              password: '',
                              passwordConfirm: '',
                              touchedPwd: false,
                              touchedConfirm: false,
                              get pwdTooShort() { return this.touchedPwd && this.password.length > 0 && this.password.length < 8; },
                              get confirmMismatch() { return this.touchedConfirm && this.passwordConfirm.length > 0 && this.password !== this.passwordConfirm; }
                          }">
                        @csrf

                        <div>
                            <label for="name" class="block text-xs font-bold uppercase tracking-wide text-slate-500 mb-2">Nama Lengkap</label>
                            <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus autocomplete="name"
                                class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3.5 text-sm text-slate-900 placeholder-slate-400 focus:border-[#CFAF53] focus:bg-white focus:ring-2 focus:ring-[#CFAF53]/20 transition-all"
                                placeholder="Cth: Siti Aminah">
                            <x-input-error :messages="$errors->get('name')" class="mt-2" />
                        </div>

                        <div>
                            <label for="email" class="block text-xs font-bold uppercase tracking-wide text-slate-500 mb-2">Alamat Email</label>
                            <input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="username"
                                class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3.5 text-sm text-slate-900 placeholder-slate-400 focus:border-[#CFAF53] focus:bg-white focus:ring-2 focus:ring-[#CFAF53]/20 transition-all"
                                placeholder="nama@email.com">
                            <x-input-error :messages="$errors->get('email')" class="mt-2" />
                        </div>

                        <div>
                            <label for="password" class="block text-xs font-bold uppercase tracking-wide text-slate-500 mb-2">Kata Sandi</label>
                            <x-password-input
                                id="password"
                                name="password"
                                autocomplete="new-password"
                                placeholder="Minimal 8 karakter"
                                x-model="password"
                                @input="touchedPwd = true" />
                            <p x-cloak x-show="pwdTooShort" x-transition.opacity
                               class="mt-2 text-xs font-medium text-rose-600">
                                Password minimal 8 karakter.
                            </p>
                            <x-input-error :messages="$errors->get('password')" class="mt-2" />
                        </div>

                        <div>
                            <label for="password_confirmation" class="block text-xs font-bold uppercase tracking-wide text-slate-500 mb-2">Konfirmasi Sandi</label>
                            <x-password-input
                                id="password_confirmation"
                                name="password_confirmation"
                                autocomplete="new-password"
                                placeholder="Ulangi kata sandi"
                                x-model="passwordConfirm"
                                @input="touchedConfirm = true" />
                            <p x-cloak x-show="confirmMismatch" x-transition.opacity
                               class="mt-2 text-xs font-medium text-rose-600">
                                Konfirmasi password tidak cocok.
                            </p>
                            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
                        </div>

                        <div class="pt-2">
                            <button type="submit" class="w-full flex justify-center items-center gap-2 rounded-xl bg-[#CFAF53] px-4 py-4 text-sm font-bold text-slate-900 shadow-lg shadow-[#CFAF53]/20 transition-all hover:bg-[#CFAF53]/90 hover:-translate-y-0.5 focus:outline-none focus:ring-2 focus:ring-[#CFAF53]/50">
                                Buat Akun
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                            </button>
                        </div>
                    </form>

                    {{-- Divider "atau" --}}
                    <div class="auth-divider mt-6">
                        <span class="auth-divider__line"></span>
                        <span class="auth-divider__text">atau</span>
                        <span class="auth-divider__line"></span>
                    </div>

                    {{-- Google OAuth button --}}
                    <div class="mt-4">
                        <x-google-auth-button label="Daftar dengan Google" />
                    </div>

                    {{-- Footer: Login Link --}}
                    <div class="mt-8 text-center">
                        <p class="text-sm text-slate-500">
                            Sudah punya akun? 
                            <a href="{{ route('login') }}" class="font-bold text-slate-900 hover:text-[#CFAF53] transition underline decoration-slate-300 hover:decoration-[#CFAF53] underline-offset-4">
                                Masuk Disini
                            </a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-guest-layout>