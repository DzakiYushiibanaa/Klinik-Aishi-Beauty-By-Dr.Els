@props(['label', 'value' => false])

<div class="rounded-xl border border-slate-200 bg-white p-3 flex items-center justify-between">
    <span class="text-slate-700">{{ $label }}</span>
    <span class="font-medium text-slate-800">
        {{ $value ? 'Ya' : 'Tidak' }}
    </span>
</div>
