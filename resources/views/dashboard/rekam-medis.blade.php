{{-- ====== TAB: REKAM MEDIS ====== --}}
<div class="bg-white rounded-2xl border border-[#E8E0D8]/60 shadow-sm p-6" x-data="{ rmSearch: '', lightboxUrl: '' }">

    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
            <h2 class="text-xl font-extrabold text-[#1A1A1A]">Rekam Medis Saya</h2>
            <p class="text-sm text-slate-500 mt-1">Lihat ringkasan layanan, hasil pemeriksaan, dan rekomendasi dokter dari setiap kunjungan Anda.</p>
        </div>
        <span class="text-sm text-slate-400 self-start sm:self-auto shrink-0">
            <span class="font-bold text-[#1A1A1A]">{{ $rekamMedis->count() }}</span> Kunjungan
        </span>
    </div>

    {{-- Search --}}
    <div class="relative mb-6">
        <svg class="w-4 h-4 text-slate-400 absolute left-4 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
        <input x-model="rmSearch"
               type="text"
               placeholder="Cari berdasarkan diagnosa atau layanan..."
               class="w-full pl-11 pr-4 py-3 rounded-xl bg-white border border-[#E8E0D8]/60 text-sm focus:border-[#CFAF53] focus:ring-[#CFAF53]/20 focus:ring-2 outline-none transition">
    </div>

    @if($rekamMedis->isEmpty())
        <div class="bg-[#FAF8F5] rounded-2xl border border-dashed border-[#E8E0D8] p-12 text-center">
            <div class="w-16 h-16 bg-emerald-50 rounded-full flex items-center justify-center mb-4 mx-auto">
                <svg class="w-8 h-8 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            </div>
            <h3 class="font-bold text-[#1A1A1A]">Belum ada rekam medis</h3>
            <p class="text-sm text-slate-400 mt-1">Riwayat akan muncul setelah kunjungan pertama Anda.</p>
        </div>
    @else
        <div class="space-y-4">
            @foreach($rekamMedis as $rm)
                @php
                    /* ── Layanan: ambil dari appointment items, pisahkan
                          consultation vs treatment/bundle, dedup nama. */
                    $items = $rm->appointment?->items ?? collect();

                    $consultationNames = $items
                        ->filter(fn ($i) => strtolower((string) $i->item_type) === 'consultation')
                        ->map(fn ($i) => $i->consultation?->nama)
                        ->filter()
                        ->unique()
                        ->values();

                    $treatmentNames = $items
                        ->filter(fn ($i) => in_array(strtolower((string) $i->item_type), ['treatment', 'bundle'], true))
                        ->map(fn ($i) => $i->item_type === 'bundle'
                            ? 'Bundle: ' . ($i->bundle?->nama ?? '')
                            : $i->treatment?->nama
                        )
                        ->filter()
                        ->unique()
                        ->values();

                    /* ── Produk rekomendasi: pecah jadi list kalau dipisah koma. */
                    $produkList = collect(explode(',', (string) ($rm->produk_rekomendasi ?? '')))
                        ->map(fn ($s) => trim($s))
                        ->filter()
                        ->values();

                    $searchableText = strtolower(
                        ($rm->diagnosa ?? '') . ' ' .
                        ($rm->tindakan ?? '') . ' ' .
                        ($rm->keluhan ?? '') . ' ' .
                        $treatmentNames->implode(', ') . ' ' .
                        $consultationNames->implode(', ') . ' ' .
                        $produkList->implode(', ')
                    );

                    $tanggalKunjungan = $rm->appointment?->tanggal_jam
                        ? \Illuminate\Support\Carbon::parse($rm->appointment->tanggal_jam)->translatedFormat('d F Y')
                        : $rm->created_at->translatedFormat('d F Y');

                    $fotoSebelum = $rm->foto_sebelum_path ? asset('storage/' . $rm->foto_sebelum_path) : null;
                    $fotoSesudah = $rm->foto_sesudah_path ? asset('storage/' . $rm->foto_sesudah_path) : null;
                @endphp

                <div x-data="{ expanded: false }"
                     x-show="!rmSearch.trim() || '{{ addslashes($searchableText) }}'.includes(rmSearch.toLowerCase())"
                     class="bg-white rounded-2xl border border-[#E8E0D8]/70 overflow-hidden">

                    {{-- ─── 1. HEADER REKAM MEDIS ─── --}}
                    <div class="p-5 sm:p-6">
                        <div class="flex items-start justify-between gap-4 flex-wrap">
                            <div class="flex items-start gap-3 min-w-0">
                                <div class="w-11 h-11 rounded-xl bg-[#FAF8F5] border border-[#E8E0D8] flex items-center justify-center shrink-0">
                                    <svg class="w-5 h-5 text-[#CFAF53]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                                </div>
                                <div class="min-w-0">
                                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Rekam Medis</p>
                                    <p class="text-base font-extrabold text-[#1A1A1A] font-mono mt-0.5">{{ $rm->no_kunjungan }}</p>
                                    <div class="flex flex-wrap items-center gap-x-3 gap-y-1 mt-1.5 text-xs text-slate-500">
                                        <span class="inline-flex items-center gap-1.5">
                                            <svg class="w-3.5 h-3.5 text-[#8C7A63]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                            {{ $tanggalKunjungan }}
                                        </span>
                                        @if($rm->dokter)
                                            <span class="text-slate-300">·</span>
                                            <span class="inline-flex items-center gap-1.5">
                                                <svg class="w-3.5 h-3.5 text-[#8C7A63]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                                <span class="font-medium text-[#1A1A1A]">{{ $rm->dokter->name }}</span>
                                            </span>
                                        @endif
                                        @if($rm->terapis)
                                            <span class="text-slate-300">·</span>
                                            <span class="inline-flex items-center gap-1.5">
                                                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Terapis</span>
                                                <span class="font-medium text-[#1A1A1A]">{{ $rm->terapis->name }}</span>
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            {{-- Toggle Detail --}}
                            <button @click="expanded = !expanded" class="text-sm font-bold text-[#CFAF53] hover:text-[#b09444] transition-colors inline-flex items-center gap-1 cursor-pointer self-start">
                                <span x-text="expanded ? 'Tutup Detail' : 'Lihat Detail'"></span>
                                <svg :class="expanded ? 'rotate-180' : ''" class="w-4 h-4 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </button>
                        </div>

                        {{-- ─── 2. RINGKASAN LAYANAN (chips) ─── --}}
                        @if($consultationNames->isNotEmpty() || $treatmentNames->isNotEmpty())
                            <div class="mt-5 pt-5 border-t border-[#E8E0D8]/60">
                                <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-2.5">Layanan yang Dilakukan</p>
                                <div class="flex flex-wrap gap-2">
                                    @foreach($consultationNames as $name)
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-blue-50 text-blue-700 text-xs font-semibold border border-blue-100">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                                            {{ $name }}
                                        </span>
                                    @endforeach
                                    @foreach($treatmentNames as $name)
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-[#FAF8F5] text-[#1A1A1A] text-xs font-semibold border border-[#E8E0D8]">
                                            <svg class="w-3 h-3 text-[#CFAF53]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                            {{ $name }}
                                        </span>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>

                    {{-- ─── EXPANDED DETAIL ─── --}}
                    <div x-show="expanded" x-collapse>
                        <div class="px-5 sm:px-6 pb-6 space-y-5 border-t border-[#E8E0D8]/60 pt-5">

                            {{-- 3. HASIL PEMERIKSAAN --}}
                            @if($rm->keluhan || $rm->diagnosa || $rm->tindakan)
                                <div class="bg-[#FAF8F5] border border-[#E8E0D8]/70 rounded-xl p-4 sm:p-5">
                                    <p class="text-[10px] font-bold text-[#CFAF53] uppercase tracking-widest mb-3">Hasil Pemeriksaan</p>
                                    <div class="space-y-3">
                                        @if($rm->keluhan)
                                            <div>
                                                <p class="text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">Keluhan</p>
                                                <p class="text-sm text-[#1A1A1A] leading-relaxed">{{ $rm->keluhan }}</p>
                                            </div>
                                        @endif
                                        @if($rm->diagnosa)
                                            <div>
                                                <p class="text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">Diagnosa</p>
                                                <p class="text-sm text-[#1A1A1A] leading-relaxed">{{ $rm->diagnosa }}</p>
                                            </div>
                                        @endif
                                        @if($rm->tindakan)
                                            <div>
                                                <p class="text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">Tindakan</p>
                                                <p class="text-sm text-[#1A1A1A] leading-relaxed">{{ $rm->tindakan }}</p>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endif

                            {{-- 4. PRODUK REKOMENDASI --}}
                            <div>
                                <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-2.5">Produk Rekomendasi</p>
                                @if($produkList->isNotEmpty())
                                    <div class="flex flex-wrap gap-2">
                                        @foreach($produkList as $produk)
                                            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-amber-50 text-amber-800 text-xs font-semibold border border-amber-100">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                                                {{ $produk }}
                                            </span>
                                        @endforeach
                                    </div>
                                @else
                                    <p class="text-sm text-slate-400 italic">Tidak ada produk rekomendasi.</p>
                                @endif
                            </div>

                            {{-- 5. CATATAN LANJUTAN --}}
                            @if($rm->catatan_lanjutan)
                                <div class="bg-blue-50/60 border-l-2 border-blue-300 rounded-r-lg pl-4 pr-4 py-3">
                                    <p class="text-[10px] font-bold text-blue-700 uppercase tracking-widest mb-1">Catatan Lanjutan</p>
                                    <p class="text-sm text-blue-900 leading-relaxed">{{ $rm->catatan_lanjutan }}</p>
                                </div>
                            @endif

                            {{-- 6. DOKUMENTASI FOTO --}}
                            @if($fotoSebelum || $fotoSesudah)
                                <div>
                                    <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-2.5">Dokumentasi Foto</p>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                        @if($fotoSebelum)
                                            <button type="button" @click="lightboxUrl = '{{ $fotoSebelum }}'" class="group relative block rounded-xl overflow-hidden border border-[#E8E0D8] aspect-[4/3] bg-slate-100 cursor-pointer">
                                                <img src="{{ $fotoSebelum }}" alt="Foto sebelum" class="w-full h-full object-cover transition-transform group-hover:scale-105">
                                                <span class="absolute top-2 left-2 px-2 py-0.5 rounded-md bg-black/60 text-white text-[10px] font-bold uppercase tracking-wider">Sebelum</span>
                                            </button>
                                        @endif
                                        @if($fotoSesudah)
                                            <button type="button" @click="lightboxUrl = '{{ $fotoSesudah }}'" class="group relative block rounded-xl overflow-hidden border border-[#E8E0D8] aspect-[4/3] bg-slate-100 cursor-pointer">
                                                <img src="{{ $fotoSesudah }}" alt="Foto sesudah" class="w-full h-full object-cover transition-transform group-hover:scale-105">
                                                <span class="absolute top-2 left-2 px-2 py-0.5 rounded-md bg-black/60 text-white text-[10px] font-bold uppercase tracking-wider">Sesudah</span>
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- LIGHTBOX FOTO (shared, single instance) --}}
        <div x-show="lightboxUrl"
             x-cloak
             @keydown.escape.window="lightboxUrl = ''"
             class="fixed inset-0 z-[10000] flex items-center justify-center bg-black/80 backdrop-blur-sm p-4"
             @click="lightboxUrl = ''"
             x-transition:enter="ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0">
            <div class="relative max-w-4xl w-full max-h-[90vh]" @click.stop>
                <button type="button"
                        @click="lightboxUrl = ''"
                        class="absolute -top-12 right-0 w-10 h-10 rounded-full bg-white/10 hover:bg-white/20 text-white flex items-center justify-center transition-colors cursor-pointer"
                        aria-label="Tutup foto">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
                <img :src="lightboxUrl" alt="" class="w-full h-full object-contain rounded-2xl shadow-2xl max-h-[90vh]">
            </div>
        </div>
    @endif
</div>
