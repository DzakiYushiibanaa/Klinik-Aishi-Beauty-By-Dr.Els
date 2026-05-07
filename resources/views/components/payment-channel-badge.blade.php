{{--
    Payment Channel Badge Component
    
    Usage: <x-payment-channel-badge :channel="$order->payment_channel" />
    Optional: <x-payment-channel-badge :channel="$order->payment_channel" size="sm" />
    
    Displays a styled badge with the payment channel icon and label.
--}}

@props([
    'channel' => null,
    'size' => 'md', // sm, md, lg
])

@php
    $ch = strtolower(trim($channel ?? ''));

    // Channel → Display Config
    $config = match($ch) {
        'bca'           => ['label' => 'BCA Virtual Account',   'icon' => 'bank',    'color' => 'bg-blue-50 text-blue-700 border-blue-200'],
        'bni'           => ['label' => 'BNI Virtual Account',   'icon' => 'bank',    'color' => 'bg-orange-50 text-orange-700 border-orange-200'],
        'bri'           => ['label' => 'BRI Virtual Account',   'icon' => 'bank',    'color' => 'bg-blue-50 text-blue-700 border-blue-200'],
        'mandiri'       => ['label' => 'Mandiri Bill',          'icon' => 'bank',    'color' => 'bg-blue-50 text-blue-700 border-blue-200'],
        'mandiri_bill'  => ['label' => 'Mandiri Bill',          'icon' => 'bank',    'color' => 'bg-blue-50 text-blue-700 border-blue-200'],
        'permata'       => ['label' => 'Permata VA',            'icon' => 'bank',    'color' => 'bg-green-50 text-green-700 border-green-200'],
        'cimb'          => ['label' => 'CIMB VA',               'icon' => 'bank',    'color' => 'bg-red-50 text-red-700 border-red-200'],
        'bsi'           => ['label' => 'Bank BSI (VA)',         'icon' => 'bank',    'color' => 'bg-emerald-50 text-emerald-700 border-emerald-200'],
        'qris'          => ['label' => 'QRIS',                  'icon' => 'qr',      'color' => 'bg-purple-50 text-purple-700 border-purple-200'],
        'gopay'         => ['label' => 'GoPay',                 'icon' => 'wallet',  'color' => 'bg-emerald-50 text-emerald-700 border-emerald-200'],
        'shopeepay'     => ['label' => 'ShopeePay',             'icon' => 'wallet',  'color' => 'bg-orange-50 text-orange-700 border-orange-200'],
        'dana'          => ['label' => 'DANA',                  'icon' => 'wallet',  'color' => 'bg-blue-50 text-blue-700 border-blue-200'],
        'credit_card'   => ['label' => 'Kartu Kredit',          'icon' => 'card',    'color' => 'bg-indigo-50 text-indigo-700 border-indigo-200'],
        'alfamart'      => ['label' => 'Alfamart',              'icon' => 'store',   'color' => 'bg-red-50 text-red-700 border-red-200'],
        'indomaret'     => ['label' => 'Indomaret',             'icon' => 'store',   'color' => 'bg-yellow-50 text-yellow-700 border-yellow-200'],
        'akulaku'       => ['label' => 'Akulaku',               'icon' => 'card',    'color' => 'bg-green-50 text-green-700 border-green-200'],
        'kredivo'       => ['label' => 'Kredivo',               'icon' => 'card',    'color' => 'bg-emerald-50 text-emerald-700 border-emerald-200'],
        'bank_transfer' => ['label' => 'Transfer Bank',         'icon' => 'bank',    'color' => 'bg-slate-50 text-slate-700 border-slate-200'],
        default         => ['label' => $ch ? strtoupper($ch) : '-', 'icon' => 'default', 'color' => 'bg-slate-50 text-slate-600 border-slate-200'],
    };

    // Size variants
    $sizeClasses = match($size) {
        'sm' => 'px-2.5 py-1 text-[10px] gap-1.5',
        'lg' => 'px-4 py-2 text-sm gap-2.5',
        default => 'px-3 py-1.5 text-xs gap-2',
    };

    $iconSize = match($size) {
        'sm' => 'w-3 h-3',
        'lg' => 'w-5 h-5',
        default => 'w-4 h-4',
    };
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center font-bold rounded-full border {$config['color']} {$sizeClasses}"]) }}>
    {{-- Icon --}}
    @switch($config['icon'])
        @case('bank')
            <svg class="{{ $iconSize }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
            </svg>
            @break
        @case('qr')
            <svg class="{{ $iconSize }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/>
            </svg>
            @break
        @case('wallet')
            <svg class="{{ $iconSize }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
            </svg>
            @break
        @case('card')
            <svg class="{{ $iconSize }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
            </svg>
            @break
        @case('store')
            <svg class="{{ $iconSize }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z"/>
            </svg>
            @break
        @default
            <svg class="{{ $iconSize }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
            </svg>
    @endswitch

    {{-- Label --}}
    <span>{{ $config['label'] }}</span>
</span>
