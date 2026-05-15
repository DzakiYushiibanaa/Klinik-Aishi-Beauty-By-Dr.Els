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
                <input type="text" name="phone" value="{{ old('phone', $user->phone) }}" maxlength="13" oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 13)" class="w-full rounded-xl border-[#E8E0D8]/60 text-sm focus:border-[#CFAF53] focus:ring-[#CFAF53]/20" placeholder="081xxx">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-500 mb-1">Tanggal Lahir</label>
                <input type="date" name="tanggal_lahir" value="{{ old('tanggal_lahir', $user->tanggal_lahir) }}" class="w-full rounded-xl border-[#E8E0D8]/60 text-sm focus:border-[#CFAF53] focus:ring-[#CFAF53]/20">
            </div>

            <div class="lg:col-span-2">
                <label class="block text-xs font-bold text-slate-500 mb-2">Jenis Kelamin</label>
                <div class="flex gap-4">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="jenis_kelamin" value="L" {{ old('jenis_kelamin', $user->jenis_kelamin) == 'L' ? 'checked' : '' }} class="text-[#CFAF53] focus:ring-[#CFAF53]">
                        <span class="text-sm text-slate-700">Laki-laki</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="jenis_kelamin" value="P" {{ old('jenis_kelamin', $user->jenis_kelamin) == 'P' ? 'checked' : '' }} class="text-[#CFAF53] focus:ring-[#CFAF53]">
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
                <input type="password" name="current_password" required autocomplete="current-password"
                       class="w-full lg:w-1/2 rounded-xl border-[#E8E0D8]/60 text-sm focus:border-[#CFAF53]">
                @error('current_password', 'updatePassword')
                    <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span>
                @enderror
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-500 mb-1">Password Baru</label>
                <input type="password" name="password" required autocomplete="new-password"
                       class="w-full rounded-xl border-[#E8E0D8]/60 text-sm focus:border-[#CFAF53]">
                @error('password', 'updatePassword')
                    <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span>
                @enderror
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-500 mb-1">Konfirmasi Password Baru</label>
                <input type="password" name="password_confirmation" required autocomplete="new-password"
                       class="w-full rounded-xl border-[#E8E0D8]/60 text-sm focus:border-[#CFAF53]">
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
    <div class="bg-white rounded-2xl border border-[#E8E0D8]/60 p-6 sm:p-8"
         x-show="activeTab === 'profile_alamat'" style="display: none;">
        <h3 id="sec-alamat" class="text-xs font-bold uppercase tracking-[0.2em] text-[#8C7A63] mb-4 scroll-mt-32">Alamat Pengiriman</h3>

        <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 mb-6">
            <p class="text-xs text-blue-800">💡 <strong>Penting:</strong> Pilih kecamatan dengan benar agar ongkir bisa dihitung otomatis saat checkout!</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
            <div>
                <label class="block text-xs font-bold text-slate-500 mb-1">Provinsi</label>
                <div class="relative">
                    <select x-model="selectedProvinceId" @change="fetchCities()" class="w-full rounded-xl border-[#E8E0D8]/60 text-sm focus:border-[#CFAF53]" :class="loadingProvinces && 'opacity-50'">
                        <option value="">-- Pilih Provinsi --</option>
                        <template x-for="item in provinces" :key="item.id"><option :value="item.id" x-text="item.name"></option></template>
                    </select>
                    <div x-show="loadingProvinces" class="absolute right-10 top-1/2 -translate-y-1/2">
                        <svg class="w-4 h-4 animate-spin text-[#CFAF53]" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                    </div>
                </div>
                <p x-show="!resolved && savedProvinceName && !selectedProvinceId" x-cloak class="text-xs text-[#8C7A63] mt-1 flex items-center gap-1">
                    <svg class="w-3 h-3 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                    Memuat: <span x-text="savedProvinceName" class="font-semibold"></span>
                </p>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-500 mb-1">Kota / Kabupaten</label>
                <div class="relative">
                    <select x-model="selectedCityId" @change="fetchDistricts()" :disabled="!selectedProvinceId" class="w-full rounded-xl border-[#E8E0D8]/60 text-sm focus:border-[#CFAF53] disabled:bg-slate-100" :class="loadingCities && 'opacity-50'">
                        <option value="">-- Pilih Kota --</option>
                        <template x-for="item in cities" :key="item.id"><option :value="item.id" x-text="item.name"></option></template>
                    </select>
                    <div x-show="loadingCities" class="absolute right-10 top-1/2 -translate-y-1/2">
                        <svg class="w-4 h-4 animate-spin text-[#CFAF53]" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                    </div>
                </div>
                <p x-show="!resolved && savedCityName && !selectedCityId" x-cloak class="text-xs text-[#8C7A63] mt-1 flex items-center gap-1">
                    <svg class="w-3 h-3 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                    Memuat: <span x-text="savedCityName" class="font-semibold"></span>
                </p>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-500 mb-1">Kecamatan</label>
                <div class="relative">
                    <select x-model="selectedDistrictId" @change="updateLocationNames()" :disabled="!selectedCityId" class="w-full rounded-xl border-[#E8E0D8]/60 text-sm focus:border-[#CFAF53] disabled:bg-slate-100" :class="loadingDistricts && 'opacity-50'">
                        <option value="">-- Pilih Kecamatan --</option>
                        <template x-for="item in districts" :key="item.id"><option :value="item.id" x-text="item.name"></option></template>
                    </select>
                    <div x-show="loadingDistricts" class="absolute right-10 top-1/2 -translate-y-1/2">
                        <svg class="w-4 h-4 animate-spin text-[#CFAF53]" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                    </div>
                </div>
                <p x-show="!resolved && savedDistrictName && !selectedDistrictId" x-cloak class="text-xs text-[#8C7A63] mt-1 flex items-center gap-1">
                    <svg class="w-3 h-3 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                    Memuat: <span x-text="savedDistrictName" class="font-semibold"></span>
                </p>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-500 mb-1">Kode Pos</label>
                <input type="text" name="kode_pos" form="profile-form" value="{{ old('kode_pos', $user->pasienProfile?->kode_pos) }}" class="w-full rounded-xl border-[#E8E0D8]/60 text-sm focus:border-[#CFAF53]">
            </div>

            <div class="lg:col-span-2">
                <label class="block text-xs font-bold text-slate-500 mb-1">Alamat Lengkap (Jalan, RT/RW)</label>
                <textarea name="alamat" form="profile-form" rows="2" class="w-full rounded-xl border-[#E8E0D8]/60 text-sm focus:border-[#CFAF53]">{{ old('alamat', $user->pasienProfile?->alamat) }}</textarea>
            </div>
        </div>

        <div class="mt-8 flex justify-end">
            <button type="submit" form="profile-form" class="bg-[#CFAF53] text-[#1A1A1A] font-bold px-8 py-3 rounded-full hover:bg-[#b59b48] shadow-lg shadow-[#CFAF53]/20 transition">
                Simpan Alamat & Profil
            </button>
        </div>
    </div>
</div>
