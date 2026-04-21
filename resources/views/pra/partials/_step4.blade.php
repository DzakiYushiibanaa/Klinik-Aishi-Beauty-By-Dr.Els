<!-- STEP 4: Unggah Foto -->
<div x-show="step === 4" x-cloak x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0 translate-x-4" x-transition:enter-end="opacity-100 translate-x-0">
    <div class="space-y-6">
        <div class="text-center">
            <h2 class="text-lg font-bold text-[#1A1A1A]">Dokumentasi Wajah (Wajib)</h2>
            <p class="text-sm text-gray-500 mt-1">Pastikan pencahayaan terang dan tanpa makeup/filter.</p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <!-- Foto Depan -->
            <div>
                <label class="group cursor-pointer block">
                    <div class="relative aspect-[3/4] rounded-2xl border-2 border-dashed bg-[#FAF8F5] overflow-hidden flex flex-col items-center justify-center transition-all hover:bg-[#F5F0EB]"
                        :class="foto_depan_error ? 'border-rose-400 hover:border-rose-500 text-rose-500' : (foto_depan_preview ? 'border-solid !border-[#8C7A63]' : 'border-gray-200 hover:border-[#8C7A63]')">
                        <!-- Silhouette (shown when no preview) -->
                        <template x-if="!foto_depan_preview">
                            <div class="absolute inset-0 flex flex-col items-center justify-center">
                                <img src="{{ asset('assets/img/wajah hadap depan.svg') }}" alt="Siluet wajah depan"
                                    class="w-full h-full object-contain p-4 opacity-30 group-hover:opacity-50 transition-opacity">
                                <div class="absolute bottom-3 flex flex-col items-center gap-1">
                                    <div
                                        class="w-8 h-8 rounded-full bg-white/80 shadow-sm flex items-center justify-center backdrop-blur-sm">
                                        <svg class="w-4 h-4 text-gray-400 group-hover:text-[#8C7A63] transition-colors"
                                            fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                                        </svg>
                                    </div>
                                    <span class="text-xs font-semibold text-gray-500">Unggah Foto</span>
                                </div>
                            </div>
                        </template>
                        <!-- Preview (shown after upload) -->
                        <template x-if="foto_depan_preview">
                            <div class="absolute inset-0">
                                <img :src="foto_depan_preview" class="w-full h-full object-cover" alt="Preview Depan">
                                <div
                                    class="absolute inset-0 bg-black/0 group-hover:bg-black/30 transition-all flex items-center justify-center">
                                    <span
                                        class="text-white text-xs font-semibold opacity-0 group-hover:opacity-100 transition-opacity bg-black/50 rounded-full px-3 py-1">Ganti
                                        Foto</span>
                                </div>
                            </div>
                        </template>
                    </div>
                    <input type="file" name="foto_depan" accept="image/*" class="sr-only"
                        @change="if($event.target.files[0]){ validateImage($event.target.files[0], 'foto_depan'); }">
                </label>
                <p class="text-center text-xs font-medium text-gray-600 mt-2">Foto Wajah Depan</p>
                <p x-show="foto_depan_name && !foto_depan_error" x-text="foto_depan_name"
                    class="text-center text-[10px] text-[#8C7A63] mt-0.5 truncate"></p>
                <template x-if="foto_depan_error">
                    <p class="text-[10px] text-rose-500 text-center mt-1 flex items-center justify-center gap-1" x-text="foto_depan_error"></p>
                </template>
            </div>

            <!-- Foto Kanan -->
            <div>
                <label class="group cursor-pointer block">
                    <div class="relative aspect-[3/4] rounded-2xl border-2 border-dashed bg-[#FAF8F5] overflow-hidden flex flex-col items-center justify-center transition-all hover:bg-[#F5F0EB]"
                        :class="foto_kanan_error ? 'border-rose-400 hover:border-rose-500 text-rose-500' : (foto_kanan_preview ? 'border-solid !border-[#8C7A63]' : 'border-gray-200 hover:border-[#8C7A63]')">
                        <template x-if="!foto_kanan_preview">
                            <div class="absolute inset-0 flex flex-col items-center justify-center">
                                <img src="{{ asset('assets/img/wajah samping kanan.svg') }}" alt="Siluet wajah kanan"
                                    class="w-full h-full object-contain p-4 opacity-30 group-hover:opacity-50 transition-opacity">
                                <div class="absolute bottom-3 flex flex-col items-center gap-1">
                                    <div
                                        class="w-8 h-8 rounded-full bg-white/80 shadow-sm flex items-center justify-center backdrop-blur-sm">
                                        <svg class="w-4 h-4 text-gray-400 group-hover:text-[#8C7A63] transition-colors"
                                            fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                                        </svg>
                                    </div>
                                    <span class="text-xs font-semibold text-gray-500">Unggah Foto</span>
                                </div>
                            </div>
                        </template>
                        <template x-if="foto_kanan_preview">
                            <div class="absolute inset-0">
                                <img :src="foto_kanan_preview" class="w-full h-full object-cover" alt="Preview Kanan">
                                <div
                                    class="absolute inset-0 bg-black/0 group-hover:bg-black/30 transition-all flex items-center justify-center">
                                    <span
                                        class="text-white text-xs font-semibold opacity-0 group-hover:opacity-100 transition-opacity bg-black/50 rounded-full px-3 py-1">Ganti
                                        Foto</span>
                                </div>
                            </div>
                        </template>
                    </div>
                    <input type="file" name="foto_kanan" accept="image/*" class="sr-only"
                        @change="if($event.target.files[0]){ validateImage($event.target.files[0], 'foto_kanan'); }">
                </label>
                <p class="text-center text-xs font-medium text-gray-600 mt-2">Foto Wajah Kanan</p>
                <p x-show="foto_kanan_name && !foto_kanan_error" x-text="foto_kanan_name"
                    class="text-center text-[10px] text-[#8C7A63] mt-0.5 truncate"></p>
                <template x-if="foto_kanan_error">
                    <p class="text-[10px] text-rose-500 text-center mt-1 flex items-center justify-center gap-1" x-text="foto_kanan_error"></p>
                </template>
            </div>

            <!-- Foto Kiri -->
            <div>
                <label class="group cursor-pointer block">
                    <div class="relative aspect-[3/4] rounded-2xl border-2 border-dashed bg-[#FAF8F5] overflow-hidden flex flex-col items-center justify-center transition-all hover:bg-[#F5F0EB]"
                        :class="foto_kiri_error ? 'border-rose-400 hover:border-rose-500 text-rose-500' : (foto_kiri_preview ? 'border-solid !border-[#8C7A63]' : 'border-gray-200 hover:border-[#8C7A63]')">
                        <template x-if="!foto_kiri_preview">
                            <div class="absolute inset-0 flex flex-col items-center justify-center">
                                <img src="{{ asset('assets/img/wajah samping kiri.svg') }}" alt="Siluet wajah kiri"
                                    class="w-full h-full object-contain p-4 opacity-30 group-hover:opacity-50 transition-opacity">
                                <div class="absolute bottom-3 flex flex-col items-center gap-1">
                                    <div
                                        class="w-8 h-8 rounded-full bg-white/80 shadow-sm flex items-center justify-center backdrop-blur-sm">
                                        <svg class="w-4 h-4 text-gray-400 group-hover:text-[#8C7A63] transition-colors"
                                            fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                                        </svg>
                                    </div>
                                    <span class="text-xs font-semibold text-gray-500">Unggah Foto</span>
                                </div>
                            </div>
                        </template>
                        <template x-if="foto_kiri_preview">
                            <div class="absolute inset-0">
                                <img :src="foto_kiri_preview" class="w-full h-full object-cover" alt="Preview Kiri">
                                <div
                                    class="absolute inset-0 bg-black/0 group-hover:bg-black/30 transition-all flex items-center justify-center">
                                    <span
                                        class="text-white text-xs font-semibold opacity-0 group-hover:opacity-100 transition-opacity bg-black/50 rounded-full px-3 py-1">Ganti
                                        Foto</span>
                                </div>
                            </div>
                        </template>
                    </div>
                    <input type="file" name="foto_kiri" accept="image/*" class="sr-only"
                        @change="if($event.target.files[0]){ validateImage($event.target.files[0], 'foto_kiri'); }">
                </label>
                <p class="text-center text-xs font-medium text-gray-600 mt-2">Foto Wajah Kiri</p>
                <p x-show="foto_kiri_name && !foto_kiri_error" x-text="foto_kiri_name"
                    class="text-center text-[10px] text-[#8C7A63] mt-0.5 truncate"></p>
                <template x-if="foto_kiri_error">
                    <p class="text-[10px] text-rose-500 text-center mt-1 flex items-center justify-center gap-1" x-text="foto_kiri_error"></p>
                </template>
            </div>
        </div>
    </div>
</div>