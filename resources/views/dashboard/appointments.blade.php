{{-- ====== TAB: JANJI TEMU ====== --}}
<div class="bg-white rounded-2xl border border-[#E8E0D8]/60 shadow-sm p-6" x-data="{ viewMode: 'list' }">

    {{-- Header --}}
    <div class="flex items-center justify-between gap-4 mb-6">
        <h2 class="text-xl font-extrabold text-[#1A1A1A]">Janji Temu Saya</h2>
        <div class="bg-white border border-[#E8E0D8]/60 p-1 rounded-xl flex items-center">
            <button @click="viewMode = 'list'" :class="viewMode === 'list' ? 'bg-[#FAF8F5] text-[#1A1A1A] shadow-sm' : 'text-slate-400'" class="px-3 py-1.5 rounded-lg text-xs font-bold transition">List</button>
            <button @click="viewMode = 'calendar'" :class="viewMode === 'calendar' ? 'bg-[#FAF8F5] text-[#CFAF53] shadow-sm' : 'text-slate-400'" class="px-3 py-1.5 rounded-lg text-xs font-bold transition">Kalender</button>
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
            <div class="space-y-4">
                @foreach($appointments as $apt)
                    @php
                        $aptStatus = strtolower($apt->status);
                        $totalDurasi = 0; $totalHarga = 0;
                        foreach($apt->items as $item) {
                            if($item->item_type == 'treatment' && $item->treatment) { $totalDurasi += $item->treatment->durasi_menit * $item->qty; $totalHarga += $item->treatment->harga * $item->qty; }
                            elseif($item->item_type == 'bundle' && $item->bundle) { $totalHarga += $item->bundle->harga * $item->qty; }
                        }
                        $statusLabel = match($aptStatus) { 'diajukan' => 'Diajukan', 'disetujui' => 'Akan Datang', 'selesai' => 'Selesai', 'batal' => 'Dibatalkan', default => ucfirst($apt->status) };
                        $statusBadge = match($aptStatus) { 'diajukan' => 'bg-amber-50 text-amber-700 border-amber-200', 'disetujui' => 'bg-blue-50 text-blue-700 border-blue-200', 'selesai' => 'bg-emerald-50 text-emerald-700 border-emerald-200', 'batal' => 'bg-rose-50 text-rose-700 border-rose-200', default => 'bg-slate-50 text-slate-600 border-slate-200' };

                        // Relasi: status pembayaran dari order
                        $relatedOrder = $appointmentOrderMap[$apt->appointment_id] ?? null;
                        $payLabel = null;
                        $payBadge = null;
                        if ($relatedOrder) {
                            $payLabel = match($relatedOrder->status) { 'menunggu_pembayaran' => 'Belum Bayar', 'sudah_dibayar', 'lunas' => 'Lunas', 'batal', 'dibatalkan' => 'Batal', default => ucfirst($relatedOrder->status) };
                            $payBadge = match($relatedOrder->status) { 'menunggu_pembayaran' => 'bg-amber-50 text-amber-600 border-amber-200', 'sudah_dibayar', 'lunas' => 'bg-emerald-50 text-emerald-600 border-emerald-200', default => 'bg-slate-50 text-slate-500 border-slate-200' };
                        }
                    @endphp

                    <div x-data="{ openCancelModal: false, showRincian: false }" class="bg-[#FAF8F5] rounded-2xl border border-[#E8E0D8]/60 p-5 flex flex-col md:flex-row gap-5">

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

                            @if($aptStatus == 'batal')
                                <div class="mt-3 text-xs text-rose-600 bg-rose-50 px-3 py-2 rounded-lg border border-rose-100">
                                    <span class="font-bold">Dibatalkan:</span> "{{ $apt->cancel_reason }}"
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
                            <div class="flex flex-wrap gap-2">
                                <span class="px-3 py-1 rounded-full text-[10px] font-bold border {{ $statusBadge }}">{{ $statusLabel }}</span>
                                @if($payLabel)
                                    <span class="px-3 py-1 rounded-full text-[10px] font-bold border {{ $payBadge }}">💰 {{ $payLabel }}</span>
                                @endif
                            </div>
                            @if($totalHarga > 0)
                                <div class="text-left md:text-right">
                                    <p class="text-[10px] text-slate-400">Total Biaya</p>
                                    <p class="text-lg font-extrabold text-[#1A1A1A]">Rp {{ number_format($totalHarga, 0, ',', '.') }}</p>
                                </div>
                            @endif
                            @if(in_array($aptStatus, ['diajukan', 'disetujui']) && $apt->tanggal_jam->isFuture())
                                <button @click="openCancelModal = true" class="text-xs font-bold text-slate-500 hover:text-rose-600 border border-slate-200 hover:border-rose-300 px-4 py-2 rounded-xl transition-colors">Batalkan Janji</button>
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
                        <div class="p-4 bg-[#FAF8F5] border border-[#E8E0D8]/60 rounded-xl">
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
