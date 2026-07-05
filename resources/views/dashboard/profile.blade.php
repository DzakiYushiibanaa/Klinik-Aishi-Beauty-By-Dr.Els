{{-- ====== TAB: PROFIL SAYA ====== --}}
<div x-data="addressSelector()" @input="profileIsDirty = true" @submit="profileIsDirty = false">
    <h2 class="text-xl font-extrabold text-[#1A1A1A] mb-6"
        x-text="activeTab === 'profile_password' ? 'Ganti Password' : (activeTab === 'profile_alamat' ? 'Alamat Saya' : 'Profil Saya')">
        Profil Saya
    </h2>

    <form id="profile-form" action="{{ route('pasien.profile.update') }}" method="POST" enctype="multipart/form-data"
          class="bg-white rounded-2xl border border-[#E8E0D8]/60 p-6 sm:p-8 mb-6"
          x-show="activeTab === 'profile_data' || activeTab === 'profile'" novalidate>
        @csrf
        @method('PATCH')

        {{-- Hidden inputs untuk API --}}
        <input type="hidden" name="provinsi" x-model="selectedProvinceName">
        <input type="hidden" name="kota" x-model="selectedCityName">
        <input type="hidden" name="district_id" x-model="selectedDistrictId">
        <input type="hidden" name="district_name" x-model="selectedDistrictName">
        <input type="hidden" name="province_id" x-model="selectedProvinceId">
        <input type="hidden" name="city_id" x-model="selectedCityId">

        {{-- Phone disubmit lewat hidden input tunggal supaya tidak bentrok
             dengan input phone visible di tab "Alamat Saya" (lihat catatan
             di addressSelector()). Dua input visible cukup pakai x-model
             tanpa atribut "name". --}}
        <input type="hidden" name="phone" x-model="phoneNumber">

        {{-- Section: Data Diri --}}
        <h3 id="sec-profil" class="text-xs font-bold uppercase tracking-[0.2em] text-[#8C7A63] mb-4 scroll-mt-32">Data Diri</h3>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-5 mb-6">
            <div class="lg:col-span-2" x-data="{ photoName: null, photoPreview: null }">
                <label class="block text-xs font-bold text-slate-500 mb-4">Ganti Foto Profil</label>
                <input type="file" name="foto" id="foto" class="hidden" 
                       x-ref="photo"
                       accept=".jpg,.jpeg,.png"
                       @change="
                            const file = $refs.photo.files[0];
                            if(file && file.size <= 1048576) {
                                photoName = file.name;
                                const reader = new FileReader();
                                reader.onload = (e) => {
                                    photoPreview = e.target.result;
                                };
                                reader.readAsDataURL(file);
                                profileIsDirty = true;
                            } else if(file) {
                                window.showToast && window.showToast('warning', 'Ukuran gambar maksimal 1 MB. Silakan pilih file yang lebih kecil.', 'Ukuran Terlalu Besar');
                                $refs.photo.value = '';
                            }
                       ">

                <div class="flex items-center gap-6">
                    {{-- Avatar Preview / Initial --}}
                    <div class="relative w-20 h-20 sm:w-24 sm:h-24 rounded-full overflow-hidden bg-[#FAF8F5] flex items-center justify-center border-4 border-[#E8E0D8]/40 shadow-sm group cursor-pointer shrink-0"
                         @click="$refs.photo.click()">
                        
                        {{-- New Preview --}}
                        <div x-show="photoPreview" class="w-full h-full" style="display: none;">
                            <img :src="photoPreview" class="w-full h-full object-cover">
                        </div>

                        {{-- Existing Photo --}}
                        <div x-show="!photoPreview" class="w-full h-full flex items-center justify-center">
                            @if($user->pasienProfile && $user->pasienProfile->foto_profil_path)
                                <img src="{{ asset('storage/' . $user->pasienProfile->foto_profil_path) }}" class="w-full h-full object-cover">
                            @else
                                <span class="text-3xl sm:text-4xl font-extrabold text-[#CFAF53] uppercase">{{ substr($user->name, 0, 1) }}</span>
                            @endif
                        </div>

                        {{-- Hover Overlay --}}
                        <div class="absolute inset-0 bg-black/40 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                            <svg class="w-6 h-6 sm:w-8 sm:h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        </div>
                    </div>

                    {{-- Actions & Info --}}
                    <div>
                        <button type="button" @click="$refs.photo.click()" class="bg-white border border-[#E8E0D8] text-[#1A1A1A] font-bold text-xs px-4 py-2 rounded-full hover:bg-slate-50 transition shadow-sm mb-2">
                            Pilih Gambar
                        </button>
                        <p class="text-[11px] text-slate-400 leading-relaxed">
                            (Ukuran gambar: maks. 1 MB<br>
                            Format gambar: .JPEG, .PNG)
                        </p>
                        @error('foto')
                            <p class="text-rose-500 text-[11px] mt-1 font-bold">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-500 mb-1">Nama Lengkap</label>
                <input type="text" name="name" value="{{ old('name', $user->name) }}" class="w-full rounded-xl border-[#E8E0D8]/60 text-sm focus:border-[#CFAF53] focus:ring-[#CFAF53]/20" required>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-500 mb-1">Email (Login)</label>
                <input type="email" name="email" value="{{ $user->email }}" class="w-full rounded-xl border-[#E8E0D8]/60 bg-[#FAF8F5] text-slate-400 text-sm cursor-not-allowed" readonly>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-500 mb-1">Nomor WhatsApp</label>
                <input type="text" x-model="phoneNumber" maxlength="13" oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 13)" class="w-full rounded-xl border-[#E8E0D8]/60 text-sm focus:border-[#CFAF53] focus:ring-[#CFAF53]/20" placeholder="081xxx">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-500 mb-1">Tanggal Lahir</label>
                <x-date-picker name="tanggal_lahir"
                               :value="old('tanggal_lahir', $user->pasienProfile?->tanggal_lahir)"
                               placeholder="dd/mm/yyyy" />
            </div>

            <div class="lg:col-span-2">
                <label class="block text-xs font-bold text-slate-500 mb-2">Jenis Kelamin</label>
                <div class="flex gap-4">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="jenis_kelamin" value="L" {{ old('jenis_kelamin', $user->pasienProfile?->jenis_kelamin) == 'L' ? 'checked' : '' }} class="text-[#CFAF53] focus:ring-[#CFAF53]">
                        <span class="text-sm text-slate-700">Laki-laki</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="jenis_kelamin" value="P" {{ old('jenis_kelamin', $user->pasienProfile?->jenis_kelamin) == 'P' ? 'checked' : '' }} class="text-[#CFAF53] focus:ring-[#CFAF53]">
                        <span class="text-sm text-slate-700">Perempuan</span>
                    </label>
                </div>
            </div>
        </div>

        <div class="mt-8 flex justify-end">
            <button type="submit" class="bg-[#CFAF53] text-[#1A1A1A] font-bold px-8 py-3 rounded-full hover:bg-[#b59b48] shadow-lg shadow-[#CFAF53]/20 transition">
                Simpan Data Diri
            </button>
        </div>
    </form>

    {{-- Section: Ganti Password --}}
    <form id="password-form" action="{{ route('password.update') }}" method="POST"
          class="bg-white rounded-2xl border border-[#E8E0D8]/60 p-6 sm:p-8 mb-6"
          x-show="activeTab === 'profile_password'" style="display: none;">
        @csrf
        @method('PUT')

        <h3 id="sec-password" class="text-xs font-bold uppercase tracking-[0.2em] text-[#8C7A63] mb-4 scroll-mt-32">Ganti Password</h3>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
            <div class="col-span-1 lg:col-span-2">
                <label class="block text-xs font-bold text-slate-500 mb-1">Password Saat Ini</label>
                <div class="w-full lg:w-1/2">
                    <x-password-input
                        name="current_password"
                        autocomplete="current-password"
                        variant="compact" />
                </div>
                @error('current_password', 'updatePassword')
                    <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span>
                @enderror
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-500 mb-1">Password Baru</label>
                <x-password-input
                    name="password"
                    autocomplete="new-password"
                    variant="compact" />
                @error('password', 'updatePassword')
                    <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span>
                @enderror
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-500 mb-1">Konfirmasi Password Baru</label>
                <x-password-input
                    name="password_confirmation"
                    autocomplete="new-password"
                    variant="compact" />
                @error('password_confirmation', 'updatePassword')
                    <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <div class="mt-8 flex justify-end items-center gap-4">
            @if (session('status') === 'password-updated')
                <span class="text-sm font-bold text-emerald-600">Password berhasil diubah.</span>
            @endif
            <button type="submit" class="bg-slate-800 text-white font-bold px-8 py-3 rounded-full hover:bg-slate-900 shadow-lg shadow-slate-200 transition">
                Ganti Password
            </button>
        </div>
    </form>

    {{-- Section: Alamat --}}
    @php
        $pasienProfile = $user->pasienProfile;

        // Ringkasan alamat hanya boleh tampil kalau SEMUA field wajib sudah
        // tersimpan ke database. Kalau salah satu kosong, form edit yang
        // dimunculkan supaya pasien menyelesaikan pengisian.
        $hasCompleteShippingAddress = $pasienProfile
            && filled($user->phone)
            && filled($pasienProfile->province_id)
            && filled($pasienProfile->provinsi)
            && filled($pasienProfile->city_id)
            && filled($pasienProfile->kota)
            && filled($pasienProfile->district_id)
            && filled($pasienProfile->district_name)
            && filled($pasienProfile->kode_pos)
            && filled($pasienProfile->alamat);
    @endphp
    <div class="bg-white rounded-2xl border border-[#E8E0D8]/60 p-6 sm:p-8"
         x-show="activeTab === 'profile_alamat'" style="display: none;">

        {{-- Header --}}
        <div class="mb-6 flex items-start justify-between gap-3 flex-wrap" id="sec-alamat" style="scroll-margin-top: 8rem;">
            <div class="min-w-0">
                <h3 class="text-xl font-extrabold tracking-tight text-[#1A1A1A]">Alamat Pengiriman</h3>
                <p class="text-sm text-slate-500 mt-1 leading-relaxed">Lengkapi data alamat untuk mempermudah proses pengiriman pesanan.</p>
            </div>
            {{-- Toggle Ubah Alamat (only visible when address is already saved & not editing) --}}
            <button type="button"
                    @click="enterEditMode()"
                    x-show="hasSavedAddress && !editingAddress"
                    x-cloak
                    class="alamat-edit-btn">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                </svg>
                <span>Ubah Alamat</span>
            </button>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-5 gap-y-4">

            {{-- Nomor HP / WhatsApp (always editable) --}}
            <div class="sm:col-span-2">
                <label for="phone-alamat" class="block text-xs font-bold text-slate-600 mb-1.5">Nomor HP / WhatsApp</label>
                <input type="text"
                       id="phone-alamat"
                       x-model="phoneNumber"
                       maxlength="15"
                       inputmode="numeric"
                       oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 15)"
                       placeholder="08xxxxxxxxxx"
                       class="alamat-input">
                <p class="text-xs text-slate-400 mt-1.5">Kami akan menghubungi nomor ini untuk konfirmasi pengiriman.</p>
            </div>

            {{-- ════════ VIEW MODE (no API hit) ════════ --}}
            <template x-if="!editingAddress && hasSavedAddress">
                <div class="sm:col-span-2 alamat-view">
                    <div class="alamat-view__grid">
                        <div class="alamat-view__cell">
                            <p class="alamat-view__label">Provinsi</p>
                            <p class="alamat-view__value" x-text="selectedProvinceName || savedProvinceName || '—'"></p>
                        </div>
                        <div class="alamat-view__cell">
                            <p class="alamat-view__label">Kota / Kabupaten</p>
                            <p class="alamat-view__value" x-text="selectedCityName || savedCityName || '—'"></p>
                        </div>
                        <div class="alamat-view__cell">
                            <p class="alamat-view__label">Kecamatan</p>
                            <p class="alamat-view__value" x-text="selectedDistrictName || savedDistrictName || '—'"></p>
                        </div>
                        <div class="alamat-view__cell">
                            <p class="alamat-view__label">Kode Pos</p>
                            <p class="alamat-view__value">{{ $user->pasienProfile?->kode_pos ?: '—' }}</p>
                        </div>
                    </div>
                </div>
            </template>

            {{-- ════════ EDIT MODE (API loads on demand) ════════ --}}
            <template x-if="editingAddress || !hasSavedAddress">
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
            </template>

            <div class="sm:col-span-2">
                <label for="alamat-lengkap" class="block text-xs font-bold text-slate-600 mb-1.5">Alamat Lengkap</label>
                <textarea id="alamat-lengkap"
                          name="alamat"
                          form="profile-form"
                          rows="3"
                          placeholder="Contoh: Jl. Melati No.10, RT 02/RW 05"
                          class="alamat-input alamat-input--textarea">{{ old('alamat', $user->pasienProfile?->alamat) }}</textarea>
            </div>
        </div>

        <div class="mt-8 flex justify-end">
            <button type="submit" form="profile-form" class="alamat-submit">
                <span>Simpan Alamat</span>
            </button>
        </div>
    </div>
