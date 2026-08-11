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
                @include('dashboard._partials.rekam-medis._rm_card')
            @endforeach
        </div>

        @include('dashboard._partials.rekam-medis._rm_lightbox')
    @endif
</div>
