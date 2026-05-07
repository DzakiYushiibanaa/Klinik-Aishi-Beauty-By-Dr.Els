{{-- ====== TAB: JANJI TEMU ====== --}}
<div class="bg-white rounded-2xl border border-[#E8E0D8]/60 shadow-sm p-6" x-data="{ 
    viewMode: 'list',
    filterStatus: 'aktif',
    filterWaktu: 'semua',
    filterStartDate: '',
    filterEndDate: '',
    filterJenis: 'semua',
    filterPayment: 'semua',
    searchQuery: '',
    sortOrder: 'terdekat',

    isDateInRange(dateStr) {
        if (this.filterWaktu === 'semua') return true;
        
        const itemDate = new Date(dateStr);
        itemDate.setHours(0, 0, 0, 0);
        
        const today = new Date();
        today.setHours(0, 0, 0, 0);

        if (this.filterWaktu === 'hari_ini') {
            return itemDate.getTime() === today.getTime();
        }
        if (this.filterWaktu === 'besok') {
            const tomorrow = new Date(today);
            tomorrow.setDate(tomorrow.getDate() + 1);
            return itemDate.getTime() === tomorrow.getTime();
        }
        if (this.filterWaktu === 'minggu_ini') {
            const dayOfWeek = today.getDay(); // 0 is Sunday
            const diffToMonday = today.getDate() - dayOfWeek + (dayOfWeek === 0 ? -6 : 1); 
            const startOfWeek = new Date(today);
            startOfWeek.setDate(diffToMonday);
            const endOfWeek = new Date(startOfWeek);
            endOfWeek.setDate(endOfWeek.getDate() + 6);
            return itemDate >= startOfWeek && itemDate <= endOfWeek;
        }
        if (this.filterWaktu === 'bulan_ini') {
            return itemDate.getMonth() === today.getMonth() && itemDate.getFullYear() === today.getFullYear();
        }
        if (this.filterWaktu === 'custom') {
            if (!this.filterStartDate || !this.filterEndDate) return true;
            const start = new Date(this.filterStartDate);
            start.setHours(0, 0, 0, 0);
            const end = new Date(this.filterEndDate);
            end.setHours(23, 59, 59, 999);
            return itemDate >= start && itemDate <= end;
        }
        return true;
    }
}">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
            <h2 class="text-xl font-extrabold text-[#1A1A1A]">Janji Temu Saya</h2>
            <p class="text-sm text-slate-500 mt-1">Lihat dan kelola semua jadwal konsultasi dan treatment Anda di sini.</p>
        </div>
        <div class="bg-white border border-[#E8E0D8]/60 p-1 rounded-xl flex items-center self-start sm:self-auto shrink-0">
            <button @click="viewMode = 'list'" :class="viewMode === 'list' ? 'bg-[#FAF8F5] text-[#1A1A1A] shadow-sm' : 'text-slate-400'" class="px-3 py-1.5 rounded-lg text-xs font-bold transition">List</button>
            <button @click="viewMode = 'calendar'" :class="viewMode === 'calendar' ? 'bg-[#FAF8F5] text-[#CFAF53] shadow-sm' : 'text-slate-400'" class="px-3 py-1.5 rounded-lg text-xs font-bold transition">Kalender</button>
        </div>
    </div>

    <div class="mb-6 space-y-4" x-show="viewMode === 'list'">
        {{-- Status Filter Chips --}}
        <div class="flex items-center gap-2 overflow-x-auto pb-2 scrollbar-hide">
            <template x-for="status in [
                { id: 'semua', label: 'Semua' },
                { id: 'aktif', label: 'Aktif' },
                { id: 'diajukan', label: 'Diajukan' },
                { id: 'disetujui', label: 'Disetujui' },
                { id: 'selesai', label: 'Selesai' },
                { id: 'batal', label: 'Dibatalkan' },
                { id: 'tidak_hadir', label: 'Tidak Hadir' }
            ]" :key="status.id">
                <button @click="filterStatus = status.id"
                        class="shrink-0 px-4 py-1.5 rounded-full text-sm font-bold transition border"
                        :class="filterStatus === status.id ? 'bg-[#3B3529] text-white border-[#3B3529]' : 'bg-white text-slate-500 border-slate-200 hover:border-[#8C7A63] hover:text-[#8C7A63]'">
                    <span x-text="status.label"></span>
                </button>
            </template>
        </div>

        {{-- Search Bar --}}
        <div class="relative mb-4">
            <svg class="w-4 h-4 text-slate-400 absolute left-4 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            <input x-model="searchQuery" type="text" placeholder="Cari nama dokter atau layanan..." class="w-full pl-11 pr-4 py-3 rounded-xl bg-white border border-[#E8E0D8]/60 text-sm focus:border-[#CFAF53] focus:ring-[#CFAF53]/20 focus:ring-2 outline-none transition">
        </div>

        {{-- Payment Filter Chips --}}
        <div class="flex items-center gap-2 overflow-x-auto pb-2 mb-2 scrollbar-hide">
            <template x-for="pay in [
                { id: 'semua', label: 'Semua Pembayaran' },
                { id: 'belum_bayar', label: 'Belum Bayar' },
                { id: 'lunas', label: 'Sudah Bayar' }
            ]" :key="pay.id">
                <button @click="filterPayment = pay.id"
                        class="shrink-0 px-4 py-1.5 rounded-full text-sm font-bold transition border"
                        :class="filterPayment === pay.id ? 'bg-[#CFAF53] text-white border-[#CFAF53]' : 'bg-white text-slate-500 border-slate-200 hover:border-[#CFAF53] hover:text-[#CFAF53]'">
                    <span x-text="pay.label"></span>
                </button>
            </template>
        </div>

        {{-- Dropdowns for Waktu, Layanan, Sorting --}}
        <div class="flex flex-col sm:flex-row gap-3 p-4 bg-[#FAF8F5] rounded-xl border border-[#E8E0D8]/60">
            <div class="flex-1">
                <label class="block text-xs font-bold text-slate-400 mb-1">Tanggal</label>
                <select x-model="filterWaktu" class="w-full text-sm border-slate-200 rounded-lg focus:ring-[#CFAF53] focus:border-[#CFAF53]">
                    <option value="semua">Semua Waktu</option>
                    <option value="hari_ini">Hari Ini</option>
                    <option value="besok">Besok</option>
                    <option value="minggu_ini">Minggu Ini</option>
                    <option value="bulan_ini">Bulan Ini</option>
                    <option value="custom">Pilih Tanggal</option>
                </select>
                <div x-show="filterWaktu === 'custom'" class="flex gap-2 mt-2" x-transition>
                    <input type="date" x-model="filterStartDate" class="w-1/2 text-sm border-slate-200 rounded-lg focus:ring-[#CFAF53] focus:border-[#CFAF53]">
                    <input type="date" x-model="filterEndDate" class="w-1/2 text-sm border-slate-200 rounded-lg focus:ring-[#CFAF53] focus:border-[#CFAF53]">
                </div>
            </div>
            
            <div class="flex-1">
                <label class="block text-xs font-bold text-slate-400 mb-1">Jenis Layanan</label>
                <select x-model="filterJenis" class="w-full text-sm border-slate-200 rounded-lg focus:ring-[#CFAF53] focus:border-[#CFAF53]">
                    <option value="semua">Semua Layanan</option>
                    <option value="konsultasi">Konsultasi</option>
                    <option value="treatment">Treatment</option>
                </select>
            </div>

            <div class="flex-1">
                <label class="block text-xs font-bold text-slate-400 mb-1">Urutkan</label>
                <select x-model="sortOrder" class="w-full text-sm border-slate-200 rounded-lg focus:ring-[#CFAF53] focus:border-[#CFAF53]">
                    <option value="terdekat">Terdekat</option>
                    <option value="terlama">Terlama</option>
                </select>
            </div>
        </div>
    </div>

    {{-- LIST VIEW --}}
    <div x-show="viewMode === 'list'" x-transition.opacity>
        @if($appointments->isEmpty())
            <div class="bg-[#FAF8F5] rounded-2xl border border-dashed border-[#E8E0D8] p-12 text-center">
                <div class="w-16 h-16 bg-[#CFAF53]/10 rounded-full flex items-center justify-center mb-4 mx-auto">
                    <svg class="w-8 h-8 text-[#CFAF53]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                </div>
                <h3 class="font-bold text-[#1A1A1A]">Belum ada janji temu</h3>
                <p class="text-sm text-slate-400 mt-1">Anda belum memiliki riwayat perawatan.</p>
                <a href="{{ route('appointment.select') }}" class="mt-4 inline-block bg-[#CFAF53] text-[#1A1A1A] px-6 py-2.5 rounded-full text-sm font-bold">Buat Janji Baru</a>
            </div>
        @else
            <div class="flex flex-col gap-4">
                @foreach($appointments as $apt)
                    @php
                        $aptStatus = strtolower($apt->status);
                        $totalDurasi = 0; $totalHarga = 0;
                        foreach($apt->items as $item) {
                            if($item->item_type == 'treatment' && $item->treatment) { $totalDurasi += $item->treatment->durasi_menit * $item->qty; $totalHarga += $item->treatment->harga * $item->qty; }
                            elseif($item->item_type == 'bundle' && $item->bundle) { $totalHarga += $item->bundle->harga * $item->qty; }
                            elseif($item->item_type == 'consultation' && $item->consultation) { $totalDurasi += $item->consultation->durasi_menit * $item->qty; $totalHarga += $item->consultation->harga * $item->qty; }
                        }
                        $statusLabel = match($aptStatus) { 'diajukan' => 'Diajukan', 'disetujui' => 'Akan Datang', 'menunggu_pembayaran' => 'Belum Bayar', 'selesai' => 'Selesai', 'batal' => 'Dibatalkan', default => ucfirst($apt->status) };
                        $statusBadge = match($aptStatus) { 'diajukan' => 'bg-amber-50 text-amber-700 border-amber-200', 'disetujui' => 'bg-blue-50 text-blue-700 border-blue-200', 'menunggu_pembayaran' => 'bg-amber-50 text-amber-600 border-amber-200', 'selesai' => 'bg-emerald-50 text-emerald-700 border-emerald-200', 'batal' => 'bg-rose-50 text-rose-700 border-rose-200', default => 'bg-slate-50 text-slate-600 border-slate-200' };

                        // Relasi: status pembayaran dari order
                        $relatedOrder = $appointmentOrderMap[$apt->appointment_id] ?? null;
                        $payLabel = null;
                        $payBadge = null;
                        $payFilterKey = 'semua';
                        if ($relatedOrder) {
                            // Jika order batal karena expired payment (canceled_by sistem), tetap anggap "Belum Bayar"
                            $isExpiredPayment = in_array($relatedOrder->status, ['batal', 'dibatalkan']) 
                                && $apt->canceled_by === 'sistem';

                            if ($isExpiredPayment) {
                                $payLabel = 'Belum Bayar';
                                $payBadge = 'bg-amber-50 text-amber-600 border-amber-200';
                                $payFilterKey = 'belum_bayar';
                            } else {
                                $payLabel = match($relatedOrder->status) { 'menunggu_pembayaran' => 'Belum Bayar', 'sudah_dibayar', 'lunas' => 'Lunas', 'batal', 'dibatalkan' => 'Batal', default => ucfirst($relatedOrder->status) };
                                $payBadge = match($relatedOrder->status) { 'menunggu_pembayaran' => 'bg-amber-50 text-amber-600 border-amber-200', 'sudah_dibayar', 'lunas' => 'bg-emerald-50 text-emerald-600 border-emerald-200', default => 'bg-slate-50 text-slate-500 border-slate-200' };
                                
                                if ($payLabel === 'Belum Bayar') {
                                    $payFilterKey = 'belum_bayar';
                                } elseif ($payLabel === 'Lunas') {
                                    $payFilterKey = 'lunas';
                                }
                            }
                        } else {
                            // Jika appointment aktif tapi belum ada order, secara logika belum bayar. 
                            // Untuk amannya set filter ke semua atau belum_bayar jika bukan batal/selesai
                            if (!in_array($aptStatus, ['batal', 'dibatalkan', 'selesai', 'tidak_hadir'])) {
                                $payFilterKey = 'belum_bayar';
                            }
                        }

                        $searchableText = strtolower(addslashes(($apt->dokter?->name ?? '') . ' ' . $apt->jenis . ' ' . collect($apt->items)->map(function($i) { return $i->treatment?->nama ?? $i->bundle?->nama ?? $i->consultation?->nama ?? ''; })->implode(' ')));
                    @endphp

                    @php
                        if ($apt->dokter_id) {
                            $aptJadwalData = $jadwals->where('user_id', $apt->dokter_id)->values();
                        } else {
                            $aptJadwalData = $jadwals->groupBy('hari')->map(function($dayJadwals, $hari) {
                                return [
                                    'hari' => $hari,
                                    'jam_mulai' => $dayJadwals->min('jam_mulai'),
                                    'jam_selesai' => $dayJadwals->max('jam_selesai'),
                                    'durasi_slot_menit' => 30
                                ];
                            })->values();
                            if ($aptJadwalData->isEmpty()) {
                                $aptJadwalData = collect(['senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu', 'minggu'])->map(function($hari) {
                                    return ['hari' => $hari, 'jam_mulai' => '09:00', 'jam_selesai' => '20:00', 'durasi_slot_menit' => 30];
                                });
                            }
                        }
                    @endphp

                    <div x-show="(filterStatus === 'semua' || (filterStatus === 'aktif' && ('{{ strtolower($apt->status) }}' === 'diajukan' || '{{ strtolower($apt->status) }}' === 'disetujui')) || filterStatus === '{{ strtolower($apt->status) }}') && (filterJenis === 'semua' || filterJenis === '{{ strtolower($apt->jenis) }}') && (filterPayment === 'semua' || filterPayment === '{{ $payFilterKey }}') && isDateInRange('{{ $apt->tanggal_jam->format('Y-m-d') }}') && (searchQuery === '' || '{{ $searchableText }}'.includes(searchQuery.toLowerCase()))"
                         :style="`order: ${sortOrder === 'terdekat' ? {{ $apt->tanggal_jam->timestamp }} : -{{ $apt->tanggal_jam->timestamp }}}`"
                         x-data="{ 
                        openCancelModal: false, 
                        openRescheduleModal: false, 
                        openRescheduleConfirmModal: false, 
                        showRincian: false, 
                        showNotaTreatment: false,
                        newTanggal: '', 
                        newJam: '',
                        displayDate: '',
                        jadwalData: @js($aptJadwalData),
                        
                        get availableDays() {
                            const docJadwals = this.jadwalData;
                            const activeDays = docJadwals.map(j => j.hari.toLowerCase());
                            
                            const days = [];
                            const dayNames = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
                            const today = new Date();
                            
                            for (let i = 0; i < 30; i++) {
                                const d = new Date(today);
                                d.setDate(today.getDate() + i);
                                const dayName = dayNames[d.getDay()];
                                
                                if (activeDays.includes(dayName.toLowerCase())) {
                                    days.push({
                                        date: d.toISOString().split('T')[0],
                                        dayName: dayName.substring(0, 3),
                                        dayNum: d.getDate(),
                                        monthShort: d.toLocaleString('id-ID', {month:'short'}),
                                        displayFull: `${dayName}, ${d.getDate()} ${d.toLocaleString('id-ID', {month:'short'})} ${d.getFullYear()}`
                                    });
                                }
                            }
                            return days;
                        },

                        get availableTimes() {
                            if (!this.newTanggal) return [];
                            
                            const selectedDate = new Date(this.newTanggal);
                            const dayNames = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
                            const dayName = dayNames[selectedDate.getDay()].toLowerCase();
                            
                            const docJadwals = this.jadwalData.filter(j => j.hari.toLowerCase() === dayName);
                            if (docJadwals.length === 0) return [];
                            
                            const jadwal = docJadwals[0];
                            const startTime = jadwal.jam_mulai;
                            const endTime = jadwal.jam_selesai;
                            const duration = jadwal.durasi_slot_menit || 30;
                            
                            const times = [];
                            let current = new Date(`${this.newTanggal}T${startTime}`);
                            const end = new Date(`${this.newTanggal}T${endTime}`);
                            const now = new Date();
                            
                            while (current < end) {
                                const nextSlot = new Date(current);
                                nextSlot.setMinutes(current.getMinutes() + duration);
                                
                                const startH = current.getHours().toString().padStart(2, '0');
                                const startM = current.getMinutes().toString().padStart(2, '0');
                                const endH = nextSlot.getHours().toString().padStart(2, '0');
                                const endM = nextSlot.getMinutes().toString().padStart(2, '0');
                                
                                const isPast = current < now;
                                
                                times.push({
                                    value: `${startH}.${startM} - ${endH}.${endM}`,
                                    isDisabled: isPast
                                });
                                
                                current = nextSlot;
                            }
                            return times;
                        }
                    }" @click="if(!$event.target.closest('button') && !$event.target.closest('form')) { @if($relatedOrder && in_array($relatedOrder->status, ['sudah_dibayar', 'lunas'])) showNotaTreatment = true; @endif }" x-init="$nextTick(() => { if ($root.highlightId == '{{ $apt->appointment_id }}') { $el.scrollIntoView({ behavior: 'smooth', block: 'center' }); } })" :class="$root.highlightId == '{{ $apt->appointment_id }}' ? 'ring-2 ring-[#CFAF53] shadow-lg shadow-[#CFAF53]/20 bg-[#CFAF53]/5' : 'bg-slate-50'" class="rounded-2xl border border-slate-200 shadow-sm p-5 flex flex-col md:flex-row gap-5 transition-all duration-1000 {{ $relatedOrder && in_array($relatedOrder->status, ['sudah_dibayar', 'lunas']) ? 'cursor-pointer hover:border-[#CFAF53]/50' : '' }}" data-apt-id="{{ $apt->appointment_id }}">

                        {{-- Kotak Tanggal --}}
                        <div class="shrink-0">
                            <div class="flex flex-col items-center justify-center bg-[#CFAF53]/10 text-[#8C7A63] w-[80px] h-[84px] rounded-xl border border-[#CFAF53]/20">
                                <span class="text-[10px] font-extrabold uppercase tracking-widest">{{ $apt->tanggal_jam->format('M') }}</span>
                                <span class="text-2xl font-black leading-none mt-0.5">{{ $apt->tanggal_jam->format('d') }}</span>
                                <span class="text-[10px] font-bold mt-0.5 opacity-70">{{ $apt->tanggal_jam->format('Y') }}</span>
                            </div>
                        </div>

                        {{-- Info --}}
                        <div class="flex-1 min-w-0">
                            <div class="flex flex-wrap items-center gap-2 mb-1">
                                <h3 class="font-extrabold text-[#1A1A1A] text-lg">{{ ucfirst($apt->jenis) }}</h3>
                                <span class="text-xs text-slate-400 flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    {{ $apt->tanggal_jam->format('H:i') }}
                                    @if($totalDurasi > 0) • {{ $totalDurasi }} Menit @endif
                                </span>
                            </div>

                            <div class="flex items-center gap-3 mt-2">
                                <div class="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center shrink-0">
                                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                </div>
                                <div>
                                    <p class="text-sm font-bold text-[#1A1A1A]">{{ $apt->dokter->name ?? 'Belum Ditentukan' }}</p>
                                    <p class="text-[11px] text-slate-400">Dokter</p>
                                </div>
                            </div>

                            {{-- Biaya & Keterangan --}}
                            @if($totalHarga > 0)
                                <div class="mt-3">
                                    <p class="text-sm font-extrabold text-[#1A1A1A]">Rp {{ number_format($totalHarga, 0, ',', '.') }}</p>
                                    <p class="text-[11px] text-slate-400 mt-0.5">(Bayar di klinik setelah sesi ini selesai)</p>
                                </div>
                            @endif

                            @if($aptStatus == 'batal')
                                @php
                                    $canceler = strtolower($apt->canceled_by ?? 'sistem');
                                    $cancelerLabel = match($canceler) {
                                        'pasien' => 'Anda',
                                        'admin', 'klinik', 'super_admin' => 'Pihak Klinik',
                                        default => ucfirst($canceler)
                                    };
                                @endphp
                                <div class="mt-3 text-xs text-rose-600 bg-rose-50 px-3 py-2 rounded-lg border border-rose-100">
                                    <div class="mb-1 text-[11px] opacity-80">
                                        Dibatalkan oleh <span class="font-bold">{{ $cancelerLabel }}</span> pada {{ $apt->updated_at->format('d M Y, H:i') }} WIB
                                    </div>
                                    <span class="font-bold">Alasan:</span> "{{ $apt->cancel_reason }}"
                                </div>
                            @endif

                            {{-- Rincian Accordion --}}
                            @if(count($apt->items) > 0)
                                <div class="mt-3 pt-3 border-t border-[#E8E0D8]/40">
                                    <button @click="showRincian = !showRincian" class="flex items-center gap-1.5 text-sm font-bold text-[#CFAF53] hover:text-[#8C7A63] transition-colors">
                                        <svg :class="showRincian ? 'rotate-180' : ''" class="w-4 h-4 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                        <span x-text="showRincian ? 'Tutup Rincian' : 'Lihat Rincian ({{ count($apt->items) }} Item)'"></span>
                                    </button>
                                    <div x-show="showRincian" x-collapse class="mt-3 space-y-2">
                                        @foreach($apt->items as $item)
                                            @if($item->item_type == 'treatment' && $item->treatment)
                                                <div class="flex justify-between text-sm"><span class="text-slate-600">{{ $item->treatment->nama }}</span><span class="font-medium text-[#1A1A1A]">Rp {{ number_format($item->treatment->harga, 0, ',', '.') }}</span></div>
                                            @elseif($item->item_type == 'bundle' && $item->bundle)
                                                <div class="flex justify-between text-sm"><span class="text-slate-600">{{ $item->bundle->nama }}</span><span class="font-medium text-[#1A1A1A]">Rp {{ number_format($item->bundle->harga, 0, ',', '.') }}</span></div>
                                            @elseif($item->item_type == 'consultation' && $item->consultation)
                                                <div class="flex justify-between text-sm"><span class="text-slate-600">{{ $item->consultation->nama }}</span><span class="font-medium text-[#1A1A1A]">Rp {{ number_format($item->consultation->harga, 0, ',', '.') }}</span></div>
                                            @endif
                                        @endforeach
                                        <div class="flex justify-between mt-2 pt-2 border-t border-[#E8E0D8]/40 font-bold text-sm">
                                            <span>Subtotal</span><span>Rp {{ number_format($totalHarga, 0, ',', '.') }}</span>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>

                        {{-- Right: Status + Actions --}}
                        <div class="flex flex-col items-start md:items-end justify-between shrink-0 gap-3">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="px-4 py-1.5 rounded-full text-xs font-bold border {{ $statusBadge }}">{{ $statusLabel }}</span>
                                @if($payLabel && (!$relatedOrder || $relatedOrder->status !== 'menunggu_pembayaran'))
                                    <span class="px-4 py-1.5 rounded-full text-xs font-bold border {{ $payBadge }}">{{ $payLabel }}</span>
                                @endif
                                {{-- Countdown Timer: inline next to badge --}}
                                @if($relatedOrder && $relatedOrder->status === 'menunggu_pembayaran' && $relatedOrder->expired_at)
                                    @if(\Carbon\Carbon::parse($relatedOrder->expired_at)->isFuture())
                                    <div x-data="countdownTimer('{{ $relatedOrder->expired_at }}')" class="flex items-center gap-1.5 bg-rose-50 border border-rose-200 px-2.5 py-1 rounded-full">
                                        <svg class="w-3.5 h-3.5 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        <div class="flex items-center text-rose-600 font-bold text-[13px] tabular-nums tracking-tighter">
                                            <template x-if="showHours">
                                                <div class="flex items-center">
                                                    <div class="h-[1em] overflow-hidden leading-none relative">
                                                        <div class="transition-transform duration-300 flex flex-col" :style="`transform: translateY(-${h1 * 10}%)`"><span>0</span><span>1</span><span>2</span><span>3</span><span>4</span><span>5</span><span>6</span><span>7</span><span>8</span><span>9</span></div>
                                                    </div>
                                                    <div class="h-[1em] overflow-hidden leading-none relative">
                                                        <div class="transition-transform duration-300 flex flex-col" :style="`transform: translateY(-${h2 * 10}%)`"><span>0</span><span>1</span><span>2</span><span>3</span><span>4</span><span>5</span><span>6</span><span>7</span><span>8</span><span>9</span></div>
                                                    </div>
                                                    <span class="mx-0.5 text-rose-400 font-black animate-pulse">:</span>
                                                </div>
                                            </template>
                                            <div class="h-[1em] overflow-hidden leading-none relative">
                                                <div class="transition-transform duration-300 flex flex-col" :style="`transform: translateY(-${m1 * 10}%)`"><span>0</span><span>1</span><span>2</span><span>3</span><span>4</span><span>5</span><span>6</span><span>7</span><span>8</span><span>9</span></div>
                                            </div>
                                            <div class="h-[1em] overflow-hidden leading-none relative">
                                                <div class="transition-transform duration-300 flex flex-col" :style="`transform: translateY(-${m2 * 10}%)`"><span>0</span><span>1</span><span>2</span><span>3</span><span>4</span><span>5</span><span>6</span><span>7</span><span>8</span><span>9</span></div>
                                            </div>
                                            <span class="mx-0.5 text-rose-400 font-black animate-pulse">:</span>
                                            <div class="h-[1em] overflow-hidden leading-none relative">
                                                <div class="transition-transform duration-300 flex flex-col" :style="`transform: translateY(-${s1 * 10}%)`"><span>0</span><span>1</span><span>2</span><span>3</span><span>4</span><span>5</span><span>6</span><span>7</span><span>8</span><span>9</span></div>
                                            </div>
                                            <div class="h-[1em] overflow-hidden leading-none relative">
                                                <div class="transition-transform duration-300 flex flex-col" :style="`transform: translateY(-${s2 * 10}%)`"><span>0</span><span>1</span><span>2</span><span>3</span><span>4</span><span>5</span><span>6</span><span>7</span><span>8</span><span>9</span></div>
                                            </div>
                                        </div>
                                    </div>
                                    @else
                                    <span class="text-[11px] font-bold text-rose-500 bg-rose-50 border border-rose-200 px-2.5 py-1 rounded-full">Kedaluwarsa</span>
                                    @endif
                                @endif
                            </div>
                            @if($totalHarga > 0)
                                <div class="text-left md:text-right">
                                    <p class="text-[10px] text-slate-400">Total Biaya</p>
                                    <p class="text-lg font-extrabold text-[#1A1A1A]">Rp {{ number_format($totalHarga, 0, ',', '.') }}</p>
                                </div>
                            @endif
                            @php
                                $canModify = false;
                                $isPendingOrApproved = in_array($aptStatus, ['diajukan', 'disetujui']);
                                if ($isPendingOrApproved) {
                                    $batasWaktu = \Carbon\Carbon::parse($apt->tanggal_jam)->subHours(3);
                                    $canModify = now()->lessThan($batasWaktu);
                                }
                            @endphp

                            @if($isPendingOrApproved)
                                @if($canModify)
                                    <div class="flex flex-col sm:flex-row gap-2 mt-2 w-full md:w-auto">
                                        <button @click="openCancelModal = true" class="w-full sm:w-auto text-sm font-bold text-slate-500 hover:text-rose-600 border border-slate-200 hover:border-rose-300 px-6 py-2.5 rounded-full transition-colors">Batalkan Janji</button>
                                        <button @click="openRescheduleModal = true" class="w-full sm:w-auto text-sm font-bold text-white bg-[#CFAF53] hover:bg-[#b09444] px-6 py-2.5 rounded-full shadow-sm transition-colors shadow-[#CFAF53]/20">Ubah Jadwal</button>
                                    </div>
                                @else
                                    <p class="text-[10px] text-slate-500 italic md:text-right mt-2 leading-tight">Untuk pembatalan atau perubahan jadwal saat ini,<br>silakan hubungi admin via WhatsApp.</p>
                                @endif
                            @elseif($relatedOrder && $relatedOrder->status === 'menunggu_pembayaran' && $relatedOrder->snap_token)
                                <div class="flex flex-col sm:flex-row gap-2 mt-2 w-full md:w-auto">
                                    <button @click="window.snap.pay('{{ $relatedOrder->snap_token }}', { onSuccess: () => window.location.reload(), onPending: () => window.location.reload() })" class="w-full sm:w-auto text-sm font-bold text-white bg-[#CFAF53] hover:bg-[#b09444] px-6 py-2.5 rounded-full shadow-sm transition-colors shadow-[#CFAF53]/20">
                                        Bayar Sekarang
                                    </button>
                                </div>
                            @endif

                            {{-- Modal Nota Treatment --}}
                            @if($relatedOrder && in_array($relatedOrder->status, ['sudah_dibayar', 'lunas']))
                                <div x-show="showNotaTreatment" style="display:none" class="fixed inset-0 z-[999] flex items-center justify-center bg-black/50 backdrop-blur-sm p-4" x-transition>
                                    <div class="bg-white w-full max-w-3xl max-h-[90vh] rounded-3xl shadow-2xl flex flex-col overflow-hidden relative" @click.away="showNotaTreatment = false"
                                         x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-8 scale-95" x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                                         x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 scale-100" x-transition:leave-end="opacity-0 translate-y-8 scale-95">

                                        {{-- Modal Header --}}
                                        <div class="px-6 py-5 border-b border-[#E8E0D8]/60 flex items-center justify-between shrink-0 bg-[#FAF8F5]">
                                            <div class="flex items-center gap-3">
                                                <div class="w-10 h-10 rounded-full bg-[#CFAF53] flex items-center justify-center shrink-0 shadow-inner">
                                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                                </div>
                                                <div>
                                                    <h3 class="text-lg font-black text-[#1A1A1A] leading-tight">Nota Treatment</h3>
                                                    <p class="text-xs text-[#8C7A63] font-bold tracking-widest uppercase">Aishi Beauty</p>
                                                </div>
                                            </div>
                                            <div class="flex items-center gap-4">
                                                <span class="px-3 py-1 text-[10px] font-black rounded uppercase tracking-wider bg-emerald-100 text-emerald-700">LUNAS</span>
                                                <button @click="showNotaTreatment = false" class="text-slate-400 hover:text-rose-500 bg-white hover:bg-rose-50 p-2 rounded-full transition-colors shadow-sm border border-[#E8E0D8]">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                                </button>
                                            </div>
                                        </div>

                                        {{-- Modal Body --}}
                                        <div class="flex-1 overflow-y-auto p-6 sm:p-8 custom-scrollbar">
                                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-8 mb-8">
                                                {{-- Detail Pelanggan --}}
                                                <div>
                                                    <h4 class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-3">Detail Pelanggan</h4>
                                                    <div class="space-y-1">
                                                        <p class="text-sm font-extrabold text-[#1A1A1A]">{{ auth()->user()->name }}</p>
                                                        <p class="text-xs text-slate-500">{{ auth()->user()->email }}</p>
                                                        <p class="text-xs text-slate-500">{{ auth()->user()->pasienProfile?->telepon ?? '-' }}</p>
                                                    </div>
                                                    <div class="mt-5 border-l-2 border-[#CFAF53] pl-3">
                                                        <p class="text-xs font-bold text-[#1A1A1A]">Aishi Beauty Klinik</p>
                                                        <p class="text-[11px] text-slate-500 leading-relaxed mt-1 max-w-[200px]">
                                                            Jl. Nusantara Raya No.99b, Beji, Kec. Beji, Kota Depok, Jawa Barat 16421
                                                        </p>
                                                    </div>
                                                </div>
                                                {{-- Detail Transaksi --}}
                                                <div class="sm:text-right">
                                                    <h4 class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-3">Detail Transaksi</h4>
                                                    <div class="space-y-3">
                                                        <div>
                                                            <p class="text-[10px] text-slate-400">Kode Pesanan</p>
                                                            <p class="text-sm font-bold text-[#1A1A1A]">{{ $relatedOrder->kode_order }}</p>
                                                        </div>
                                                        <div>
                                                            <p class="text-[10px] text-slate-400">Tanggal Transaksi</p>
                                                            <p class="text-sm font-bold text-[#1A1A1A]">{{ $relatedOrder->paid_at ? $relatedOrder->paid_at->translatedFormat('d M Y') : $relatedOrder->created_at->translatedFormat('d M Y') }}</p>
                                                        </div>
                                                        <div>
                                                            <p class="text-[10px] text-slate-400">Metode Pembayaran</p>
                                                            @php
                                                                $channelDisplay = $relatedOrder->payment_channel ?? $relatedOrder->payment_method ?? '-';
                                                                $channelLabels = [
                                                                    'qris' => 'QRIS',
                                                                    'bca' => 'Bank BCA (VA)',
                                                                    'bni' => 'Bank BNI (VA)',
                                                                    'bri' => 'Bank BRI (VA)',
                                                                    'mandiri' => 'Mandiri Bill',
                                                                    'mandiri_bill' => 'Mandiri Bill',
                                                                    'permata' => 'Bank Permata (VA)',
                                                                    'cimb' => 'Bank CIMB (VA)',
                                                                    'bsi' => 'Bank BSI (VA)',
                                                                    'gopay' => 'GoPay',
                                                                    'shopeepay' => 'ShopeePay',
                                                                    'dana' => 'DANA',
                                                                    'alfamart' => 'Alfamart',
                                                                    'indomaret' => 'Indomaret',
                                                                    'bank_transfer' => 'Transfer Bank',
                                                                    'credit_card' => 'Kartu Kredit',
                                                                    'akulaku' => 'Akulaku',
                                                                    'kredivo' => 'Kredivo',
                                                                    'web_redirect' => 'Midtrans',
                                                                    'midtrans_snap' => 'Midtrans',
                                                                ];
                                                                $displayLabel = $channelLabels[strtolower($channelDisplay)] ?? strtoupper($channelDisplay);
                                                            @endphp
                                                            <p class="text-sm font-bold text-[#1A1A1A]">{{ $displayLabel }}</p>
                                                        </div>
                                                        <div>
                                                            <p class="text-[10px] text-slate-400">Jadwal Treatment</p>
                                                            <p class="text-sm font-bold text-[#1A1A1A]">{{ $apt->tanggal_jam->translatedFormat('d M Y, H:i') }} WIB</p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            {{-- Item Table --}}
                                            <div class="rounded-2xl border border-[#E8E0D8]/60 overflow-hidden mb-6">
                                                <table class="w-full text-left border-collapse">
                                                    <thead class="bg-[#FAF8F5] border-b border-[#E8E0D8]/60">
                                                        <tr>
                                                            <th class="py-3 px-4 text-[10px] font-bold text-slate-500 uppercase tracking-widest">Treatment</th>
                                                            <th class="py-3 px-4 text-[10px] font-bold text-slate-500 uppercase tracking-widest text-center">Qty</th>
                                                            <th class="py-3 px-4 text-[10px] font-bold text-slate-500 uppercase tracking-widest text-right">Harga</th>
                                                            <th class="py-3 px-4 text-[10px] font-bold text-slate-500 uppercase tracking-widest text-right">Subtotal</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody class="divide-y divide-[#E8E0D8]/40">
                                                        @foreach($relatedOrder->items as $notaItem)
                                                            @php
                                                                $notaName = $notaItem->treatment?->nama ?? $notaItem->bundle?->nama ?? $notaItem->consultation?->nama ?? $notaItem->description ?? 'Item';
                                                                $notaPrice = $notaItem->harga_satuan;
                                                                $notaQty = $notaItem->qty;
                                                            @endphp
                                                            <tr class="hover:bg-slate-50/50 transition-colors">
                                                                <td class="py-4 px-4">
                                                                    <p class="text-sm font-bold text-[#1A1A1A]">{{ $notaName }}</p>
                                                                </td>
                                                                <td class="py-4 px-4 text-center">
                                                                    <span class="text-xs font-bold text-slate-600 bg-slate-100 px-2 py-1 rounded-md">{{ $notaQty }}</span>
                                                                </td>
                                                                <td class="py-4 px-4 text-right">
                                                                    <p class="text-xs font-medium text-slate-500">Rp {{ number_format($notaPrice, 0, ',', '.') }}</p>
                                                                </td>
                                                                <td class="py-4 px-4 text-right">
                                                                    <p class="text-sm font-bold text-[#1A1A1A]">Rp {{ number_format($notaPrice * $notaQty, 0, ',', '.') }}</p>
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>

                                            {{-- Payment Summary --}}
                                            <div class="flex justify-end">
                                                <div class="w-full sm:w-72 space-y-3">
                                                    <div class="flex justify-between items-center text-sm">
                                                        <span class="text-slate-500">Subtotal</span>
                                                        <span class="font-medium text-[#1A1A1A]">Rp {{ number_format($relatedOrder->subtotal ?? $relatedOrder->grand_total, 0, ',', '.') }}</span>
                                                    </div>
                                                    @if(($relatedOrder->diskon_total ?? 0) > 0)
                                                        <div class="flex justify-between items-center text-sm">
                                                            <span class="text-slate-500">Diskon</span>
                                                            <span class="font-medium text-rose-500">-Rp {{ number_format($relatedOrder->diskon_total, 0, ',', '.') }}</span>
                                                        </div>
                                                    @endif
                                                    <div class="pt-3 border-t border-dashed border-[#E8E0D8] flex justify-between items-center">
                                                        <span class="font-bold text-[#1A1A1A]">Total Pembayaran</span>
                                                        <span class="text-lg font-black text-[#CFAF53]">Rp {{ number_format($relatedOrder->grand_total, 0, ',', '.') }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        {{-- Modal Footer --}}
                                        <div class="px-6 py-4 border-t border-[#E8E0D8]/60 bg-[#FAF8F5] flex items-center justify-end shrink-0 gap-3">
                                            <button @click="sendEmailInvoice('{{ $relatedOrder->kode_order }}', $event)" class="w-full sm:w-auto px-6 py-2.5 rounded-full bg-white border border-[#CFAF53] text-[#CFAF53] hover:bg-[#FAF8F5] text-sm font-bold transition-colors flex items-center justify-center gap-2">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                                Kirim Email
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>

                        {{-- Modal Batal --}}
                        <div x-show="openCancelModal" style="display:none" class="fixed inset-0 z-[999] flex items-center justify-center bg-black/50 backdrop-blur-sm p-4" x-transition>
                            <div class="bg-white w-full max-w-sm rounded-2xl p-6 shadow-2xl" @click.away="openCancelModal = false">
                                <h3 class="text-lg font-bold text-[#1A1A1A]">Batalkan Janji?</h3>
                                <p class="text-sm text-slate-500 mt-2">Tindakan ini tidak dapat dibatalkan.</p>
                                <form action="{{ route('appointments.cancel', $apt->appointment_id) }}" method="POST" class="mt-4">
                                    @csrf @method('PATCH')
                                    <textarea name="cancel_reason" rows="2" required class="w-full rounded-xl border-slate-200 text-sm mb-4" placeholder="Alasan pembatalan..."></textarea>
                                    <div class="flex gap-3">
                                        <button type="button" @click="openCancelModal = false" class="flex-1 py-2.5 rounded-xl border border-slate-200 text-sm font-bold text-slate-700">Batal</button>
                                        <button type="submit" class="flex-1 py-2.5 rounded-xl bg-rose-600 text-white text-sm font-bold">Ya, Batalkan</button>
                                    </div>
                                </form>
                            </div>
                        </div>

                        {{-- Modal Reschedule 1 (Pilih Tanggal & Jam) --}}
                        <div x-show="openRescheduleModal" style="display:none" class="fixed inset-0 z-[999] flex items-center justify-center bg-black/50 backdrop-blur-sm p-4" x-transition>
                            <div class="bg-white w-full max-w-lg rounded-2xl p-6 shadow-2xl max-h-[90vh] overflow-y-auto" @click.away="openRescheduleModal = false">
                                <h3 class="text-xl font-bold text-[#1A1A1A]">Ubah Jadwal</h3>
                                <p class="text-sm text-slate-500 mt-1 mb-6">Pilih jadwal baru sesuai hari praktik dokter Anda.</p>
                                
                                {{-- Pilih Tanggal --}}
                                <div class="mb-6">
                                    <div class="text-sm font-bold text-[#1A1A1A] mb-3">Pilih Tanggal Baru</div>
                                    
                                    <div x-show="availableDays.length === 0" class="p-4 bg-gray-50 border border-gray-200 rounded-xl text-center">
                                        <p class="text-sm text-gray-500">Dokter ini belum memiliki jadwal praktik aktif.</p>
                                    </div>

                                    <div class="flex gap-3 overflow-x-auto pb-2 scrollbar-hide snap-x">
                                        <template x-for="day in availableDays" :key="day.date">
                                            <label class="snap-start shrink-0 cursor-pointer">
                                                <input type="radio" name="reschedule_tanggal_{{ $apt->appointment_id }}" :value="day.date" x-model="newTanggal" @change="displayDate = day.displayFull; newJam = ''" class="peer sr-only">
                                                <div class="flex flex-col items-center justify-center w-[70px] h-[105px] rounded-2xl border transition-all duration-200"
                                                    :class="newTanggal === day.date ? 'border-[#3B3529] bg-[#3B3529] text-white shadow-md' : 'border-gray-200 bg-white hover:border-[#8C7A63] text-gray-600'">
                                                    <span class="text-xs uppercase mb-1 font-medium tracking-wide" :class="newTanggal === day.date ? 'text-white/80' : 'text-gray-400'" x-text="day.dayName"></span>
                                                    <span class="text-2xl font-bold leading-none" :class="newTanggal === day.date ? 'text-white' : 'text-[#1A1A1A]'" x-text="day.dayNum"></span>
                                                    <span class="text-[11px] mt-1 font-medium" :class="newTanggal === day.date ? 'text-white/80' : 'text-gray-400'" x-text="day.monthShort"></span>
                                                </div>
                                            </label>
                                        </template>
                                    </div>
                                </div>

                                {{-- Pilih Jam --}}
                                <div x-show="newTanggal !== ''" x-cloak>
                                    <div class="text-sm font-bold text-[#1A1A1A] mb-3">Pilih Jam Baru (WIB)</div>
                                    
                                    <div x-show="availableTimes.length === 0" class="p-4 bg-gray-50 border border-gray-200 rounded-xl text-center">
                                        <p class="text-sm text-gray-500">Tidak ada jadwal tersedia pada tanggal ini.</p>
                                    </div>

                                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                                        <template x-for="timeSlot in availableTimes" :key="timeSlot.value">
                                            <label :class="timeSlot.isDisabled ? 'cursor-not-allowed opacity-50' : 'cursor-pointer'">
                                                <input type="radio" name="reschedule_jam_{{ $apt->appointment_id }}" :value="timeSlot.value" x-model="newJam" class="peer sr-only" :disabled="timeSlot.isDisabled">
                                                <div class="py-3.5 px-4 rounded-xl text-sm font-medium border transition-all text-center"
                                                    :class="timeSlot.isDisabled ? 'bg-gray-100 border-gray-200 text-gray-400' : (newJam === timeSlot.value ? 'border-[#3B3529] bg-[#FAF8F5] text-[#1A1A1A] font-bold shadow-sm' : 'border-gray-200 bg-white text-gray-500 hover:border-[#8C7A63]')">
                                                    <span x-text="timeSlot.value"></span>
                                                </div>
                                            </label>
                                        </template>
                                    </div>
                                </div>

                                <div class="flex gap-3 mt-6">
                                    <button type="button" @click="openRescheduleModal = false; newTanggal = ''; newJam = ''" class="flex-1 py-2.5 rounded-xl border border-slate-200 text-sm font-bold text-slate-700 hover:bg-slate-50">Batal</button>
                                    <button type="button" @click="if(newTanggal && newJam) { openRescheduleModal = false; openRescheduleConfirmModal = true; } else { alert('Mohon pilih tanggal dan jam baru.'); }" class="flex-1 py-2.5 rounded-xl bg-[#CFAF53] hover:bg-[#b09444] text-white text-sm font-bold transition-colors">Lanjutkan</button>
                                </div>
                            </div>
                        </div>

                        {{-- Modal Reschedule 2 (Konfirmasi) --}}
                        <div x-show="openRescheduleConfirmModal" style="display:none" class="fixed inset-0 z-[999] flex items-center justify-center bg-black/50 backdrop-blur-sm p-4" x-transition>
                            <div class="bg-white w-full max-w-sm rounded-2xl p-6 shadow-2xl" @click.away="openRescheduleConfirmModal = false">
                                <h3 class="text-lg font-bold text-[#1A1A1A]">Konfirmasi Perubahan</h3>
                                <p class="text-sm text-slate-500 mt-2">Anda akan mengubah jadwal janji temu ini.</p>
                                
                                <div class="mt-4 bg-slate-50 border border-slate-200 rounded-xl p-4 space-y-3">
                                    <div>
                                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Jadwal Lama</p>
                                        <p class="text-sm font-bold text-[#1A1A1A]">{{ $apt->tanggal_jam->format('d M Y, H:i') }}</p>
                                    </div>
                                    <div class="flex justify-center text-slate-300">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path></svg>
                                    </div>
                                    <div>
                                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Jadwal Baru</p>
                                        <p class="text-sm font-bold text-[#CFAF53]"><span x-text="displayDate"></span> • Jam <span x-text="newJam"></span> WIB</p>
                                    </div>
                                </div>

                                <div class="mt-4 p-3 bg-amber-50 border border-amber-200 rounded-xl flex gap-3 items-start">
                                    <svg class="w-5 h-5 text-amber-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    <p class="text-[10px] text-amber-700 font-medium">Perubahan jadwal akan mengubah status menjadi <span class="font-bold">Diajukan</span> dan memerlukan konfirmasi ulang dari admin.</p>
                                </div>

                                <form action="{{ route('appointments.reschedule', $apt->appointment_id) }}" method="POST" class="mt-6">
                                    @csrf @method('PATCH')
                                    <input type="hidden" name="new_tanggal" :value="newTanggal">
                                    <input type="hidden" name="new_jam" :value="newJam">
                                    <div class="flex gap-3">
                                        <button type="button" @click="openRescheduleConfirmModal = false; openRescheduleModal = true;" class="flex-1 py-2.5 rounded-xl border border-slate-200 text-sm font-bold text-slate-700 hover:bg-slate-50">Kembali</button>
                                        <button type="submit" class="flex-1 py-2.5 rounded-xl bg-[#CFAF53] hover:bg-[#b09444] text-white text-sm font-bold transition-colors">Ya, Ubah Jadwal</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    {{-- CALENDAR VIEW --}}
    <div x-show="viewMode === 'calendar'" x-transition.opacity style="display:none">
        <div x-data="appCalendar()">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <span x-text="monthNames[month]" class="text-lg font-bold text-[#1A1A1A]"></span>
                    <span x-text="year" class="ml-1 text-lg text-slate-400"></span>
                </div>
                <div class="flex items-center rounded-lg border border-[#E8E0D8]/60 overflow-hidden">
                    <button @click="prevMonth()" class="px-3 py-1 hover:bg-[#FAF8F5]"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg></button>
                    <button @click="currentMonth()" class="px-3 py-1 text-xs font-bold text-[#CFAF53]">Hari Ini</button>
                    <button @click="nextMonth()" class="px-3 py-1 hover:bg-[#FAF8F5]"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg></button>
                </div>
            </div>
            <div class="grid grid-cols-7 mb-2">
                <template x-for="(day, i) in dayNames" :key="i"><div class="text-center text-[10px] font-bold text-slate-400 uppercase py-1" x-text="day"></div></template>
            </div>
            <div class="grid grid-cols-7 gap-1">
                <template x-for="b in blankdays" :key="b"><div class="h-14 md:h-16"></div></template>
                <template x-for="(date, i) in no_of_days" :key="i">
                    <div @click="selectDate(date)" class="h-14 md:h-16 border rounded-lg relative cursor-pointer flex flex-col items-center justify-start pt-2 transition"
                        :class="{ 'bg-[#CFAF53]/10 border-[#CFAF53] text-[#8C7A63]': isToday(date), 'border-transparent hover:border-[#E8E0D8]': !isToday(date) && selectedDate != date, 'ring-2 ring-[#CFAF53] border-[#CFAF53]': selectedDate == date }">
                        <span class="text-sm font-bold" x-text="date"></span>
                        <template x-if="hasEvent(date)"><div class="flex gap-0.5 mt-1"><template x-for="evt in getEvents(date)"><div class="w-1.5 h-1.5 rounded-full" :class="{ 'bg-amber-400': evt.status==='diajukan', 'bg-blue-400': evt.status==='disetujui', 'bg-emerald-400': evt.status==='selesai', 'bg-rose-400': evt.status==='batal' }"></div></template></div></template>
                    </div>
                </template>
            </div>
            <div class="mt-6 pt-6 border-t border-[#E8E0D8]/40">
                <h4 class="text-xs font-bold text-slate-400 uppercase mb-3">Jadwal: <span x-text="selectedDate + ' ' + monthNames[month]" class="text-[#1A1A1A]"></span></h4>
                <div x-show="selectedEvents.length === 0" class="text-center py-4 bg-[#FAF8F5] rounded-xl border border-dashed border-[#E8E0D8]"><p class="text-sm text-slate-400">Tidak ada jadwal.</p></div>
                <div class="space-y-3">
                    <template x-for="event in selectedEvents">
                        <div class="p-4 bg-slate-50 border border-slate-200 shadow-sm rounded-xl">
                            <div class="flex items-start justify-between gap-3">
                                <div class="flex items-center gap-3">
                                    <div class="bg-[#CFAF53]/10 text-[#8C7A63] px-3 py-1.5 rounded-lg text-center">
                                        <span class="text-sm font-bold block" x-text="event.time"></span>
                                        <span class="text-[9px] font-bold uppercase tracking-wider opacity-70">WIB</span>
                                    </div>
                                    <div>
                                        <div class="font-bold text-[#1A1A1A]" x-text="event.title"></div>
                                        <div class="text-xs text-slate-400 mt-0.5">Dokter: <span x-text="event.doctor" class="text-slate-600"></span></div>
                                    </div>
                                </div>
                                <span class="text-[10px] font-bold px-2 py-1 rounded-md uppercase border" :class="{ 'bg-amber-50 text-amber-700 border-amber-200': event.status==='diajukan', 'bg-blue-50 text-blue-700 border-blue-200': event.status==='disetujui', 'bg-emerald-50 text-emerald-700 border-emerald-200': event.status==='selesai', 'bg-rose-50 text-rose-700 border-rose-200': event.status==='batal' }" x-text="event.status"></span>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    async function sendEmailInvoice(orderKode, btnEvent) {
        const btn = btnEvent.currentTarget;
        const originalText = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = `<svg class="animate-spin h-4 w-4 text-[#8B6F1B]" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Mengirim...`;
        
        try {
            const response = await fetch('/orders/' + orderKode + '/invoice/email', {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            
            let data;
            try {
                data = await response.json();
            } catch (jsonErr) {
                throw new Error('Server mengembalikan response tidak valid (status: ' + response.status + ')');
            }
            
            if (response.ok && data.success) {
                alert('✅ ' + data.message);
            } else {
                alert('❌ ' + (data.error || 'Gagal mengirim email'));
            }
        } catch (err) {
            console.error('Email send error:', err);
            alert('Gagal mengirim email: ' + err.message);
        } finally {
            btn.disabled = false;
            btn.innerHTML = originalText;
        }
    }
</script>
