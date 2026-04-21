<!-- STEP 5: Ringkasan -->
<div x-show="step === 5" x-cloak x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-x-4" x-transition:enter-end="opacity-100 translate-x-0">
    <div class="space-y-4">
        <!-- Info Banner -->
        <div class="flex items-start gap-3 rounded-xl border border-[#E8E0D8] bg-[#FAF8F5] px-5 py-4">
            <div class="w-6 h-6 rounded-full border-2 border-[#8C7A63] flex items-center justify-center shrink-0 mt-0.5">
                <svg class="w-3.5 h-3.5 text-[#8C7A63]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            </div>
            <p class="text-sm text-gray-600 leading-relaxed">Hampir selesai! Silakan periksa kembali kelengkapan dan kebenaran data Anda di bawah ini sebelum mengirimkan form ke tim medis kami.</p>
        </div>

        <!-- Section 1: Kondisi Kulit -->
        <div class="rounded-xl border border-gray-200 bg-white p-5">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-2">
                    <span class="w-6 h-6 rounded-full bg-[#1A1A1A] text-white text-xs font-bold flex items-center justify-center">1</span>
                    <h3 class="font-bold text-sm text-[#1A1A1A]">Kondisi Kulit</h3>
                </div>
                <button type="button" @click="goToStep(1)" class="text-xs font-semibold text-[#8C7A63] hover:text-[#6B5D4D] transition-colors">Ubah</button>
            </div>
            <div class="space-y-2.5 text-sm">
                <div class="flex justify-between"><span class="text-gray-500">Keluhan Utama</span><span class="font-semibold text-[#1A1A1A] text-right max-w-[60%] truncate" x-text="keluhan_utama || '-'"></span></div>
                <div class="flex justify-between"><span class="text-gray-500">Jenis Kulit</span><span class="font-semibold text-[#1A1A1A]" x-text="jenis_kulit || '-'"></span></div>
                <div class="flex justify-between"><span class="text-gray-500">Golongan Darah</span><span class="font-semibold text-[#1A1A1A]" x-text="golongan_darah || '-'"></span></div>
            </div>
        </div>

        <!-- Section 2: Riwayat Produk -->
        <div class="rounded-xl border border-gray-200 bg-white p-5">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-2">
                    <span class="w-6 h-6 rounded-full bg-[#1A1A1A] text-white text-xs font-bold flex items-center justify-center">2</span>
                    <h3 class="font-bold text-sm text-[#1A1A1A]">Riwayat Produk</h3>
                </div>
                <button type="button" @click="goToStep(2)" class="text-xs font-semibold text-[#8C7A63] hover:text-[#6B5D4D] transition-colors">Ubah</button>
            </div>
            <div class="space-y-2.5 text-sm">
                <div class="flex justify-between"><span class="text-gray-500">Produk Saat Ini</span><span class="font-semibold text-[#1A1A1A] text-right max-w-[60%] truncate" x-text="skincare_sebelumnya || '-'"></span></div>
                <div class="flex justify-between"><span class="text-gray-500">Lama Pemakaian</span><span class="font-semibold text-[#1A1A1A]" x-text="(periode_skincare_bulan || '0') + ' Bulan'"></span></div>
                <div class="flex justify-between"><span class="text-gray-500">Treatment</span><span class="font-semibold text-[#1A1A1A] text-right max-w-[60%]" x-text="treatmentList"></span></div>
            </div>
        </div>

        <!-- Section 3: Riwayat Medis -->
        <div class="rounded-xl border border-gray-200 bg-white p-5">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-2">
                    <span class="w-6 h-6 rounded-full bg-[#1A1A1A] text-white text-xs font-bold flex items-center justify-center">3</span>
                    <h3 class="font-bold text-sm text-[#1A1A1A]">Riwayat Medis</h3>
                </div>
                <button type="button" @click="goToStep(3)" class="text-xs font-semibold text-[#8C7A63] hover:text-[#6B5D4D] transition-colors">Ubah</button>
            </div>
            <div class="space-y-2.5 text-sm">
                <div class="flex justify-between"><span class="text-gray-500">Penyakit Khusus</span><span class="font-semibold text-[#1A1A1A] text-right max-w-[60%] truncate" x-text="riwayat_penyakit || '-'"></span></div>
                <div class="flex justify-between"><span class="text-gray-500">Riwayat Alergi</span><span class="font-semibold text-[#1A1A1A] text-right max-w-[60%] truncate" x-text="riwayat_alergi || '-'"></span></div>
            </div>
        </div>

        <!-- Section 4: Dokumentasi Visual -->
        <div class="rounded-xl border border-gray-200 bg-white p-5">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-2">
                    <span class="w-6 h-6 rounded-full bg-[#1A1A1A] text-white text-xs font-bold flex items-center justify-center">4</span>
                    <h3 class="font-bold text-sm text-[#1A1A1A]">Dokumentasi Visual</h3>
                </div>
                <button type="button" @click="goToStep(4)" class="text-xs font-semibold text-[#8C7A63] hover:text-[#6B5D4D] transition-colors">Ubah</button>
            </div>

            <!-- Photo Previews Grid -->
            <div class="grid grid-cols-3 gap-3 mb-4">
                <!-- Depan -->
                <div class="text-center">
                    <div class="aspect-[3/4] rounded-xl overflow-hidden border border-gray-100 bg-[#FAF8F5]">
                        <template x-if="foto_depan_preview">
                            <img :src="foto_depan_preview" class="w-full h-full object-cover" alt="Foto Depan">
                        </template>
                        <template x-if="!foto_depan_preview">
                            <div class="w-full h-full flex flex-col items-center justify-center">
                                <img src="{{ asset('assets/img/wajah hadap depan.svg') }}" alt="" class="w-10 h-10 opacity-20">
                                <span class="text-[10px] text-rose-400 font-medium mt-1">Belum ada</span>
                            </div>
                        </template>
                    </div>
                    <p class="text-[10px] text-gray-500 mt-1">Depan</p>
                </div>
                <!-- Kanan -->
                <div class="text-center">
                    <div class="aspect-[3/4] rounded-xl overflow-hidden border border-gray-100 bg-[#FAF8F5]">
                        <template x-if="foto_kanan_preview">
                            <img :src="foto_kanan_preview" class="w-full h-full object-cover" alt="Foto Kanan">
                        </template>
                        <template x-if="!foto_kanan_preview">
                            <div class="w-full h-full flex flex-col items-center justify-center">
                                <img src="{{ asset('assets/img/wajah samping kanan.svg') }}" alt="" class="w-10 h-10 opacity-20">
                                <span class="text-[10px] text-rose-400 font-medium mt-1">Belum ada</span>
                            </div>
                        </template>
                    </div>
                    <p class="text-[10px] text-gray-500 mt-1">Kanan</p>
                </div>
                <!-- Kiri -->
                <div class="text-center">
                    <div class="aspect-[3/4] rounded-xl overflow-hidden border border-gray-100 bg-[#FAF8F5]">
                        <template x-if="foto_kiri_preview">
                            <img :src="foto_kiri_preview" class="w-full h-full object-cover" alt="Foto Kiri">
                        </template>
                        <template x-if="!foto_kiri_preview">
                            <div class="w-full h-full flex flex-col items-center justify-center">
                                <img src="{{ asset('assets/img/wajah samping kiri.svg') }}" alt="" class="w-10 h-10 opacity-20">
                                <span class="text-[10px] text-rose-400 font-medium mt-1">Belum ada</span>
                            </div>
                        </template>
                    </div>
                    <p class="text-[10px] text-gray-500 mt-1">Kiri</p>
                </div>
            </div>

            <!-- Status Text -->
            <div class="space-y-2 text-sm border-t border-gray-100 pt-3">
                <div class="flex justify-between"><span class="text-gray-500">Foto Wajah Depan</span><span class="font-semibold" :class="foto_depan_name ? 'text-emerald-600' : 'text-rose-500'" x-text="foto_depan_name ? '✓ Diunggah' : 'Belum Diunggah'"></span></div>
                <div class="flex justify-between"><span class="text-gray-500">Foto Wajah Kanan</span><span class="font-semibold" :class="foto_kanan_name ? 'text-emerald-600' : 'text-rose-500'" x-text="foto_kanan_name ? '✓ Diunggah' : 'Belum Diunggah'"></span></div>
                <div class="flex justify-between"><span class="text-gray-500">Foto Wajah Kiri</span><span class="font-semibold" :class="foto_kiri_name ? 'text-emerald-600' : 'text-rose-500'" x-text="foto_kiri_name ? '✓ Diunggah' : 'Belum Diunggah'"></span></div>
            </div>
        </div>
    </div>
</div>
