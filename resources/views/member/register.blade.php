<x-guest-layout title="Daftar Member">
    
    {{-- BACKGROUND ACCENT --}}
    <div class="absolute top-0 left-0 w-full h-64 bg-[#CFAF53]/10 -z-10"></div>

    <div class="max-w-5xl mx-auto px-4 py-12">
        
        {{-- Header --}}
        <div class="text-center mb-10">
            <h1 class="text-3xl font-extrabold text-slate-900">Bergabung Menjadi Member</h1>
            <p class="text-slate-500 mt-2">Lengkapi data kesehatan kulit Anda untuk mendapatkan layanan yang lebih personal.</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            {{-- KOLOM KIRI: INFO BENEFIT --}}
            <div class="lg:col-span-1 space-y-6">
                {{-- Kartu Biaya --}}
                <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100 text-center lg:text-left">
                    <div class="w-12 h-12 bg-[#CFAF53]/20 rounded-full flex items-center justify-center text-[#8B6F1B] mb-4 mx-auto lg:mx-0">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <h3 class="font-bold text-slate-800 text-lg mb-2">Biaya Pendaftaran</h3>
                    <p class="text-3xl font-extrabold text-[#CFAF53]">Rp 50.000</p>
                    <p class="text-xs text-slate-400 mt-1">Dibayar sekali untuk keanggotaan seumur hidup.</p>
                </div>

                {{-- Kartu Benefit --}}
                <div class="bg-[#CFAF53] rounded-2xl p-6 shadow-lg text-slate-900">
                    <h3 class="font-bold text-lg mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg>
                        Benefit Member
                    </h3>
                    <ul class="space-y-3 text-sm font-medium">
                        <li class="flex items-start gap-2">
                            <svg class="w-4 h-4 mt-0.5 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            Diskon Eksklusif Treatment & Produk
                        </li>
                        <li class="flex items-start gap-2">
                            <svg class="w-4 h-4 mt-0.5 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            Prioritas Booking Jadwal
                        </li>
                        <li class="flex items-start gap-2">
                            <svg class="w-4 h-4 mt-0.5 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            Akses Promo Ulang Tahun
                        </li>
                    </ul>
                </div>
            </div>

            {{-- KOLOM KANAN: FORMULIR LENGKAP --}}
            <div class="lg:col-span-2">
                <div class="bg-white rounded-3xl p-6 md:p-8 shadow-xl shadow-slate-200/50 border border-slate-100">
                    
                    <form action="{{ route('member.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        {{-- SECTIONS: DATA DIRI --}}
                        <div class="mb-8">
                            <h3 class="text-sm font-bold text-[#CFAF53] uppercase tracking-wider mb-4 border-b border-slate-100 pb-2">1. Data Diri</h3>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-5">
                                <div>
                                    <label class="block text-xs font-bold text-slate-500 mb-2">Nama Lengkap</label>
                                    <input type="text" value="{{ $user->name }}" class="w-full rounded-xl border-slate-200 bg-slate-50 text-slate-500 text-sm cursor-not-allowed" readonly>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-slate-500 mb-2">Email</label>
                                    <input type="text" value="{{ $user->email }}" class="w-full rounded-xl border-slate-200 bg-slate-50 text-slate-500 text-sm cursor-not-allowed" readonly>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 mb-2">No. WhatsApp <span class="text-rose-500">*</span></label>

                                    @if($user->phone)
                                        {{-- Jika nomor sudah ada, tampilkan readonly tapi warna teks lebih gelap --}}
                                        <input type="text" name="phone" value="{{ $user->phone }}" class="w-full rounded-xl border-slate-200 bg-slate-50 text-slate-600 text-sm cursor-not-allowed font-medium" readonly>
                                    @else
                                        {{-- Jika nomor kosong, izinkan input (HANYA ANGKA) --}}
                                        <input 
                                            type="text" 
                                            name="phone" 
                                            required 
                                            class="w-full rounded-xl border-slate-200 text-sm focus:border-[#CFAF53] focus:ring-[#CFAF53]/50 placeholder:text-slate-300" 
                                            placeholder="0812xxxx (Wajib diisi)"
                                            inputmode="numeric"
                                            oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                                        >
                                    @endif
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 mb-2">Golongan Darah</label>
                                    <select name="golongan_darah" class="w-full rounded-xl border-slate-200 text-sm focus:border-[#CFAF53] focus:ring-[#CFAF53]/50">
                                        <option value="">-- Pilih Gol. Darah --</option>
                                        <option value="A">A</option>
                                        <option value="B">B</option>
                                        <option value="AB">AB</option>
                                        <option value="O">O</option>
                                    </select>
                                </div>
                            </div>

                            <div class="mb-5">
                                <label class="block text-xs font-bold text-slate-700 mb-2">Riwayat Penyakit / Alergi</label>
                                <textarea name="riwayat_penyakit" rows="2" class="w-full rounded-xl border-slate-200 text-sm focus:border-[#CFAF53] focus:ring-[#CFAF53]/50" placeholder="Contoh: Alergi seafood, Asma, Diabetes (kosongkan jika tidak ada)"></textarea>
                            </div>
                        </div>

                        {{-- SECTIONS: KONDISI KULIT --}}
                        <div class="mb-8">
                            <h3 class="text-sm font-bold text-[#CFAF53] uppercase tracking-wider mb-4 border-b border-slate-100 pb-2">2. Kondisi Kulit</h3>

                            <div class="mb-5">
                                <label class="block text-xs font-bold text-slate-700 mb-2">Jenis Kulit <span class="text-rose-500">*</span></label>
                                <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                                    <label class="cursor-pointer">
                                        <input type="radio" name="jenis_kulit" value="Normal" class="peer sr-only" required>
                                        <div class="rounded-xl border border-slate-200 p-3 text-center text-sm font-medium text-slate-600 transition-all peer-checked:border-[#CFAF53] peer-checked:bg-[#CFAF53]/10 peer-checked:text-[#8B6F1B] hover:bg-slate-50">
                                            Normal
                                        </div>
                                    </label>
                                    <label class="cursor-pointer">
                                        <input type="radio" name="jenis_kulit" value="Kering" class="peer sr-only">
                                        <div class="rounded-xl border border-slate-200 p-3 text-center text-sm font-medium text-slate-600 transition-all peer-checked:border-[#CFAF53] peer-checked:bg-[#CFAF53]/10 peer-checked:text-[#8B6F1B] hover:bg-slate-50">
                                            Kering
                                        </div>
                                    </label>
                                    <label class="cursor-pointer">
                                        <input type="radio" name="jenis_kulit" value="Berminyak" class="peer sr-only">
                                        <div class="rounded-xl border border-slate-200 p-3 text-center text-sm font-medium text-slate-600 transition-all peer-checked:border-[#CFAF53] peer-checked:bg-[#CFAF53]/10 peer-checked:text-[#8B6F1B] hover:bg-slate-50">
                                            Berminyak
                                        </div>
                                    </label>
                                    <label class="cursor-pointer">
                                        <input type="radio" name="jenis_kulit" value="Sensitif" class="peer sr-only">
                                        <div class="rounded-xl border border-slate-200 p-3 text-center text-sm font-medium text-slate-600 transition-all peer-checked:border-[#CFAF53] peer-checked:bg-[#CFAF53]/10 peer-checked:text-[#8B6F1B] hover:bg-slate-50">
                                            Sensitif
                                        </div>
                                    </label>
                                </div>
                            </div>

                            <div class="mb-5">
                                <label class="block text-xs font-bold text-slate-700 mb-2">Keluhan Utama pada Wajah <span class="text-rose-500">*</span></label>
                                <textarea name="keluhan_utama" rows="3" required class="w-full rounded-xl border-slate-200 text-sm focus:border-[#CFAF53] focus:ring-[#CFAF53]/50" placeholder="Ceritakan masalah kulit yang ingin Anda atasi..."></textarea>
                            </div>
                        </div>

                        {{-- SECTIONS: RIWAYAT SKINCARE --}}
                        <div class="mb-8">
                            <h3 class="text-sm font-bold text-[#CFAF53] uppercase tracking-wider mb-4 border-b border-slate-100 pb-2">3. Riwayat Perawatan</h3>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-5">
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 mb-2">Skincare yang digunakan saat ini</label>
                                    <textarea name="skincare_sebelumnya" rows="3" class="w-full rounded-xl border-slate-200 text-sm focus:border-[#CFAF53] focus:ring-[#CFAF53]/50" placeholder="Sebutkan merek/jenis produk..."></textarea>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 mb-2">Lama Penggunaan (Bulan)</label>
                                    <input type="number" name="periode_skincare_bulan" class="w-full rounded-xl border-slate-200 text-sm focus:border-[#CFAF53] focus:ring-[#CFAF53]/50" placeholder="Contoh: 6">
                                </div>
                            </div>

                            <div class="space-y-3">
                                <p class="text-xs font-bold text-slate-700">Treatment yang pernah dilakukan:</p>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <label class="flex items-center gap-3 p-3 border border-slate-200 rounded-xl cursor-pointer hover:bg-slate-50 transition">
                                        <input type="checkbox" name="pernah_facial" value="1" class="rounded text-[#CFAF53] focus:ring-[#CFAF53]">
                                        <span class="text-sm text-slate-600">Pernah Facial</span>
                                    </label>
                                    <label class="flex items-center gap-3 p-3 border border-slate-200 rounded-xl cursor-pointer hover:bg-slate-50 transition">
                                        <input type="checkbox" name="pernah_laser" value="1" class="rounded text-[#CFAF53] focus:ring-[#CFAF53]">
                                        <span class="text-sm text-slate-600">Pernah Laser</span>
                                    </label>
                                    <label class="flex items-center gap-3 p-3 border border-slate-200 rounded-xl cursor-pointer hover:bg-slate-50 transition">
                                        <input type="checkbox" name="pernah_skin_booster" value="1" class="rounded text-[#CFAF53] focus:ring-[#CFAF53]">
                                        <span class="text-sm text-slate-600">Pernah Skin Booster</span>
                                    </label>
                                    <label class="flex items-center gap-3 p-3 border border-slate-200 rounded-xl cursor-pointer hover:bg-slate-50 transition">
                                        <input type="checkbox" name="pernah_inject_infus" value="1" class="rounded text-[#CFAF53] focus:ring-[#CFAF53]">
                                        <span class="text-sm text-slate-600">Pernah Inject/Infus</span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        {{-- DISCLAIMER PEMBAYARAN --}}
                        <div class="bg-blue-50 border border-blue-100 rounded-xl p-4 mb-8 flex gap-3 items-start">
                            <svg class="w-5 h-5 text-blue-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <p class="text-xs text-blue-800 leading-relaxed">
                                Dengan klik tombol di bawah, Anda setuju untuk membayar biaya keanggotaan sebesar <strong>Rp 50.000</strong>. Data kesehatan Anda akan disimpan dengan aman untuk keperluan medis klinik.
                            </p>
                        </div>

                        {{-- TOMBOL SUBMIT --}}
                        <button type="submit" class="w-full bg-[#CFAF53] text-slate-900 font-bold py-4 rounded-xl shadow-lg shadow-[#CFAF53]/20 hover:bg-[#b59b48] hover:-translate-y-1 transition transform flex items-center justify-center gap-2">
                            <span>Daftar & Lanjut Pembayaran</span>
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                        </button>

                    </form>
                </div>
            </div>
        </div>
    </div>
</x-guest-layout>