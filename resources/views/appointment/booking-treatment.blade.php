<x-guest-layout :title="'Booking Treatment'">
<div class="w-full min-h-screen bg-[#FAF8F5]" x-data="bookingTreatment()" id="wizard-top">
    <!-- Back Navigation -->
    <div class="mx-auto max-w-2xl px-4 pt-6">
        <button type="button" @click="confirmCancel()" class="inline-flex items-center gap-2 text-sm text-[#8C7A63] hover:text-[#6B5D4D] transition-colors font-medium">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            Kembali
        </button>
    </div>

    <!-- Main Card -->
    <div class="mx-auto max-w-2xl px-4 py-6">
        <div class="rounded-2xl bg-white shadow-sm border border-[#E8E0D8]/60 overflow-clip relative">

            <!-- Progress Header -->
            <div class="px-6 pt-8 pb-6 text-center sm:px-10 border-b border-gray-100">
                <h1 class="text-3xl sm:text-4xl font-bold text-[#1A1A1A] mb-3">Booking Treatment</h1>

                <!-- 4-Step Stepper -->
                <div class="mt-8 mb-6">
                    <div class="flex items-center justify-between max-w-md mx-auto">
                        <template x-for="(label, i) in stepLabels" :key="i">
                            <div class="flex items-center" :class="i < 3 ? 'flex-1' : ''">
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
                                <div x-show="i < 3" class="flex-1 h-0.5 mx-1 transition-colors duration-300"
                                    :class="step > (i+1) ? 'bg-[#8C7A63]' : 'bg-gray-200'"></div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            <!-- Step 1: Pilih Layanan -->
            <div class="px-6 sm:px-10 pt-6 pb-6 min-h-[400px]" x-show="step === 1 && !detailTreatment">

                <!-- Info Box -->
                <div class="mb-8 rounded-2xl border border-[#E8E0D8] bg-[#FAF8F5] p-5">
                    <div class="flex gap-3">
                        <div class="shrink-0 mt-0.5">
                            <div class="w-8 h-8 rounded-full bg-[#8C7A63]/10 flex items-center justify-center">
                                <svg class="w-4 h-4 text-[#8C7A63]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </div>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-[#1A1A1A] mb-1">Pesan Penting untuk Kesehatan Kulit Anda</h3>
                            <p class="text-xs text-gray-500 leading-relaxed text-justify">
                                Agar hasil perawatan optimal dan aman, kami menyarankan Anda untuk tidak memilih treatment secara sembarangan jika belum mengetahui kondisi kulit dengan pasti.
                            </p>
                            <p class="text-xs text-gray-500 leading-relaxed mt-2">
                                Bingung pilih yang mana? <a href="{{ route('pra.create') }}" class="text-[#8C7A63] font-semibold hover:underline">Isi Form Pra-Konsultasi Online</a> secara gratis.
                                <br>Atau <a href="{{ route('appointment.konsultasi') }}" class="text-[#8C7A63] font-semibold hover:underline">Buat Janji Temu Dokter</a> untuk diagnosis tatap muka.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Section Title -->
                <div class="mb-5">
                    <h2 class="text-2xl font-bold text-[#1A1A1A]">Pilih Layanan</h2>
                    <p class="text-sm text-gray-500 mt-1">Temukan treatment yang sesuai dengan kebutuhan Anda.</p>
                </div>

                <!-- Search -->
                <div class="relative mb-5">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                        <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>
                    <input type="text" x-model="searchQuery" placeholder="Cari nama treatment..."
                        class="w-full pl-11 pr-4 py-3 text-sm border border-gray-200 rounded-xl bg-white focus:outline-none focus:ring-2 focus:ring-[#8C7A63]/30 focus:border-[#8C7A63] transition-all placeholder:text-gray-400">
                </div>

                <!-- Category Tabs -->
                <div class="flex gap-2 overflow-x-auto pb-3 mb-6 scrollbar-hide">
                    <button @click="activeCategory = 'semua'"
                        class="shrink-0 px-4 py-2 rounded-full text-xs font-semibold transition-all duration-200 border"
                        :class="activeCategory === 'semua' ? 'bg-[#1A1A1A] text-white border-[#1A1A1A]' : 'bg-white text-gray-600 border-gray-200 hover:border-[#8C7A63]'">
                        Semua
                    </button>
                    @foreach($categories as $cat)
                    <button @click="activeCategory = '{{ $cat->treatment_category_id }}'"
                        class="shrink-0 px-4 py-2 rounded-full text-xs font-semibold transition-all duration-200 border"
                        :class="activeCategory === '{{ $cat->treatment_category_id }}' ? 'bg-[#1A1A1A] text-white border-[#1A1A1A]' : 'bg-white text-gray-600 border-gray-200 hover:border-[#8C7A63]'">
                        {{ $cat->nama }}
                    </button>
                    @endforeach
                </div>

                <!-- Treatment Cards -->
                <div class="space-y-4">
                    @foreach($treatments as $treatment)
                    <div x-show="filterTreatment('{{ $treatment->treatment_category_id }}', '{{ addslashes($treatment->nama) }}')"
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 translate-y-2"
                         x-transition:enter-end="opacity-100 translate-y-0"
                         class="rounded-2xl border transition-all duration-200"
                         :class="isSelected({{ $treatment->treatment_id }}) ? 'border-[#8C7A63] bg-[#FAF8F5]/50' : 'border-gray-200 bg-white'">

                        <div class="p-4 cursor-pointer" @click="showTreatmentDetail({{ $treatment->treatment_id }})">
                            <div class="flex gap-4">
                                <!-- Treatment Image -->
                                <div class="w-20 h-20 sm:w-24 sm:h-24 rounded-xl overflow-hidden shrink-0 bg-gray-100">
                                    @if($treatment->foto_path)
                                        <img src="{{ Storage::url($treatment->foto_path) }}" alt="{{ $treatment->nama }}" class="w-full h-full object-cover">
                                    @else
                                        <div class="w-full h-full flex items-center justify-center bg-[#E8E0D8] text-[#8C7A63]">
                                            <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                        </div>
                                    @endif
                                </div>

                                <!-- Treatment Info -->
                                <div class="flex-1 min-w-0">
                                    <span class="text-[10px] font-bold uppercase tracking-wider text-[#8C7A63]">{{ $treatment->category->nama ?? 'Uncategorized' }}</span>
                                    <h3 class="text-sm sm:text-base font-bold text-[#1A1A1A] mt-0.5 leading-snug">{{ $treatment->nama }}</h3>
                                    <div class="flex items-center justify-between mt-2">
                                        <div class="flex items-center gap-1.5 text-gray-400">
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            <span class="text-xs font-medium">{{ $treatment->durasi_menit }} Menit</span>
                                        </div>
                                        <span class="text-sm sm:text-base font-bold text-[#1A1A1A]">Rp {{ number_format($treatment->harga, 0, ',', '.') }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Select/Deselect Button -->
                        <div class="px-4 pb-4">
                            <button @click="toggleTreatment({{ $treatment->treatment_id }}, '{{ addslashes($treatment->nama) }}', {{ $treatment->harga }}, {{ $treatment->durasi_menit }}, '{{ $treatment->foto_path ? Storage::url($treatment->foto_path) : '' }}')"
                                class="w-full py-3 rounded-xl text-sm font-semibold transition-all duration-200 flex items-center justify-center gap-2"
                                :class="isSelected({{ $treatment->treatment_id }}) ? 'bg-white border border-[#8C7A63] text-[#8C7A63]' : 'bg-[#3B3529] text-white hover:bg-[#2A261D]'">
                                <template x-if="!isSelected({{ $treatment->treatment_id }})">
                                    <span class="flex items-center gap-1.5">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                        Pilih
                                    </span>
                                </template>
                                <template x-if="isSelected({{ $treatment->treatment_id }})">
                                    <span class="flex items-center gap-1.5">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                        Terpilih
                                    </span>
                                </template>
                            </button>
                        </div>
                    </div>
                    @endforeach

                    <!-- Empty State -->
                    <div x-show="noResults" class="py-12 text-center">
                        <svg class="w-12 h-12 mx-auto text-gray-300 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        <p class="text-sm text-gray-400">Treatment tidak ditemukan.</p>
                    </div>
                </div>
            </div>

            <!-- ================== TREATMENT DETAIL VIEW ================== -->
            <div x-show="step === 1 && detailTreatment" x-cloak x-transition.opacity.duration.300ms class="pb-6">
                <!-- Hero Image -->
                <div class="relative w-full aspect-[4/3] bg-[#E8E0D8] overflow-hidden">
                    <template x-if="detailTreatment && detailTreatment.foto">
                        <img :src="detailTreatment.foto" class="w-full h-full object-cover" :alt="detailTreatment.nama">
                    </template>
                    <template x-if="detailTreatment && !detailTreatment.foto">
                        <div class="w-full h-full flex items-center justify-center">
                            <svg class="w-16 h-16 text-[#8C7A63]/50" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        </div>
                    </template>
                    <!-- Category Badge -->
                    <div class="absolute top-4 left-4">
                        <span class="inline-block text-[10px] font-bold uppercase tracking-wider bg-[#FAF8F5]/90 backdrop-blur-sm text-[#8C7A63] px-3 py-1.5 rounded-lg" x-text="detailTreatment?.category_nama"></span>
                    </div>
                </div>

                <div class="px-6 sm:px-10">
                    <!-- Name + Price + Duration -->
                    <div class="flex items-start justify-between mt-6 mb-2">
                        <h2 class="text-xl sm:text-2xl font-bold text-[#1A1A1A] leading-tight flex-1 pr-4" x-text="detailTreatment?.nama"></h2>
                        <div class="text-right shrink-0">
                            <div class="text-base sm:text-lg font-bold text-[#1A1A1A]" x-text="'Rp ' + (detailTreatment?.harga || 0).toLocaleString('id-ID')"></div>
                            <div class="flex items-center gap-1 text-xs text-gray-400 justify-end mt-0.5">
                                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <span x-text="(detailTreatment?.durasi || 0) + ' Menit'"></span>
                            </div>
                        </div>
                    </div>

                    <hr class="border-gray-100 my-5">

                    <!-- Deskripsi Treatment -->
                    <div class="mb-6">
                        <h3 class="text-sm font-bold text-[#1A1A1A] mb-3">Deskripsi Treatment</h3>
                        <p class="text-sm text-gray-600 leading-relaxed whitespace-pre-line" x-text="detailTreatment?.deskripsi || 'Deskripsi belum tersedia.'"></p>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex items-center gap-3 mt-8">
                        <button @click="closeTreatmentDetail()" class="px-6 py-3.5 rounded-2xl border border-gray-200 bg-white text-sm font-medium text-gray-600 hover:bg-gray-50 transition-all shadow-sm">
                            Kembali
                        </button>
                        <button @click="addFromDetail()" x-show="detailTreatment && !isSelected(detailTreatment.id)"
                            class="flex-1 py-3.5 rounded-2xl bg-[#3B3529] text-sm font-semibold text-white hover:bg-[#2A261D] transition-all shadow-sm text-center">
                            Tambahkan ke Pilihan
                        </button>
                        <button @click="closeTreatmentDetail()" x-show="detailTreatment && isSelected(detailTreatment.id)" x-cloak
                            class="flex-1 py-3.5 rounded-2xl bg-white border border-[#8C7A63] text-sm font-semibold text-[#8C7A63] transition-all shadow-sm text-center flex items-center justify-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            Sudah Dipilih
                        </button>
                    </div>
                </div>
            </div>

            <!-- ================== STEP 2: JADWAL ================== -->
            <div x-show="step === 2" x-cloak x-transition.opacity.duration.300ms class="px-6 sm:px-10 pt-6 pb-6">
                <!-- Layanan Terpilih -->
                <div class="mb-8">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-xl font-bold text-[#1A1A1A]">Layanan Terpilih</h2>
                        <button @click="step = 1" class="text-sm font-semibold text-[#8C7A63] hover:underline">+ Tambah</button>
                    </div>
                    <div class="space-y-3">
                        <template x-for="item in selectedTreatments" :key="item.id">
                            <div class="flex items-center gap-4 p-4 rounded-2xl border border-gray-200 bg-white">
                                <div class="w-14 h-14 rounded-xl bg-[#E8E0D8] shrink-0 overflow-hidden flex items-center justify-center">
                                    <template x-if="item.foto"><img :src="item.foto" class="w-full h-full object-cover"></template>
                                    <template x-if="!item.foto"><svg class="w-6 h-6 text-[#8C7A63]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg></template>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h4 class="text-sm font-bold text-[#1A1A1A] truncate" x-text="item.nama"></h4>
                                    <div class="flex items-center gap-2 mt-1">
                                        <span class="text-xs text-gray-400 flex items-center gap-1">
                                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            <span x-text="item.durasi + ' Menit'"></span>
                                        </span>
                                        <span class="text-xs font-semibold text-[#8C7A63]" x-text="'Rp ' + item.harga.toLocaleString('id-ID')"></span>
                                    </div>
                                </div>
                                <button @click="removeTreatment(item.id)" class="shrink-0 p-2 text-gray-300 hover:text-red-400 transition-colors">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Pilih Tanggal -->
                <div class="mb-8">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-xl font-bold text-[#1A1A1A]">Pilih Tanggal</h2>
                        <span class="text-xs text-[#C89B53] bg-[#FAF8F5] px-2 py-1 rounded-md font-medium" x-text="currentMonthYear"></span>
                    </div>
                    <div class="flex gap-3 overflow-x-auto pb-2 scrollbar-hide snap-x">
                        <template x-for="day in availableDays" :key="day.date">
                            <label class="snap-start shrink-0 cursor-pointer">
                                <input type="radio" name="tanggal" :value="day.date" x-model="tanggal" @change="displayDate = day.displayFull; jam = ''; fetchSlots()" class="peer sr-only">
                                <div class="flex flex-col items-center justify-center w-[80px] h-[105px] rounded-2xl border transition-all duration-200"
                                    :class="tanggal === day.date ? 'border-[#3B3529] bg-[#3B3529] text-white shadow-md' : 'border-gray-200 bg-white hover:border-[#8C7A63] text-gray-600'">
                                    <span class="text-xs uppercase mb-1 font-medium tracking-wide" :class="tanggal === day.date ? 'text-white/80' : 'text-gray-400'" x-text="day.dayName"></span>
                                    <span class="text-3xl font-bold leading-none" :class="tanggal === day.date ? 'text-white' : 'text-[#1A1A1A]'" x-text="day.dayNum"></span>
                                    <span class="text-[11px] mt-1 font-medium" :class="tanggal === day.date ? 'text-white/80' : 'text-gray-400'" x-text="day.monthShort"></span>
                                </div>
                            </label>
                        </template>
                    </div>
                </div>

                <!-- Pilih Waktu -->
                <div x-show="tanggal !== ''" class="mb-8">
                    <h2 class="text-xl font-bold text-[#1A1A1A] mb-4">Pilih Waktu (WIB)</h2>
                    
                    <!-- Loading State -->
                    <div x-show="loadingSlots" class="flex items-center justify-center py-8">
                        <svg class="w-6 h-6 animate-spin text-[#CFAF53]" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span class="ml-2 text-sm text-slate-500">Mengecek ketersediaan...</span>
                    </div>
                    
                    <div x-show="!loadingSlots" class="grid grid-cols-2 gap-3">
                        <template x-for="slot in timeSlots" :key="slot.value">
                            <label :class="slot.penuh ? 'cursor-not-allowed' : 'cursor-pointer'">
                                <input type="radio" name="jam" :value="slot.value" x-model="jam" class="peer sr-only" :disabled="slot.penuh">
                                <div class="py-4 px-4 rounded-2xl border transition-all duration-200"
                                    :class="slot.penuh 
                                        ? 'border-rose-200 bg-rose-50/50 opacity-60' 
                                        : (jam === slot.value 
                                            ? 'border-[#3B3529] bg-[#FAF8F5] shadow-sm' 
                                            : 'border-gray-200 bg-white hover:border-[#8C7A63]')">
                                    <div class="flex items-center gap-2 text-sm font-medium" 
                                        :class="slot.penuh 
                                            ? 'text-slate-400' 
                                            : (jam === slot.value ? 'text-[#1A1A1A] font-bold' : 'text-gray-500')">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        <span x-text="slot.value"></span>
                                    </div>
                                    <span class="text-[10px] font-semibold mt-1 block" 
                                        :class="slot.penuh 
                                            ? 'text-rose-500' 
                                            : (jam === slot.value ? 'text-[#C89B53]' : 'text-gray-400')" 
                                        x-text="slot.label"></span>
                                </div>
                            </label>
                        </template>
                    </div>
                </div>

                <!-- Navigation buttons -->
                <div class="space-y-3 mt-4">
                    <button @click="goBack()" class="w-full flex items-center justify-center gap-2 rounded-2xl border border-gray-200 bg-white px-6 py-3.5 text-sm font-medium text-gray-600 transition-all hover:bg-gray-50 shadow-sm">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                        Kembali ke Layanan
                    </button>
                    <button @click="nextStep()" x-show="tanggal !== '' && jam !== ''" x-cloak x-transition
                        class="w-full flex items-center justify-center gap-2 rounded-2xl bg-[#3B3529] py-4 text-base font-medium text-white transition-all hover:bg-[#2A261D] shadow-sm">
                        Lanjutkan ke Data Diri
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </button>
                </div>
            </div>

            <!-- Bottom Bar (Inside Card) - Step 1 only -->
            <div x-show="selectedTreatments.length > 0 && step === 1 && !detailTreatment" x-cloak
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 class="border-t border-gray-200 bg-[#FDFCFB] sticky bottom-0 z-40 shadow-[0_-10px_20px_-10px_rgba(0,0,0,0.05)]">

                <!-- Expandable Detail -->
                <div x-show="showDetail" x-cloak
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 -translate-y-2"
                     x-transition:enter-end="opacity-100 translate-y-0"
                     class="border-b border-gray-100 px-6 sm:px-10 py-4">
                    <div class="flex items-center justify-between mb-3">
                        <h4 class="text-sm font-bold text-[#1A1A1A]">Rincian Pesanan</h4>
                        <span class="text-xs font-semibold text-[#8C7A63] bg-[#FAF8F5] px-2 py-1 rounded-md" x-text="selectedTreatments.length + ' Item'"></span>
                    </div>
                    <div class="space-y-2">
                        <template x-for="item in selectedTreatments" :key="item.id">
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-gray-600 truncate mr-4" x-text="item.nama"></span>
                                <span class="font-semibold text-[#1A1A1A] shrink-0" x-text="'Rp ' + item.harga.toLocaleString('id-ID')"></span>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Summary Bar -->
                <div class="px-6 sm:px-10 py-4">
                    <div class="flex items-center justify-between">
                        <div class="flex-1 min-w-0">
                            <button @click="showDetail = !showDetail" class="flex items-center gap-1.5 text-xs font-semibold text-gray-500 hover:text-[#8C7A63] transition-colors mb-1">
                                <span x-text="selectedTreatments.length + ' Layanan Terpilih'"></span>
                                <svg class="w-3.5 h-3.5 transition-transform duration-200" :class="showDetail ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg>
                            </button>
                            <div class="truncate text-xs text-gray-400" x-text="selectedTreatments.map(t => t.nama).join(', ')"></div>
                            <div class="text-lg font-bold text-[#1A1A1A] mt-0.5" x-text="'Rp ' + totalHarga.toLocaleString('id-ID')"></div>
                        </div>
                        <button @click="nextStep()" class="shrink-0 ml-4 inline-flex items-center gap-2 rounded-2xl bg-[#3B3529] px-6 py-3.5 text-sm font-semibold text-white hover:bg-[#2A261D] transition-all shadow-sm">
                            Lanjut Atur Jadwal
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </button>
                    </div>
                </div>
            </div>

            <!-- ================== STEP 3: DATA DIRI ================== -->
            <div x-show="step === 3" x-cloak x-transition.opacity.duration.300ms class="px-6 sm:px-10 pt-6 pb-6">
                <h2 class="text-2xl font-bold text-[#1A1A1A] mb-2">Data Pasien</h2>
                <p class="text-sm text-gray-500 mb-6">Silakan lengkapi data diri Anda untuk keperluan reservasi.</p>

                <!-- Warning if profile incomplete -->
                <div x-show="!isProfileComplete" class="mb-6 rounded-2xl border border-red-200 bg-red-50 p-5">
                    <div class="flex gap-3">
                        <svg class="w-5 h-5 text-red-500 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        <div>
                            <p class="text-sm font-bold text-red-700 mb-1">Profil Belum Lengkap</p>
                            <p class="text-xs text-red-600">Silakan lengkapi Nama dan Nomor WhatsApp di halaman Dashboard terlebih dahulu.</p>
                            <a href="/dashboard" class="inline-block mt-2 text-xs font-bold text-red-700 underline hover:text-red-900">Lengkapi Profil →</a>
                        </div>
                    </div>
                </div>

                <div class="space-y-5">
                    <div>
                        <label class="block text-sm font-semibold text-[#1A1A1A] mb-2">Nama Lengkap</label>
                        <div class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3.5 text-sm text-[#1A1A1A]" x-text="nama_pasien || 'Masukkan nama lengkap'"></div>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-[#1A1A1A] mb-2">No. WhatsApp</label>
                        <div class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3.5 text-sm text-[#1A1A1A]" x-text="nomor_wa || '+62 812 3456 7890'"></div>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-[#1A1A1A] mb-2">Email</label>
                        <div class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3.5 text-sm text-[#1A1A1A]" x-text="email || 'contoh@email.com'"></div>
                    </div>
                </div>

                <div class="space-y-3 mt-8">
                    <button @click="goBack()" class="w-full flex items-center justify-center gap-2 rounded-2xl border border-gray-200 bg-white px-6 py-3.5 text-sm font-medium text-gray-600 transition-all hover:bg-gray-50 shadow-sm">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                        Kembali ke Jadwal
                    </button>
                    <button @click="nextStep()" x-show="isProfileComplete" x-cloak x-transition
                        class="w-full flex items-center justify-center gap-2 rounded-2xl bg-[#3B3529] py-4 text-base font-medium text-white transition-all hover:bg-[#2A261D] shadow-sm">
                        Lanjutkan Pembayaran
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </button>
                </div>
            </div>

            <!-- ================== STEP 4: PEMBAYARAN ================== -->
            <div x-show="step === 4" x-cloak x-transition.opacity.duration.300ms class="px-6 sm:px-10 pt-6 pb-6">
                <h2 class="text-2xl font-bold text-[#1A1A1A] mb-6">Detail Pembayaran</h2>

                <div class="rounded-[1.5rem] border border-[#F0EBE1] bg-[#FAF8F5]/30 p-5 sm:p-6 mb-6">
                    <!-- Jadwal Booking -->
                    <div class="mb-5">
                        <div class="text-[10px] font-bold tracking-widest text-gray-400 uppercase mb-3">JADWAL BOOKING</div>
                        <div class="flex items-center gap-4 text-sm text-[#1A1A1A]">
                            <span class="flex items-center gap-1.5">
                                <svg class="w-4 h-4 text-[#8C7A63]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                <span x-text="displayDate"></span>
                            </span>
                            <span class="flex items-center gap-1.5">
                                <svg class="w-4 h-4 text-[#8C7A63]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <span x-text="jam.split(' - ')[0] + ' WIB'"></span>
                            </span>
                        </div>
                    </div>

                    <hr class="border-[#F0EBE1] mb-5">

                    <!-- Rincian Layanan -->
                    <div class="mb-5">
                        <div class="text-[10px] font-bold tracking-widest text-gray-400 uppercase mb-3">RINCIAN LAYANAN</div>
                        <div class="space-y-3">
                            <template x-for="item in selectedTreatments" :key="item.id">
                                <div class="flex justify-between text-sm">
                                    <div>
                                        <span class="font-medium text-[#1A1A1A]" x-text="item.nama"></span>
                                        <span class="block text-xs text-gray-400" x-text="item.durasi + ' Menit'"></span>
                                    </div>
                                    <span class="font-semibold text-[#1A1A1A] shrink-0" x-text="'Rp ' + item.harga.toLocaleString('id-ID')"></span>
                                </div>
                            </template>
                        </div>
                    </div>

                    <hr class="border-[#F0EBE1] mb-5">

                    <!-- Totals -->
                    <div class="space-y-2 mb-4">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500">Subtotal Layanan</span>
                            <span class="text-[#1A1A1A]" x-text="'Rp ' + totalHarga.toLocaleString('id-ID')"></span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500">Biaya Admin</span>
                            <span class="text-[#1A1A1A]">Rp 0</span>
                        </div>
                    </div>
                    <hr class="border-dashed border-gray-300 mb-4">
                    <div class="flex justify-between items-center">
                        <span class="text-base font-bold text-[#1A1A1A]">Total Pembayaran</span>
                        <span class="text-xl font-bold text-[#C89B53]" x-text="'Rp ' + totalHarga.toLocaleString('id-ID')"></span>
                    </div>
                </div>

                <!-- Data Pasien -->
                <div class="rounded-[1.5rem] border border-[#F0EBE1] bg-[#FAF8F5]/30 p-5 sm:p-6 mb-6">
                    <div class="text-[10px] font-bold tracking-widest text-gray-400 uppercase mb-3">DATA PASIEN</div>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between"><span class="text-gray-500">Nama Lengkap</span><span class="font-medium text-[#1A1A1A]" x-text="nama_pasien"></span></div>
                        <div class="flex justify-between"><span class="text-gray-500">No. WhatsApp</span><span class="font-medium text-[#1A1A1A]" x-text="nomor_wa"></span></div>
                        <div class="flex justify-between"><span class="text-gray-500">Email</span><span class="font-medium text-[#1A1A1A]" x-text="email || '-'"></span></div>
                    </div>
                </div>

                <!-- Kebijakan -->
                <div class="flex gap-4 rounded-2xl border border-[#F0EBE1] bg-[#FAF8F5]/30 p-5 mb-8">
                    <div class="shrink-0 mt-0.5">
                        <svg class="w-5 h-5 text-[#8C7A63]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <div class="text-xs text-gray-500 leading-relaxed">
                        <strong class="text-[#1A1A1A]">Kebijakan Perubahan & Pembatalan</strong><br>
                        <strong>Reschedule:</strong> Maksimal H-1 untuk jadwal Weekday (Senin-Jumat) dan H-3 untuk jadwal Weekend (Sabtu-Minggu).<br>
                        <strong>Refund:</strong> Pengajuan pembatalan akan diproses dalam waktu maksimal 24 jam.
                    </div>
                </div>

                <!-- Navigation buttons -->
                <div class="space-y-3">
                    <button @click="goBack()" class="w-full flex items-center justify-center gap-2 rounded-2xl border border-gray-200 bg-white px-6 py-3.5 text-sm font-medium text-gray-600 transition-all hover:bg-gray-50 shadow-sm">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                        Kembali ke Data Diri
                    </button>
                    <button type="button" @click="bayarSekarang()" :disabled="isSubmitting"
                        class="w-full flex justify-center items-center gap-2 rounded-2xl bg-[#3B3529] py-4 text-base font-medium text-white transition-all hover:bg-[#2A261D] disabled:opacity-70 shadow-sm">
                        <svg x-show="!isSubmitting" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                        <svg x-show="isSubmitting" x-cloak class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        <span x-text="isSubmitting ? 'Memproses...' : 'Bayar Sekarang'"></span>
                    </button>
                </div>
                <p class="text-center text-[10px] text-gray-400 mt-3 uppercase tracking-wider font-bold flex items-center justify-center gap-1">
                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                    Secure Payment via Midtrans
                </p>
            </div>

        </div>
    </div>

    <!-- CANCEL CONFIRMATION MODAL -->
    <div x-show="showCancelModal" x-cloak class="fixed inset-0 z-[9999] flex items-center justify-center p-4" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
        <div class="fixed inset-0 bg-[#3B3529]/40 backdrop-blur-sm" @click="showCancelModal = false"></div>
        <div class="relative bg-white rounded-[2rem] shadow-2xl max-w-sm w-full p-8 text-center z-10" x-transition:enter="transition ease-out duration-300 delay-100" x-transition:enter-start="opacity-0 scale-95 translate-y-4" x-transition:enter-end="opacity-100 scale-100 translate-y-0">
            <div class="w-16 h-16 rounded-full border border-red-100 bg-red-50 flex items-center justify-center mx-auto mb-6">
                <svg class="w-8 h-8 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            </div>
            <h3 class="text-2xl font-bold text-[#1A1A1A] mb-3">Batalkan Booking?</h3>
            <p class="text-base text-gray-500 mb-8 leading-relaxed">
                Jika Anda kembali, semua treatment yang telah dipilih akan terhapus. Apakah Anda yakin?
            </p>
            <div class="flex gap-3">
                <button type="button" @click="showCancelModal = false" class="flex-1 rounded-2xl bg-gray-100 py-3.5 text-sm font-bold text-gray-700 transition-all hover:bg-gray-200">
                    Tidak
                </button>
                <button type="button" @click="window.location.href = '{{ route('appointment.select') }}'" class="flex-1 rounded-2xl bg-red-600 py-3.5 text-sm font-bold text-white transition-all hover:bg-red-700">
                    Ya, Batalkan
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function bookingTreatment() {
    const dayNames = ['MIN','SEN','SEL','RAB','KAM','JUM','SAB'];
    const monthNames = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
    return {
        step: 1,
        stepLabels: ['Layanan', 'Jadwal', 'Data Diri', 'Pembayaran'],
        showCancelModal: false,
        showDetail: false,
        detailTreatment: null,
        searchQuery: '',
        activeCategory: 'semua',
        selectedTreatments: [],
        tanggal: '',
        jam: '',
        displayDate: '',
        isSubmitting: false,
        nama_pasien: @json($user->name ?? ''),
        nomor_wa: @json($user->phone ?? ''),
        email: @json($user->email ?? ''),
        treatmentsData: @json($treatmentsJson),
        timeSlots: [],
        loadingSlots: false,

        async fetchSlots() {
            if (!this.tanggal) { this.timeSlots = []; return; }
            this.loadingSlots = true;
            this.jam = ''; // reset pilihan jam saat ganti tanggal
            try {
                const res = await fetch(`{{ route('appointment.booking-treatment.check-slots') }}?tanggal=${this.tanggal}`);
                const data = await res.json();
                this.timeSlots = data.map(s => ({
                    value: s.value,
                    label: s.penuh ? 'Penuh' : `Sisa ${s.sisa} slot`,
                    penuh: s.penuh,
                    sisa: s.sisa,
                }));
            } catch(e) {
                // Fallback jika API gagal
                this.timeSlots = [
                    { value: '08:00 - 10:00', label: 'Tersedia', penuh: false, sisa: 2 },
                    { value: '10:30 - 12:30', label: 'Tersedia', penuh: false, sisa: 2 },
                    { value: '13:00 - 15:00', label: 'Tersedia', penuh: false, sisa: 2 },
                    { value: '15:30 - 17:30', label: 'Tersedia', penuh: false, sisa: 2 },
                ];
            }
            this.loadingSlots = false;
        },

        get totalHarga() { return this.selectedTreatments.reduce((s, t) => s + t.harga, 0); },
        get isProfileComplete() { return !!(this.nama_pasien && this.nomor_wa); },
        get currentMonthYear() {
            const d = this.tanggal ? new Date(this.tanggal) : new Date();
            return monthNames[d.getMonth()] + ' ' + d.getFullYear();
        },
        get availableDays() {
            const days = []; const now = new Date();
            for (let i = 1; i <= 14; i++) {
                const d = new Date(now); d.setDate(now.getDate() + i);
                const y = d.getFullYear(), m = String(d.getMonth()+1).padStart(2,'0'), day = String(d.getDate()).padStart(2,'0');
                days.push({ 
                    date: `${y}-${m}-${day}`, 
                    dayName: dayNames[d.getDay()], 
                    dayNum: d.getDate(), 
                    monthShort: d.toLocaleString('id-ID', {month:'short'}),
                    displayFull: `${d.getDate()} ${monthNames[d.getMonth()]} ${y}` 
                });
            }
            return days;
        },
        get noResults() {
            const q = this.searchQuery.toLowerCase().trim(), cat = this.activeCategory;
            return !this.treatmentsData.some(t => {
                return (cat === 'semua' || String(t.category_id) === String(cat)) && (!q || t.nama.toLowerCase().includes(q));
            });
        },

        filterTreatment(cId, nama) {
            const matchCat = this.activeCategory === 'semua' || String(cId) === String(this.activeCategory);
            const q = this.searchQuery.toLowerCase().trim();
            return matchCat && (!q || nama.toLowerCase().includes(q));
        },
        isSelected(id) { return this.selectedTreatments.some(t => t.id === id); },
        showTreatmentDetail(id) {
            this.detailTreatment = this.treatmentsData.find(t => t.id === id) || null;
            window.scrollTo({ top: 0, behavior: 'smooth' });
        },
        closeTreatmentDetail() {
            this.detailTreatment = null;
        },
        addFromDetail() {
            if (!this.detailTreatment) return;
            const t = this.detailTreatment;
            if (!this.isSelected(t.id)) {
                this.selectedTreatments.push({ id: t.id, nama: t.nama, harga: t.harga, durasi: t.durasi, foto: t.foto });
            }
            this.detailTreatment = null;
        },
        toggleTreatment(id, nama, harga, durasi, foto) {
            const idx = this.selectedTreatments.findIndex(t => t.id === id);
            if (idx > -1) { this.selectedTreatments.splice(idx, 1); }
            else { this.selectedTreatments.push({ id, nama, harga, durasi, foto }); }
        },
        removeTreatment(id) {
            this.selectedTreatments = this.selectedTreatments.filter(t => t.id !== id);
            if (this.selectedTreatments.length === 0) { this.step = 1; }
        },
        confirmCancel() {
            if (this.selectedTreatments.length > 0) { this.showCancelModal = true; }
            else { window.location.href = '{{ route("appointment.select") }}'; }
        },
        goBack() {
            if (this.step > 1) { this.step--; window.scrollTo({ top: 0, behavior: 'smooth' }); }
            else { this.confirmCancel(); }
        },
        nextStep() {
            if (this.step === 1 && this.selectedTreatments.length === 0) return;
            if (this.step === 2 && (!this.tanggal || !this.jam)) return;
            if (this.step === 3 && !this.isProfileComplete) return;
            this.step++;
            window.scrollTo({ top: 0, behavior: 'smooth' });
        },
        async bayarSekarang() {
            if (this.isSubmitting) return;
            this.isSubmitting = true;
            try {
                const res = await fetch('{{ route("appointment.booking-treatment.store") }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                    body: JSON.stringify({ treatments: this.selectedTreatments.map(t => ({ id: t.id })), tanggal: this.tanggal, jam: this.jam })
                });
                const data = await res.json();
                if (data.status === 'success' && data.snap_token) {
                    const aptId = data.appointment_id;
                    window.snap.pay(data.snap_token, {
                        onSuccess: () => { window.location.href = '/dashboard?tab=appointment&highlight=' + aptId; },
                        onPending: () => { window.location.href = '/dashboard?tab=appointment&highlight=' + aptId; },
                        onError: () => { window.showToast && window.showToast('error', 'Terjadi kesalahan saat memproses pembayaran. Silakan coba lagi.', 'Pembayaran Gagal'); this.isSubmitting = false; },
                        onClose: () => { this.isSubmitting = false; }
                    });
                } else if (data.status === 'success_no_payment') {
                    window.showToast && window.showToast('success', data.message || 'Janji temu berhasil dibuat.', 'Berhasil');
                    setTimeout(() => { window.location.href = '/dashboard?tab=appointment&highlight=' + (data.appointment_id || ''); }, 800);
                } else {
                    window.showToast && window.showToast('error', data.message || 'Terjadi kesalahan saat memproses janji temu.', 'Gagal');
                    this.isSubmitting = false;
                }
            } catch (e) {
                window.showToast && window.showToast('error', 'Tidak dapat terhubung ke server. Silakan periksa jaringan Anda.', 'Gagal Terhubung');
                this.isSubmitting = false;
            }
        }
    }
}
</script>

<!-- Midtrans Snap -->
<script src="https://app.{{ config('midtrans.is_production') ? '' : 'sandbox.' }}midtrans.com/snap/snap.js" data-client-key="{{ $midtransClientKey }}"></script>
<style>[x-cloak]{display:none!important}</style>
</x-guest-layout>