</div>

@once
    <style>
        /* ─────────  ALAMAT PENGIRIMAN  ───────── */
        .alamat-notice {
            display: flex;
            align-items: flex-start;
            gap: 0.625rem;
            padding: 0.75rem 0.875rem;
            background: linear-gradient(135deg, #FFF8EB 0%, #FFFCF5 100%);
            border: 1px solid rgba(245, 158, 11, 0.22);
            border-radius: 12px;
        }
        .alamat-notice__icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 28px; height: 28px;
            border-radius: 9px;
            background: rgba(207, 175, 83, 0.18);
            color: #B8956F;
            flex-shrink: 0;
            margin-top: 1px;
        }
        .alamat-notice__icon svg { width: 16px; height: 16px; }
        .alamat-notice__text {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 0.8125rem;
            line-height: 1.55;
            color: #6F5A3F;
            margin: 0;
        }

        /* Inputs & Textareas (premium minimal) */
        .alamat-input {
            width: 100%;
            min-height: 44px;
            padding: 0.625rem 0.875rem;
            border: 1px solid rgba(232, 224, 216, 0.85);
            border-radius: 12px;
            background: #ffffff;
            color: #1A1A1A;
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 0.9375rem;
            line-height: 1.5;
            box-shadow: 0 1px 2px rgba(26, 26, 26, 0.02);
            transition: border-color 180ms ease, box-shadow 180ms ease, background 180ms ease;
        }
        .alamat-input::placeholder { color: #94A3B8; }
        .alamat-input:hover { border-color: rgba(184, 149, 111, 0.5); }
        .alamat-input:focus {
            outline: none;
            border-color: #CFAF53;
            box-shadow: 0 0 0 3px rgba(207, 175, 83, 0.18);
        }
        .alamat-input--textarea {
            min-height: 110px;
            resize: vertical;
            line-height: 1.6;
        }

        /* Select wrapper */
        .alamat-select-wrap {
            position: relative;
        }
        .alamat-select {
            width: 100%;
            min-height: 44px;
            padding: 0.625rem 2.5rem 0.625rem 0.875rem;
            border: 1px solid rgba(232, 224, 216, 0.85);
            border-radius: 12px;
            background: #ffffff;
            color: #1A1A1A;
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 0.9375rem;
            line-height: 1.5;
            cursor: pointer;
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
            box-shadow: 0 1px 2px rgba(26, 26, 26, 0.02);
            transition: border-color 180ms ease, box-shadow 180ms ease, background 180ms ease;
        }
        .alamat-select:hover { border-color: rgba(184, 149, 111, 0.5); }
        .alamat-select:focus {
            outline: none;
            border-color: #CFAF53;
            box-shadow: 0 0 0 3px rgba(207, 175, 83, 0.18);
        }
        .alamat-select:disabled {
            background: #F8FAFC;
            border-color: rgba(232, 224, 216, 0.7);
            color: #94A3B8;
            cursor: not-allowed;
            opacity: 0.85;
        }
        .alamat-select--loading { opacity: 0.6; }
        .alamat-select__caret {
            position: absolute;
            top: 50%;
            right: 0.875rem;
            transform: translateY(-50%);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 16px; height: 16px;
            color: #94A3B8;
            pointer-events: none;
        }
        .alamat-select__caret svg { width: 14px; height: 14px; }
        .alamat-select:focus + .alamat-select__caret,
        .alamat-select-wrap:hover .alamat-select__caret { color: #B8956F; }

        /* Submit button */
        .alamat-submit {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.4375rem;
            padding: 0.625rem 1.5rem;
            min-height: 44px;
            border-radius: 9999px;
            background: linear-gradient(135deg, #CFAF53 0%, #B8956F 100%);
            color: #ffffff;
            border: 1px solid transparent;
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 0.875rem;
            font-weight: 700;
            cursor: pointer;
            box-shadow:
                0 1px 2px rgba(184, 149, 111, 0.25),
                0 8px 18px -8px rgba(184, 149, 111, 0.55);
            transition: transform 200ms ease, box-shadow 200ms ease, filter 200ms ease;
        }
        .alamat-submit svg { width: 14px; height: 14px; }
        .alamat-submit:hover {
            transform: translateY(-1px);
            filter: brightness(1.05);
            box-shadow:
                0 1px 2px rgba(184, 149, 111, 0.3),
                0 14px 24px -8px rgba(184, 149, 111, 0.6);
        }
        .alamat-submit:active { transform: translateY(0); }

        @media (max-width: 639px) {
            .alamat-submit { width: 100%; }
        }
        @media (prefers-reduced-motion: reduce) {
            .alamat-input,
            .alamat-select,
            .alamat-submit { transition: none; }
            .alamat-submit:hover { transform: none; filter: none; }
        }

        /* ── View Mode Display (when address is already saved) ── */
        .alamat-view {
            padding: 1rem 1.125rem;
            background: linear-gradient(180deg, #FAF8F5 0%, #FFFFFF 100%);
            border: 1px solid rgba(232, 224, 216, 0.7);
            border-radius: 14px;
        }
        .alamat-view__grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 0.875rem 1.25rem;
        }
        @media (min-width: 640px) {
            .alamat-view__grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        }
        .alamat-view__cell { min-width: 0; }
        .alamat-view__label {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 0.6875rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.16em;
            color: #94A3B8;
            margin: 0 0 0.25rem 0;
        }
        .alamat-view__value {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 0.9375rem;
            font-weight: 700;
            color: #1A1A1A;
            line-height: 1.4;
            margin: 0;
            word-break: break-word;
        }

        /* ── Edit toggle button (header) ── */
        .alamat-edit-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.375rem;
            padding: 0.4375rem 0.875rem;
            border-radius: 9999px;
            background: rgba(207, 175, 83, 0.08);
            color: #B8956F;
            border: 1px solid rgba(184, 149, 111, 0.3);
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 0.75rem;
            font-weight: 700;
            cursor: pointer;
            transition: background 200ms ease, color 200ms ease, border-color 200ms ease, transform 200ms ease;
            white-space: nowrap;
        }
        .alamat-edit-btn svg { width: 12px; height: 12px; }
        .alamat-edit-btn:hover {
            background: linear-gradient(135deg, #CFAF53 0%, #B8956F 100%);
            border-color: transparent;
            color: #ffffff;
            transform: translateY(-1px);
        }

        /* ── Cancel edit (when in edit mode with saved data) ── */
        .alamat-cancel-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.375rem;
            padding: 0.4375rem 0.875rem;
            border-radius: 9999px;
            background: transparent;
            color: #64748B;
            border: 1px solid #E2E8F0;
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 0.75rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 180ms ease, color 180ms ease, border-color 180ms ease;
        }
        .alamat-cancel-btn:hover {
            background: #F8FAFC;
            color: #1A1A1A;
            border-color: #CBD5E1;
        }

        @media (prefers-reduced-motion: reduce) {
            .alamat-edit-btn,
            .alamat-cancel-btn { transition: none; }
            .alamat-edit-btn:hover { transform: none; }
        }
    </style>
@endonce
