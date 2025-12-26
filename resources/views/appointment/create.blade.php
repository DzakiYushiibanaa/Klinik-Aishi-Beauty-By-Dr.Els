<x-guest-layout :title="'Buat Janji Temu'">

{{-- STATE MANAGEMENT (ALPINE JS) --}}
<div x-data="{
    // State Reactive
    jenis: '{{ old('jenis', 'treatment') }}', 
    selectedTreatments: {{ json_encode(old('treatments', $autoTreatments)) }}, 
    selectedDoctor: '{{ old('dokter_id', $autoDoctorId) }}', 
    terapisId: '{{ old('terapis_id') }}', 

    // Data Master
    therapists: {{ $therapists_json }},
    treatmentsList: {{ $treatments->map(fn($t) => ['id' => $t->treatment_id, 'min_level' => $t->min_level ?? 1])->toJson() }},
    
    // Alert System
    alertOpen: false,
    alertMessage: '',

    // Logic: Hitung Level Tertinggi dari Treatment yang dipilih
    get requiredLevel() {
        let maxLevel = 1;
        this.selectedTreatments.forEach(id => {
            let t = this.treatmentsList.find(item => item.id == id);
            if (t && t.min_level > maxLevel) maxLevel = t.min_level;
        });
        return maxLevel; 
    },

    // Logic: Filter Terapis (Langsung hide yang tidak qualified)
    get filteredTherapists() {
        const req = this.requiredLevel;
        return this.therapists.filter(t => t.level >= req);
    },

    // Logic: Reset Pilihan jika Filter Berubah
    init() {
        this.$watch('requiredLevel', (newVal) => {
            if (this.terapisId) {
                let current = this.therapists.find(t => t.id == this.terapisId);
                // Kalau terapis yang dipilih levelnya dibawah standar baru -> Reset
                if (current && current.level < newVal) {
                    this.terapisId = ''; 
                }
            }
        });
    },

    // Validasi Submit Button
    submit(e) {
        if (this.jenis === 'treatment' && this.selectedTreatments.length === 0) {
            this.alertMessage = 'Untuk jenis Treatment, Anda wajib memilih minimal 1 item perawatan.';
            this.alertOpen = true;
            e.preventDefault();
            return;
        }

        if (this.jenis === 'konsultasi' && !this.selectedDoctor) {
            this.alertMessage = 'Untuk Konsultasi, Anda wajib memilih Dokter yang bertugas.';
            this.alertOpen = true;
            e.preventDefault();
            return;
        }
    }
}" x-init="init()">

