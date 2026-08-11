{{-- Partial: List View --}}
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
                @include('dashboard._partials.appointments._appointment_card')
            @endforeach
        </div>
    @endif
</div>
