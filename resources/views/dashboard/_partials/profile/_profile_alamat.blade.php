{{-- Partial: Profile Alamat --}}
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
<div class="bg-white rounded-2xl border border-[#E8E0D8]/60 p-6 sm:p-8" x-show="activeTab === 'profile_alamat'"
    style="display: none;">

    {{-- Header --}}
    <div class="mb-6 flex items-start justify-between gap-3 flex-wrap" id="sec-alamat" style="scroll-margin-top: 8rem;">
        <div class="min-w-0">
            <h3 class="text-xl font-extrabold tracking-tight text-[#1A1A1A]">Alamat Pengiriman</h3>
            <p class="text-sm text-slate-500 mt-1 leading-relaxed">Lengkapi data alamat untuk mempermudah proses
                pengiriman pesanan.</p>
        </div>
        {{-- Toggle Ubah Alamat (only visible when address is already saved & not editing) --}}
        <button type="button" @click="enterEditMode()" x-show="hasSavedAddress && !editingAddress" x-cloak
            class="alamat-edit-btn">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
            </svg>
            <span>Ubah Alamat</span>
        </button>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-5 gap-y-4">

        {{-- Nomor HP / WhatsApp (always editable) --}}
        <div class="sm:col-span-2">
            <label for="phone-alamat" class="block text-xs font-bold text-slate-600 mb-1.5">Nomor HP / WhatsApp</label>
            <input type="text" id="phone-alamat" x-model="phoneNumber" maxlength="15" inputmode="numeric"
                oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 15)" placeholder="08xxxxxxxxxx"
                class="alamat-input">
            <p class="text-xs text-slate-400 mt-1.5">Kami akan menghubungi nomor ini untuk konfirmasi pengiriman.</p>
        </div>

        {{-- â•â•â•â•â•â•â•â• VIEW MODE (no API hit) â•â•â•â•â•â•â•â• --}}
        <template x-if="!editingAddress && hasSavedAddress">
            @include('dashboard._partials.profile._profile_alamat_view')
        </template>

        {{-- â•â•â•â•â•â•â•â• EDIT MODE (API loads on demand) â•â•â•â•â•â•â•â• --}}
        <template x-if="editingAddress || !hasSavedAddress">
            @include('dashboard._partials.profile._profile_alamat_edit')
        </template>

        <div class="sm:col-span-2">
            <label for="alamat-lengkap" class="block text-xs font-bold text-slate-600 mb-1.5">Alamat Lengkap</label>
            <textarea id="alamat-lengkap" name="alamat" form="profile-form" rows="3"
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