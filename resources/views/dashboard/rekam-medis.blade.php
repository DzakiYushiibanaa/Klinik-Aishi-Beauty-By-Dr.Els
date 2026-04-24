{{-- ====== TAB: REKAM MEDIS ====== --}}
<div class="bg-white rounded-2xl border border-[#E8E0D8]/60 shadow-sm p-6" x-data="{ rmSearch: '' }">

    <div class="flex items-center justify-between mb-6">
        <h2 class="text-xl font-extrabold text-[#1A1A1A]">Rekam Medis Saya</h2>
        <span class="text-sm text-slate-400"><span class="font-bold text-[#1A1A1A]">{{ $rekamMedis->count() }}</span> Kunjungan</span>
    </div>

    {{-- Search --}}
    <div class="relative mb-6">
        <svg class="w-4 h-4 text-slate-400 absolute left-4 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
        <input x-model="rmSearch" type="text" placeholder="Cari berdasarkan diagnosa atau tindakan..." class="w-full pl-11 pr-4 py-3 rounded-xl bg-white border border-[#E8E0D8]/60 text-sm focus:border-[#CFAF53] focus:ring-[#CFAF53]/20 focus:ring-2 outline-none transition">
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
                    $rmStatusClass = match($rm->status_tindakan) {
                        'dilakukan' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                        'direncanakan' => 'bg-blue-50 text-blue-700 border-blue-200',
                        'ditunda' => 'bg-amber-50 text-amber-700 border-amber-200',
                        'dibatalkan' => 'bg-rose-50 text-rose-700 border-rose-200',
                        default => 'bg-slate-50 text-slate-600 border-slate-200',
                    };
                    $treatmentNames = $rm->appointment?->items?->map(fn($i) => $i->treatment?->nama)->filter()->implode(', ') ?? '';
                    $searchableText = strtolower(($rm->diagnosa ?? '') . ' ' . ($rm->tindakan ?? '') . ' ' . ($rm->keluhan ?? '') . ' ' . $treatmentNames);
                @endphp

                <div x-data="{ expanded: false }"
                     x-show="!rmSearch.trim() || '{{ addslashes($searchableText) }}'.includes(rmSearch.toLowerCase())"
                     class="bg-[#FAF8F5] rounded-2xl border border-[#E8E0D8]/60 overflow-hidden">

                    {{-- Card Header --}}
                    <div class="p-5">
                        <div class="flex items-start justify-between gap-3 mb-3">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-emerald-50 flex items-center justify-center shrink-0">
                                    <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                                </div>
                                <div>
                                    <p class="font-mono text-xs text-slate-400">{{ $rm->no_kunjungan }}</p>
                                    <p class="text-sm font-bold text-[#1A1A1A]">{{ $rm->created_at->translatedFormat('d F Y') }}</p>
                                </div>
                            </div>
                            <span class="px-3 py-1 rounded-full text-[10px] font-bold border {{ $rmStatusClass }} uppercase shrink-0">{{ ucfirst($rm->status_tindakan) }}</span>
                        </div>

                        {{-- Dokter & Terapis --}}
                        <div class="flex flex-wrap gap-4 text-sm mb-3">
                            @if($rm->dokter)
                                <div class="flex items-center gap-2">
                                    <div class="w-7 h-7 rounded-full bg-blue-50 flex items-center justify-center">
                                        <svg class="w-3.5 h-3.5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                    </div>
                                    <div><p class="text-[10px] text-slate-400">Dokter</p><p class="font-bold text-[#1A1A1A] text-xs">{{ $rm->dokter->name }}</p></div>
                                </div>
                            @endif
                            @if($rm->terapis)
                                <div class="flex items-center gap-2">
                                    <div class="w-7 h-7 rounded-full bg-purple-50 flex items-center justify-center">
                                        <svg class="w-3.5 h-3.5 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                    </div>
                                    <div><p class="text-[10px] text-slate-400">Terapis</p><p class="font-bold text-[#1A1A1A] text-xs">{{ $rm->terapis->name }}</p></div>
                                </div>
                            @endif
                        </div>

                        {{-- Summary --}}
                        <div class="space-y-2">
                            @if($rm->keluhan)
                                <div class="flex gap-2"><span class="text-xs font-bold text-slate-400 w-16 shrink-0">Keluhan</span><p class="text-sm text-slate-600 line-clamp-1">{{ $rm->keluhan }}</p></div>
                            @endif
                            @if($rm->diagnosa)
                                <div class="flex gap-2"><span class="text-xs font-bold text-slate-400 w-16 shrink-0">Diagnosa</span><p class="text-sm text-slate-600 line-clamp-1">{{ $rm->diagnosa }}</p></div>
                            @endif
                            @if($rm->tindakan)
                                <div class="flex gap-2"><span class="text-xs font-bold text-slate-400 w-16 shrink-0">Tindakan</span><p class="text-sm text-slate-600 line-clamp-1">{{ $rm->tindakan }}</p></div>
                            @endif
                        </div>

                        {{-- Expand Button --}}
                        <button @click="expanded = !expanded" class="mt-4 flex items-center gap-1.5 text-sm font-bold text-[#CFAF53] hover:text-[#8C7A63] transition-colors">
                            <svg :class="expanded ? 'rotate-180' : ''" class="w-4 h-4 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            <span x-text="expanded ? 'Tutup Detail' : 'Lihat Detail'"></span>
                        </button>
                    </div>

                    {{-- Expanded Detail --}}
                    <div x-show="expanded" x-collapse>
                        <div class="px-5 pb-5 pt-0 space-y-4 border-t border-[#E8E0D8]/40 mt-0 pt-4">
                            @if($rm->produk_rekomendasi)
                                <div class="bg-amber-50/50 border border-amber-100 rounded-xl p-4">
                                    <p class="text-xs font-bold text-amber-700 mb-1">💊 Produk Rekomendasi</p>
                                    <p class="text-sm text-amber-900">{{ $rm->produk_rekomendasi }}</p>
                                </div>
                            @endif
                            @if($rm->catatan_lanjutan)
                                <div class="bg-blue-50/50 border border-blue-100 rounded-xl p-4">
                                    <p class="text-xs font-bold text-blue-700 mb-1">📝 Catatan Lanjutan</p>
                                    <p class="text-sm text-blue-900">{{ $rm->catatan_lanjutan }}</p>
                                </div>
                            @endif
                            @if($rm->foto_sebelum_path || $rm->foto_sesudah_path)
                                <div class="grid grid-cols-2 gap-4">
                                    @if($rm->foto_sebelum_path)
                                        <div>
                                            <p class="text-xs font-bold text-slate-400 mb-2">📷 Sebelum</p>
                                            <div class="rounded-xl overflow-hidden border border-[#E8E0D8]/60 aspect-square bg-slate-50">
                                                <img src="{{ asset('storage/' . $rm->foto_sebelum_path) }}" alt="Sebelum" class="w-full h-full object-cover">
                                            </div>
                                        </div>
                                    @endif
                                    @if($rm->foto_sesudah_path)
                                        <div>
                                            <p class="text-xs font-bold text-slate-400 mb-2">📷 Sesudah</p>
                                            <div class="rounded-xl overflow-hidden border border-[#E8E0D8]/60 aspect-square bg-slate-50">
                                                <img src="{{ asset('storage/' . $rm->foto_sesudah_path) }}" alt="Sesudah" class="w-full h-full object-cover">
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            @endif
                            @if($treatmentNames)
                                <div>
                                    <p class="text-xs font-bold text-slate-400 mb-1">Treatment</p>
                                    <p class="text-sm text-[#1A1A1A] font-medium">{{ $treatmentNames }}</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
