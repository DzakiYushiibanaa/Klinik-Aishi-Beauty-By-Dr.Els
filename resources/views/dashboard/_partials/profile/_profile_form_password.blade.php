{{-- Partial: Profile Form Password --}}
<form id="password-form" action="{{ route('password.update') }}" method="POST"
    class="bg-white rounded-2xl border border-[#E8E0D8]/60 p-6 sm:p-8 mb-6" x-show="activeTab === 'profile_password'"
    style="display: none;">
    @csrf
    @method('PUT')

    <h3 id="sec-password" class="text-xs font-bold uppercase tracking-[0.2em] text-[#8C7A63] mb-4 scroll-mt-32">Ganti
        Password</h3>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
        <div class="col-span-1 lg:col-span-2">
            <label class="block text-xs font-bold text-slate-500 mb-1">Password Saat Ini</label>
            <div class="w-full lg:w-1/2">
                <x-password-input name="current_password" autocomplete="current-password" variant="compact" />
            </div>
            @error('current_password', 'updatePassword')
                <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span>
            @enderror
        </div>

        <div>
            <label class="block text-xs font-bold text-slate-500 mb-1">Password Baru</label>
            <x-password-input name="password" autocomplete="new-password" variant="compact" />
            @error('password', 'updatePassword')
                <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span>
            @enderror
        </div>

        <div>
            <label class="block text-xs font-bold text-slate-500 mb-1">Konfirmasi Password Baru</label>
            <x-password-input name="password_confirmation" autocomplete="new-password" variant="compact" />
            @error('password_confirmation', 'updatePassword')
                <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span>
            @enderror
        </div>
    </div>

    <div class="mt-8 flex justify-end items-center gap-4">
        @if (session('status') === 'password-updated')
            <span class="text-sm font-bold text-emerald-600">Password berhasil diubah.</span>
        @endif
        <button type="submit"
            class="bg-slate-800 text-white font-bold px-8 py-3 rounded-full hover:bg-slate-900 shadow-lg shadow-slate-200 transition">
            Ganti Password
        </button>
    </div>
</form>