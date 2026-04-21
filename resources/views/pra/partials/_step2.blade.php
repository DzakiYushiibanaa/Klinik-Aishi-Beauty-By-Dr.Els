<!-- STEP 2: Riwayat Produk -->
<div x-show="step === 2" x-cloak x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-x-4" x-transition:enter-end="opacity-100 translate-x-0">
    <div class="space-y-6">
        <!-- Produk Skincare -->
        <div class="space-y-2">
            <label class="block text-sm font-bold text-[#1A1A1A]">Produk Skincare Saat Ini</label>
            <input type="text" name="skincare_sebelumnya" x-model="skincare_sebelumnya" value="{{ old('skincare_sebelumnya') }}"
                class="w-full rounded-xl border bg-white px-4 py-3 text-sm text-[#1A1A1A] placeholder:text-gray-400 transition-all focus:outline-none focus:ring-2"
                :class="skincare_sebelumnya_error ? 'border-rose-400 focus:border-rose-400 focus:ring-rose-400/30' : 'border-gray-200 focus:border-[#8C7A63] focus:ring-[#8C7A63]/30'"
                placeholder="Sebutkan merk / krim dokter yang sedang dipakai...">
            <template x-if="skincare_sebelumnya_error">
                <p class="text-xs text-rose-500 flex items-center gap-1"><svg class="w-3.5 h-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01"/></svg> <span x-text="skincare_sebelumnya_error"></span></p>
            </template>
        </div>

        <!-- Lama Pemakaian -->
        <div class="space-y-2">
            <label class="block text-sm font-bold text-[#1A1A1A]">Lama Pemakaian (Bulan)</label>
            <input type="number" name="periode_skincare_bulan" min="0" x-model="periode_skincare_bulan" value="{{ old('periode_skincare_bulan') }}"
                class="w-full rounded-xl border bg-white px-4 py-3 text-sm text-[#1A1A1A] placeholder:text-gray-400 transition-all focus:outline-none focus:ring-2"
                :class="periode_skincare_bulan_error ? 'border-rose-400 focus:border-rose-400 focus:ring-rose-400/30' : 'border-gray-200 focus:border-[#8C7A63] focus:ring-[#8C7A63]/30'"
                placeholder="Contoh: 6">
            <template x-if="periode_skincare_bulan_error">
                <p class="text-xs text-rose-500 flex items-center gap-1"><svg class="w-3.5 h-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01"/></svg> <span x-text="periode_skincare_bulan_error"></span></p>
            </template>
        </div>

        <!-- Riwayat Treatment -->
        <div class="space-y-3">
            <label class="block text-sm font-bold text-[#1A1A1A]">Riwayat Treatment (Opsional)</label>
            <div class="grid grid-cols-2 gap-3">
                <label class="cursor-pointer">
                    <input type="checkbox" name="pernah_facial" value="1" class="peer sr-only" x-model="pernah_facial">
                    <div class="flex items-center gap-3 rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm font-medium text-gray-600 transition-all hover:border-[#8C7A63]/50 peer-checked:border-[#8C7A63] peer-checked:bg-[#8C7A63]/5 peer-checked:text-[#6B5D4D]">
                        <div class="w-4 h-4 rounded border-2 flex items-center justify-center shrink-0 transition-all"
                            :class="pernah_facial ? 'bg-[#8C7A63] border-[#8C7A63]' : 'border-gray-300'">
                            <svg x-show="pernah_facial" class="w-3 h-3 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                        </div>
                        Facial
                    </div>
                </label>
                <label class="cursor-pointer">
                    <input type="checkbox" name="pernah_skin_booster" value="1" class="peer sr-only" x-model="pernah_skin_booster">
                    <div class="flex items-center gap-3 rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm font-medium text-gray-600 transition-all hover:border-[#8C7A63]/50 peer-checked:border-[#8C7A63] peer-checked:bg-[#8C7A63]/5 peer-checked:text-[#6B5D4D]">
                        <div class="w-4 h-4 rounded border-2 flex items-center justify-center shrink-0 transition-all"
                            :class="pernah_skin_booster ? 'bg-[#8C7A63] border-[#8C7A63]' : 'border-gray-300'">
                            <svg x-show="pernah_skin_booster" class="w-3 h-3 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                        </div>
                        Skin Booster
                    </div>
                </label>
                <label class="cursor-pointer">
                    <input type="checkbox" name="pernah_laser" value="1" class="peer sr-only" x-model="pernah_laser">
                    <div class="flex items-center gap-3 rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm font-medium text-gray-600 transition-all hover:border-[#8C7A63]/50 peer-checked:border-[#8C7A63] peer-checked:bg-[#8C7A63]/5 peer-checked:text-[#6B5D4D]">
                        <div class="w-4 h-4 rounded border-2 flex items-center justify-center shrink-0 transition-all"
                            :class="pernah_laser ? 'bg-[#8C7A63] border-[#8C7A63]' : 'border-gray-300'">
                            <svg x-show="pernah_laser" class="w-3 h-3 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                        </div>
                        Laser
                    </div>
                </label>
                <label class="cursor-pointer">
                    <input type="checkbox" name="pernah_inject_infus" value="1" class="peer sr-only" x-model="pernah_inject_infus">
                    <div class="flex items-center gap-3 rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm font-medium text-gray-600 transition-all hover:border-[#8C7A63]/50 peer-checked:border-[#8C7A63] peer-checked:bg-[#8C7A63]/5 peer-checked:text-[#6B5D4D]">
                        <div class="w-4 h-4 rounded border-2 flex items-center justify-center shrink-0 transition-all"
                            :class="pernah_inject_infus ? 'bg-[#8C7A63] border-[#8C7A63]' : 'border-gray-300'">
                            <svg x-show="pernah_inject_infus" class="w-3 h-3 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                        </div>
                        Inject / Infus
                    </div>
                </label>
            </div>
        </div>
    </div>
</div>
