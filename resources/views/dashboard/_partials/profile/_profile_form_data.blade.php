{{-- Partial: Profile Form Data --}}
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

    <input type="hidden" name="phone" x-model="phoneNumber">

    {{-- Section: Data Diri --}}
    <h3 id="sec-profil" class="text-xs font-bold uppercase tracking-[0.2em] text-[#8C7A63] mb-4 scroll-mt-32">Data Diri
    </h3>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5 mb-6">
        <div class="lg:col-span-2" x-data="{ photoName: null, photoPreview: null }">
            <label class="block text-xs font-bold text-slate-500 mb-4">Ganti Foto Profil</label>
            <input type="file" name="foto" id="foto" class="hidden" x-ref="photo" accept=".jpg,.jpeg,.png" @change="
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
                            <img src="{{ asset('storage/' . $user->pasienProfile->foto_profil_path) }}"
                                class="w-full h-full object-cover">
                        @else
                            <span
                                class="text-3xl sm:text-4xl font-extrabold text-[#CFAF53] uppercase">{{ substr($user->name, 0, 1) }}</span>
                        @endif
                    </div>

                    {{-- Hover Overlay --}}
                    <div
                        class="absolute inset-0 bg-black/40 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                        <svg class="w-6 h-6 sm:w-8 sm:h-8 text-white" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </div>
                </div>

                {{-- Actions & Info --}}
                <div>
                    <button type="button" @click="$refs.photo.click()"
                        class="bg-white border border-[#E8E0D8] text-[#1A1A1A] font-bold text-xs px-4 py-2 rounded-full hover:bg-slate-50 transition shadow-sm mb-2">
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
            <input type="text" name="name" value="{{ old('name', $user->name) }}"
                class="w-full rounded-xl border-[#E8E0D8]/60 text-sm focus:border-[#CFAF53] focus:ring-[#CFAF53]/20"
                required>
        </div>

        <div>
            <label class="block text-xs font-bold text-slate-500 mb-1">Email (Login)</label>
            <input type="email" name="email" value="{{ $user->email }}"
                class="w-full rounded-xl border-[#E8E0D8]/60 bg-[#FAF8F5] text-slate-400 text-sm cursor-not-allowed"
                readonly>
        </div>

        <div>
            <label class="block text-xs font-bold text-slate-500 mb-1">Nomor WhatsApp</label>
            <input type="text" x-model="phoneNumber" maxlength="13"
                oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 13)"
                class="w-full rounded-xl border-[#E8E0D8]/60 text-sm focus:border-[#CFAF53] focus:ring-[#CFAF53]/20"
                placeholder="081xxx">
        </div>

        <div>
            <label class="block text-xs font-bold text-slate-500 mb-1">Tanggal Lahir</label>
            <x-date-picker name="tanggal_lahir" :value="old('tanggal_lahir', $user->pasienProfile?->tanggal_lahir)"
                placeholder="dd/mm/yyyy" />
        </div>

        <div class="lg:col-span-2">
            <label class="block text-xs font-bold text-slate-500 mb-2">Jenis Kelamin</label>
            <div class="flex gap-4">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="radio" name="jenis_kelamin" value="L" {{ old('jenis_kelamin', $user->pasienProfile?->jenis_kelamin) == 'L' ? 'checked' : '' }}
                        class="text-[#CFAF53] focus:ring-[#CFAF53]">
                    <span class="text-sm text-slate-700">Laki-laki</span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="radio" name="jenis_kelamin" value="P" {{ old('jenis_kelamin', $user->pasienProfile?->jenis_kelamin) == 'P' ? 'checked' : '' }}
                        class="text-[#CFAF53] focus:ring-[#CFAF53]">
                    <span class="text-sm text-slate-700">Perempuan</span>
                </label>
            </div>
        </div>
    </div>

    <div class="mt-8 flex justify-end">
        <button type="submit"
            class="bg-[#CFAF53] text-[#1A1A1A] font-bold px-8 py-3 rounded-full hover:bg-[#b59b48] shadow-lg shadow-[#CFAF53]/20 transition">
            Simpan Data Diri
        </button>
    </div>
</form>