<x-guest-layout :title="'Janji Temu Saya'">

<h1 class="text-2xl font-semibold text-slate-900">Janji Temu Saya</h1>

<div class="mt-6 space-y-3">
@forelse ($appointments as $a)
    @php
        $statusLabel = match ($a->status) {
            'akan_datang', 'diproses', 'disetujui' => 'Disetujui',
            'selesai' => 'Selesai',
            'batal' => 'Dibatalkan',
            default => ucfirst(str_replace('_', ' ', (string) $a->status)),
        };
    @endphp
    <div class="rounded-xl border border-slate-200 bg-white p-4">
        <p class="font-medium">
            {{ ucfirst($a->jenis) }} —
            {{ $a->tanggal_jam->format('d M Y H:i') }}
        </p>
        <p class="text-sm text-slate-600">
            Status: {{ $statusLabel }}
        </p>
    </div>
@empty
    <div class="rounded-xl border border-slate-200 bg-slate-50 p-4 text-slate-600">
        Belum ada janji temu.
    </div>
@endforelse
</div>

</x-guest-layout>
