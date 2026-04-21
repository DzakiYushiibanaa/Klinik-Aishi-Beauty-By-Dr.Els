<!-- STEP 3: Riwayat Medis -->
<div x-show="step === 3" x-cloak x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-x-4" x-transition:enter-end="opacity-100 translate-x-0">
    <div class="space-y-6">
        <!-- Info Banner -->
        <div class="flex items-start gap-3 rounded-xl border border-[#E8E0D8] bg-[#FAF8F5] px-5 py-4">
            <div class="w-6 h-6 rounded-full border-2 border-[#8C7A63] flex items-center justify-center shrink-0 mt-0.5">
                <svg class="w-3.5 h-3.5 text-[#8C7A63]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <p class="text-sm text-gray-600 leading-relaxed">Data riwayat medis sangat penting untuk memastikan keamanan treatment dan resep produk yang akan direkomendasikan dokter.</p>
        </div>

        <!-- Riwayat Penyakit Khusus -->
        <div class="space-y-2">
            <label class="block text-sm font-bold text-[#1A1A1A]">Riwayat Penyakit Khusus</label>
            <textarea name="riwayat_penyakit" rows="3" x-model="riwayat_penyakit"
                class="w-full rounded-xl border bg-white px-4 py-3 text-sm text-[#1A1A1A] placeholder:text-gray-400 transition-all focus:outline-none focus:ring-2 resize-none @error('riwayat_penyakit') border-rose-400 focus:border-rose-400 focus:ring-rose-400/30 @else border-gray-200 focus:border-[#8C7A63] focus:ring-[#8C7A63]/30 @enderror"
                placeholder="Apakah Anda memiliki riwayat penyakit tertentu? (Jantung, Diabetes, Hipertensi, dll). Tulis 'Tidak ada' jika tidak ada.">{{ old('riwayat_penyakit') }}</textarea>
            @error('riwayat_penyakit')
                <p class="text-xs text-rose-500 flex items-center gap-1"><svg class="w-3.5 h-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01"/></svg> {{ $message }}</p>
            @enderror
        </div>

        <!-- Riwayat Alergi -->
        <div class="space-y-2">
            <label class="block text-sm font-bold text-[#1A1A1A]">
                Riwayat Alergi (Obat / Zat Kimia)
            </label>
            <textarea name="riwayat_alergi" rows="3" x-model="riwayat_alergi"
                class="w-full rounded-xl border bg-white px-4 py-3 text-sm text-[#1A1A1A] placeholder:text-gray-400 transition-all focus:outline-none focus:ring-2 resize-none @error('riwayat_alergi') border-rose-400 focus:border-rose-400 focus:ring-rose-400/30 @else border-gray-200 focus:border-[#8C7A63] focus:ring-[#8C7A63]/30 @enderror"
                placeholder="Sebutkan jika ada alergi obat, makanan, atau zat kimia tertentu. Sangat penting bagi dokter untuk mengetahuinya.">{{ old('riwayat_alergi') }}</textarea>
            @error('riwayat_alergi')
                <p class="text-xs text-rose-500 flex items-center gap-1"><svg class="w-3.5 h-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01"/></svg> {{ $message }}</p>
            @enderror
        </div>
    </div>
</div>
