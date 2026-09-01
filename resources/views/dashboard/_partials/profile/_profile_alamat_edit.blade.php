{{-- Partial: Profile Alamat Edit --}}
                <div class="sm:col-span-2 grid grid-cols-1 sm:grid-cols-2 gap-x-5 gap-y-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-600 mb-1.5">Provinsi</label>
                        <div class="alamat-select-wrap">
                            <select x-model="selectedProvinceId" @change="fetchCities()" class="alamat-select" :class="loadingProvinces && 'alamat-select--loading'">
                                <option value="">Pilih provinsi</option>
                                <template x-for="item in provinces" :key="item.id"><option :value="item.id" x-text="item.name"></option></template>
                            </select>
                            <span class="alamat-select__caret" x-show="!loadingProvinces" aria-hidden="true">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </span>
                            <span class="alamat-select__caret" x-show="loadingProvinces" aria-hidden="true">
                                <svg class="animate-spin" fill="none" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" class="opacity-25"/><path fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" class="opacity-75"/></svg>
                            </span>
                        </div>
                        <p x-show="resolvingEdit && savedProvinceName && !selectedProvinceId" x-cloak class="text-xs text-[#8C7A63] mt-1 flex items-center gap-1">
                            <svg class="w-3 h-3 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                            Memuat: <span x-text="savedProvinceName" class="font-semibold"></span>
                        </p>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-600 mb-1.5">Kota / Kabupaten</label>
                        <div class="alamat-select-wrap">
                            <select x-model="selectedCityId" @change="fetchDistricts()" :disabled="!selectedProvinceId" class="alamat-select" :class="loadingCities && 'alamat-select--loading'">
                                <option value="">Pilih kota / kabupaten</option>
                                <template x-for="item in cities" :key="item.id"><option :value="item.id" x-text="item.name"></option></template>
                            </select>
                            <span class="alamat-select__caret" x-show="!loadingCities" aria-hidden="true">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </span>
                            <span class="alamat-select__caret" x-show="loadingCities" aria-hidden="true">
                                <svg class="animate-spin" fill="none" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" class="opacity-25"/><path fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" class="opacity-75"/></svg>
                            </span>
                        </div>
                        <p x-show="resolvingEdit && savedCityName && !selectedCityId" x-cloak class="text-xs text-[#8C7A63] mt-1 flex items-center gap-1">
                            <svg class="w-3 h-3 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                            Memuat: <span x-text="savedCityName" class="font-semibold"></span>
                        </p>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-600 mb-1.5">Kecamatan</label>
                        <div class="alamat-select-wrap">
                            <select x-model="selectedDistrictId"
                                    @change="updateLocationNames()"
                                    :disabled="!selectedCityId"
                                    class="alamat-select"
                                    :class="loadingDistricts && 'alamat-select--loading'">
                                <option value="" x-text="selectedCityId ? 'Pilih kecamatan' : 'Pilih kota terlebih dahulu'"></option>
                                <template x-for="item in districts" :key="item.id"><option :value="item.id" x-text="item.name"></option></template>
                            </select>
                            <span class="alamat-select__caret" x-show="!loadingDistricts" aria-hidden="true">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </span>
                            <span class="alamat-select__caret" x-show="loadingDistricts" aria-hidden="true">
                                <svg class="animate-spin" fill="none" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" class="opacity-25"/><path fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" class="opacity-75"/></svg>
                            </span>
                        </div>
                        <p x-show="resolvingEdit && savedDistrictName && !selectedDistrictId" x-cloak class="text-xs text-[#8C7A63] mt-1 flex items-center gap-1">
                            <svg class="w-3 h-3 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                            Memuat: <span x-text="savedDistrictName" class="font-semibold"></span>
                        </p>
                    </div>

                    <div>
                        <label for="kode-pos" class="block text-xs font-bold text-slate-600 mb-1.5">Kode Pos</label>
                        <input type="text"
                               id="kode-pos"
                               name="kode_pos"
                               form="profile-form"
                               value="{{ old('kode_pos', $user->pasienProfile?->kode_pos) }}"
                               maxlength="6"
                               inputmode="numeric"
                               oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 6)"
                               placeholder="Masukkan kode pos"
                               class="alamat-input">
                        <p class="text-xs text-slate-400 mt-1.5">Kode pos membantu estimasi pengiriman lebih akurat.</p>
                    </div>

                    {{-- Cancel button when editing existing address --}}
                    <div class="sm:col-span-2 flex justify-end" x-show="editingAddress && hasSavedAddress" x-cloak>
                        <button type="button" @click="cancelEditMode()" class="alamat-cancel-btn">
                            Batal Ubah
                        </button>
                    </div>
                </div>
