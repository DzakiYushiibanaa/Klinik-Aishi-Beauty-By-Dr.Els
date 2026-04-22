<x-guest-layout :title="'Konsultasi Dokter'">
<div class="w-full min-h-screen bg-[#FAF8F5]" x-data="konsultasiWizard()" id="wizard-top">
    <!-- Back Navigation -->
    <div class="mx-auto max-w-2xl px-4 pt-6">
        <button type="button" @click="confirmCancel()" class="inline-flex items-center gap-2 text-sm text-[#8C7A63] hover:text-[#6B5D4D] transition-colors font-medium">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            Kembali
        </button>
    </div>

    <!-- Main Card -->
    <div class="mx-auto max-w-2xl px-4 py-6">
        <div class="rounded-2xl bg-white shadow-sm border border-[#E8E0D8]/60 overflow-hidden relative">
            
            <!-- Progress Header with Stepper -->
            <div class="px-6 pt-8 pb-6 text-center sm:px-10 border-b border-gray-100">
                <h1 class="font-serif text-3xl sm:text-4xl font-bold text-[#1A1A1A] mb-3">Konsultasi Dokter</h1>

                <!-- Stepper -->
                <div class="mt-8 mb-6">
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
                                    <span class="absolute top-full mt-1 sm:mt-1.5 text-[9px] sm:text-[10px] whitespace-nowrap font-medium transition-colors"
                                        :class="step >= (i+1) ? 'text-[#8C7A63]' : 'text-gray-400'" x-text="label"></span>
                                </div>
                                <div x-show="i < 4" class="flex-1 h-0.5 mx-1 transition-colors duration-300"
                                    :class="step > (i+1) ? 'bg-[#8C7A63]' : 'bg-gray-200'"></div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            <div class="px-6 sm:px-10 pt-6 pb-6 min-h-[300px]">
                
                <!-- ================== STEP 1 ================== -->
                <!-- STEP 1: Pilih Jenis Konsultasi -->
                <div x-show="step === 1" x-transition.opacity.duration.300ms>
                    <h2 class="font-serif text-2xl sm:text-3xl font-bold text-[#1A1A1A] mb-2">Pilih Jenis Konsultasi</h2>
                    <p class="text-base text-gray-500 mb-8">Sesuaikan layanan konsultasi dengan kebutuhan kulit Anda saat ini.</p>
                    <div class="space-y-4 max-w-xl mx-auto">
                        <!-- Option 1 -->
                        <label class="block relative cursor-pointer group">
                            <input type="radio" name="jenis" value="awal" x-model="jenisKonsultasi" class="peer sr-only">
                            <div class="flex items-center justify-between rounded-2xl border p-6 transition-all duration-200"
                                :class="jenisKonsultasi === 'awal' ? 'border-[#3B3529] bg-[#FAF8F5]' : 'border-gray-200 hover:border-[#8C7A63] hover:bg-gray-50'">
                                <div class="flex items-center gap-5">
                                    <input type="radio" name="jenis_display" value="awal" x-model="jenisKonsultasi" 
                                        class="w-5 h-5 text-[#3B3529] bg-gray-100 border-gray-300 focus:ring-[#3B3529] focus:ring-2 transition-all">
                                    <div>
                                        <h3 class="text-base font-bold text-[#1A1A1A]">Konsultasi Awal</h3>
                                        <p class="mt-1 text-sm text-gray-500">Untuk pasien baru atau memiliki keluhan baru.</p>
                                    </div>
                                </div>
                                <span class="text-base font-bold text-[#1A1A1A]">Rp 125.000</span>
                            </div>
                        </label>

                        <!-- Option 2 -->
                        <label class="block relative cursor-pointer group">
                            <input type="radio" name="jenis" value="lanjutan" x-model="jenisKonsultasi" class="peer sr-only">
                            <div class="flex items-center justify-between rounded-2xl border p-6 transition-all duration-200"
                                :class="jenisKonsultasi === 'lanjutan' ? 'border-[#3B3529] bg-[#FAF8F5]' : 'border-gray-200 hover:border-[#8C7A63] hover:bg-gray-50'">
                                <div class="flex items-center gap-5">
                                    <input type="radio" name="jenis_display" value="lanjutan" x-model="jenisKonsultasi" 
                                        class="w-5 h-5 text-[#3B3529] bg-gray-100 border-gray-300 focus:ring-[#3B3529] focus:ring-2 transition-all">
                                    <div>
                                        <h3 class="text-base font-bold text-[#1A1A1A]">Konsultasi Lanjutan</h3>
                                        <p class="mt-1 text-sm text-gray-500">Kontrol rutin untuk perawatan yang sedang berjalan.</p>
                                    </div>
                                </div>
                                <span class="text-base font-bold text-[#1A1A1A]">Rp 100.000</span>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- ================== STEP 2 ================== -->
                <!-- STEP 2: Pilih Dokter Spesialis -->
                <div x-show="step === 2" x-cloak x-transition.opacity.duration.300ms>
                    <h2 class="font-serif text-2xl sm:text-3xl font-bold text-[#1A1A1A] mb-2">Pilih Dokter Spesialis</h2>
                    <p class="text-base text-gray-500 mb-8">Tentukan dokter spesialis yang akan menangani permasalahan kulit Anda.</p>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        @foreach($doctors as $dokter)
                        <label class="block relative cursor-pointer group">
                            <input type="radio" name="dokter_id" value="{{ $dokter->id }}" x-model="dokterId" @change="dokterName = '{{ $dokter->name }}'" class="peer sr-only">
                            <div class="flex items-center gap-5 rounded-2xl border p-5 transition-all duration-200"
                                :class="dokterId == {{ $dokter->id }} ? 'border-[#3B3529] bg-[#FAF8F5] shadow-sm' : 'border-gray-200 hover:border-[#8C7A63] hover:bg-gray-50'">
                                
                                <div class="relative">
                                    @if($dokter->dokterProfile && $dokter->dokterProfile->foto_path)
                                        <img src="{{ Storage::url($dokter->dokterProfile->foto_path) }}" alt="{{ $dokter->name }}" class="w-16 h-16 rounded-full object-cover border-2 border-white shadow-sm">
                                    @else
                                        <div class="w-16 h-16 rounded-full bg-[#E8E0D8] text-[#8C7A63] flex items-center justify-center font-serif font-bold text-2xl border-2 border-white shadow-sm">
                                            {{ substr($dokter->name, 0, 1) }}
                                        </div>
                                    @endif
                                    <div class="absolute -bottom-1 -right-1 h-5 w-5 rounded-full border-2 border-white bg-[#25D366]"></div>
                                </div>
                                
                                <div class="flex-1">
                                    <h3 class="text-base font-bold text-[#1A1A1A]">{{ $dokter->name }}</h3>
                                    <p class="text-sm text-gray-500">Spesialis Estetika</p>
                                </div>

                                <input type="radio" name="dokter_display_{{ $dokter->id }}" value="{{ $dokter->id }}" x-model="dokterId" 
                                        class="w-5 h-5 shrink-0 text-[#3B3529] bg-gray-100 border-gray-300 focus:ring-[#3B3529] focus:ring-2 transition-all">
                            </div>
                        </label>
                        @endforeach
                    </div>
                </div>

                <!-- ================== STEP 3 ================== -->
                <div x-show="step === 3" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0" style="display: none;" x-cloak>
                    <h2 class="font-serif text-2xl sm:text-3xl font-bold text-[#1A1A1A] mb-2">Tentukan Waktu Kunjungan</h2>
                    <p class="text-base text-gray-500 mb-8">Pilih tanggal dan jam yang tersedia untuk sesi konsultasi.</p>

                    <!-- Realtime Tanggal -->
                    <div class="mb-8">
                        <div class="flex items-center justify-between mb-4">
                            <span class="text-sm font-bold text-[#1A1A1A]">Pilih Tanggal</span>
                            <span class="text-xs text-[#C89B53] bg-[#FAF8F5] px-2 py-1 rounded-md" x-text="currentMonthYear">Bulan Ini</span>
                        </div>
                        
                        <div x-show="availableDays.length === 0" class="p-6 bg-gray-50 border border-gray-200 rounded-2xl text-center">
                            <p class="text-sm text-gray-500">Maaf, dokter ini belum memiliki jadwal praktik aktif.</p>
                        </div>

                        <div class="flex gap-3 overflow-x-auto pb-2 scrollbar-hide snap-x">
                            <template x-for="day in availableDays" :key="day.date">
                                <label class="snap-start shrink-0 cursor-pointer">
                                    <input type="radio" name="tanggal" :value="day.date" x-model="tanggal" @change="displayDate = day.displayFull; jam = ''" class="peer sr-only">
                                    <div class="flex flex-col items-center justify-center w-[80px] h-[95px] rounded-2xl border transition-all duration-200"
                                        :class="tanggal === day.date ? 'border-[#3B3529] bg-[#3B3529] text-white shadow-md' : 'border-gray-200 bg-white hover:border-[#8C7A63] text-gray-600'">
                                        <span class="text-xs uppercase mb-1 font-medium tracking-wide" :class="tanggal === day.date ? 'text-white/80' : 'text-gray-400'" x-text="day.dayName"></span>
                                        <span class="text-3xl font-serif font-bold" :class="tanggal === day.date ? 'text-white' : 'text-[#1A1A1A]'" x-text="day.dayNum"></span>
                                    </div>
                                </label>
                            </template>
                        </div>
                    </div>

                    <!-- Realtime Waktu -->
                    <div x-show="tanggal !== ''">
                        <div class="text-sm font-bold text-[#1A1A1A] mb-4">Pilih Jam (WIB)</div>
                        
                        <div x-show="availableTimes.length === 0" class="p-4 bg-gray-50 border border-gray-200 rounded-xl text-center">
                            <p class="text-sm text-gray-500">Tidak ada jadwal tersedia pada tanggal ini.</p>
                        </div>

                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                            <template x-for="timeSlot in availableTimes" :key="timeSlot.value">
                                <label :class="timeSlot.isDisabled ? 'cursor-not-allowed opacity-50' : 'cursor-pointer'">
                                    <input type="radio" name="jam" :value="timeSlot.value" x-model="jam" class="peer sr-only" :disabled="timeSlot.isDisabled">
                                    <div class="py-3.5 px-4 rounded-xl text-sm font-medium border transition-all text-center"
                                        :class="timeSlot.isDisabled ? 'bg-gray-100 border-gray-200 text-gray-400' : (jam === timeSlot.value ? 'border-[#3B3529] bg-[#FAF8F5] text-[#1A1A1A] font-bold shadow-sm' : 'border-gray-200 bg-white text-gray-500 hover:border-[#8C7A63]')">
                                        <span x-text="timeSlot.value"></span>
                                    </div>
                                </label>
                            </template>
                        </div>
                    </div>
                </div>

                <!-- ================== STEP 4 ================== -->
                <!-- STEP 4: Data Pasien -->
                <div x-show="step === 4" x-cloak x-transition.opacity.duration.300ms>
                    <h2 class="font-serif text-2xl sm:text-3xl font-bold text-[#1A1A1A] mb-2">Lengkapi Data Pasien</h2>
                    <p class="text-base text-gray-500 mb-8">Mohon isi data diri Anda untuk keperluan rekam medis di klinik.</p>
                    <div class="bg-[#FAF8F5] rounded-2xl p-6 mb-6 border border-[#E8E0D8]">
                        <div class="flex gap-3 mb-6">
                            <svg class="w-6 h-6 text-[#C89B53] shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <p class="text-sm text-[#8C7A63] leading-relaxed">Data Anda telah terisi otomatis sesuai profil akun. Pastikan nomor WhatsApp aktif untuk menerima konfirmasi.</p>
                        </div>

                        <div class="space-y-5">
                            <div>
                                <label class="block text-xs font-bold tracking-wider text-gray-500 uppercase mb-2">Nama Lengkap</label>
                                <div class="w-full bg-white border border-gray-200 rounded-xl px-4 py-3 text-base text-[#1A1A1A] font-medium" x-text="nama_pasien"></div>
                            </div>
                            <div>
                                <label class="block text-xs font-bold tracking-wider text-gray-500 uppercase mb-2">Nomor WhatsApp</label>
                                <div class="w-full bg-white border border-gray-200 rounded-xl px-4 py-3 text-base text-[#1A1A1A] font-medium" x-text="nomor_wa"></div>
                            </div>
                            <div>
                                <label class="block text-xs font-bold tracking-wider text-gray-500 uppercase mb-2">Email</label>
                                <div class="w-full bg-white border border-gray-200 rounded-xl px-4 py-3 text-base text-[#1A1A1A] font-medium" x-text="email"></div>
                            </div>
                            <div>
                                <label class="block text-xs font-bold tracking-wider text-gray-500 uppercase mb-2">Tanggal Lahir</label>
                                <div class="w-full bg-white border border-gray-200 rounded-xl px-4 py-3 text-base text-[#1A1A1A] font-medium" x-text="tanggal_lahir"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ================== STEP 5 ================== -->
                <div x-show="step === 5" x-cloak x-transition.opacity.duration.300ms>
                    <h2 class="font-serif text-2xl sm:text-3xl font-bold text-[#1A1A1A] mb-2">Detail Janji Temu</h2>
                    <p class="text-base text-gray-500 mb-8">Pastikan seluruh detail reservasi Anda sudah sesuai sebelum menyelesaikan.</p>

                    <div class="rounded-[1.5rem] border border-[#F0EBE1] bg-[#FAF8F5]/30 p-5 sm:p-8 mb-6">
                        
                        <!-- Dokter -->
                        <div class="mb-6">
                            <div class="text-xs font-bold tracking-widest text-gray-400 uppercase mb-3 block">Dokter Pilihan Anda</div>
                            <div class="flex items-center gap-4">
                                <div class="h-14 w-14 shrink-0 rounded-full bg-gray-200 overflow-hidden border border-gray-300">
                                    <template x-if="dokterImg">
                                        <img :src="dokterImg" class="w-full h-full object-cover">
                                    </template>
                                    <template x-if="!dokterImg">
                                        <div class="w-full h-full flex items-center justify-center bg-[#E8E0D8] text-[#8C7A63] font-serif font-bold text-2xl" x-text="dokterName.substring(0,1)"></div>
                                    </template>
                                </div>
                                <div>
                                    <div class="font-bold text-[#1A1A1A] text-lg" x-text="dokterName"></div>
                                    <div class="text-sm border border-gray-200 rounded px-2 py-0.5 mt-1 text-gray-500 inline-block">
                                        <span x-text="jenisKonsultasi === 'awal' ? 'Konsultasi Awal' : 'Konsultasi Rutin'"></span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <hr class="border-[#F0EBE1] mb-6">

                        <!-- Jadwal -->
                        <div class="mb-6">
                            <div class="text-xs font-bold tracking-widest text-gray-400 uppercase mb-4">Jadwal & Lokasi</div>
                            <div class="space-y-4">
                                <div class="flex gap-4">
                                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-white border border-[#E8E0D8] text-[#C89B53]">
                                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                    </div>
                                    <div>
                                        <div class="text-sm font-bold text-[#1A1A1A]">Waktu Konsultasi</div>
                                        <div class="text-sm text-gray-500 mt-1"><span x-text="displayDate"></span> • Jam <span x-text="jam"></span> WIB</div>
                                    </div>
                                </div>
                                <div class="flex gap-4">
                                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-white border border-[#E8E0D8] text-[#C89B53]">
                                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                    </div>
                                    <div>
                                        <div class="text-sm font-bold text-[#1A1A1A]">Aishi Beauty Clinic</div>
                                        <div class="text-sm text-gray-500 mt-1 leading-relaxed">Jl. Nusantara Raya No.99b, Beji, Kec. Beji, Kota Depok, Jawa Barat 16421</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <hr class="border-[#F0EBE1] mb-6">

                        <!-- Biaya -->
                        <div>
                            <div class="text-xs font-bold tracking-widest text-gray-400 uppercase mb-4">Total Biaya</div>
                            <div class="space-y-3 mb-4">
                                <div class="flex justify-between items-center text-sm">
                                    <span class="text-gray-500 flex items-center gap-2">Biaya Konsultasi <span class="text-[10px] border border-[#C89B53]/30 text-[#C89B53] px-1.5 py-0.5 rounded uppercase flex items-center gap-1"><svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg> Member Benefit</span></span>
                                    <span class="font-bold text-[#1A1A1A]" x-text="jenisKonsultasi === 'awal' ? 'Rp 125.000' : 'Rp 100.000'"></span>
                                </div>
                                <div class="text-[11px] text-[#C89B53] italic mb-2">*Sudah termasuk biaya aktivasi Member Aishi Beauty</div>
                                <div class="flex justify-between items-center text-sm">
                                    <span class="text-gray-500">Biaya Admin</span>
                                    <span class="font-bold text-[#1A1A1A]">Rp 0</span>
                                </div>
                            </div>
                            <hr class="border-dashed border-gray-300 mb-4">
                            <div class="flex justify-between items-center">
                                <span class="text-base font-bold text-[#1A1A1A]">Total Biaya</span>
                                <span class="text-xl font-bold text-[#C89B53]" x-text="jenisKonsultasi === 'awal' ? 'Rp 125.000' : 'Rp 100.000'"></span>
                            </div>
                        </div>
                    </div>

                    <!-- Info Box -->
                    <div class="flex gap-4 rounded-2xl border border-[#F0EBE1] bg-[#FAF8F5]/30 p-5 mb-8">
                        <div class="shrink-0 mt-0.5">
                            <svg class="w-6 h-6 text-[#C89B53]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                        </div>
                        <p class="text-sm leading-relaxed text-gray-500">
                            <strong class="text-[#1A1A1A]">Pembayaran di Klinik</strong><br>
                            Anda tidak perlu membayar sekarang. Pembayaran dilakukan di kasir klinik <strong class="text-[#1A1A1A]">setelah sesi konsultasi Anda selesai</strong>. Dengan menyelesaikan janji temu ini, Anda secara otomatis akan terdaftar sebagai member resmi untuk mendapatkan promo eksklusif di kunjungan berikutnya.
                        </p>
                    </div>

                    <!-- Step 5 Action Button -->
                    <div class="flex flex-col gap-3">
                        <button type="button" @click="submitForm()" :disabled="isSubmitting"
                            class="w-full flex justify-center items-center gap-2 rounded-2xl bg-[#3B3529] py-4 text-base font-medium text-white transition-all hover:bg-[#2A261D] disabled:opacity-70 shadow-sm">
                            <span x-text="isSubmitting ? 'Memproses...' : 'Buat Janji Temu'"></span>
                            <svg x-show="!isSubmitting" class="w-4 h-4 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            <svg x-show="isSubmitting" class="w-5 h-5 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        </button>
                        <button type="button" @click="goBack()"
                            class="w-full flex items-center justify-center gap-2 rounded-2xl border border-gray-200 bg-white px-6 py-4 text-sm font-medium text-gray-600 transition-all hover:bg-gray-50 shadow-sm">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                            Kembali ke Halaman Sebelumnya
                        </button>
                    </div>
                </div>
            </div>

            <!-- Bottom Navigation Bar (Steps 1-4) -->
            <div x-show="step < 5" class="flex items-center justify-between px-6 sm:px-10 py-5 border-t border-gray-100 bg-[#FDFCFB]">
                    <button type="button" @click="goBack()" x-cloak
                        class="inline-flex items-center gap-2 rounded-xl border border-gray-300 bg-white px-5 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50 transition-all">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                        Kembali
                    </button>
                    <div x-show="step === 1 && jenisKonsultasi === ''"></div>
                    
                    <!-- Step 1 Button -->
                    <button type="button" x-show="step === 1 && jenisKonsultasi !== ''" @click="nextStep()" x-cloak x-transition
                        class="inline-flex items-center gap-2 rounded-xl bg-[#1A1A1A] px-6 py-2.5 text-sm font-semibold text-white hover:bg-[#333] transition-all">
                        Selanjutnya
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </button>

                    <!-- Step 2 Button -->
                    <button type="button" x-show="step === 2 && dokterId !== ''" @click="nextStep()" x-cloak x-transition
                        class="inline-flex items-center gap-2 rounded-xl bg-[#1A1A1A] px-6 py-2.5 text-sm font-semibold text-white hover:bg-[#333] transition-all">
                        Selanjutnya
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </button>

                    <!-- Step 3 Button -->
                    <button type="button" x-show="step === 3 && tanggal !== '' && jam !== ''" @click="nextStep()" x-cloak x-transition
                        class="inline-flex items-center gap-2 rounded-xl bg-[#1A1A1A] px-6 py-2.5 text-sm font-semibold text-white hover:bg-[#333] transition-all">
                        Selanjutnya
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </button>

                    <!-- Step 4 Button -->
                    <button type="button" x-show="step === 4" @click="nextStep()" x-cloak x-transition
                        class="inline-flex items-center gap-2 rounded-xl bg-[#1A1A1A] px-6 py-2.5 text-sm font-semibold text-white hover:bg-[#333] transition-all">
                        Selanjutnya
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </button>
                </div>
        </div>
    </div>

    <!-- SUCCESS MODAL -->
    <div x-show="showModal" x-cloak class="fixed inset-0 z-[9999] flex items-center justify-center p-4" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
        <div class="fixed inset-0 bg-[#3B3529]/40 backdrop-blur-sm" @click="showModal = false"></div>
        <div class="relative bg-white rounded-[2rem] shadow-2xl max-w-sm w-full p-8 text-center z-10" x-transition:enter="transition ease-out duration-300 delay-100" x-transition:enter-start="opacity-0 scale-95 translate-y-4" x-transition:enter-end="opacity-100 scale-100 translate-y-0">
            
            <div class="w-16 h-16 rounded-full border border-[#E8E0D8] bg-[#FAF8F5] flex items-center justify-center mx-auto mb-6">
                <svg class="w-8 h-8 text-[#C89B53]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            </div>
            
            <h3 class="font-serif text-2xl font-bold text-[#1A1A1A] mb-3">Reservasi Berhasil</h3>
            <p class="text-xs text-gray-500 mb-8 leading-relaxed">
                Terima kasih, <strong class="text-[#1A1A1A]" x-text="nama_pasien"></strong>.<br>
                Jadwal konsultasi Anda bersama <strong class="text-[#1A1A1A]" x-text="dokterName"></strong> telah dikonfirmasi. Detail selengkapnya akan segera dikirimkan melalui <strong class="text-[#1A1A1A]">WhatsApp</strong> dan <strong class="text-[#1A1A1A]">Email (Gmail)</strong>.
            </p>
            
            <button type="button" @click="executeSubmit()" class="w-full rounded-2xl bg-[#3B3529] py-3.5 text-sm font-bold text-white transition-all hover:bg-[#2A261D]">
                Selesai & Kembali
            </button>
        </div>
    </div>

    <!-- CANCEL CONFIRMATION MODAL -->
    <div x-show="showCancelModal" x-cloak class="fixed inset-0 z-[9999] flex items-center justify-center p-4" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
        <div class="fixed inset-0 bg-[#3B3529]/40 backdrop-blur-sm" @click="showCancelModal = false"></div>
        <div class="relative bg-white rounded-[2rem] shadow-2xl max-w-sm w-full p-8 text-center z-10" x-transition:enter="transition ease-out duration-300 delay-100" x-transition:enter-start="opacity-0 scale-95 translate-y-4" x-transition:enter-end="opacity-100 scale-100 translate-y-0">
            
            <div class="w-16 h-16 rounded-full border border-red-100 bg-red-50 flex items-center justify-center mx-auto mb-6">
                <svg class="w-8 h-8 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            </div>
            
            <h3 class="font-serif text-2xl font-bold text-[#1A1A1A] mb-3">Batalkan Janji Temu?</h3>
            <p class="text-base text-gray-500 mb-8 leading-relaxed">
                Jika Anda kembali ke halaman utama, semua data janji temu yang telah Anda isi akan terhapus. Apakah Anda yakin?
            </p>
            
            <div class="flex gap-3">
                <button type="button" @click="showCancelModal = false" class="flex-1 rounded-2xl bg-gray-100 py-3.5 text-sm font-bold text-gray-700 transition-all hover:bg-gray-200">
                    Tidak
                </button>
                <button type="button" @click="window.location.href = '/'" class="flex-1 rounded-2xl bg-red-600 py-3.5 text-sm font-bold text-white transition-all hover:bg-red-700">
                    Ya, Batalkan
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function konsultasiWizard() {
    return {
        step: 1,
        showModal: false,
        showCancelModal: false,
        isSubmitting: false,
        jenisKonsultasi: '',
        dokterId: '',
        dokterName: '',
        tanggal: '',
        jam: '',
        displayDate: '',
        stepLabels: ['Konsultasi', 'Dokter', 'Jadwal', 'Data Pasien', 'Konfirmasi'],
        
        // Data dari backend
        nama_pasien: '{{ $user->name }}',
        nomor_wa: '{{ $user->phone }}',
        email: '{{ $user->email }}',
        tanggal_lahir: '{{ $user->tanggal_lahir }}',

        // Data jadwal dari backend
        jadwalData: @json($jadwals),

        get currentMonthYear() {
            const today = new Date();
            const months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
            return `${months[today.getMonth()]} ${today.getFullYear()}`;
        },

        get availableDays() {
            if (!this.dokterId) return [];
            
            // Get all schedules for the selected doctor
            const docJadwals = this.jadwalData.filter(j => j.user_id == this.dokterId);
            const activeDays = docJadwals.map(j => j.hari.toLowerCase());
            
            const days = [];
            const dayNames = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
            const today = new Date();
            
            // Generate next 14 days
            for (let i = 0; i < 14; i++) {
                const d = new Date(today);
                d.setDate(today.getDate() + i);
                const dayName = dayNames[d.getDay()];
                
                if (activeDays.includes(dayName.toLowerCase())) {
                    days.push({
                        date: d.toISOString().split('T')[0],
                        dayName: dayName.substring(0, 3), // Sen, Sel, etc.
                        dayNum: d.getDate(),
                        displayFull: `${dayName}, ${d.getDate()} ${d.toLocaleString('id-ID', {month:'short'})} ${d.getFullYear()}`
                    });
                }
            }
            return days;
        },

        get availableTimes() {
            if (!this.dokterId || !this.tanggal) return [];
            
            const selectedDate = new Date(this.tanggal);
            const dayNames = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
            const dayName = dayNames[selectedDate.getDay()].toLowerCase();
            
            const docJadwals = this.jadwalData.filter(j => j.user_id == this.dokterId && j.hari.toLowerCase() === dayName);
            if (docJadwals.length === 0) return [];
            
            const jadwal = docJadwals[0];
            const startTime = jadwal.jam_mulai; // e.g. "08:00:00"
            const endTime = jadwal.jam_selesai;
            const duration = jadwal.durasi_slot_menit || 30;
            
            const times = [];
            let current = new Date(`${this.tanggal}T${startTime}`);
            const end = new Date(`${this.tanggal}T${endTime}`);
            const now = new Date();
            
            while (current < end) {
                const nextTime = new Date(current);
                nextTime.setMinutes(current.getMinutes() + duration);
                
                const startH = current.getHours().toString().padStart(2, '0');
                const startM = current.getMinutes().toString().padStart(2, '0');
                const endH = nextTime.getHours().toString().padStart(2, '0');
                const endM = nextTime.getMinutes().toString().padStart(2, '0');
                
                // Compare times based on full Date objects to see if it has passed
                const isPast = current < now;
                
                times.push({
                    value: `${startH}.${startM} - ${endH}.${endM}`,
                    isDisabled: isPast
                });
                
                current = nextTime;
            }
            return times;
        },

        stepTitle() {
            const titles = [
                'Pilih Konsultasi',
                'Pilih Dokter',
                'Pilih Jadwal',
                'Data Pasien',
                'Konfirmasi'
            ];
            return titles[this.step - 1];
        },

        nextStep() {
            if (this.step < 5) {
                this.step++;
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }
        },

        confirmCancel() {
            if (this.jenisKonsultasi !== '' || this.dokterId !== '') {
                this.showCancelModal = true;
            } else {
                window.location.href = '/';
            }
        },

        goBack() {
            if (this.step > 1) {
                this.step--;
                window.scrollTo({ top: 0, behavior: 'smooth' });
            } else {
                this.confirmCancel();
            }
        },

        async submitForm() {
            if(this.isSubmitting) return;
            this.isSubmitting = true;

            try {
                const response = await fetch('{{ route('appointment.konsultasi.store') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        jenis: this.jenisKonsultasi,
                        dokter_id: this.dokterId,
                        tanggal: this.tanggal,
                        jam: this.jam
                    })
                });

                if (response.ok) {
                    this.showModal = true;
                } else {
                    alert('Terjadi kesalahan saat memproses janji temu. Silakan coba lagi.');
                }
            } catch (error) {
                alert('Terjadi kesalahan koneksi.');
            } finally {
                this.isSubmitting = false;
            }
        },

        executeSubmit() {
            // Karena flash message sudah diset oleh backend di proses ajax, 
            // kita bisa langsung redirect ke dashboard dan dashboard akan menampilkan toast.
            window.location.href = '/dashboard';
        }
    }
}
</script>
<style>[x-cloak]{display:none!important}</style>
</x-guest-layout>
