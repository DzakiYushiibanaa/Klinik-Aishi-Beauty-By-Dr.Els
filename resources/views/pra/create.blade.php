<x-guest-layout :title="'Identifikasi Kebutuhan Kulit'">
<div class="w-full min-h-screen bg-[#FAF8F5]" x-data="praForm()" id="form-top">
    <!-- Back Navigation -->
    <div class="mx-auto max-w-2xl px-4 pt-6">
        <a href="{{ route('dashboard', ['tab' => 'prakonsul']) }}" class="inline-flex items-center gap-2 text-sm text-[#8C7A63] hover:text-[#6B5D4D] transition-colors font-medium">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            Kembali
        </a>
    </div>

    <!-- Main Card -->
    <div class="mx-auto max-w-2xl px-4 py-6">
        <div class="rounded-2xl bg-white shadow-sm border border-[#E8E0D8]/60 overflow-hidden">
            <!-- Header -->
            <div class="px-6 pt-8 pb-6 text-center sm:px-10">
                <div class="inline-flex items-center gap-2 rounded-full bg-[#FAF8F5] px-4 py-1.5 text-xs text-[#8C7A63] mb-5">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Hanya butuh waktu sekitar 3-5 menit untuk melengkapi.
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-[#1A1A1A] mb-3">Identifikasi Kebutuhan Kulit</h1>
                <p class="text-sm text-gray-500 max-w-md mx-auto leading-relaxed">Bantu kami mengenal kulit Anda lebih dalam. Pengisian data riwayat dan unggah foto wajah akan memudahkan dokter kami dalam menyusun rencana perawatan yang paling efektif khusus untuk Anda.</p>

                <!-- Stepper -->
                <div class="mt-8 mb-4">
                    <div class="flex items-center justify-between max-w-lg mx-auto">
                        <template x-for="(label, i) in stepLabels" :key="i">
                            <div class="flex items-center" :class="i < 4 ? 'flex-1' : ''">
                                <div class="flex flex-col items-center relative z-10">
                                    <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold transition-all duration-300"
                                        :class="step > (i+1) ? 'bg-[#8C7A63] text-white' : (step === (i+1) ? 'bg-[#8C7A63] text-white ring-4 ring-[#8C7A63]/20' : 'bg-white border-2 border-gray-300 text-gray-400')">
                                        <template x-if="step > (i+1)">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                        </template>
                                        <template x-if="step <= (i+1)"><span x-text="i+1"></span></template>
                                    </div>
                                    <span class="hidden sm:block text-[10px] mt-1.5 whitespace-nowrap font-medium transition-colors"
                                        :class="step >= (i+1) ? 'text-[#8C7A63]' : 'text-gray-400'" x-text="label"></span>
                                </div>
                                <div x-show="i < 4" class="flex-1 h-0.5 mx-1 transition-colors duration-300"
                                    :class="step > (i+1) ? 'bg-[#8C7A63]' : 'bg-gray-200'"></div>
                            </div>
                        </template>
                    </div>
                </div>
                <p class="text-sm italic text-[#8C7A63] mt-4" x-text="'&quot;' + stepQuotes[step-1] + '&quot;'"></p>
            </div>



            <!-- Form -->
            <form method="POST" action="{{ route('pra.store') }}" enctype="multipart/form-data" x-ref="praForm">
                @csrf
                <div class="px-6 sm:px-10 pb-6">
                    @include('pra.partials._step1')
                    @include('pra.partials._step2')
                    @include('pra.partials._step3')
                    @include('pra.partials._step4')
                    @include('pra.partials._step5')
                </div>

                <!-- Navigation -->
                <div class="flex items-center justify-between px-6 sm:px-10 py-5 border-t border-gray-100 bg-[#FDFCFB]">
                    <button type="button" x-show="step > 1" @click="prevStep()" x-cloak
                        class="inline-flex items-center gap-2 rounded-xl border border-gray-300 bg-white px-5 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50 transition-all">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                        Kembali
                    </button>
                    <div x-show="step === 1"></div>
                    <button type="button" x-show="step < 5" @click="nextStep()"
                        class="inline-flex items-center gap-2 rounded-xl bg-[#1A1A1A] px-6 py-2.5 text-sm font-semibold text-white hover:bg-[#333] transition-all">
                        Selanjutnya
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </button>
                    <button type="button" x-show="step === 5" @click="showModal = true" x-cloak
                        class="inline-flex items-center gap-2 rounded-xl bg-[#1A1A1A] px-6 py-2.5 text-sm font-bold text-white hover:bg-[#333] transition-all">
                        Kirim Form Sekarang
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Confirmation Modal -->
    <div x-show="showModal" x-cloak class="fixed inset-0 z-[9999] flex items-center justify-center p-4" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
        <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" @click="showModal = false"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl max-w-md w-full p-8 text-center z-10" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100">
            <div class="w-14 h-14 rounded-full bg-[#FFF8E7] flex items-center justify-center mx-auto mb-4">
                <svg class="w-7 h-7 text-[#8C7A63]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            </div>
            <h3 class="font-serif text-xl font-bold text-[#1A1A1A] mb-2">Konfirmasi Pengiriman</h3>
            <p class="text-sm text-gray-500 mb-6 leading-relaxed">Pastikan data yang Anda masukkan <strong class="text-[#1A1A1A]">sebenar-benarnya</strong>. Data ini akan digunakan oleh dokter untuk menyusun rencana perawatan Anda.</p>
            <div class="flex gap-3">
                <button type="button" @click="showModal = false" class="flex-1 rounded-xl border border-gray-300 px-4 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50 transition">Batal</button>
                <button type="button" @click="$refs.praForm.submit()" class="flex-1 rounded-xl bg-[#1A1A1A] px-4 py-2.5 text-sm font-bold text-white hover:bg-[#333] transition">Ya, Kirim Sekarang</button>
            </div>
        </div>
    </div>
</div>

<style>[x-cloak]{display:none!important}</style>
<script>
function praForm(){
    return {
        step: @if($errors->hasAny(['keluhan_utama','jenis_kulit','golongan_darah'])) 1 @elseif($errors->hasAny(['skincare_sebelumnya','periode_skincare_bulan'])) 2 @elseif($errors->hasAny(['riwayat_penyakit','riwayat_alergi'])) 3 @elseif($errors->hasAny(['foto_depan','foto_kiri','foto_kanan'])) 4 @else 1 @endif,
        showModal:false,
        stepLabels:['Kondisi Kulit','Riwayat Produk','Riwayat Medis','Unggah Foto','Ringkasan'],
        stepQuotes:['Mari mulai perjalanan kulit sehat Anda.','Bagus! Lanjut ceritakan rutinitas skincare Anda.','Sedikit lagi! Pastikan riwayat medis Anda sesuai.','Satu langkah lagi! Unggah foto wajah Anda.','Periksa kembali data Anda sebelum mengirimkan form.'],
        jenis_kulit: @json(old('jenis_kulit','')),
        golongan_darah: @json(old('golongan_darah','')),
        keluhan_utama: @json(old('keluhan_utama','')),
        skincare_sebelumnya: @json(old('skincare_sebelumnya','')),
        periode_skincare_bulan: @json(old('periode_skincare_bulan','')),
        riwayat_penyakit: @json(old('riwayat_penyakit','')),
        riwayat_alergi: @json(old('riwayat_alergi','')),
        keluhan_utama_error: @json($errors->first('keluhan_utama') ?: ''),
        skincare_sebelumnya_error: @json($errors->first('skincare_sebelumnya') ?: ''),
        periode_skincare_bulan_error: @json($errors->first('periode_skincare_bulan') ?: ''),
        pernah_facial:{{ old('pernah_facial') ? 'true':'false' }},
        pernah_laser:{{ old('pernah_laser') ? 'true':'false' }},
        pernah_skin_booster:{{ old('pernah_skin_booster') ? 'true':'false' }},
        pernah_inject_infus:{{ old('pernah_inject_infus') ? 'true':'false' }},
        foto_depan_name:'',foto_kanan_name:'',foto_kiri_name:'',
        foto_depan_preview:'',foto_kanan_preview:'',foto_kiri_preview:'',
        foto_depan_error: @json($errors->first('foto_depan') ?: ''),
        foto_kanan_error: @json($errors->first('foto_kanan') ?: ''),
        foto_kiri_error: @json($errors->first('foto_kiri') ?: ''),
        init() {
            this.$watch('keluhan_utama', value => {
                if (!value || value.trim() === '') {
                    this.keluhan_utama_error = 'Keluhan utama wajib diisi.';
                } else if (value.trim().length < 10) {
                    this.keluhan_utama_error = 'Keluhan utama minimal 10 karakter.';
                } else {
                    this.keluhan_utama_error = '';
                }
            });
            
            this.$watch('skincare_sebelumnya', value => {
                if (value && value.length > 2000) {
                    this.skincare_sebelumnya_error = 'Skincare sebelumnya maksimal 2000 karakter.';
                } else {
                    this.skincare_sebelumnya_error = '';
                }
            });
            
            this.$watch('periode_skincare_bulan', value => {
                if (value && (value < 0 || value > 240)) {
                    this.periode_skincare_bulan_error = 'Periode pemakaian harus antara 0 hingga 240 bulan.';
                } else {
                    this.periode_skincare_bulan_error = '';
                }
            });
        },
        validateImage(file, position) {
            this[`${position}_error`] = '';
            this[`${position}_name`] = file.name;
            this[`${position}_preview`] = '';
            
            if (file.size > 4 * 1024 * 1024) {
                this[`${position}_error`] = `Ukuran foto maksimal 4MB.`;
                return;
            }
            
            const validTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/jpg'];
            if (!validTypes.includes(file.type)) {
                this[`${position}_error`] = `Format foto harus berupa JPG, JPEG, PNG, atau WEBP.`;
                return;
            }
            
            const _this = this;
            const reader = new FileReader();
            reader.onload = (e) => {
                const img = new Image();
                img.onload = () => {
                    if (img.width < 720 || img.height < 720) {
                        _this[`${position}_error`] = `Resolusi foto minimal 720x720 pixel.`;
                    } else {
                        _this[`${position}_preview`] = e.target.result;
                    }
                };
                img.src = e.target.result;
            };
            reader.readAsDataURL(file);
        },
        nextStep(){if(this.step<5){this.step++;this.$nextTick(()=>document.getElementById('form-top').scrollIntoView({behavior:'smooth'}))}},
        prevStep(){if(this.step>1){this.step--;this.$nextTick(()=>document.getElementById('form-top').scrollIntoView({behavior:'smooth'}))}},
        goToStep(s){this.step=s;this.$nextTick(()=>document.getElementById('form-top').scrollIntoView({behavior:'smooth'}))},
        get treatmentList(){let l=[];if(this.pernah_facial)l.push('Facial');if(this.pernah_laser)l.push('Laser');if(this.pernah_skin_booster)l.push('Skin Booster');if(this.pernah_inject_infus)l.push('Inject / Infus');return l.length?l.join(', '):'-'}
    }
}
</script>
</x-guest-layout>
