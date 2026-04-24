{{-- ====== TAB: PROFIL SAYA ====== --}}
<div>
    <h2 class="text-xl font-extrabold text-[#1A1A1A] mb-6">Profil Saya</h2>

    <form action="{{ route('pasien.profile.update') }}" method="POST" enctype="multipart/form-data"
          class="bg-white rounded-2xl border border-[#E8E0D8]/60 p-6 sm:p-8"
          x-data="addressSelector()">
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
        <h3 class="text-xs font-bold uppercase tracking-[0.2em] text-[#8C7A63] mb-4">Data Diri</h3>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-6">
            <div class="col-span-2">
                <label class="block text-xs font-bold text-slate-500 mb-2">Ganti Foto Profil</label>
                <input type="file" name="foto" class="block w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-[#CFAF53]/10 file:text-[#CFAF53] hover:file:bg-[#CFAF53]/20"/>
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
                <input type="text" name="phone" value="{{ old('phone', $user->phone) }}" class="w-full rounded-xl border-[#E8E0D8]/60 text-sm focus:border-[#CFAF53] focus:ring-[#CFAF53]/20" placeholder="081xxx">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-500 mb-1">Tanggal Lahir</label>
                <input type="date" name="tanggal_lahir" value="{{ old('tanggal_lahir', $user->tanggal_lahir) }}" class="w-full rounded-xl border-[#E8E0D8]/60 text-sm focus:border-[#CFAF53] focus:ring-[#CFAF53]/20">
            </div>

            <div class="col-span-2">
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

        {{-- Section: Alamat --}}
        <div class="border-t border-[#E8E0D8]/40 my-6"></div>
        <h3 class="text-xs font-bold uppercase tracking-[0.2em] text-[#8C7A63] mb-4">📍 Alamat Pengiriman</h3>

        <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 mb-6">
            <p class="text-xs text-blue-800">💡 <strong>Penting:</strong> Pilih kecamatan dengan benar agar ongkir bisa dihitung otomatis saat checkout!</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <div>
                <label class="block text-xs font-bold text-slate-500 mb-1">Provinsi</label>
                <select x-model="selectedProvinceId" @change="fetchCities()" class="w-full rounded-xl border-[#E8E0D8]/60 text-sm focus:border-[#CFAF53]">
                    <option value="">-- Pilih Provinsi --</option>
                    <template x-for="item in provinces" :key="item.id"><option :value="item.id" x-text="item.name"></option></template>
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-500 mb-1">Kota / Kabupaten</label>
                <select x-model="selectedCityId" @change="fetchDistricts()" :disabled="!selectedProvinceId" class="w-full rounded-xl border-[#E8E0D8]/60 text-sm focus:border-[#CFAF53] disabled:bg-slate-100">
                    <option value="">-- Pilih Kota --</option>
                    <template x-for="item in cities" :key="item.id"><option :value="item.id" x-text="item.name"></option></template>
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-500 mb-1">Kecamatan</label>
                <select x-model="selectedDistrictId" @change="updateLocationNames()" :disabled="!selectedCityId" class="w-full rounded-xl border-[#E8E0D8]/60 text-sm focus:border-[#CFAF53] disabled:bg-slate-100">
                    <option value="">-- Pilih Kecamatan --</option>
                    <template x-for="item in districts" :key="item.id"><option :value="item.id" x-text="item.name"></option></template>
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-500 mb-1">Kode Pos</label>
                <input type="text" name="kode_pos" value="{{ old('kode_pos', $user->pasienProfile?->kode_pos) }}" class="w-full rounded-xl border-[#E8E0D8]/60 text-sm focus:border-[#CFAF53]">
            </div>

            <div class="col-span-2">
                <label class="block text-xs font-bold text-slate-500 mb-1">Alamat Lengkap (Jalan, RT/RW)</label>
                <textarea name="alamat" rows="2" class="w-full rounded-xl border-[#E8E0D8]/60 text-sm focus:border-[#CFAF53]">{{ old('alamat', $user->pasienProfile?->alamat) }}</textarea>
            </div>
        </div>

        <div class="mt-8 flex justify-end">
            <button type="submit" class="bg-[#CFAF53] text-[#1A1A1A] font-bold px-8 py-3 rounded-full hover:bg-[#b59b48] shadow-lg shadow-[#CFAF53]/20 transition">
                Simpan Perubahan
            </button>
        </div>
    </form>
</div>
