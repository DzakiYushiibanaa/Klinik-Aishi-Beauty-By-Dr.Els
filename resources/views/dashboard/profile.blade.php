{{-- ====== TAB: PROFIL SAYA ====== --}}
<div x-data="addressSelector()" @input="profileIsDirty = true" @submit="profileIsDirty = false">
    <h2 class="text-xl font-extrabold text-[#1A1A1A] mb-6"
        x-text="activeTab === 'profile_password' ? 'Ganti Password' : (activeTab === 'profile_alamat' ? 'Alamat Saya' : 'Profil Saya')">
        Profil Saya
    </h2>
    @include('dashboard._partials.profile._profile_form_data')
    @include('dashboard._partials.profile._profile_form_password')
    @include('dashboard._partials.profile._profile_alamat')
    @include('dashboard._partials.profile._profile_styles')
</div>
