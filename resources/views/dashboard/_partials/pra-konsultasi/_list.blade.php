{{-- Partial: Pra-Konsultasi List --}}
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
        @php $praCount = $praKonsuls->count(); @endphp
        <div class="prakonsul-grid">
            @foreach($praKonsuls as $form)
                @include('dashboard._partials.pra-konsultasi._card')
            @endforeach
        </div>
    @endif
