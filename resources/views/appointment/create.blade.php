<x-guest-layout :title="'Buat Janji Temu'">

{{-- STATE MANAGEMENT (ALPINE JS) --}}
<div x-data="{
    // State Reactive
    jenis: '{{ old('jenis', 'treatment') }}',
    selectedTreatments: {{ json_encode(old('treatments', $autoTreatments)) }},
    selectedDoctor: '{{ old('dokter_id', $autoDoctorId) }}',
    terapisId: '{{ old('terapis_id') }}',

    // --- NEW: State untuk Waktu ---
    selectedDate: '{{ old('tanggal', date('Y-m-d')) }}', 
    waktu: '{{ old('waktu') }}',

    // Data Master
    therapists: {{ $therapists_json }},
    treatmentsList: {{ $treatments->map(fn($t) => [
        'id' => $t->treatment_id, 
        'nama' => $t->nama,
        'harga' => $t->harga,
        'durasi_menit' => $t->durasi_menit,
        'min_level' => $t->min_level ?? 1
    ])->toJson() }},
    
    // Alert System
    alertOpen: false,
    alertMessage: '',

    get selectedTreatmentDetails() {
        return this.treatmentsList.filter(t => this.selectedTreatments.includes(String(t.id)) || this.selectedTreatments.includes(Number(t.id)));
    },
    get totalHarga() {
        return this.selectedTreatmentDetails.reduce((sum, t) => sum + Number(t.harga), 0);
    },
    get totalWaktu() {
        return this.selectedTreatmentDetails.reduce((sum, t) => sum + Number(t.durasi_menit), 0);
    },
    formatRupiah(angka) {
        return new Intl.NumberFormat('id-ID').format(angka);
    },

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

    // --- NEW: Logic Validasi Jam ---
    isTimeValid(timeSlot) {
        if (!this.selectedDate) return true;

        const now = new Date();
        const selected = new Date(this.selectedDate);
        const today = new Date();
        
        // Reset jam untuk komparasi tanggal murni
        today.setHours(0,0,0,0);
        selected.setHours(0,0,0,0);

        // Jika tanggal yang dipilih adalah masa depan, semua jam valid
        if (selected.getTime() > today.getTime()) return true;

        // Jika tanggal masa lalu (seharusnya dicegah min attr), invalid
        if (selected.getTime() < today.getTime()) return false;

        // Jika HARI INI, cek jamnya
        const [slotHour, slotMinute] = timeSlot.split(':').map(Number);
        const currentHour = now.getHours();
        const currentMinute = now.getMinutes();

        // Bandingkan jam
        if (slotHour > currentHour) return true;
        if (slotHour === currentHour && slotMinute > currentMinute) return true;

        return false; // Jam sudah lewat
    },

    // Init & Watchers
    init() {
        this.$watch('requiredLevel', (newVal) => {
            if (this.terapisId) {
                let current = this.therapists.find(t => t.id == this.terapisId);
                if (current && current.level < newVal) {
                    this.terapisId = ''; 
                }
            }
        });

        // --- NEW: Reset jam jika tanggal berubah dan jam yg dipilih jadi basi ---
        this.$watch('selectedDate', (val) => {
            if (this.waktu && !this.isTimeValid(this.waktu)) {
                this.waktu = ''; // Reset pilihan waktu
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

        // --- NEW: Validasi Jam saat submit ---
        if (this.waktu && !this.isTimeValid(this.waktu)) {
            this.alertMessage = 'Jam yang Anda pilih sudah berlalu. Silakan pilih jam lain.';
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
        <p class="mt-2 text-slate-500">Pilih layanan perawatan dan jadwalkan kedatangan Anda dengan mudah.</p>
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
            <div class="flex items-center gap-3 mb-4">
                <div class="flex h-6 w-6 items-center justify-center rounded-full bg-[#CFAF53]/20 text-xs font-bold text-[#8B6F1B]">1</div>
                <h2 class="text-lg font-bold text-slate-800">Pilih Jenis Kunjungan</h2>
            </div>
            <section class="mb-8 p-5 rounded-3xl border border-slate-200 bg-white">
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
            <div class="flex items-center gap-3 mb-4 mt-8">
                <div class="flex h-6 w-6 items-center justify-center rounded-full bg-[#CFAF53]/20 text-xs font-bold text-[#8B6F1B]">2</div>
                <h2 class="text-lg font-bold text-slate-800">
                    Pilih Treatment 
                    <span x-show="jenis === 'konsultasi'" class="text-sm font-normal text-slate-500 ml-2">(Opsional)</span>
                </h2>
            </div>
            <section class="mb-8 p-5 rounded-3xl border border-slate-200 bg-slate-50/50">
                <div class="mb-5 flex justify-end">
                    
                    {{-- Badge Level Dinamis --}}
                    <div x-show="requiredLevel > 1" class="text-xs font-bold text-orange-600 bg-orange-100 px-3 py-1 rounded-full border border-orange-200" style="display: none;" x-transition>
                        🔥 Butuh Terapis Senior
                    </div>
                </div>

                @forelse($treatments->groupBy('category.nama') as $category => $items)
                    <div x-data="{ open: {{ $loop->first ? 'true' : 'false' }} }" class="mb-4 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
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
                                                        <h4 class="font-bold text-slate-900 peer-checked:text-[#CFAF53] text-base sm:text-lg">{{ $t->nama }}</h4>
                                                        <div class="mt-2 flex flex-wrap items-center gap-2">
                                                            <span class="inline-flex items-center gap-1.5 rounded-md bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-600 border border-slate-200">
                                                                <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                                                {{ $t->durasi_menit }} Menit
                                                            </span>
                                                        </div>
                                                        @if($t->deskripsi)
                                                            <p class="mt-3 text-sm text-slate-600 leading-relaxed">{{ $t->deskripsi }}</p>
                                                        @endif
                                                    </div>
                                                    <div class="text-left sm:text-right mt-1 sm:mt-0">
                                                        <span class="block text-base font-bold text-[#CFAF53]">Rp {{ number_format($t->harga, 0, ',', '.') }}</span>
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
            <div x-show="jenis === 'konsultasi'" x-transition class="flex items-center gap-3 mb-4 mt-8">
                <div class="flex h-6 w-6 items-center justify-center rounded-full bg-[#CFAF53]/20 text-xs font-bold text-[#8B6F1B]">3</div>
                <h2 class="text-lg font-bold text-slate-800">Pilih Dokter</h2>
            </div>
            <section x-show="jenis === 'konsultasi'" x-transition class="mb-8 p-5 rounded-3xl border border-slate-200 bg-white">
                
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
            <div class="flex items-center gap-3 mb-4 mt-8">
                <div class="flex h-6 w-6 items-center justify-center rounded-full bg-[#CFAF53]/20 text-xs font-bold text-[#8B6F1B]" x-text="jenis === 'konsultasi' ? '4' : '3'"></div>
                <h2 class="text-lg font-bold text-slate-800">
                    Pilih Terapis
                    <span class="text-sm font-normal text-slate-500 ml-2">Opsional, biarkan kosong untuk terapis acak.</span>
                </h2>
            </div>
            <section class="mb-8 p-5 rounded-3xl border border-slate-200 bg-white">
                <div class="mb-4 hidden">
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
        <aside class="space-y-6">
            <section class="rounded-[2rem] border border-slate-200 bg-white p-6 sticky top-6 shadow-sm">
                <div class="flex items-center gap-3 mb-6">
                    <div class="flex h-6 w-6 items-center justify-center rounded-full bg-[#CFAF53]/20 text-xs font-bold text-[#8B6F1B]" x-text="jenis === 'konsultasi' ? '5' : '4'"></div>
                    <h2 class="text-lg font-bold text-slate-800">Jadwal & Konfirmasi</h2>
                </div>

                {{-- Ringkasan Pesanan --}}
                <div class="mb-6" x-show="jenis === 'treatment'">
                    <div class="flex items-center gap-2 mb-4 text-slate-800 font-bold text-base">
                        <svg class="h-5 w-5 text-[#CFAF53]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                        Ringkasan Pesanan
                    </div>
                    
                    {{-- Kondisi Kosong --}}
                    <div x-show="selectedTreatmentDetails.length === 0" class="text-base text-slate-500 italic mb-4">
                        Belum ada treatment yang dipilih.
                    </div>

                    {{-- List Treatment --}}
                    <div class="space-y-4 mb-5" x-show="selectedTreatmentDetails.length > 0">
                        <template x-for="item in selectedTreatmentDetails" :key="item.id">
                            <div class="flex justify-between items-start text-base">
                                <div>
                                    <div class="flex items-center gap-2">
                                        <svg class="h-4 w-4 text-[#CFAF53]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                        <span class="font-semibold text-slate-700 text-sm" x-text="item.nama"></span>
                                    </div>
                                    <div class="text-xs text-slate-400 ml-6" x-text="item.durasi_menit + ' menit'"></div>
                                </div>
                                <div class="font-medium text-slate-800 text-sm" x-text="'Rp ' + formatRupiah(item.harga)"></div>
                            </div>
                        </template>
                    </div>

                    <div class="pt-4 border-t border-slate-100" x-show="selectedTreatmentDetails.length > 0">
                        <div class="flex justify-between items-center text-xs text-slate-500 mb-2">
                            <span class="flex items-center gap-1.5"><svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg> Total Waktu</span>
                            <span class="font-semibold text-sm" x-text="totalWaktu + ' menit'"></span>
                        </div>
                        <div class="flex justify-between items-center mt-3">
                            <span class="flex items-center gap-1.5 font-bold text-slate-800 text-base"><svg class="h-5 w-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" /></svg> Total Biaya</span>
                            <span class="font-bold text-[#CFAF53] text-lg lg:text-xl" x-text="'Rp ' + formatRupiah(totalHarga)"></span>
                        </div>
                    </div>

                    {{-- Info Terapis (hanya treatment) --}}
                    <div class="mt-5 pt-4 border-t border-slate-100" x-show="terapisId !== '' && therapists.find(t => t.id == terapisId)">
                        <div class="flex items-start gap-2.5">
                            <svg class="h-5 w-5 text-[#CFAF53] mt-0.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                            <div>
                                <div class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Terapis Pilihan</div>
                                <div class="text-sm font-bold text-slate-800" x-text="therapists.find(t => t.id == terapisId)?.name"></div>
                            </div>
                        </div>
                    </div>
                    <div class="mt-5 pt-4 border-t border-slate-100" x-show="terapisId === ''">
                        <div class="flex items-start gap-2.5">
                            <svg class="h-5 w-5 text-slate-400 mt-0.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                            <div>
                                <div class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Terapis</div>
                                <div class="text-sm font-medium text-slate-600">Dipilihkan oleh Admin</div>
                            </div>
                        </div>
                    </div>

                <div class="mb-6" x-show="jenis === 'konsultasi'">
                    <div class="flex items-center gap-2 mb-3 text-slate-800 font-bold text-sm">
                        <svg class="h-4 w-4 text-[#CFAF53]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                        Ringkasan Pesanan
                    </div>
                    <div class="text-sm text-slate-600">
                        Konsultasi Dokter
                        <div class="text-[10px] text-slate-400 mt-1">Biaya akan diinfokan di klinik.</div>
                    </div>
                </div>

                <hr class="border-slate-100 my-6">

                <div class="flex items-center gap-2 mb-4 text-slate-800 font-bold text-sm">
                    <svg class="h-4 w-4 text-[#CFAF53]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                    Pilih Waktu Kunjungan
                </div>
                
                {{-- Tanggal dengan x-model --}}
                <div class="mb-5">
                    <label class="block text-sm font-bold text-slate-500 mb-2 hidden">Tanggal</label>
                    <input type="date" 
                           name="tanggal" 
                           min="{{ date('Y-m-d') }}" 
                           x-model="selectedDate" 
                           required 
                           class="w-full rounded-xl border-slate-200 text-base py-2.5 focus:border-[#CFAF53] focus:ring-[#CFAF53]/20">
                </div>

                {{-- Jam dengan Validasi isTimeValid --}}
                <div class="mb-6">
                    <label class="flex items-center gap-2 text-sm font-bold text-slate-500 mb-3">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        Jam Kedatangan
                    </label>
                    <div class="grid grid-cols-3 gap-3">
                        @foreach(['09:00','10:00','11:00','13:00','14:00','15:00','16:00','17:00','18:00'] as $slot)
                            <label class="relative" :class="!isTimeValid('{{ $slot }}') ? 'cursor-not-allowed opacity-50' : 'cursor-pointer'">
                                <input type="radio" 
                                       name="waktu" 
                                       value="{{ $slot }}" 
                                       x-model="waktu" 
                                       :disabled="!isTimeValid('{{ $slot }}')" 
                                       required 
                                       class="peer sr-only">
                                       
                                <div class="rounded-lg border border-slate-200 py-2.5 text-center text-sm font-bold text-slate-600 transition-colors 
                                            peer-checked:bg-[#CFAF53] peer-checked:text-white peer-checked:border-[#CFAF53]
                                            hover:border-[#CFAF53]"
                                     :class="!isTimeValid('{{ $slot }}') ? 'bg-slate-100 text-slate-300 border-slate-100' : ''">
                                    {{ $slot }}
                                </div>
                            </label>
                        @endforeach
                    </div>
                    <p class="text-xs text-slate-400 mt-3" x-show="selectedDate && isTimeValid('23:59') === false">
                        *Jam yang ditampilkan menyesuaikan waktu saat ini.
                    </p>
                </div>

                <div class="mb-5">
                    <label class="flex items-center gap-2 text-sm font-bold text-slate-500 mb-3">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                        Catatan / Keluhan
                    </label>
                    <textarea name="catatan_pasien" rows="3" placeholder="Tuliskan kondisi kulit atau keluhan khusus Anda." class="w-full rounded-xl border border-slate-200 px-4 py-3 text-base focus:border-[#CFAF53] focus:ring-[#CFAF53]/20"></textarea>
                </div>
                <button type="submit" class="w-full rounded-xl bg-[#CFAF53] py-4 text-base font-bold text-slate-900 shadow-lg hover:bg-[#CFAF53]/90 transition">
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