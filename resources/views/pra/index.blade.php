<x-guest-layout :title="'Pra Konsul'">
    <div class="rounded-3xl bg-gradient-to-br from-[#CFAF53]/15 via-white to-[#CFAF53]/10 p-5 sm:p-8 lg:p-10">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="text-center sm:text-left">
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-[#CFAF53]">Riwayat</p>
                <h1 class="text-2xl font-semibold text-slate-900 sm:text-3xl">Pra Konsultasi</h1>
                <p class="text-sm text-slate-600 mt-1 sm:text-base">Riwayat pengisian pra-konsultasi kamu.</p>
            </div>
            <a href="{{ route('pra.create') }}"
               class="inline-flex w-full items-center justify-center rounded-xl bg-[#CFAF53] px-4 py-2 text-sm font-semibold text-zinc-900 shadow-sm transition hover:bg-[#CFAF53]/90 focus:outline-none focus:ring-2 focus:ring-[#CFAF53]/30 sm:w-auto">
                Buat Baru
            </a>
        </div>

        <div class="mt-6 space-y-4">
            @forelse($forms as $f)
                @php($no = $formNumberMap[$f->patient_form_id] ?? $f->patient_form_id)

                <a href="{{ route('pra.show', $f) }}"
                   class="block rounded-2xl border border-slate-200 bg-white p-4 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md sm:p-5">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                        <div>
                            <p class="font-semibold text-slate-900">Form #{{ $no }}</p>
                            <p class="text-sm text-slate-600 mt-1">
                                Status:
                                <span class="font-medium text-slate-800">{{ $f->status }}</span>
                                <span class="mx-2 text-slate-300">|</span>
                                {{ $f->created_at->format('d M Y H:i') }}
                            </p>
                        </div>

                        <span class="shrink-0 rounded-full border border-[#CFAF53]/20 bg-[#CFAF53]/10 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-[#8B6F1B]">
                            Lihat Detail
                        </span>
                    </div>

                    <p class="mt-3 text-sm text-slate-700 line-clamp-2">
                        {{ $f->keluhan_utama }}
                    </p>
                </a>
            @empty
                <div class="rounded-2xl border border-dashed border-slate-200 bg-white p-5 text-slate-600 shadow-sm sm:p-6">
                    Belum ada pra-konsultasi. Klik "Buat Baru" untuk mulai.
                </div>
            @endforelse
        </div>

        <div class="mt-6">
            {{ $forms->links() }}
        </div>
    </div>
</x-guest-layout>
