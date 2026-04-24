{{-- ====== TAB: PRA-KONSULTASI ====== --}}
<div class="bg-white rounded-2xl border border-[#E8E0D8]/60 shadow-sm p-6">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-xl font-extrabold text-[#1A1A1A]">Riwayat Pra-Konsultasi</h2>
        <a href="{{ route('pra.create') }}" class="inline-flex items-center gap-2 bg-[#CFAF53] text-[#1A1A1A] font-bold px-5 py-2.5 rounded-full text-sm hover:bg-[#b59b48] transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Buat Baru
        </a>
    </div>

    @if($praKonsuls->isEmpty())
        <div class="bg-[#FAF8F5] rounded-2xl border border-dashed border-[#E8E0D8] p-12 text-center">
            <div class="w-16 h-16 bg-[#CFAF53]/10 rounded-full flex items-center justify-center mb-4 mx-auto">
                <svg class="w-8 h-8 text-[#CFAF53]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
            </div>
            <h3 class="font-bold text-[#1A1A1A]">Belum ada pra-konsultasi</h3>
            <p class="text-sm text-slate-400 mt-1">Isi formulir untuk konsultasi awal dengan dokter.</p>
            <a href="{{ route('pra.create') }}" class="mt-4 inline-block text-sm font-bold text-[#CFAF53]">Isi Formulir Sekarang →</a>
        </div>
    @else
        <div class="space-y-4">
            @foreach($praKonsuls as $form)
                @php
                    $praStatusClass = match(strtolower($form->status ?? 'submitted')) {
                        'submitted', 'menunggu' => 'bg-amber-50 text-amber-700 border-amber-200',
                        'reviewed', 'selesai' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                        'in_review', 'diproses' => 'bg-blue-50 text-blue-700 border-blue-200',
                        default => 'bg-slate-50 text-slate-600 border-slate-200',
                    };
                    $yesNoLabel = fn($val) => $val ? 'Ya' : 'Tidak';
                @endphp
                <div x-data="{ showModal: false }" class="bg-[#FAF8F5] rounded-2xl border border-[#E8E0D8]/60 p-5">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="text-xs text-slate-400">ID: #PK-{{ $form->patient_form_id }} • {{ $form->created_at->translatedFormat('d M Y') }}</p>
                            <p class="font-bold text-[#1A1A1A] mt-1 line-clamp-1">{{ $form->keluhan_utama ?? 'Tidak ada judul' }}</p>
                        </div>
                        <span class="shrink-0 px-3 py-1 rounded-full text-[10px] font-bold border {{ $praStatusClass }} uppercase">{{ $form->status ?? 'Submitted' }}</span>
                    </div>
                    <div class="mt-4 pt-3 border-t border-[#E8E0D8]/40 flex items-center justify-between">
                        <p class="text-xs text-slate-400">Dokter: <span class="text-slate-600 font-medium">{{ $form->dokter->name ?? 'Menunggu...' }}</span></p>
                        <button @click="showModal = true" class="text-sm font-bold text-[#CFAF53] hover:text-[#8C7A63] transition-colors">Lihat Detail →</button>
                    </div>

                    {{-- ══════ MODAL DETAIL PRA-KONSULTASI ══════ --}}
                    <template x-teleport="body">
                        <div x-show="showModal" style="display:none"
                             class="fixed inset-0 z-[9999] flex items-end sm:items-center justify-center bg-black/50 backdrop-blur-sm"
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0"
                             x-transition:enter-end="opacity-100"
                             x-transition:leave="transition ease-in duration-150"
                             x-transition:leave-start="opacity-100"
                             x-transition:leave-end="opacity-0">

                            <div class="bg-white w-full sm:max-w-xl sm:rounded-2xl rounded-t-2xl max-h-[90vh] sm:max-h-[85vh] shadow-2xl overflow-hidden flex flex-col"
                                 @click.away="showModal = false"
                                 x-transition:enter="transition ease-out duration-200"
                                 x-transition:enter-start="translate-y-full sm:translate-y-0 sm:scale-95"
                                 x-transition:enter-end="translate-y-0 sm:scale-100"
                                 x-transition:leave="transition ease-in duration-150"
                                 x-transition:leave-start="translate-y-0 sm:scale-100"
                                 x-transition:leave-end="translate-y-full sm:translate-y-0 sm:scale-95">

                                {{-- Drag Handle (Mobile) --}}
                                <div class="sm:hidden flex justify-center pt-3 pb-1 shrink-0">
                                    <div class="w-10 h-1 rounded-full bg-slate-300"></div>
                                </div>

                                {{-- Header --}}
                                <div class="flex items-center justify-between px-5 sm:px-6 py-4 border-b border-[#E8E0D8]/40 shrink-0">
                                    <div>
                                        <h3 class="text-base sm:text-lg font-extrabold text-[#1A1A1A]">Detail Pra-Konsultasi</h3>
                                        <p class="text-[11px] text-slate-400 mt-0.5">#PK-{{ $form->patient_form_id }} • {{ $form->created_at->translatedFormat('d F Y, H:i') }} WIB</p>
                                    </div>
                                    <button @click="showModal = false" class="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center hover:bg-slate-200 transition shrink-0">
                                        <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </button>
                                </div>

                                {{-- Body --}}
                                <div class="overflow-y-auto flex-1 px-5 sm:px-6 py-5 space-y-5">

                                    {{-- Status & Dokter --}}
                                    <div class="flex flex-wrap items-center gap-2">
                                        <span class="px-3 py-1 rounded-full text-[10px] font-bold border {{ $praStatusClass }} uppercase">{{ $form->status ?? 'Submitted' }}</span>
                                        <span class="text-xs text-slate-400">Tipe: <strong class="text-slate-600">{{ ucfirst($form->tipe_form ?? '-') }}</strong></span>
                                        @if($form->dokter)
                                            <span class="text-xs text-slate-400">• Dokter: <strong class="text-[#1A1A1A]">{{ $form->dokter->name }}</strong></span>
                                        @endif
                                    </div>

                                    {{-- Keluhan Utama --}}
                                    @if($form->keluhan_utama)
                                        <div class="bg-[#FAF8F5] rounded-xl p-4 border border-[#E8E0D8]/40">
                                            <p class="text-[10px] font-bold text-[#8C7A63] uppercase tracking-wider mb-1.5">Keluhan Utama</p>
                                            <p class="text-sm text-[#1A1A1A] leading-relaxed">{{ $form->keluhan_utama }}</p>
                                        </div>
                                    @endif

                                    {{-- Grid: Info Kulit --}}
                                    <div>
                                        <p class="text-[10px] font-bold text-[#8C7A63] uppercase tracking-wider mb-3">Informasi Kulit & Riwayat</p>
                                        <div class="grid grid-cols-2 gap-3">
                                            <div class="bg-[#FAF8F5] rounded-xl p-3 border border-[#E8E0D8]/30">
                                                <p class="text-[10px] text-slate-400 font-bold">Golongan Darah</p>
                                                <p class="text-sm font-bold text-[#1A1A1A] mt-0.5">{{ $form->golongan_darah ?? '-' }}</p>
                                            </div>
                                            <div class="bg-[#FAF8F5] rounded-xl p-3 border border-[#E8E0D8]/30">
                                                <p class="text-[10px] text-slate-400 font-bold">Jenis Kulit</p>
                                                <p class="text-sm font-bold text-[#1A1A1A] mt-0.5">{{ ucfirst($form->jenis_kulit ?? '-') }}</p>
                                            </div>
                                            <div class="bg-[#FAF8F5] rounded-xl p-3 border border-[#E8E0D8]/30 col-span-2">
                                                <p class="text-[10px] text-slate-400 font-bold">Skincare Sebelumnya</p>
                                                <p class="text-sm text-[#1A1A1A] mt-0.5">{{ $form->skincare_sebelumnya ?? '-' }}</p>
                                                @if($form->periode_skincare_bulan)
                                                    <p class="text-xs text-slate-400 mt-1">Periode: {{ $form->periode_skincare_bulan }} bulan</p>
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Riwayat Penyakit --}}
                                    @if($form->riwayat_penyakit)
                                        <div>
                                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Riwayat Penyakit</p>
                                            <p class="text-sm text-slate-600 leading-relaxed">{{ $form->riwayat_penyakit }}</p>
                                        </div>
                                    @endif

                                    {{-- Riwayat Perawatan --}}
                                    <div>
                                        <p class="text-[10px] font-bold text-[#8C7A63] uppercase tracking-wider mb-3">Riwayat Perawatan</p>
                                        <div class="grid grid-cols-2 gap-2">
                                            @php
                                                $treatments = [
                                                    ['label' => 'Facial', 'val' => $form->pernah_facial],
                                                    ['label' => 'Skin Booster', 'val' => $form->pernah_skin_booster],
                                                    ['label' => 'Laser', 'val' => $form->pernah_laser],
                                                    ['label' => 'Inject/Infus', 'val' => $form->pernah_inject_infus],
                                                ];
                                            @endphp
                                            @foreach($treatments as $t)
                                                <div class="flex items-center gap-2 px-3 py-2 rounded-lg {{ $t['val'] ? 'bg-emerald-50 border border-emerald-100' : 'bg-slate-50 border border-slate-100' }}">
                                                    @if($t['val'])
                                                        <svg class="w-4 h-4 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                    @else
                                                        <svg class="w-4 h-4 text-slate-300 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                    @endif
                                                    <span class="text-xs font-bold {{ $t['val'] ? 'text-emerald-700' : 'text-slate-400' }}">{{ $t['label'] }}</span>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>

                                    {{-- Foto Wajah --}}
                                    @if($form->foto_depan_path || $form->foto_kiri_path || $form->foto_kanan_path)
                                        <div>
                                            <p class="text-[10px] font-bold text-[#8C7A63] uppercase tracking-wider mb-3">Foto Wajah</p>
                                            <div class="grid grid-cols-3 gap-2">
                                                @foreach(['foto_depan_path' => 'Depan', 'foto_kiri_path' => 'Kiri', 'foto_kanan_path' => 'Kanan'] as $field => $label)
                                                    @if($form->$field)
                                                        <div class="space-y-1">
                                                            <div class="rounded-xl overflow-hidden border border-[#E8E0D8]/60 bg-slate-50 aspect-square">
                                                                <img src="{{ asset('storage/' . $form->$field) }}" alt="Foto {{ $label }}" class="w-full h-full object-cover">
                                                            </div>
                                                            <p class="text-[10px] text-slate-400 text-center font-bold">{{ $label }}</p>
                                                        </div>
                                                    @else
                                                        <div class="rounded-xl border border-dashed border-[#E8E0D8] bg-[#FAF8F5] aspect-square flex flex-col items-center justify-center">
                                                            <svg class="w-5 h-5 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                                            <p class="text-[9px] text-slate-300 mt-1">{{ $label }}</p>
                                                        </div>
                                                    @endif
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif

                                    {{-- Rekomendasi Dokter --}}
                                    @if($form->rekomendasi_dokter || $form->rekomendasiItems->isNotEmpty())
                                        <div class="bg-blue-50 border border-blue-100 rounded-xl p-4">
                                            <p class="text-[10px] font-bold text-blue-700 uppercase tracking-wider mb-2">💊 Rekomendasi Dokter</p>
                                            @if($form->rekomendasi_dokter)
                                                <p class="text-sm text-blue-900 leading-relaxed mb-3">{{ $form->rekomendasi_dokter }}</p>
                                            @endif

                                            {{-- Daftar Item Rekomendasi --}}
                                            @if($form->rekomendasiItems->isNotEmpty())
                                                <div class="space-y-2 mt-2">
                                                    @php
                                                        $tipeColor = [
                                                            'Produk' => 'bg-amber-100 text-amber-700',
                                                            'Treatment' => 'bg-purple-100 text-purple-700',
                                                            'Bundle' => 'bg-emerald-100 text-emerald-700',
                                                        ];
                                                    @endphp
                                                    @foreach($form->rekomendasiItems as $item)
                                                        @php $tipe = $item->item_tipe; @endphp
                                                        <div class="flex items-start gap-3 bg-white rounded-lg p-3 border border-blue-100">
                                                            <span class="shrink-0 px-2 py-0.5 rounded-full text-[10px] font-bold {{ $tipeColor[$tipe] ?? 'bg-slate-100 text-slate-600' }}">{{ $tipe }}</span>
                                                            <div class="flex-1 min-w-0">
                                                                <p class="text-sm font-bold text-[#1A1A1A] leading-tight">{{ $item->item_nama }}</p>
                                                                @if($item->catatan)
                                                                    <p class="text-xs text-slate-500 mt-0.5">{{ $item->catatan }}</p>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>
                                    @endif
                                </div>

                                {{-- Footer --}}
                                <div class="px-5 sm:px-6 py-4 border-t border-[#E8E0D8]/40 shrink-0">
                                    <button @click="showModal = false" class="w-full py-3 rounded-xl bg-[#1A1A1A] text-white text-sm font-bold hover:bg-[#333] transition">Tutup</button>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
            @endforeach
        </div>
    @endif
</div>
