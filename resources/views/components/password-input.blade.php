@props([
    'name'         => 'password',
    'id'           => null,
    'placeholder'  => '••••••••',
    'autocomplete' => 'current-password',
    'required'     => true,
    'autofocus'    => false,
    'value'        => null,
    'variant'      => 'auth',  // 'auth' (slate-50 bg) atau 'compact' (white bg, lebih kecil)
])

@php
    $id ??= $name;

    $base = match ($variant) {
        'compact' => 'w-full rounded-xl border border-[#E8E0D8]/60 bg-white pl-4 pr-12 py-2.5 text-sm text-[#1A1A1A] placeholder-slate-400 focus:border-[#CFAF53] focus:ring-2 focus:ring-[#CFAF53]/20 transition-all',
        default   => 'w-full rounded-xl border border-slate-200 bg-slate-50 pl-4 pr-12 py-3.5 text-sm text-slate-900 placeholder-slate-400 focus:border-[#CFAF53] focus:bg-white focus:ring-2 focus:ring-[#CFAF53]/20 transition-all',
    };
@endphp

<div class="relative" x-data="{ show: false }">
    <input
        :type="show ? 'text' : 'password'"
        id="{{ $id }}"
        name="{{ $name }}"
        @if($required) required @endif
        @if($autofocus) autofocus @endif
        autocomplete="{{ $autocomplete }}"
        @if(!is_null($value)) value="{{ $value }}" @endif
        placeholder="{{ $placeholder }}"
        {{ $attributes->class([$base]) }}>

    {{-- Toggle button --}}
    <button type="button"
            @click="show = !show"
            tabindex="-1"
            class="absolute inset-y-0 right-0 flex items-center pr-4 text-slate-400 hover:text-[#CFAF53] transition-colors cursor-pointer focus:outline-none"
            :aria-label="show ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi'"
            aria-label="Tampilkan kata sandi">
        {{-- Eye (terlihat) --}}
        <svg x-show="!show" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
        </svg>
        {{-- Eye-off (sedang ditampilkan) --}}
        <svg x-show="show" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.542 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
        </svg>
    </button>
</div>
