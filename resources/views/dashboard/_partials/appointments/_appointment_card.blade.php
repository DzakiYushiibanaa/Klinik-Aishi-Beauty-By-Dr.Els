{{-- Partial: Appointment Card --}}
                    @php
                        $aptStatus = strtolower((string) $apt->status);
                        if (in_array($aptStatus, ['akan_datang', 'diproses'], true)) {
                            $aptStatus = 'disetujui';
                        }
                        $totalDurasi = 0; $totalHarga = 0;
                        $displayItems = count($apt->orderItems) > 0 ? $apt->orderItems : $apt->items;
                        foreach($displayItems as $item) {
                            if (isset($item->subtotal)) {
                                $itemPrice = $item->subtotal;
                            } else {
                                $itemPrice = match($item->item_type) {
                                    'treatment' => $item->treatment ? $item->treatment->harga * $item->qty : 0,
                                    'consultation' => $item->consultation ? $item->consultation->harga * $item->qty : 0,
                                    'product' => $item->product ? $item->product->harga * $item->qty : 0,
                                    default => 0,
                                };
                            }
                            $totalHarga += $itemPrice;

                            if($item->item_type == 'treatment' && $item->treatment) { $totalDurasi += $item->treatment->durasi_menit * $item->qty; }
                            elseif($item->item_type == 'consultation' && $item->consultation) { $totalDurasi += $item->consultation->durasi_menit * $item->qty; }
                        }
                        $statusLabel = match($aptStatus) { 'disetujui', 'akan_datang', 'diproses' => 'Disetujui', 'menunggu_pembayaran' => 'Belum Bayar', 'selesai' => 'Selesai', 'batal' => 'Dibatalkan', default => ucfirst(str_replace('_', ' ', (string) $apt->status)) };
                        $statusBadge = match($aptStatus) { 'disetujui' => 'bg-blue-50 text-blue-700 border-blue-200', 'menunggu_pembayaran' => 'bg-amber-50 text-amber-600 border-amber-200', 'selesai' => 'bg-emerald-50 text-emerald-700 border-emerald-200', 'batal' => 'bg-rose-50 text-rose-700 border-rose-200', default => 'bg-slate-50 text-slate-600 border-slate-200' };

                        $handlerItem = $apt->items
                            ->first(fn ($item) => in_array($item->item_type, ['treatment', 'consultation'], true) && $item->handler);
                        $staffName = $handlerItem?->handler?->name
                            ?? $apt->dokter?->name
                            ?? $apt->terapis?->name
                            ?? 'Belum Ditentukan';
                        $staffRole = match ($handlerItem?->handled_by_type) {
                            'dokter' => 'Dokter',
                            'terapis' => 'Terapis',
                            default => $apt->dokter ? 'Dokter' : ($apt->terapis ? 'Terapis' : 'Staff'),
                        };

                        // Relasi: status pembayaran dari order
                        $relatedOrders = $appointmentOrderMap[$apt->appointment_id] ?? collect();
                        $paidOrders = collect();
                        $pendingOrder = null;
                        
                        if ($relatedOrders->isNotEmpty()) {
                            $paidOrders = $relatedOrders->where('status', 'sudah_dibayar');
                            $pendingOrder = $relatedOrders->where('status', 'menunggu_pembayaran')->first();
                        }
                        
                        $relatedOrder = $pendingOrder ?: ($paidOrders->sortByDesc('created_at')->first() ?: $relatedOrders->sortByDesc('created_at')->first());
                        
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
                                $payLabel = match($relatedOrder->status) { 'menunggu_pembayaran' => 'Belum Bayar', 'sudah_dibayar' => 'Sudah Dibayar', 'batal', 'dibatalkan' => 'Batal', default => ucfirst($relatedOrder->status) };
                                $payBadge = match($relatedOrder->status) { 'menunggu_pembayaran' => 'bg-amber-50 text-amber-600 border-amber-200', 'sudah_dibayar' => 'bg-emerald-50 text-emerald-600 border-emerald-200', default => 'bg-slate-50 text-slate-500 border-slate-200' };
                                
                                if ($payLabel === 'Belum Bayar') {
                                    $payFilterKey = 'belum_bayar';
                                } elseif ($payLabel === 'Sudah Dibayar') {
                                    $payFilterKey = 'sudah_dibayar';
                                }
                            }
                        } else {
                            // Jika appointment aktif tapi belum ada order, secara logika belum bayar. 
                            // Untuk amannya set filter ke semua atau belum_bayar jika bukan batal/selesai
                            if (!in_array($aptStatus, ['batal', 'dibatalkan', 'selesai', 'tidak_hadir'])) {
                                $payFilterKey = 'belum_bayar';
                            }
                        }

                        $searchableText = strtolower(addslashes($staffName . ' ' . $apt->jenis . ' ' . collect($apt->items)->map(function($i) { return $i->treatment?->nama ?? $i->consultation?->nama ?? ''; })->implode(' ')));
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

                    <div x-show="(filterStatus === 'semua' || (filterStatus === 'aktif' && '{{ $aptStatus }}' === 'disetujui') || filterStatus === '{{ $aptStatus }}') && (filterJenis === 'semua' || filterJenis === '{{ strtolower($apt->jenis) }}') && (filterPayment === 'semua' || filterPayment === '{{ $payFilterKey }}') && isDateInRange('{{ $apt->tanggal_jam->format('Y-m-d') }}') && (searchQuery === '' || '{{ $searchableText }}'.includes(searchQuery.toLowerCase()))"
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
                        jenis: @js(strtolower($apt->jenis)),
                        jadwalData: @js($aptJadwalData),
                        treatmentSlots: [],
                        loadingTreatmentSlots: false,

                        async fetchTreatmentSlots() {
                            if (this.jenis !== 'treatment' || !this.newTanggal) { this.treatmentSlots = []; return; }
                            this.loadingTreatmentSlots = true;
                            try {
                                const res = await fetch(`{{ route('appointment.booking-treatment.check-slots') }}?tanggal=${this.newTanggal}`);
                                const data = await res.json();
                                const today = new Date(); today.setSeconds(0,0);
                                this.treatmentSlots = data.map(s => {
                                    const startStr = s.value.split(' - ')[0];
                                    const slotStart = new Date(`${this.newTanggal}T${startStr}:00`);
                                    const isPast = slotStart < today;
                                    const isFull = !!s.penuh;
                                    return {
                                        value: s.value.replace(/:/g, '.'),
                                        rawValue: s.value,
                                        sisa: s.sisa,
                                        penuh: isFull,
                                        isPast: isPast,
                                        isDisabled: isFull || isPast,
                                        label: isPast ? 'Lewat' : (isFull ? 'Penuh' : `Sisa ${s.sisa} slot`),
                                    };
                                });
                            } catch (e) {
                                this.treatmentSlots = [];
                            }
                            this.loadingTreatmentSlots = false;
                        },

                        get availableDays() {
                            const days = [];
                            const dayNames = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
                            const today = new Date();

                            // Treatment: 14 hari ke depan, semua hari (slot dikelola admin via endpoint)
                            if (this.jenis === 'treatment') {
                                for (let i = 1; i <= 14; i++) {
                                    const d = new Date(today);
                                    d.setDate(today.getDate() + i);
                                    days.push({
                                        date: `${d.getFullYear()}-${String(d.getMonth()+1).padStart(2,'0')}-${String(d.getDate()).padStart(2,'0')}`,
                                        dayName: dayNames[d.getDay()].substring(0, 3),
                                        dayNum: d.getDate(),
                                        monthShort: d.toLocaleString('id-ID', {month:'short'}),
                                        displayFull: `${dayNames[d.getDay()]}, ${d.getDate()} ${d.toLocaleString('id-ID', {month:'short'})} ${d.getFullYear()}`
                                    });
                                }
                                return days;
                            }

                            // Konsultasi: ikuti hari praktik dokter
                            const activeDays = this.jadwalData.map(j => j.hari.toLowerCase());
                            for (let i = 0; i < 30; i++) {
                                const d = new Date(today);
                                d.setDate(today.getDate() + i);
                                const dayName = dayNames[d.getDay()];

                                if (activeDays.includes(dayName.toLowerCase())) {
                                    days.push({
                                        date: `${d.getFullYear()}-${String(d.getMonth()+1).padStart(2,'0')}-${String(d.getDate()).padStart(2,'0')}`,
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

                            // Treatment: pakai slot dari API (sudah di-fetch)
                            if (this.jenis === 'treatment') return this.treatmentSlots;

                            // Konsultasi: generate slot 30 menit dari jadwal dokter
                            const [yy, mm, dd] = this.newTanggal.split('-').map(Number);
                            const selectedDate = new Date(yy, mm - 1, dd);
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
                                    isDisabled: isPast,
                                    label: isPast ? 'Lewat' : 'Tersedia',
                                    penuh: false,
                                    isPast: isPast,
                                });

                                current = nextSlot;
                            }
                            return times;
                        }
                    }" @click="if(!$event.target.closest('button') && !$event.target.closest('form')) { @if($relatedOrder && $relatedOrder->status === 'sudah_dibayar') showNotaTreatment = true; @endif }" x-init="$nextTick(() => { if ($root.highlightId == '{{ $apt->appointment_id }}') { $el.scrollIntoView({ behavior: 'smooth', block: 'center' }); } @if($errors->has('cancel_reason') && old('_cancel_apt_id') == $apt->appointment_id) openCancelModal = true; $el.scrollIntoView({ behavior: 'smooth', block: 'center' }); @endif })" :class="$root.highlightId == '{{ $apt->appointment_id }}' ? 'ring-2 ring-[#CFAF53] shadow-lg shadow-[#CFAF53]/20 bg-[#CFAF53]/5' : 'bg-slate-50'" class="rounded-2xl border border-slate-200 shadow-sm p-5 flex flex-col md:flex-row gap-5 transition-all duration-1000 {{ $relatedOrder && $relatedOrder->status === 'sudah_dibayar' ? 'cursor-pointer hover:border-[#CFAF53]/50' : '' }}" data-apt-id="{{ $apt->appointment_id }}">

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
                                    <p class="text-sm font-bold text-[#1A1A1A]">{{ $staffName }}</p>
                                    <p class="text-[11px] text-slate-400">{{ $staffRole }}</p>
                                </div>
                            </div>

                            {{-- Biaya & Keterangan --}}
                            @if($totalHarga > 0)
                                <div class="mt-3">
                                    <p class="text-sm font-extrabold text-[#1A1A1A]">Rp {{ number_format($totalHarga, 0, ',', '.') }}</p>
                                    @if($apt->jenis === 'konsultasi')
                                        <p class="text-[11px] text-slate-400 mt-0.5">(Bayar di klinik setelah sesi ini selesai)</p>
                                    @endif
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
                            @if(count($displayItems) > 0)
                                <div class="mt-3 pt-3 border-t border-[#E8E0D8]/40">
                                    <button @click="showRincian = !showRincian" class="flex items-center gap-1.5 text-sm font-bold text-[#CFAF53] hover:text-[#8C7A63] transition-colors">
                                        <svg :class="showRincian ? 'rotate-180' : ''" class="w-4 h-4 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                        <span x-text="showRincian ? 'Tutup Rincian' : 'Lihat Rincian ({{ count($displayItems) }} Item)'"></span>
                                    </button>
                                    <div x-show="showRincian" x-collapse class="mt-3 space-y-2">
                                        @foreach($displayItems as $item)
                                            @php
                                                if (isset($item->subtotal)) {
                                                    $itemPrice = $item->subtotal;
                                                } else {
                                                    $itemPrice = match($item->item_type) {
                                                        'treatment' => $item->treatment ? $item->treatment->harga * $item->qty : 0,
                                                        'consultation' => $item->consultation ? $item->consultation->harga * $item->qty : 0,
                                                        'product' => $item->product ? $item->product->harga * $item->qty : 0,
                                                        default => 0,
                                                    };
                                                }
                                            @endphp
                                            @if($item->item_type == 'treatment' && $item->treatment)
                                                <div class="flex justify-between text-sm"><span class="text-slate-600">{{ $item->treatment->nama }} {!! $item->qty > 1 ? "<span class='text-xs opacity-50'>x{$item->qty}</span>" : "" !!}</span><span class="font-medium text-[#1A1A1A]">Rp {{ number_format($itemPrice, 0, ',', '.') }}</span></div>
                                            @elseif($item->item_type == 'consultation' && $item->consultation)
                                                <div class="flex justify-between text-sm"><span class="text-slate-600">{{ $item->consultation->nama }} {!! $item->qty > 1 ? "<span class='text-xs opacity-50'>x{$item->qty}</span>" : "" !!}</span><span class="font-medium text-[#1A1A1A]">Rp {{ number_format($itemPrice, 0, ',', '.') }}</span></div>
                                            @elseif($item->item_type == 'product' && $item->product)
                                                <div class="flex justify-between text-sm"><span class="text-slate-600">{{ $item->product->nama }} {!! $item->qty > 1 ? "<span class='text-xs opacity-50'>x{$item->qty}</span>" : "" !!}</span><span class="font-medium text-[#1A1A1A]">Rp {{ number_format($itemPrice, 0, ',', '.') }}</span></div>
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
                                $isPendingOrApproved = $aptStatus === 'disetujui';
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
                            @if($relatedOrder && $relatedOrder->status === 'sudah_dibayar')
                                <div x-show="showNotaTreatment" @click.stop style="display:none" class="fixed inset-0 z-[999] flex items-center justify-center bg-black/50 backdrop-blur-sm p-4" x-transition>
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
                                                <span class="px-3 py-1 text-[10px] font-black rounded uppercase tracking-wider bg-emerald-100 text-emerald-700">SUDAH DIBAYAR</span>
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
                                                            <p class="text-sm font-bold text-[#1A1A1A]">{{ $paidOrders->pluck('kode_order')->join(', ') }}</p>
                                                        </div>
                                                        <div>
                                                            <p class="text-[10px] text-slate-400">Tanggal Transaksi</p>
                                                            <p class="text-sm font-bold text-[#1A1A1A]">{{ $paidOrders->sortByDesc('paid_at')->first()?->paid_at?->translatedFormat('d M Y') ?? $relatedOrder->created_at->translatedFormat('d M Y') }}</p>
                                                        </div>
                                                        <div>
                                                            <p class="text-[10px] text-slate-400">Metode Pembayaran</p>
                                                            @php
                                                                $channels = $paidOrders->map(fn($o) => $o->payment_channel ?? $o->payment_method ?? '-')->unique();
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
                                                                $displayLabels = $channels->map(fn($c) => $channelLabels[strtolower($c)] ?? strtoupper($c))->join(', ');
                                                            @endphp
                                                            <p class="text-sm font-bold text-[#1A1A1A]">{{ $displayLabels ?: '-' }}</p>
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
                                                            <th class="py-3 px-4 text-[10px] font-bold text-slate-500 uppercase tracking-widest">Item</th>
                                                            <th class="py-3 px-4 text-[10px] font-bold text-slate-500 uppercase tracking-widest text-center">Qty</th>
                                                            <th class="py-3 px-4 text-[10px] font-bold text-slate-500 uppercase tracking-widest text-right">Harga</th>
                                                            <th class="py-3 px-4 text-[10px] font-bold text-slate-500 uppercase tracking-widest text-right">Subtotal</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody class="divide-y divide-[#E8E0D8]/40">
                                                        @foreach($paidOrders->pluck('items')->flatten() as $notaItem)
                                                            @php
                                                                $notaName = $notaItem->treatment?->nama ?? $notaItem->consultation?->nama ?? $notaItem->product?->nama ?? $notaItem->description ?? 'Item';
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
                                                <div class="w-full sm:w-72 space-y-2.5">
                                                    @php
                                                        $notaSubtotal    = (int) $paidOrders->sum(fn($o) => $o->subtotal ?? $o->grand_total);
                                                        $notaDiskon      = (int) $paidOrders->sum('diskon_total');
                                                        $notaOngkir      = (int) $paidOrders->sum('ongkir');
                                                        $notaGrand       = (int) $paidOrders->sum('grand_total');
                                                        $notaCashRcvd    = (int) $paidOrders->sum('cash_received');
                                                        $notaIsCash      = $channels->map(fn($c) => strtolower((string)$c))->contains('cash');
                                                        $notaKembalian   = $notaIsCash && $notaCashRcvd > 0 ? max(0, $notaCashRcvd - $notaGrand) : 0;
                                                    @endphp

                                                    <div class="flex justify-between items-center text-sm">
                                                        <span class="text-slate-500">Subtotal</span>
                                                        <span class="font-medium text-[#1A1A1A] tabular-nums">Rp {{ number_format($notaSubtotal, 0, ',', '.') }}</span>
                                                    </div>

                                                    @if($notaDiskon > 0)
                                                        <div class="flex justify-between items-center text-sm">
                                                            <span class="text-slate-500">Diskon Transaksi</span>
                                                            <span class="font-medium text-rose-600 tabular-nums">-Rp {{ number_format($notaDiskon, 0, ',', '.') }}</span>
                                                        </div>
                                                    @endif

                                                    @if($notaOngkir > 0)
                                                        <div class="flex justify-between items-center text-sm">
                                                            <span class="text-slate-500">Ongkos Kirim</span>
                                                            <span class="font-medium text-[#1A1A1A] tabular-nums">Rp {{ number_format($notaOngkir, 0, ',', '.') }}</span>
                                                        </div>
                                                    @endif

                                                    <div class="pt-3 mt-1 border-t border-dashed border-[#E8E0D8] flex justify-between items-center">
                                                        <span class="font-bold text-[#1A1A1A]">Total Pembayaran</span>
                                                        <span class="text-lg font-black text-[#CFAF53] tabular-nums">Rp {{ number_format($notaGrand, 0, ',', '.') }}</span>
                                                    </div>

                                                    @if($notaIsCash && $notaCashRcvd > 0)
                                                        <div class="pt-2 mt-1 border-t border-[#E8E0D8]/60 space-y-1.5">
                                                            <div class="flex justify-between items-center text-xs">
                                                                <span class="text-slate-400">Tunai Diterima</span>
                                                                <span class="font-medium text-slate-600 tabular-nums">Rp {{ number_format($notaCashRcvd, 0, ',', '.') }}</span>
                                                            </div>
                                                            <div class="flex justify-between items-center text-xs">
                                                                <span class="text-slate-400">Kembalian</span>
                                                                <span class="font-bold text-emerald-600 tabular-nums">Rp {{ number_format($notaKembalian, 0, ',', '.') }}</span>
                                                            </div>
                                                        </div>
                                                    @endif
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
                        <div x-show="openCancelModal" @click.stop style="display:none" class="fixed inset-0 z-[999] flex items-center justify-center bg-black/50 backdrop-blur-sm p-4" x-transition>
                            <div class="bg-white w-full max-w-md rounded-2xl p-7 shadow-2xl" @click.away="openCancelModal = false">
                                <div class="flex items-start gap-4">
                                    <div class="shrink-0 w-12 h-12 rounded-full bg-rose-50 flex items-center justify-center">
                                        <svg class="w-6 h-6 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M5.07 19h13.86a2 2 0 001.74-3l-6.93-12a2 2 0 00-3.48 0l-6.93 12a2 2 0 001.74 3z"/></svg>
                                    </div>
                                    <div class="flex-1">
                                        <h3 class="text-[1.35rem] font-extrabold text-[#1A1A1A] tracking-tight leading-tight">Batalkan Janji?</h3>
                                        <p class="text-[0.95rem] text-slate-500 mt-1.5 leading-relaxed">Tindakan ini tidak dapat dibatalkan setelah dikonfirmasi.</p>
                                    </div>
                                </div>

                                <form action="{{ route('appointments.cancel', $apt->appointment_id) }}" method="POST" class="mt-6">
                                    @csrf @method('PATCH')
                                    <input type="hidden" name="_cancel_apt_id" value="{{ $apt->appointment_id }}">

                                    <label class="block text-[0.9rem] font-bold text-[#1A1A1A] mb-2">
                                        Alasan pembatalan
                                        <span class="text-rose-500">*</span>
                                    </label>
                                    <textarea
                                        name="cancel_reason"
                                        rows="3"
                                        required
                                        minlength="5"
                                        maxlength="255"
                                        placeholder="Contoh: Ada keperluan mendadak yang tidak bisa ditunda."
                                        class="w-full rounded-xl border-slate-300 focus:border-[#CFAF53] focus:ring-[#CFAF53]/40 text-[0.95rem] placeholder:text-slate-400 resize-none @error('cancel_reason') @if(old('_cancel_apt_id') == $apt->appointment_id) border-rose-400 focus:border-rose-400 focus:ring-rose-200 @endif @enderror">@if(old('_cancel_apt_id') == $apt->appointment_id){{ old('cancel_reason') }}@endif</textarea>

                                    @error('cancel_reason')
                                        @if(old('_cancel_apt_id') == $apt->appointment_id)
                                            <p class="mt-2 text-[0.85rem] text-rose-600 font-medium flex items-start gap-1.5">
                                                <svg class="w-4 h-4 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                <span>{{ $message }}</span>
                                            </p>
                                        @else
                                            <p class="mt-2 text-[0.8rem] text-slate-400">Minimal 5 karakter, maksimal 255 karakter.</p>
                                        @endif
                                    @else
                                        <p class="mt-2 text-[0.8rem] text-slate-400">Minimal 5 karakter, maksimal 255 karakter.</p>
                                    @enderror

                                    <div class="flex gap-3 mt-6">
                                        <button type="button" @click="openCancelModal = false" class="flex-1 py-3 rounded-xl border border-slate-200 text-[0.95rem] font-bold text-slate-700 hover:bg-slate-50 cursor-pointer transition-colors">Batal</button>
                                        <button type="submit" class="flex-1 py-3 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-[0.95rem] font-bold cursor-pointer transition-colors">Ya, Batalkan</button>
                                    </div>
                                </form>
                            </div>
                        </div>

                        {{-- Modal Reschedule 1 (Pilih Tanggal & Jam) --}}
                        <div x-show="openRescheduleModal" @click.stop style="display:none" class="fixed inset-0 z-[999] flex items-center justify-center bg-black/50 backdrop-blur-sm p-4" x-transition>
                            <div class="bg-white w-full max-w-lg rounded-2xl p-6 shadow-2xl max-h-[90vh] overflow-y-auto" @click.away="openRescheduleModal = false">
                                <h3 class="text-xl font-bold text-[#1A1A1A]">Ubah Jadwal</h3>
                                <p class="text-sm text-slate-500 mt-1 mb-6">
                                    <template x-if="jenis === 'treatment'">
                                        <span>Pilih tanggal dan slot treatment baru. Setiap slot menampung maksimal 2 pasien.</span>
                                    </template>
                                    <template x-if="jenis !== 'treatment'">
                                        <span>Pilih jadwal baru sesuai hari praktik dokter Anda.</span>
                                    </template>
                                </p>
                                
                                {{-- Pilih Tanggal --}}
                                <div class="mb-6">
                                    <div class="text-sm font-bold text-[#1A1A1A] mb-3">Pilih Tanggal Baru</div>
                                    
                                    <div x-show="availableDays.length === 0" class="p-4 bg-gray-50 border border-gray-200 rounded-xl text-center">
                                        <p class="text-sm text-gray-500">
                                            <template x-if="jenis === 'treatment'">
                                                <span>Belum ada tanggal tersedia.</span>
                                            </template>
                                            <template x-if="jenis !== 'treatment'">
                                                <span>Dokter ini belum memiliki jadwal praktik aktif.</span>
                                            </template>
                                        </p>
                                    </div>

                                    <div class="flex gap-3 overflow-x-auto pb-2 scrollbar-hide snap-x">
                                        <template x-for="day in availableDays" :key="day.date">
                                            <label class="snap-start shrink-0 cursor-pointer">
                                                <input type="radio" name="reschedule_tanggal_{{ $apt->appointment_id }}" :value="day.date" x-model="newTanggal" @change="displayDate = day.displayFull; newJam = ''; fetchTreatmentSlots();" class="peer sr-only">
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
                                    <div class="text-sm font-bold text-[#1A1A1A] mb-3">
                                        <template x-if="jenis === 'treatment'">
                                            <span>Pilih Slot Treatment Baru (WIB)</span>
                                        </template>
                                        <template x-if="jenis !== 'treatment'">
                                            <span>Pilih Jam Baru (WIB)</span>
                                        </template>
                                    </div>

                                    {{-- Loading state khusus treatment --}}
                                    <div x-show="jenis === 'treatment' && loadingTreatmentSlots" class="flex items-center justify-center py-8">
                                        <svg class="w-5 h-5 animate-spin text-[#CFAF53]" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        <span class="ml-2 text-sm text-slate-500">Mengecek ketersediaan slot...</span>
                                    </div>

                                    <div x-show="!(jenis === 'treatment' && loadingTreatmentSlots) && availableTimes.length === 0" class="p-4 bg-gray-50 border border-gray-200 rounded-xl text-center">
                                        <p class="text-sm text-gray-500">Tidak ada jadwal tersedia pada tanggal ini.</p>
                                    </div>

                                    {{-- Grid slot - Treatment (slot 2 jam dengan info kapasitas) --}}
                                    <template x-if="jenis === 'treatment' && !loadingTreatmentSlots">
                                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                            <template x-for="slot in availableTimes" :key="slot.value">
                                                <label :class="slot.isDisabled ? 'cursor-not-allowed' : 'cursor-pointer'">
                                                    <input type="radio" name="reschedule_jam_{{ $apt->appointment_id }}" :value="slot.value" x-model="newJam" class="peer sr-only" :disabled="slot.isDisabled">
                                                    <div class="py-3.5 px-4 rounded-xl border transition-all duration-200"
                                                        :class="slot.isDisabled
                                                            ? (slot.penuh ? 'border-rose-200 bg-rose-50/50' : 'border-gray-200 bg-gray-50') + ' opacity-60'
                                                            : (newJam === slot.value
                                                                ? 'border-[#3B3529] bg-[#FAF8F5] shadow-sm'
                                                                : 'border-gray-200 bg-white hover:border-[#8C7A63]')">
                                                        <div class="flex items-center gap-2 text-sm font-medium"
                                                            :class="slot.isDisabled
                                                                ? 'text-slate-400'
                                                                : (newJam === slot.value ? 'text-[#1A1A1A] font-bold' : 'text-gray-600')">
                                                            <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                            <span x-text="slot.value"></span>
                                                        </div>
                                                        <span class="text-[10px] font-semibold mt-1 block uppercase tracking-wide"
                                                            :class="slot.penuh
                                                                ? 'text-rose-500'
                                                                : (slot.isPast
                                                                    ? 'text-slate-400'
                                                                    : (newJam === slot.value ? 'text-[#C89B53]' : 'text-emerald-600'))"
                                                            x-text="slot.label"></span>
                                                    </div>
                                                </label>
                                            </template>
                                        </div>
                                    </template>

                                    {{-- Grid slot - Konsultasi (slot 30 menit dari jadwal dokter) --}}
                                    <template x-if="jenis !== 'treatment'">
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
                                    </template>
                                </div>

                                <div class="flex gap-3 mt-6">
                                    <button type="button" @click="openRescheduleModal = false; newTanggal = ''; newJam = ''" class="flex-1 py-2.5 rounded-xl border border-slate-200 text-sm font-bold text-slate-700 hover:bg-slate-50 cursor-pointer">Batal</button>
                                    <button type="button" @click="if(newTanggal && newJam) { openRescheduleModal = false; openRescheduleConfirmModal = true; } else { window.showToast && window.showToast('warning', 'Mohon pilih tanggal dan ' + (jenis === 'treatment' ? 'slot' : 'jam') + ' baru terlebih dahulu.', 'Data Belum Lengkap'); }" class="flex-1 py-2.5 rounded-xl bg-[#CFAF53] hover:bg-[#b09444] text-white text-sm font-bold transition-colors cursor-pointer">Lanjutkan</button>
                                </div>
                            </div>
                        </div>

                        {{-- Modal Reschedule 2 (Konfirmasi) --}}
                        <div x-show="openRescheduleConfirmModal" @click.stop style="display:none" class="fixed inset-0 z-[999] flex items-center justify-center bg-black/50 backdrop-blur-sm p-4" x-transition>
                            <div class="bg-white w-full max-w-md rounded-2xl p-7 shadow-2xl" @click.away="openRescheduleConfirmModal = false">
                                <h3 class="text-[1.35rem] font-extrabold text-[#1A1A1A] tracking-tight leading-tight">Konfirmasi Perubahan</h3>
                                <p class="text-[0.95rem] text-slate-500 mt-2.5 leading-relaxed">Anda akan mengubah jadwal janji temu ini.</p>

                                <div class="mt-6 bg-[#FAF8F5] border border-[#E8E0D8] rounded-xl p-5 space-y-4">
                                    <div>
                                        <p class="text-[0.8rem] font-bold text-slate-500 uppercase tracking-widest mb-1.5">Jadwal Lama</p>
                                        <p class="text-[1rem] font-bold text-[#1A1A1A] leading-snug">{{ $apt->tanggal_jam->locale('id')->translatedFormat('l, d M Y') }} Jam {{ $apt->tanggal_jam->format('H:i') }} WIB</p>
                                    </div>
                                    <div class="flex justify-center text-[#CFAF53]/50">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path></svg>
                                    </div>
                                    <div>
                                        <p class="text-[0.8rem] font-bold text-[#CFAF53] uppercase tracking-widest mb-1.5">Jadwal Baru</p>
                                        <p class="text-[1.05rem] font-extrabold text-[#CFAF53] leading-snug"><span x-text="displayDate"></span> Jam <span x-text="newJam"></span> WIB</p>
                                    </div>
                                </div>

                                <form action="{{ route('appointments.reschedule', $apt->appointment_id) }}" method="POST" class="mt-6">
                                    @csrf @method('PATCH')
                                    <input type="hidden" name="new_tanggal" :value="newTanggal">
                                    <input type="hidden" name="new_jam" :value="newJam">
                                    <div class="flex gap-3">
                                        <button type="button" @click="openRescheduleConfirmModal = false; openRescheduleModal = true;" class="flex-1 py-3 rounded-xl border border-slate-200 text-[0.95rem] font-bold text-slate-700 hover:bg-slate-50 cursor-pointer transition-colors">Kembali</button>
                                        <button type="submit" class="flex-1 py-3 rounded-xl bg-[#CFAF53] hover:bg-[#b09444] text-white text-[0.95rem] font-bold cursor-pointer transition-colors">Ya, Ubah Jadwal</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