<div class="max-w-5xl mx-auto rounded-3xl bg-white p-6 sm:p-8 shadow-sm">

    {{-- HEADER --}}
    <div class="mb-8">
        <p class="text-xs font-bold uppercase tracking-[0.2em] text-[#CFAF53]">Booking Online</p>
        <h1 class="text-2xl sm:text-3xl font-bold text-slate-900 mt-2">Form Janji Temu</h1>
        <p class="mt-2 text-slate-500">Sesuaikan kebutuhan perawatan wajah Anda.</p>
    </div>

    {{-- ERROR LARAVEL --}}
    @if ($errors->any())
        <div class="mb-6 rounded-2xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-700">
            <ul class="list-disc pl-5">
                @foreach ($errors->all() as $e) <li>{{ $e }}</li> @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('appointment.store') }}" @submit="submit" class="grid gap-8 lg:grid-cols-3">
        @csrf
        @if($linkedFormId) <input type="hidden" name="patient_form_id" value="{{ $linkedFormId }}"> @endif

        <div class="lg:col-span-2 space-y-8">

            {{-- 1. JENIS KUNJUNGAN --}}
            <section class="rounded-3xl border border-slate-200 bg-white p-5">
                <h2 class="text-base font-bold text-slate-800 mb-4">Jenis Kunjungan <span class="text-rose-500">*</span></h2>
                <div class="grid grid-cols-2 gap-4">
                    <label class="cursor-pointer">
                        <input type="radio" name="jenis" value="treatment" x-model="jenis" class="peer sr-only">
                        <div class="flex flex-col items-center justify-center rounded-2xl border-2 border-slate-100 bg-slate-50 p-4 text-center transition-all hover:border-[#CFAF53] peer-checked:border-[#CFAF53] peer-checked:bg-[#CFAF53]/10 peer-checked:text-[#8B6F1B]">
                            <svg class="w-8 h-8 mb-2 text-slate-400 peer-checked:text-[#CFAF53]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.384-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path></svg>
                            <span class="font-bold text-sm">Treatment</span>
                        </div>
                    </label>
                    <label class="cursor-pointer">
                        <input type="radio" name="jenis" value="konsultasi" x-model="jenis" class="peer sr-only">
                        <div class="flex flex-col items-center justify-center rounded-2xl border-2 border-slate-100 bg-slate-50 p-4 text-center transition-all hover:border-[#CFAF53] peer-checked:border-[#CFAF53] peer-checked:bg-[#CFAF53]/10 peer-checked:text-[#8B6F1B]">
                            <svg class="w-8 h-8 mb-2 text-slate-400 peer-checked:text-[#CFAF53]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path></svg>
                            <span class="font-bold text-sm">Konsultasi Dokter</span>
                        </div>
                    </label>
                </div>
            </section>

            {{-- 2. PILIH TREATMENT --}}
            <section class="rounded-3xl border border-slate-200 bg-slate-50/50 p-5">
                <div class="mb-5 flex items-center justify-between">
                    <h2 class="text-base font-bold text-slate-800">
                        Pilih Treatment 
                        <span x-text="jenis === 'treatment' ? '*' : '(Opsional)'" :class="jenis === 'treatment' ? 'text-rose-500' : 'text-slate-400 font-normal text-xs'"></span>
                    </h2>
                    
                    {{-- Badge Level Dinamis --}}
                    <div x-show="requiredLevel > 1" class="text-xs font-bold text-orange-600 bg-orange-100 px-3 py-1 rounded-full border border-orange-200" style="display: none;" x-transition>
                        🔥 Butuh Terapis Senior
                    </div>
                </div>

                @forelse($treatments->groupBy('category.nama') as $category => $items)
                    <div x-data="{ open: true }" class="mb-4 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                        <button type="button" @click="open = !open" class="flex w-full items-center justify-between bg-white px-5 py-4 text-left hover:bg-slate-50 transition">
                            <span class="font-semibold text-slate-900">{{ $category ?: 'Lainnya' }}</span>
                            <svg :class="open ? 'rotate-180' : ''" class="h-5 w-5 text-slate-400 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                        </button>

                        <div x-show="open" class="border-t border-slate-100 bg-slate-50/50 p-4">
                            <div class="grid gap-3">
                                @foreach($items as $t)
                                    <label class="relative group cursor-pointer block">
                                        <input type="checkbox" name="treatments[]" value="{{ $t->treatment_id }}" x-model="selectedTreatments" class="peer sr-only">
                                        
                                        <div class="flex flex-col sm:flex-row gap-4 rounded-xl border border-slate-200 bg-white p-4 transition-all hover:border-[#CFAF53]/50 hover:shadow-sm peer-checked:border-[#CFAF53] peer-checked:bg-[#CFAF53]/5 peer-checked:ring-1 peer-checked:ring-[#CFAF53] peer-checked:[&_.custom-check]:bg-[#CFAF53] peer-checked:[&_.custom-check]:border-[#CFAF53]">
                                            <div class="flex items-start gap-3 shrink-0">
                                                <div class="custom-check mt-1 flex h-5 w-5 shrink-0 items-center justify-center rounded border border-slate-300 bg-white text-white transition-colors">
                                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                                                </div>
                                                <div class="h-20 w-20 overflow-hidden rounded-lg bg-slate-100 border border-slate-100">
                                                    @if(!empty($t->foto_path)) 
                                                        <img src="{{ asset('storage/' . $t->foto_path) }}" alt="{{ $t->nama }}" class="h-full w-full object-cover">
                                                    @else
                                                        <div class="flex h-full w-full items-center justify-center text-slate-300">
                                                            <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-2">
                                                    <div>
                                                        <h4 class="font-bold text-slate-900 peer-checked:text-[#CFAF53] text-sm sm:text-base">{{ $t->nama }}</h4>
                                                        <div class="mt-2 flex flex-wrap items-center gap-2">
                                                            <span class="inline-flex items-center gap-1 rounded-md bg-slate-100 px-2 py-1 text-[10px] font-medium text-slate-600 border border-slate-200">
                                                                {{ $t->durasi_menit }} Menit
                                                            </span>
                                                        </div>
                                                    </div>
                                                    <div class="text-left sm:text-right mt-1 sm:mt-0">
                                                        <span class="block text-sm font-bold text-[#CFAF53]">Rp {{ number_format($t->harga, 0, ',', '.') }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-8 text-slate-500">Belum ada data treatment.</div>
                @endforelse
            </section>

            {{-- 3. PILIH DOKTER (Hanya Muncul Jika Konsultasi) --}}
            <section x-show="jenis === 'konsultasi'" x-transition 
                     class="rounded-3xl border border-slate-200 bg-white p-5">
                
                <label class="text-base font-bold text-slate-800 mb-4 block">
                    Pilih Dokter <span class="text-rose-500">*</span>
                </label>
                
                <div class="grid grid-cols-2 gap-4 sm:grid-cols-3">
                    @foreach($doctors as $doc)
                        <label class="cursor-pointer">
                            {{-- x-model ke selectedDoctor --}}
                            <input type="radio" 
                                   name="dokter_id" 
                                   value="{{ $doc->id }}" 
                                   x-model="selectedDoctor"
                                   class="peer sr-only">
                            
                            <div class="flex flex-col items-center rounded-2xl border border-slate-100 p-4 text-center transition-all hover:bg-slate-50 peer-checked:border-[#CFAF53] peer-checked:bg-[#CFAF53]/5 peer-checked:ring-1 peer-checked:ring-[#CFAF53]">
                                <div class="h-12 w-12 rounded-full bg-slate-200 mb-2 overflow-hidden">
                                    @if($doc->dokterProfile && $doc->dokterProfile->foto_profil_path)
                                        <img src="{{ asset('storage/' . $doc->dokterProfile->foto_profil_path) }}" class="h-full w-full object-cover">
                                    @endif
                                </div>
                                <p class="text-sm font-bold text-slate-900">{{ $doc->name }}</p>
                                <p class="text-xs text-slate-500">{{ $doc->spesialis ?? 'Umum' }}</p>
                            </div>
                        </label>
                    @endforeach
                </div>
            </section>

            {{-- 4. PILIH TERAPIS (AUTO FILTER) --}}
            <section class="rounded-3xl border border-slate-200 bg-white p-5">
                <div class="mb-4">
                    <label class="text-base font-bold text-slate-800">Pilih Terapis <span class="text-slate-400 font-normal text-xs">(Opsional)</span></label>
                    
                    {{-- Info Text Dinamis --}}
                    <p class="text-xs text-slate-500 mt-1" 
                       x-text="requiredLevel > 1 ? 'Menampilkan Terapis Senior/Expert sesuai treatment.' : 'Menampilkan semua terapis.'">
                    </p>
                </div>

                <div class="grid grid-cols-2 gap-4 sm:grid-cols-3">
                    
                    {{-- Opsi Siapa Saja --}}
                    <label class="cursor-pointer">
                        <input type="radio" name="terapis_id" value="" x-model="terapisId" class="peer sr-only">
                        <div class="flex flex-col items-center justify-center rounded-2xl border-2 border-dashed border-slate-200 bg-slate-50 p-4 text-center transition-all hover:border-[#CFAF53] peer-checked:border-[#CFAF53] peer-checked:bg-[#CFAF53]/10">
                            {{-- Label Dinamis --}}
                            <span class="text-xs font-bold text-slate-600" 
                                  x-text="requiredLevel > 1 ? 'Pilihkan Senior' : 'Siapa Saja'">
                            </span>
                            <span class="text-[10px] text-slate-400 mt-1">(Dipilihkan Admin)</span>
                        </div>
                    </label>
                    
                    {{-- Loop Terapis TERFILTER --}}
                    <template x-for="therapist in filteredTherapists" :key="therapist.id">
                        <label class="cursor-pointer">
                            <input type="radio" name="terapis_id" :value="therapist.id" x-model="terapisId" class="peer sr-only">
                            <div class="flex flex-col items-center rounded-2xl border border-slate-100 p-4 text-center transition-all hover:bg-slate-50 peer-checked:border-[#CFAF53] peer-checked:bg-[#CFAF53]/5 peer-checked:ring-1 peer-checked:ring-[#CFAF53]">
                                <div class="h-12 w-12 rounded-full bg-slate-200 mb-2 flex items-center justify-center font-bold text-slate-500" x-text="therapist.initial"></div>
                                <p class="text-sm font-bold text-slate-900" x-text="therapist.name"></p>
                                <span class="text-[10px] px-2 py-0.5 rounded-full mt-1" :class="therapist.level > 1 ? 'bg-indigo-100 text-indigo-700' : 'bg-slate-100 text-slate-500'" x-text="therapist.display_label"></span>
                            </div>
                        </label>
                    </template>

                </div>
            </section>

        </div>

        {{-- RIGHT COLUMN --}}
        <aside class="space-y-8">
            <section class="rounded-3xl border border-slate-200 bg-white p-5 sticky top-6 shadow-sm">
                <h2 class="text-base font-bold text-slate-800 mb-4">Waktu Kunjungan</h2>
                <div class="mb-4">
                    <label class="block text-xs font-bold text-slate-500 mb-1">Tanggal</label>
                    <input type="date" name="tanggal" min="{{ date('Y-m-d') }}" required class="w-full rounded-xl border-slate-200 text-sm focus:border-[#CFAF53] focus:ring-[#CFAF53]/20">
                </div>
                <div class="mb-6">
                    <label class="block text-xs font-bold text-slate-500 mb-2">Jam</label>
                    <div class="grid grid-cols-3 gap-2">
                        @foreach(['09:00','10:00','11:00','13:00','14:00','15:00','16:00','17:00','19:00'] as $slot)
                            <label class="cursor-pointer">
                                <input type="radio" name="waktu" value="{{ $slot }}" required class="peer sr-only">
                                <div class="rounded-lg border border-slate-200 py-2 text-center text-xs font-bold text-slate-600 hover:border-[#CFAF53] peer-checked:bg-[#CFAF53] peer-checked:text-white peer-checked:border-[#CFAF53]">
                                    {{ $slot }}
                                </div>
                            </label>
                        @endforeach
                    </div>
                </div>
                <div class="mb-4">
                    <label class="block text-xs font-bold text-slate-500 mb-1">Catatan</label>
                    <textarea name="catatan_pasien" rows="2" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:border-[#CFAF53] focus:ring-[#CFAF53]/20"></textarea>
                </div>
                <button type="submit" class="w-full rounded-xl bg-[#CFAF53] py-3 text-sm font-bold text-slate-900 shadow-lg hover:bg-[#CFAF53]/90 transition">
                    Konfirmasi Booking
                </button>
            </section>
        </aside>

    </form>
</div>

{{-- ALERT MODAL --}}
<div x-show="alertOpen" style="display: none;" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4" x-transition.opacity>
    <div class="w-full max-w-sm rounded-2xl bg-white p-6 text-center shadow-2xl" @click.away="alertOpen = false">
        <h3 class="text-lg font-bold text-slate-900">Perhatian</h3>
        <p class="mt-2 text-sm text-slate-600" x-text="alertMessage"></p>
        <button @click="alertOpen = false" class="mt-6 w-full rounded-xl bg-slate-900 py-3 text-sm font-bold text-white hover:bg-slate-800">Oke</button>
    </div>
</div>

</div>
</x-guest-layout>