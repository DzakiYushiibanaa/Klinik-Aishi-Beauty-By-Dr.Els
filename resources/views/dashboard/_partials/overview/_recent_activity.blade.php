{{-- Partial: Recent Activity --}}
    {{-- Main: Aktivitas Terkini --}}
    <div class="mt-8">
        <div class="dash-section">
            <div class="dash-section__head">
                <div>
                    <p class="dash-section__eyebrow">Aktivitas Terkini</p>
                    <p class="dash-section__sub">Update terbaru dari konsultasi dan pesanan Anda.</p>
                </div>
            </div>

            <div class="space-y-4">
            @php $latestPraKonsul = $praKonsuls->first(); @endphp
            @php $latestOrder = $orders->first(); @endphp

            {{-- Card: Pra Konsultasi Terbaru --}}
            @if($latestPraKonsul)
                @php
                    $praStatusKey = strtolower($latestPraKonsul->status ?? 'baru');
                    $isReviewed = in_array($praStatusKey, ['reviewed', 'direkomendasikan', 'selesai']);
                    $isInReview = in_array($praStatusKey, ['ditinjau', 'in_review', 'diproses']);
                    $praStatusLabel = match ($praStatusKey) {
                        'baru', 'submitted', 'menunggu' => 'Menunggu Respon',
                        'ditinjau', 'in_review', 'diproses', 'reviewed' => 'Sedang Ditinjau',
                        'direkomendasikan', 'selesai' => 'Direkomendasikan',
                        'ditutup' => 'Ditutup',
                        default => ucfirst($praStatusKey),
                    };
                    $praStatusClass = match ($praStatusKey) {
                        'baru', 'submitted', 'menunggu' => 'bg-amber-100 text-amber-800 border-amber-300',
                        'ditinjau', 'in_review', 'diproses', 'reviewed' => 'bg-blue-100 text-blue-800 border-blue-300',
                        'direkomendasikan', 'selesai' => 'bg-blue-600 text-white border-blue-700 shadow-sm shadow-blue-600/20',
                        'ditutup' => 'bg-rose-100 text-rose-800 border-rose-300',
                        default => 'bg-slate-100 text-slate-700 border-slate-300',
                    };
                @endphp
                <div class="bg-[#FAF8F5] rounded-2xl border {{ $isReviewed ? 'border-blue-200 ring-4 ring-blue-50' : 'border-[#E8E0D8]/60' }} p-5 relative overflow-hidden transition-all">
                    @if($isReviewed)
                        <div class="absolute top-0 right-0 w-32 h-32 bg-blue-500/5 rounded-full -mr-10 -mt-10 blur-2xl"></div>
                    @endif
                    <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4 relative z-10">
                        <div class="flex items-start gap-4 w-full">
                            <div class="w-10 h-10 rounded-xl {{ $isReviewed ? 'bg-blue-100 text-blue-600' : 'bg-[#CFAF53]/10 text-[#CFAF53]' }} flex items-center justify-center shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex flex-col sm:flex-row sm:items-center gap-1.5 sm:gap-3 mb-1.5">
                                    <h4 class="font-bold text-[#1A1A1A] text-base truncate">{{ $isReviewed ? 'Hasil Konsultasi Tersedia' : 'Tinjauan Konsultasi' }}</h4>
                                    <span class="w-fit shrink-0 px-2.5 py-0.5 rounded-full text-[10px] font-extrabold border tracking-wider {{ $praStatusClass }} uppercase">
                                        {{ $praStatusLabel }}
                                    </span>
                                </div>
                                <p class="text-xs text-slate-400 flex items-center gap-1">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    Diajukan pada {{ $latestPraKonsul->created_at->translatedFormat('d M Y, H:i') }} WIB
                                </p>

                                @if($isReviewed)
                                    <div class="mt-3 bg-white px-3.5 py-2.5 rounded-xl border border-blue-100 shadow-sm border-l-4 border-l-blue-500">
                                        <p class="text-sm font-medium text-slate-700">
                                            Dokter telah memberikan rekomendasi untuk kondisi kamu.
                                        </p>
                                    </div>
                                @else
                                    <p class="text-sm text-slate-500 mt-2 line-clamp-2">{{ $latestPraKonsul->keluhan_utama ?? 'Sedang ditinjau oleh tim dokter.' }}</p>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div x-data="{ showLatestDetail: false }" class="relative z-10">
                        <div class="mt-4 pt-4 border-t border-[#E8E0D8]/40 flex justify-end">
                            @if($isReviewed)
                                <button @click="showLatestDetail = true" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold rounded-full transition-colors shadow-lg shadow-blue-600/20">
                                    Lihat Rekomendasi
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                                </button>
                            @else
                                <button @click="showLatestDetail = true" class="text-sm font-bold text-[#8C7A63] hover:text-[#1A1A1A] transition-colors">Lihat Detail →</button>
                            @endif
                        </div>

                        {{-- Modal: Detail Pra-Konsultasi Terbaru --}}
                        <template x-teleport="body">
                            <div x-show="showLatestDetail" 
                                 x-transition:enter="transition ease-out duration-300"
                                 x-transition:enter-start="opacity-0"
                                 x-transition:enter-end="opacity-100"
                                 x-transition:leave="transition ease-in duration-200"
                                 x-transition:leave-start="opacity-100"
                                 x-transition:leave-end="opacity-0"
                                 class="fixed inset-0 z-[999] flex items-center justify-center p-4 bg-[#1A1A1A]/40 backdrop-blur-sm"
                                 style="display: none;">

                                <div @click.away="showLatestDetail = false" 
                                     x-transition:enter="transition ease-out duration-300"
                                     x-transition:enter-start="opacity-0 scale-95 translate-y-4"
                                     x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                                     class="bg-white w-full max-w-2xl max-h-[90vh] rounded-3xl shadow-2xl overflow-hidden flex flex-col">

                                    {{-- Modal Header --}}
                                    <div class="px-6 py-5 border-b border-[#E8E0D8]/60 flex items-center justify-between shrink-0 bg-white">
                                        <div>
                                            <h3 class="text-xl font-extrabold text-[#1A1A1A]">Detail Pra-Konsultasi</h3>
                                            <p class="text-xs text-slate-400 mt-0.5">#PK-{{ $latestPraKonsul->patient_form_id }} • {{ $latestPraKonsul->created_at->translatedFormat('d F Y, H:i') }} WIB</p>
                                        </div>
                                        <button @click="showLatestDetail = false" class="w-10 h-10 rounded-full bg-slate-50 flex items-center justify-center text-slate-400 hover:bg-slate-100 hover:text-slate-600 transition-colors">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                        </button>
                                    </div>

                                    {{-- Modal Body --}}
                                    <div class="p-6 overflow-y-auto custom-scrollbar space-y-6">
                                        @php $latestReviewDoctor = $latestPraKonsul->reviewer ?: $latestPraKonsul->dokter; @endphp
                                        @if($latestReviewDoctor)
                                            <div class="bg-[#FAF8F5] rounded-2xl border border-[#E8E0D8]/60 p-4 flex items-center gap-3">
                                                <div class="w-10 h-10 rounded-full bg-white border border-[#E8E0D8] flex items-center justify-center text-[#CFAF53] shrink-0">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                                    </svg>
                                                </div>
                                                <div class="min-w-0">
                                                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Dokter Reviewer</p>
                                                    <p class="text-sm font-extrabold text-[#1A1A1A] truncate">Dr. {{ $latestReviewDoctor->name }}</p>
                                                </div>
                                            </div>
                                        @endif

                                        {{-- Row 1: Vital Stats --}}
                                        <div class="grid grid-cols-2 gap-4">
                                            <div class="bg-[#FAF8F5] rounded-2xl border border-[#E8E0D8]/60 p-4">
                                                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Golongan Darah</p>
                                                <p class="text-lg font-extrabold text-[#1A1A1A]">{{ $latestPraKonsul->golongan_darah ?? '-' }}</p>
                                            </div>
                                            <div class="bg-[#FAF8F5] rounded-2xl border border-[#E8E0D8]/60 p-4">
                                                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Jenis Kulit</p>
                                                <p class="text-lg font-extrabold text-[#1A1A1A]">{{ $latestPraKonsul->jenis_kulit ?? '-' }}</p>
                                            </div>
                                        </div>

                                        {{-- Skincare --}}
                                        <div class="bg-[#FAF8F5] rounded-2xl border border-[#E8E0D8]/60 p-5">
                                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2">Skincare Sebelumnya</p>
                                            <p class="text-sm text-[#1A1A1A] font-medium leading-relaxed">{{ $latestPraKonsul->skincare_sebelumnya ?? '-' }}</p>
                                        </div>

                                        {{-- Riwayat Perawatan --}}
                                        <div>
                                            <h4 class="text-[10px] font-bold text-[#8C7A63] uppercase tracking-[0.2em] mb-3">Riwayat Perawatan</h4>
                                            <div class="grid grid-cols-2 gap-3">
                                                @php
                                                    $perawatan = [
                                                        ['label' => 'Facial', 'value' => $latestPraKonsul->pernah_facial],
                                                        ['label' => 'Skin Booster', 'value' => $latestPraKonsul->pernah_skin_booster],
                                                        ['label' => 'Laser', 'value' => $latestPraKonsul->pernah_laser],
                                                        ['label' => 'Inject/Infus', 'value' => $latestPraKonsul->pernah_inject_infus],
                                                    ];
                                                @endphp
                                                @foreach($perawatan as $p)
                                                    <div class="flex items-center gap-3 px-4 py-3 rounded-xl border border-[#E8E0D8]/60 {{ $p['value'] ? 'bg-emerald-50/50 border-emerald-100' : 'bg-slate-50/50' }}">
                                                        <div class="w-5 h-5 rounded-full flex items-center justify-center {{ $p['value'] ? 'bg-emerald-500 text-white' : 'bg-slate-200 text-slate-400' }}">
                                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                @if($p['value'])
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                                                                @else
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"/>
                                                                @endif
                                                            </svg>
                                                        </div>
                                                        <span class="text-sm font-bold {{ $p['value'] ? 'text-emerald-700' : 'text-slate-400' }}">{{ $p['label'] }}</span>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>

                                        {{-- Foto Wajah --}}
                                        <div>
                                            <h4 class="text-[10px] font-bold text-[#8C7A63] uppercase tracking-[0.2em] mb-3">📸 Foto Wajah</h4>
                                            <div class="grid grid-cols-3 gap-3">
                                                @foreach(['foto_depan_path' => 'Depan', 'foto_kiri_path' => 'Kiri', 'foto_kanan_path' => 'Kanan'] as $field => $pos)
                                                    @php $path = $latestPraKonsul->$field; @endphp
                                                    <div class="text-center">
                                                        <div class="aspect-square rounded-2xl border border-[#E8E0D8]/60 overflow-hidden bg-slate-50 mb-1.5 shadow-sm">
                                                            @if($path)
                                                                <img src="{{ asset('storage/' . $path) }}" class="w-full h-full object-cover">
                                                            @else
                                                                <div class="w-full h-full flex flex-col items-center justify-center text-slate-300">
                                                                    <svg class="w-8 h-8 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                                                    <span class="text-[10px] font-bold uppercase tracking-wider">No Image</span>
                                                                </div>
                                                            @endif
                                                        </div>
                                                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">{{ $pos }}</span>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>

                                        {{-- Diagnosa & Rekomendasi Dokter --}}
                                        @if($latestPraKonsul->diagnosa || $latestPraKonsul->rekomendasiItems->isNotEmpty())
                                            <div class="space-y-4 border-t border-[#E8E0D8]/60 mt-4 pt-4">
                                                
                                                {{-- Dokter's General Note --}}
                                                @if($latestPraKonsul->diagnosa)
                                                    <div class="bg-white border border-[#E8E0D8] rounded-xl p-4 shadow-sm relative overflow-hidden group">
                                                        <div class="absolute left-0 top-0 bottom-0 w-1 bg-[#CFAF53] rounded-l-xl"></div>
                                                        <div class="flex items-center gap-2 mb-2 pl-2">
                                                            <svg class="w-4 h-4 text-[#CFAF53]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
                                                            <p class="text-xs font-bold text-[#8C7A63] uppercase tracking-wider">Diagnosa Dokter</p>
                                                        </div>
                                                        <p class="text-sm text-[#1A1A1A] leading-relaxed pl-2">{{ $latestPraKonsul->diagnosa }}</p>
                                                    </div>
                                                @endif
    
                                                {{-- Produk/Treatment Iteration --}}
                                                @if($latestPraKonsul->rekomendasiItems->isNotEmpty())
                                                    @foreach($latestPraKonsul->rekomendasiItems as $item)
                                                        @php 
                                                            $tipe = $item->item_tipe;
                                                            $obj = $tipe === 'Produk' ? $item->product : ($tipe === 'Treatment' ? $item->treatment : null);
                                                        @endphp
                                                        <div class="bg-white border border-[#E8E0D8] rounded-xl p-4 shadow-sm hover:border-[#CFAF53]/40 transition-colors relative overflow-hidden">
                                                            <div class="flex items-center gap-2 mb-4 border-b border-[#E8E0D8]/60 pb-3">
                                                                @if($tipe === 'Treatment')
                                                                    <svg class="w-4 h-4 text-[#CFAF53]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                                    <p class="text-xs font-bold text-[#8C7A63] uppercase tracking-wider">Treatment Rekomendasi</p>
                                                                @else
                                                                    <svg class="w-4 h-4 text-[#CFAF53]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                                                                    <p class="text-xs font-bold text-[#8C7A63] uppercase tracking-wider">Produk Rekomendasi</p>
                                                                @endif
                                                            </div>
    
                                                            <div class="flex flex-col sm:flex-row items-start gap-4">
                                                                {{-- Image --}}
                                                                <div class="w-20 h-20 rounded-lg bg-[#FAF8F5] border border-[#E8E0D8] shrink-0 overflow-hidden flex items-center justify-center">
                                                                    @if($obj && isset($obj->foto_path) && $obj->foto_path)
                                                                        <img src="{{ asset('storage/' . $obj->foto_path) }}" alt="{{ $item->item_nama }}" class="w-full h-full object-cover">
                                                                    @else
                                                                        <svg class="w-8 h-8 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                                                    @endif
                                                                </div>
    
                                                                {{-- Details --}}
                                                                <div class="flex-1 min-w-0 flex flex-col justify-between h-full w-full">
                                                                    <div>
                                                                        <p class="font-bold text-[#1A1A1A] text-base leading-tight mb-1">{{ $item->item_nama }}</p>
                                                                        
                                                                        @if($item->catatan)
                                                                            <p class="text-sm text-slate-600 mb-2">{{ $item->catatan }}</p>
                                                                        @endif
    
                                                                        @if($tipe === 'Treatment' && isset($obj->durasi_menit))
                                                                            <p class="text-xs font-medium text-slate-500 mb-2">{{ $obj->durasi_menit }} menit</p>
                                                                        @endif
                                                                        
                                                                        @if($obj && (isset($obj->harga) || isset($obj->harga_promo) || isset($obj->harga_normal)))
                                                                            <p class="text-sm font-bold text-[#CFAF53]">Rp {{ number_format($obj->harga ?? $obj->harga_promo ?? $obj->harga_normal ?? 0, 0, ',', '.') }}</p>
                                                                        @endif
                                                                    </div>
    
                                                                    {{-- Actions --}}
                                                                    <div class="mt-4 flex flex-wrap items-center gap-2">
                                                                        @if($tipe === 'Treatment')
                                                                            <a href="{{ route('appointment.select') }}" class="inline-flex items-center justify-center px-5 py-2 text-xs font-bold text-white bg-[#1A1A1A] rounded-lg hover:bg-[#333] hover:shadow-lg hover:shadow-[#1A1A1A]/20 transition-all w-full sm:w-auto text-center">Buat Janji Temu</a>
                                                                        @else
                                                                            <form action="{{ route('cart.add', $obj->product_id ?? 0) }}" method="POST" class="inline-block flex-1 sm:flex-none">
                                                                                @csrf
                                                                                <button type="submit" class="w-full inline-flex items-center justify-center px-5 py-2 text-xs font-bold text-[#1A1A1A] bg-[#FAF8F5] border border-[#E8E0D8] rounded-lg hover:bg-white hover:border-[#CFAF53] transition-all shadow-sm">+ Keranjang</button>
                                                                            </form>
                                                                            <a href="{{ route('cart.index') }}" class="inline-flex items-center justify-center px-5 py-2 text-xs font-bold text-white bg-[#1A1A1A] rounded-lg hover:bg-[#333] hover:shadow-lg hover:shadow-[#1A1A1A]/20 transition-all flex-1 sm:flex-none text-center">Beli Sekarang</a>
                                                                        @endif
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                @endif
                                            </div>
                                        @endif

                                        @if($latestPraKonsul->catatan_tambahan)
                                            <div class="bg-white border border-[#E8E0D8] rounded-xl p-4 shadow-sm relative overflow-hidden">
                                                <div class="absolute left-0 top-0 bottom-0 w-1 bg-[#CFAF53] rounded-l-xl"></div>
                                                <div class="flex items-center gap-2 mb-2 pl-2">
                                                    <svg class="w-4 h-4 text-[#CFAF53]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h7m-9 8l3.5-3.5H19a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v8.5a2 2 0 002 2z"/>
                                                    </svg>
                                                    <p class="text-xs font-bold text-[#8C7A63] uppercase tracking-wider">Komentar Dokter</p>
                                                </div>
                                                <p class="text-sm text-[#1A1A1A] leading-relaxed pl-2">{{ $latestPraKonsul->catatan_tambahan }}</p>
                                            </div>
                                        @endif
                                    </div>

                                    {{-- Modal Footer --}}
                                    <div class="p-6 border-t border-[#E8E0D8]/60 bg-slate-50/50 shrink-0">
                                        <button @click="showLatestDetail = false" class="w-full bg-[#1A1A1A] text-white font-bold py-4 rounded-2xl hover:bg-black transition-colors shadow-lg shadow-black/10">
                                            Tutup
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            @endif

            {{-- Card: Pesanan Terbaru --}}
            @if($latestOrder)
                <div class="bg-[#FAF8F5] rounded-2xl border border-[#E8E0D8]/60 p-5">
                    @php
                        // Deteksi apakah order ini terkait janji temu (treatment/konsultasi)
                        $linkedAppointmentId = $latestOrder->items->firstWhere('appointment_id', '!=', null)?->appointment_id;
                        $isAppointmentBased = $linkedAppointmentId
                            || in_array($latestOrder->order_type, ['treatment', 'konsultasi']);

                        $shipIcon = $isAppointmentBased
                            ? '<svg class="w-5 h-5 text-[#CFAF53]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>'
                            : match ($latestOrder->shipping_status) {
                                'dikirim' => '<svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10l2-1 2 1 2-1 2 1m0 0V6a1 1 0 011-1h2a1 1 0 011 1v4l4 2v6h-3m-7 0h7m-7 0a2 2 0 11-4 0m11 0a2 2 0 11-4 0"/></svg>',
                                'selesai' => '<svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>',
                                default => '<svg class="w-5 h-5 text-[#CFAF53]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>',
                            };
                        $orderStatusLabel = match ($latestOrder->status) {
                            'menunggu_pembayaran' => 'Belum Bayar',
                            'sudah_dibayar' => match ($latestOrder->shipping_status) {
                                    'dikirim' => 'Dikirim',
                                    'selesai' => 'Selesai',
                                    default => 'Dikemas',
                                },
                            'batal', 'dibatalkan' => 'Dibatalkan',
                            default => ucfirst($latestOrder->status),
                        };
                        $orderStatusBadge = match ($orderStatusLabel) {
                            'Belum Bayar' => 'bg-amber-50 text-amber-700 border-amber-200',
                            'Dikemas' => 'bg-blue-50 text-blue-700 border-blue-200',
                            'Dikirim' => 'bg-indigo-50 text-indigo-700 border-indigo-200',
                            'Selesai' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                            'Dibatalkan' => 'bg-rose-50 text-rose-700 border-rose-200',
                            default => 'bg-slate-50 text-slate-600 border-slate-200',
                        };
                    @endphp
                    <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4">
                        <div class="flex items-start gap-4 w-full">
                            <div class="w-10 h-10 rounded-xl bg-slate-50 flex items-center justify-center shrink-0">
                                {!! $shipIcon !!}
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex flex-col sm:flex-row sm:items-center gap-1.5 sm:gap-3 mb-1.5">
                                    <h4 class="font-bold text-[#1A1A1A] text-base truncate">{{ $latestOrder->items->first()?->product?->nama ?? $latestOrder->items->first()?->treatment?->nama ?? 'Pesanan Aishi Beauty' }}</h4>
                                    <span class="w-fit shrink-0 px-2.5 py-0.5 rounded-full text-[10px] font-extrabold border tracking-wider {{ $orderStatusBadge }} uppercase">
                                        {{ $orderStatusLabel }}
                                    </span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="text-xs text-slate-400 font-mono">{{ $latestOrder->kode_order }}</span>
                                    <span class="text-slate-300">•</span>
                                    <span class="text-xs text-slate-400">{{ $latestOrder->created_at->translatedFormat('d M Y') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="mt-4 pt-4 border-t border-[#E8E0D8]/40 flex justify-end">
                        @if($isAppointmentBased)
                            <button
                                @click="activeTab = 'appointment'; @if($linkedAppointmentId) highlightId = '{{ $linkedAppointmentId }}'; setTimeout(() => highlightId = null, 4000); @endif"
                                class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-full font-bold text-sm text-[#1A1A1A] bg-white border border-[#E8E0D8] hover:border-[#CFAF53] hover:bg-[#CFAF53]/5 transition-colors shadow-sm cursor-pointer">
                                Lihat Janji Temu
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                                </svg>
                            </button>
                        @else
                            <button @click="activeTab = 'orders'" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-full font-bold text-sm text-[#1A1A1A] bg-white border border-[#E8E0D8] hover:border-[#CFAF53] hover:bg-[#CFAF53]/5 transition-colors shadow-sm cursor-pointer">
                                Lihat Pesanan
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                                </svg>
                            </button>
                        @endif
                    </div>
                </div>
            @endif

            @if(!$latestPraKonsul && !$latestOrder)
                <div class="dash-empty">
                    <div aria-hidden="true" class="dash-empty__orb"></div>
                    <div class="dash-empty__icon">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                        </svg>
                    </div>
                    <p class="dash-empty__title">Belum ada aktivitas terkini</p>
                    <p class="dash-empty__desc">Riwayat konsultasi, pesanan, dan rekam perawatan Anda akan tampil di sini.</p>
                </div>
            @endif
            </div>
        </div>
    </div>
