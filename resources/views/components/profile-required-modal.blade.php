{{--
  Profile Required Modal
  ----------------------------------------------------------------------------
  Tampil otomatis kalau session('show_profile_required_modal') true.
  Dipasang sekali di dashboard.blade.php. User dapat opsi: lengkapi profil
  sekarang (auto-scroll ke section profil + buka tab profile) atau Nanti Saja.
--}}

@php
    $shouldShow = session('show_profile_required_modal') ? true : false;
@endphp

<div x-data="{
        open: @js($shouldShow),
        scrollToProfile() {
            // Buka tab profile dulu
            this.$dispatch('switch-tab', 'profile_data');
            this.open = false;

            // Scroll ke heading profile setelah Alpine update DOM
            this.$nextTick(() => {
                const target = document.getElementById('sec-profil') || document.getElementById('sec-profile') || document.querySelector('[data-profile-section]');
                if (target) {
                    target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            });
        },
     }"
     x-show="open"
     x-cloak
     @keydown.escape.window="open = false"
     class="fixed inset-0 z-[10000] flex items-center justify-center bg-black/50 backdrop-blur-sm p-4"
     role="alertdialog"
     aria-modal="true"
     aria-labelledby="profile-required-title"
     x-transition:enter="ease-out duration-200"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="ease-in duration-150"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0">

    <div class="bg-white w-full max-w-sm rounded-3xl p-7 shadow-2xl text-center"
         @click.away="open = false"
         x-transition:enter="ease-out duration-300"
         x-transition:enter-start="opacity-0 scale-90"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="ease-in duration-150"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-90">

        {{-- Icon: warning amber --}}
        <div class="mx-auto mb-5 flex h-20 w-20 items-center justify-center rounded-full bg-amber-500 shadow-lg shadow-amber-500/30">
            <svg class="h-10 w-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
        </div>

        <h3 id="profile-required-title" class="text-lg font-extrabold text-[#1A1A1A]">Lengkapi Profil Anda</h3>
        <p class="text-sm text-slate-500 mt-2 leading-relaxed">
            Sebelum membuat janji temu, mohon lengkapi <span class="font-bold text-[#1A1A1A]">Nomor WhatsApp</span> dan <span class="font-bold text-[#1A1A1A]">Tanggal Lahir</span> Anda terlebih dahulu.
        </p>

        <div class="mt-3 inline-block max-w-full rounded-full bg-[#FAF8F5] border border-[#E8E0D8] px-4 py-2 text-xs font-bold text-[#1A1A1A]">
            Data ini diperlukan klinik untuk konfirmasi janji
        </div>

        <div class="flex flex-col sm:flex-row gap-2 mt-6">
            <button type="button"
                    @click="open = false"
                    class="flex-1 py-2.5 rounded-full border border-slate-200 text-sm font-bold text-slate-700 hover:bg-slate-50 cursor-pointer transition-colors">
                Nanti Saja
            </button>
            <button type="button"
                    @click="scrollToProfile()"
                    class="flex-1 inline-flex items-center justify-center gap-1.5 rounded-full bg-[#1A1A1A] hover:bg-[#CFAF53] text-white text-sm font-bold py-2.5 transition-colors cursor-pointer">
                Lengkapi Sekarang
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                </svg>
            </button>
        </div>
    </div>
</div>
