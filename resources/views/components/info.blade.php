@props(['label', 'value' => null])

<div class="rounded-xl border border-slate-200 bg-slate-50 p-3">
    <p class="text-slate-500 text-sm">{{ $label }}</p>
    <p class="mt-1 font-medium text-slate-800">
        {{ $value ?? '-' }}
    </p>
</div>
