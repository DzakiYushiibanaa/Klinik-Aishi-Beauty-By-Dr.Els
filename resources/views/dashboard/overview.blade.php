{{-- ====== TAB: OVERVIEW ====== --}}
<div class="space-y-6">

    {{-- Hero Header — Dashboard Overview --}}
    @php
        $hour = (int) now()->format('H');
        $greeting = match (true) {
            $hour >= 4  && $hour < 11 => 'Selamat Pagi',
            $hour >= 11 && $hour < 15 => 'Selamat Siang',
            $hour >= 15 && $hour < 18 => 'Selamat Sore',
            default                   => 'Selamat Malam',
        };
        $firstName = trim(strtok($user->name, ' '));
    @endphp
    <section class="dash-hero">
        {{-- Decorative background layers --}}
        <div aria-hidden="true" class="dash-hero__glow dash-hero__glow--gold"></div>
        <div aria-hidden="true" class="dash-hero__glow dash-hero__glow--brown"></div>
        <div aria-hidden="true" class="dash-hero__pattern"></div>
        <div aria-hidden="true" class="dash-hero__sparkle dash-hero__sparkle--1"></div>
        <div aria-hidden="true" class="dash-hero__sparkle dash-hero__sparkle--2"></div>

        <div class="dash-hero__inner">
            {{-- LEFT: Greeting + date --}}
            <div class="dash-hero__left">
                <div class="dash-hero__eyebrow">
                    <span class="dash-hero__eyebrow-dot"></span>
                    Dashboard
                </div>

                <h1 class="dash-hero__title">
                    <span class="dash-hero__title-text">{{ $greeting }}, {{ $firstName }}</span>
                    <span class="dash-hero__wave" aria-hidden="true">👋</span>
                </h1>

                <p class="dash-hero__subtitle">
                    Ringkasan janji temu, pesanan, dan rekam perawatan Anda.
                </p>

                <div class="dash-hero__date">
                    <span class="dash-hero__date-icon">
                        <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </span>
                    <span class="dash-hero__date-text">{{ now()->translatedFormat('l, d F Y') }}</span>
                </div>
            </div>

            {{-- RIGHT: Quick action pills --}}
            <div class="dash-hero__actions">
                <a href="{{ route('appointment.select') }}" class="dash-pill dash-pill--primary">
                    <svg class="dash-pill__icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                    </svg>
                    <span>Buat Janji</span>
                </a>

                <a href="{{ route('pra.create') }}" class="dash-pill">
                    <svg class="dash-pill__icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <span>Konsultasi</span>
                </a>

                @if (\Illuminate\Support\Facades\Route::has('treatment.index'))
                    <a href="{{ route('treatment.index') }}" class="dash-pill">
                        <svg class="dash-pill__icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                        </svg>
                        <span>Treatment</span>
                    </a>
                @endif

                @if (\Illuminate\Support\Facades\Route::has('shop.index'))
                    <a href="{{ route('shop.index') }}" class="dash-pill">
                        <svg class="dash-pill__icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                        </svg>
                        <span>Belanja</span>
                    </a>
                @endif
            </div>
        </div>
    </section>

    <style>
        /* ─────────  HERO HEADER (compact horizontal)  ───────── */
        .dash-hero {
            position: relative;
            overflow: hidden;
            border-radius: 24px;
            border: 1px solid rgba(232, 224, 216, 0.7);
            background:
                radial-gradient(110% 130% at 0% 0%, #FFFFFF 0%, transparent 55%),
                linear-gradient(110deg, #FAF8F5 0%, #F5F0EB 60%, #EFE7DD 100%);
            box-shadow:
                0 1px 2px rgba(26, 26, 26, 0.04),
                0 10px 28px -16px rgba(184, 149, 111, 0.22);
        }

        /* Decorative glows */
        .dash-hero__glow {
            position: absolute;
            border-radius: 9999px;
            filter: blur(60px);
            pointer-events: none;
        }
        .dash-hero__glow--gold {
            top: -90px; right: -70px;
            width: 240px; height: 240px;
            background: rgba(207, 175, 83, 0.2);
            animation: dashHeroPulse 7s ease-in-out infinite;
        }
        .dash-hero__glow--brown {
            bottom: -120px; left: -60px;
            width: 280px; height: 280px;
            background: rgba(184, 149, 111, 0.13);
            animation: dashHeroPulse 9s ease-in-out infinite reverse;
        }

        /* Subtle dot pattern overlay */
        .dash-hero__pattern {
            position: absolute; inset: 0;
            background-image: radial-gradient(rgba(184, 149, 111, 0.16) 1px, transparent 1px);
            background-size: 22px 22px;
            mask-image: linear-gradient(120deg, transparent 25%, black 50%, transparent 80%);
            -webkit-mask-image: linear-gradient(120deg, transparent 25%, black 50%, transparent 80%);
            opacity: 0.55;
            pointer-events: none;
        }

        /* Tiny sparkles */
        .dash-hero__sparkle {
            position: absolute;
            width: 3px; height: 3px;
            border-radius: 9999px;
            background: #CFAF53;
            box-shadow: 0 0 8px 2px rgba(207, 175, 83, 0.55);
            pointer-events: none;
        }
        .dash-hero__sparkle--1 { top: 22%; right: 38%; animation: dashHeroTwinkle 3.2s ease-in-out infinite; }
        .dash-hero__sparkle--2 { bottom: 28%; right: 18%; animation: dashHeroTwinkle 4.6s ease-in-out infinite 1.4s; }

        .dash-hero__inner {
            position: relative;
            z-index: 1;
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
            padding: 1.5rem 1.5rem;
        }
        @media (min-width: 768px) {
            .dash-hero__inner {
                padding: 1.625rem 2rem;
            }
        }
        @media (min-width: 1024px) {
            .dash-hero__inner {
                flex-direction: row;
                align-items: center;
                justify-content: space-between;
                gap: 2rem;
                padding: 1.75rem 2.25rem;
            }
        }

        /* LEFT — Greeting */
        .dash-hero__left { min-width: 0; flex: 1 1 auto; }

        .dash-hero__eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 0.625rem;
            font-weight: 800;
            letter-spacing: 0.28em;
            text-transform: uppercase;
            color: #B8956F;
        }
        .dash-hero__eyebrow-dot {
            width: 5px; height: 5px; border-radius: 9999px;
            background: #CFAF53;
            box-shadow: 0 0 0 3px rgba(207, 175, 83, 0.18);
        }

        .dash-hero__title {
            margin-top: 0.5rem;
            display: inline-flex;
            flex-wrap: wrap;
            align-items: baseline;
            column-gap: 0.5rem;
            row-gap: 0.25rem;
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 1.5rem;
            font-weight: 900;
            line-height: 1.15;
            letter-spacing: -0.015em;
            color: #1A1A1A;
        }
        @media (min-width: 768px) { .dash-hero__title { font-size: 1.75rem; } }
        @media (min-width: 1280px) { .dash-hero__title { font-size: 1.875rem; } }

        .dash-hero__title-text {
            background: linear-gradient(120deg, #1A1A1A 0%, #2C2622 55%, #B8956F 110%);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .dash-hero__wave {
            display: inline-block;
            transform-origin: 70% 70%;
            font-size: 0.85em;
            animation: dashHeroWave 2.6s ease-in-out infinite;
        }

        .dash-hero__subtitle {
            margin-top: 0.375rem;
            max-width: 32rem;
            font-size: 0.8125rem;
            line-height: 1.55;
            color: #64748B;
        }
        @media (min-width: 768px) { .dash-hero__subtitle { font-size: 0.875rem; } }

        /* Date pill — compact elegant */
        .dash-hero__date {
            margin-top: 0.875rem;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.3125rem 0.75rem 0.3125rem 0.3125rem;
            border-radius: 9999px;
            border: 1px solid rgba(232, 224, 216, 0.9);
            background: rgba(255, 255, 255, 0.78);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            font-size: 0.75rem;
            font-weight: 700;
            color: #1A1A1A;
            box-shadow: 0 4px 10px -6px rgba(184, 149, 111, 0.28);
            letter-spacing: 0.01em;
        }
        .dash-hero__date-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 22px; height: 22px;
            border-radius: 9999px;
            background: linear-gradient(135deg, #CFAF53 0%, #B8956F 100%);
            color: #fff;
            box-shadow: 0 2px 5px -2px rgba(184, 149, 111, 0.6);
        }

        /* RIGHT — Quick action pills (horizontal row) */
        .dash-hero__actions {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 0.5rem;
            flex-shrink: 0;
        }
        @media (min-width: 1024px) { .dash-hero__actions { flex-wrap: nowrap; } }

        .dash-pill {
            position: relative;
            display: inline-flex;
            align-items: center;
            gap: 0.4375rem;
            padding: 0.5625rem 0.95rem;
            border-radius: 12px;
            border: 1px solid rgba(232, 224, 216, 0.85);
            background: rgba(255, 255, 255, 0.82);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            color: #1A1A1A;
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 0.8125rem;
            font-weight: 700;
            text-decoration: none;
            cursor: pointer;
            transition: transform 200ms cubic-bezier(0.34, 1.4, 0.64, 1),
                        box-shadow 200ms ease,
                        border-color 200ms ease,
                        background 200ms ease,
                        color 200ms ease;
            box-shadow: 0 1px 2px rgba(26, 26, 26, 0.04);
        }
        .dash-pill:hover {
            transform: translateY(-2px);
            border-color: rgba(184, 149, 111, 0.55);
            background: rgba(255, 255, 255, 0.96);
            color: #B8956F;
            box-shadow:
                0 1px 2px rgba(26, 26, 26, 0.05),
                0 10px 22px -12px rgba(184, 149, 111, 0.45);
        }
        .dash-pill:active { transform: translateY(0); }
        .dash-pill:focus-visible {
            outline: none;
            box-shadow:
                0 0 0 3px rgba(207, 175, 83, 0.35),
                0 10px 22px -12px rgba(184, 149, 111, 0.45);
        }

        .dash-pill__icon {
            width: 1rem;
            height: 1rem;
            color: #B8956F;
            flex-shrink: 0;
            transition: transform 200ms ease;
        }
        .dash-pill:hover .dash-pill__icon { transform: scale(1.1); }

        /* Primary variant */
        .dash-pill--primary {
            border-color: transparent;
            background: linear-gradient(135deg, #CFAF53 0%, #B8956F 100%);
            color: #fff;
            box-shadow:
                0 1px 2px rgba(184, 149, 111, 0.25),
                0 8px 18px -8px rgba(184, 149, 111, 0.55);
        }
        .dash-pill--primary:hover {
            background: linear-gradient(135deg, #d8b85b 0%, #c19f78 100%);
            border-color: transparent;
            color: #fff;
            box-shadow:
                0 1px 2px rgba(184, 149, 111, 0.3),
                0 14px 24px -10px rgba(184, 149, 111, 0.6);
        }
        .dash-pill--primary .dash-pill__icon { color: #fff; }

        /* Animations */
        @keyframes dashHeroWave {
            0%, 60%, 100% { transform: rotate(0deg); }
            10%, 30%      { transform: rotate(14deg); }
            20%           { transform: rotate(-8deg); }
            40%           { transform: rotate(14deg); }
            50%           { transform: rotate(-4deg); }
        }
        @keyframes dashHeroPulse {
            0%, 100% { opacity: 1; transform: scale(1); }
            50%      { opacity: 0.65; transform: scale(1.06); }
        }
        @keyframes dashHeroTwinkle {
            0%, 100% { opacity: 0.4; transform: scale(0.85); }
            50%      { opacity: 1;   transform: scale(1.2); }
        }
        @media (prefers-reduced-motion: reduce) {
            .dash-hero__wave,
            .dash-hero__glow--gold,
            .dash-hero__glow--brown,
            .dash-hero__sparkle { animation: none; }
            .dash-pill,
            .dash-pill__icon { transition: none; }
            .dash-pill:hover { transform: none; }
            .dash-pill:hover .dash-pill__icon { transform: none; }
        }
    </style>

    {{-- Section A: Pending Payments (Priority Action) --}}
    @if(isset($pendingPayments) && count($pendingPayments) > 0)
        @foreach($pendingPayments as $payment)
            @php
                $order = $payment['order'];
                $appointment = $payment['appointment'] ?? null;

                // Eager-load relasi item supaya nama tampil dengan benar.
                $order->loadMissing(['items.product', 'items.treatment', 'items.bundle', 'items.consultation']);

                // Group item per kategori untuk rendering yang clean.
                $groupedItems = [
                    'Konsultasi' => collect(),
                    'Treatment'  => collect(),
                    'Produk'     => collect(),
                ];
                foreach ($order->items as $item) {
                    $type = strtolower((string) $item->item_type);
                    $bucket = match ($type) {
                        'consultation' => 'Konsultasi',
                        'treatment'    => 'Treatment',
                        'bundle'       => 'Treatment',
                        'product'      => 'Produk',
                        default        => 'Produk',
                    };
                    $groupedItems[$bucket]->push($item);
                }
                // Buang grup yang kosong.
                $groupedItems = collect($groupedItems)->filter(fn ($c) => $c->isNotEmpty());

                // Resolver nama item dengan fallback aman.
                $resolveItemName = function ($item) {
                    return $item->product?->nama
                        ?? $item->treatment?->nama
                        ?? ($item->bundle ? 'Bundle: '.$item->bundle->nama : null)
                        ?? $item->consultation?->nama
                        ?? $item->description
                        ?? 'Item';
                };

                // Cek apakah order punya pengiriman (untuk show row Ongkir).
                $hasShipping = !empty($order->shipping_method)
                    || !empty($order->shipping_address)
                    || (int) ($order->ongkir ?? 0) > 0;

                $subtotal     = (int) ($order->subtotal ?? 0);
                $diskonTotal  = (int) ($order->diskon_total ?? 0);
                $ongkir       = $hasShipping ? (int) ($order->ongkir ?? 0) : 0;
                $grandTotal   = (int) ($order->grand_total ?? 0);
            @endphp

            <div class="bg-white rounded-2xl border border-[#E8E0D8]/70 shadow-sm mb-6 overflow-hidden">
                {{-- HEADER --}}
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center p-4 sm:p-5 border-b border-[#E8E0D8]/60 bg-[#FAF8F5]/70 gap-3">
                    <div class="flex items-center gap-3 flex-wrap">
                        <h2 class="text-[16px] font-bold text-[#1A1A1A]">Invoice</h2>
                        <span class="text-[11px] font-mono font-medium text-slate-500 bg-white px-2 py-1 rounded border border-slate-200 tracking-wide">{{ $order->kode_order }}</span>
                        <span class="text-[11px] text-slate-500">·</span>
                        <span class="text-[11px] text-slate-500">{{ $order->created_at->translatedFormat('d M Y') }}</span>
                    </div>

                    <div class="flex items-center gap-2.5 w-full sm:w-auto justify-between sm:justify-end">
                        @if($order->status === 'cancel_requested')
                            <span class="bg-orange-100 text-orange-800 text-[10px] font-bold px-2.5 py-1 rounded-md uppercase tracking-wide">Menunggu Konfirmasi Pembatalan</span>
                        @else
                            <span class="bg-amber-100 text-amber-800 text-[10px] font-bold px-2.5 py-1 rounded-md uppercase tracking-wide">Menunggu Pembayaran</span>
                        @endif
                        @if($order->status !== 'cancel_requested' && $order->expired_at)
                        <div x-data="countdownTimer('{{ $order->expired_at }}')" class="flex items-center gap-1.5 bg-rose-50 border border-rose-200 px-2.5 py-1 rounded-md">
                            <svg class="w-3.5 h-3.5 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <div class="flex items-center text-rose-600 font-bold text-[13px] tabular-nums tracking-tighter">
                                <template x-if="showHours">
                                    <div class="flex items-center">
                                        <div class="h-[1em] overflow-hidden leading-none relative">
                                            <div class="transition-transform duration-300 flex flex-col" :style="`transform: translateY(-${h1 * 10}%)`"><span>0</span><span>1</span><span>2</span><span>3</span><span>4</span><span>5</span><span>6</span><span>7</span><span>8</span><span>9</span></div>
                                        </div>
                                        <div class="h-[1em] overflow-hidden leading-none relative">
                                            <div class="transition-transform duration-300 flex flex-col" :style="`transform: translateY(-${h2 * 10}%)`"><span>0</span><span>1</span><span>2</span><span>3</span><span>4</span><span>5</span><span>6</span><span>7</span><span>8</span><span>9</span></div>
                                        </div>
                                        <span class="mx-0.5 text-rose-400 font-black animate-pulse">:</span>
                                    </div>
                                </template>
                                <div class="h-[1em] overflow-hidden leading-none relative">
                                    <div class="transition-transform duration-300 flex flex-col" :style="`transform: translateY(-${m1 * 10}%)`"><span>0</span><span>1</span><span>2</span><span>3</span><span>4</span><span>5</span><span>6</span><span>7</span><span>8</span><span>9</span></div>
                                </div>
                                <div class="h-[1em] overflow-hidden leading-none relative">
                                    <div class="transition-transform duration-300 flex flex-col" :style="`transform: translateY(-${m2 * 10}%)`"><span>0</span><span>1</span><span>2</span><span>3</span><span>4</span><span>5</span><span>6</span><span>7</span><span>8</span><span>9</span></div>
                                </div>
                                <span class="mx-0.5 text-rose-400 font-black animate-pulse">:</span>
                                <div class="h-[1em] overflow-hidden leading-none relative">
                                    <div class="transition-transform duration-300 flex flex-col" :style="`transform: translateY(-${s1 * 10}%)`"><span>0</span><span>1</span><span>2</span><span>3</span><span>4</span><span>5</span><span>6</span><span>7</span><span>8</span><span>9</span></div>
                                </div>
                                <div class="h-[1em] overflow-hidden leading-none relative">
                                    <div class="transition-transform duration-300 flex flex-col" :style="`transform: translateY(-${s2 * 10}%)`"><span>0</span><span>1</span><span>2</span><span>3</span><span>4</span><span>5</span><span>6</span><span>7</span><span>8</span><span>9</span></div>
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>

                {{-- ITEMS BY GROUP --}}
                <div class="px-4 sm:px-5 py-2 divide-y divide-[#E8E0D8]/50">
                    @if($appointment)
                        <div class="py-3 flex items-center gap-2 text-xs">
                            <svg class="w-4 h-4 text-[#CFAF53]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            <span class="text-slate-500">Jadwal:</span>
                            <span class="font-semibold text-[#1A1A1A]">{{ $appointment->tanggal_jam->translatedFormat('d F Y • H:i') }} WIB</span>
                        </div>
                    @endif

                    @foreach($groupedItems as $groupName => $items)
                        <div class="py-3">
                            <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-2.5">{{ $groupName }}</p>
                            <ul class="space-y-2">
                                @foreach($items as $item)
                                    @php
                                        $name     = $resolveItemName($item);
                                        $qty      = max(1, (int) ($item->qty ?? 1));
                                        $unitPrice= (int) ($item->harga_satuan ?? 0);
                                        $diskonItem = (int) ($item->diskon ?? 0);
                                        $lineSubtotal = (int) ($item->subtotal ?? ($unitPrice * $qty - $diskonItem));
                                    @endphp
                                    <li class="flex items-start justify-between gap-3">
                                        <div class="min-w-0 flex-1">
                                            <p class="text-sm font-semibold text-[#1A1A1A] line-clamp-2">{{ $name }}</p>
                                            <p class="text-xs text-slate-500 mt-0.5">{{ $qty }} × Rp {{ number_format($unitPrice, 0, ',', '.') }}</p>
                                            @if($diskonItem > 0)
                                                <p class="text-[11px] text-rose-600 mt-0.5">Diskon Rp {{ number_format($diskonItem, 0, ',', '.') }}</p>
                                            @endif
                                        </div>
                                        <p class="text-sm font-bold text-[#1A1A1A] tabular-nums whitespace-nowrap">Rp {{ number_format($lineSubtotal, 0, ',', '.') }}</p>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endforeach
                </div>

                {{-- RINGKASAN PEMBAYARAN --}}
                <div class="px-4 sm:px-5 py-4 bg-[#FAF8F5]/60 border-t border-[#E8E0D8]/60">
                    <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-2.5">Ringkasan Pembayaran</p>
                    <div class="space-y-1.5 text-sm">
                        <div class="flex justify-between text-slate-600">
                            <span>Subtotal</span>
                            <span class="tabular-nums">Rp {{ number_format($subtotal, 0, ',', '.') }}</span>
                        </div>
                        @if($diskonTotal > 0)
                            <div class="flex justify-between text-rose-600">
                                <span>Diskon</span>
                                <span class="tabular-nums">-Rp {{ number_format($diskonTotal, 0, ',', '.') }}</span>
                            </div>
                        @endif
                        @if($ongkir > 0)
                            <div class="flex justify-between text-slate-600">
                                <span>Ongkos Kirim</span>
                                <span class="tabular-nums">Rp {{ number_format($ongkir, 0, ',', '.') }}</span>
                            </div>
                        @endif
                        <div class="flex justify-between items-center pt-2.5 mt-2 border-t border-dashed border-[#E8E0D8]">
                            <span class="text-[11px] font-bold text-[#8C7A63] uppercase tracking-widest">Total Pembayaran</span>
                            <span class="text-lg font-extrabold text-[#CFAF53] tabular-nums">Rp {{ number_format($grandTotal, 0, ',', '.') }}</span>
                        </div>
                    </div>
                </div>

                {{-- FOOTER --}}
                <div class="px-4 sm:px-5 py-4 bg-white border-t border-[#E8E0D8]/60 flex flex-col sm:flex-row justify-between items-stretch sm:items-center gap-3">
                    @if($order->status === 'cancel_requested')
                        <p class="text-xs text-slate-500 leading-relaxed flex-1">
                            <svg class="inline-block w-4 h-4 text-amber-500 mr-1 -mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                            Permintaan pembatalan sedang diproses.
                        </p>
                    @else
                        <p class="text-xs text-slate-500 leading-relaxed flex-1">
                            Invoice ini dibuat berdasarkan layanan dan produk yang tercatat pada transaksi Anda. Silakan lakukan pembayaran untuk menyelesaikan transaksi ini.
                        </p>
                    @endif
                    <div class="flex items-center gap-3 sm:flex-shrink-0">
                        <div class="text-right hidden sm:block">
                            <p class="text-[10px] text-slate-500 uppercase tracking-widest font-semibold">Total</p>
                            <p class="text-base font-extrabold text-[#1A1A1A] tabular-nums">Rp {{ number_format($grandTotal, 0, ',', '.') }}</p>
                        </div>
                        @if($order->status === 'menunggu_pembayaran')
                            <button type="button"
                                    @click="window.confirmDialog({
                                        title: 'Batalkan Pesanan?',
                                        message: 'Apakah Anda yakin ingin membatalkan pesanan ini?',
                                        confirmText: 'Ya, Batalkan',
                                        cancelText: 'Kembali',
                                        variant: 'warning',
                                    }).then(ok => { if (ok) document.getElementById('cancel-order-form-{{ $order->order_id }}').submit() })"
                                    class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-rose-50 hover:bg-rose-100 text-rose-700 text-sm font-bold rounded-full border border-rose-200 transition-colors cursor-pointer">
                                Batalkan Pesanan
                            </button>
                            <form id="cancel-order-form-{{ $order->order_id }}" action="{{ route('orders.cancel', $order->order_id) }}" method="POST" class="hidden">
                                @csrf
                                @method('PATCH')
                            </form>
                            <button @click="processPayment('{{ $order->kode_order }}', $event)" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-2.5 bg-[#CFAF53] hover:bg-[#b09444] text-white text-sm font-bold rounded-full transition-colors shadow-lg shadow-[#CFAF53]/20">
                                Bayar Sekarang
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    @endif

    {{-- Section A: Lanjutkan Aktivitas (Other logic) --}}
    @if(isset($nextAction))
        @php
            $naColors = match ($nextAction['color']) {
                'amber' => [
                    'bg_grad'      => 'linear-gradient(135deg, #FFF8EB 0%, #FFFDF7 50%, #FFFFFF 100%)',
                    'border'       => 'rgba(245, 158, 11, 0.18)',
                    'glow'         => 'rgba(245, 158, 11, 0.16)',
                    'icon_bg'      => 'rgba(245, 158, 11, 0.12)',
                    'icon_color'   => '#D97706',
                    'btn_sec_text' => '#92400E',
                    'btn_pri_grad' => 'linear-gradient(135deg, #F59E0B 0%, #D97706 100%)',
                    'btn_pri_glow' => 'rgba(217, 119, 6, 0.45)',
                ],
                'orange' => [
                    'bg_grad'      => 'linear-gradient(135deg, #FFF4EC 0%, #FFFCF8 50%, #FFFFFF 100%)',
                    'border'       => 'rgba(234, 88, 12, 0.18)',
                    'glow'         => 'rgba(234, 88, 12, 0.16)',
                    'icon_bg'      => 'rgba(234, 88, 12, 0.12)',
                    'icon_color'   => '#C2410C',
                    'btn_sec_text' => '#9A3412',
                    'btn_pri_grad' => 'linear-gradient(135deg, #F97316 0%, #C2410C 100%)',
                    'btn_pri_glow' => 'rgba(194, 65, 12, 0.45)',
                ],
                'blue' => [
                    'bg_grad'      => 'linear-gradient(135deg, #EFF6FF 0%, #F8FBFF 50%, #FFFFFF 100%)',
                    'border'       => 'rgba(37, 99, 235, 0.18)',
                    'glow'         => 'rgba(37, 99, 235, 0.16)',
                    'icon_bg'      => 'rgba(37, 99, 235, 0.12)',
                    'icon_color'   => '#1D4ED8',
                    'btn_sec_text' => '#1E40AF',
                    'btn_pri_grad' => 'linear-gradient(135deg, #3B82F6 0%, #1D4ED8 100%)',
                    'btn_pri_glow' => 'rgba(29, 78, 216, 0.45)',
                ],
                'emerald' => [
                    'bg_grad'      => 'linear-gradient(135deg, #ECFDF5 0%, #F4FBF7 50%, #FFFFFF 100%)',
                    'border'       => 'rgba(16, 185, 129, 0.2)',
                    'glow'         => 'rgba(16, 185, 129, 0.18)',
                    'icon_bg'      => 'rgba(16, 185, 129, 0.12)',
                    'icon_color'   => '#047857',
                    'btn_sec_text' => '#065F46',
                    'btn_pri_grad' => 'linear-gradient(135deg, #10B981 0%, #047857 100%)',
                    'btn_pri_glow' => 'rgba(4, 120, 87, 0.45)',
                ],
                default => [
                    'bg_grad'      => 'linear-gradient(135deg, #FAF8F5 0%, #FDFCFA 50%, #FFFFFF 100%)',
                    'border'       => 'rgba(184, 149, 111, 0.22)',
                    'glow'         => 'rgba(184, 149, 111, 0.18)',
                    'icon_bg'      => 'rgba(184, 149, 111, 0.12)',
                    'icon_color'   => '#B8956F',
                    'btn_sec_text' => '#1A1A1A',
                    'btn_pri_grad' => 'linear-gradient(135deg, #CFAF53 0%, #B8956F 100%)',
                    'btn_pri_glow' => 'rgba(184, 149, 111, 0.5)',
                ]
            };
        @endphp
        <section class="dash-promo"
                 style="--promo-grad: {{ $naColors['bg_grad'] }};
                        --promo-border: {{ $naColors['border'] }};
                        --promo-glow: {{ $naColors['glow'] }};
                        --promo-icon-bg: {{ $naColors['icon_bg'] }};
                        --promo-icon-color: {{ $naColors['icon_color'] }};
                        --promo-btn-sec: {{ $naColors['btn_sec_text'] }};
                        --promo-btn-pri-grad: {{ $naColors['btn_pri_grad'] }};
                        --promo-btn-pri-glow: {{ $naColors['btn_pri_glow'] }};">
            <div aria-hidden="true" class="dash-promo__orb dash-promo__orb--a"></div>
            <div aria-hidden="true" class="dash-promo__orb dash-promo__orb--b"></div>
            <div aria-hidden="true" class="dash-promo__pattern"></div>

            <div class="dash-promo__inner">
                <div class="dash-promo__left">
                    <div class="dash-promo__icon">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">{!! $nextAction['icon'] !!}</svg>
                    </div>
                    <div class="dash-promo__text">
                        <h2 class="dash-promo__title">{{ $nextAction['title'] }}</h2>
                        <p class="dash-promo__desc">{{ $nextAction['description'] }}</p>
                    </div>
                </div>
                <div class="dash-promo__actions">
                    @if(isset($nextAction['secondary_cta_text']))
                        <a href="{{ $nextAction['secondary_cta_link'] }}" class="dash-promo__btn dash-promo__btn--secondary">
                            {{ $nextAction['secondary_cta_text'] }}
                        </a>
                    @endif

                    @if(isset($nextAction['cta_tab']))
                        <button @click="activeTab = '{{ $nextAction['cta_tab'] }}'" class="dash-promo__btn dash-promo__btn--primary">
                            <span>{{ $nextAction['cta_text'] }}</span>
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.4" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                            </svg>
                        </button>
                    @elseif(isset($nextAction['cta_link']))
                        <a href="{{ $nextAction['cta_link'] }}" class="dash-promo__btn dash-promo__btn--primary">
                            <span>{{ $nextAction['cta_text'] }}</span>
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.4" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                            </svg>
                        </a>
                    @endif
                </div>
            </div>
        </section>

        <style>
            /* ─────────  PROMO / NEXT ACTION BANNER  ───────── */
            .dash-promo {
                position: relative;
                overflow: hidden;
                border-radius: 22px;
                border: 1px solid var(--promo-border);
                background: var(--promo-grad);
                box-shadow:
                    0 1px 2px rgba(26, 26, 26, 0.04),
                    0 14px 32px -18px var(--promo-glow);
            }
            .dash-promo__orb {
                position: absolute;
                border-radius: 9999px;
                filter: blur(60px);
                pointer-events: none;
                background: var(--promo-glow);
            }
            .dash-promo__orb--a { top: -80px; right: -60px; width: 220px; height: 220px; opacity: 0.85; }
            .dash-promo__orb--b { bottom: -100px; left: -50px; width: 240px; height: 240px; opacity: 0.55; }
            .dash-promo__pattern {
                position: absolute; inset: 0;
                background-image: radial-gradient(rgba(184, 149, 111, 0.14) 1px, transparent 1px);
                background-size: 22px 22px;
                mask-image: linear-gradient(120deg, transparent 35%, black 65%, transparent 95%);
                -webkit-mask-image: linear-gradient(120deg, transparent 35%, black 65%, transparent 95%);
                opacity: 0.5;
                pointer-events: none;
            }

            .dash-promo__inner {
                position: relative;
                z-index: 1;
                display: flex;
                flex-direction: column;
                gap: 1.25rem;
                padding: 1.5rem 1.5rem;
            }
            @media (min-width: 768px) {
                .dash-promo__inner { padding: 1.5rem 2rem; flex-direction: row; align-items: center; justify-content: space-between; gap: 1.75rem; }
            }

            .dash-promo__left {
                display: flex;
                align-items: flex-start;
                gap: 1rem;
                min-width: 0;
            }
            .dash-promo__icon {
                width: 48px; height: 48px;
                border-radius: 14px;
                background: var(--promo-icon-bg);
                color: var(--promo-icon-color);
                display: inline-flex;
                align-items: center;
                justify-content: center;
                flex-shrink: 0;
                box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.6),
                            0 6px 16px -8px var(--promo-glow);
            }
            .dash-promo__icon svg { width: 22px; height: 22px; stroke-width: 2; }
            .dash-promo__text { min-width: 0; }
            .dash-promo__title {
                font-family: 'Plus Jakarta Sans', sans-serif;
                font-size: 1.0625rem;
                font-weight: 800;
                line-height: 1.3;
                letter-spacing: -0.01em;
                color: #1A1A1A;
            }
            .dash-promo__desc {
                margin-top: 0.25rem;
                font-size: 0.8125rem;
                line-height: 1.55;
                color: #64748B;
            }

            .dash-promo__actions {
                display: flex;
                flex-wrap: wrap;
                align-items: center;
                gap: 0.625rem;
                flex-shrink: 0;
            }
            .dash-promo__btn {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                gap: 0.4375rem;
                padding: 0.625rem 1.125rem;
                border-radius: 9999px;
                font-family: 'Plus Jakarta Sans', sans-serif;
                font-size: 0.8125rem;
                font-weight: 700;
                text-decoration: none;
                cursor: pointer;
                transition: transform 200ms ease, box-shadow 200ms ease, background 200ms ease, border-color 200ms ease;
                white-space: nowrap;
            }
            .dash-promo__btn--secondary {
                background: rgba(255, 255, 255, 0.85);
                backdrop-filter: blur(8px);
                -webkit-backdrop-filter: blur(8px);
                color: var(--promo-btn-sec);
                border: 1px solid rgba(0, 0, 0, 0.06);
                box-shadow: 0 1px 2px rgba(26, 26, 26, 0.04);
            }
            .dash-promo__btn--secondary:hover {
                background: #fff;
                transform: translateY(-1px);
                box-shadow: 0 8px 16px -8px rgba(0, 0, 0, 0.1);
            }
            .dash-promo__btn--primary {
                background: var(--promo-btn-pri-grad);
                color: #fff;
                border: 1px solid transparent;
                box-shadow:
                    0 1px 2px rgba(0, 0, 0, 0.08),
                    0 10px 22px -10px var(--promo-btn-pri-glow);
            }
            .dash-promo__btn--primary:hover {
                transform: translateY(-1px);
                box-shadow:
                    0 1px 2px rgba(0, 0, 0, 0.1),
                    0 14px 26px -10px var(--promo-btn-pri-glow);
                filter: brightness(1.05);
            }
            .dash-promo__btn--primary:active { transform: translateY(0); }

            @media (max-width: 767px) {
                .dash-promo__btn,
                .dash-promo__btn--secondary,
                .dash-promo__btn--primary { width: 100%; }
            }
            @media (prefers-reduced-motion: reduce) {
                .dash-promo__btn { transition: none; }
                .dash-promo__btn:hover { transform: none; filter: none; }
            }
        </style>
    @endif

    {{-- Stats Cards --}}
    <div class="dash-stats">
        {{-- Janji Temu Aktif — gold --}}
        <div class="dash-stat dash-stat--gold">
            <div aria-hidden="true" class="dash-stat__glow"></div>
            <div class="dash-stat__head">
                <div class="dash-stat__icon">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
                <span class="dash-stat__chip">Aktif</span>
            </div>
            <p class="dash-stat__value">{{ $stats['upcoming'] }}</p>
            <p class="dash-stat__label">Janji Temu Aktif</p>
            <p class="dash-stat__hint">Jadwal menunggu &amp; disetujui</p>
        </div>

        {{-- Pesanan Aktif — amber/orange --}}
        <div class="dash-stat dash-stat--amber">
            <div aria-hidden="true" class="dash-stat__glow"></div>
            <div class="dash-stat__head">
                <div class="dash-stat__icon">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                    </svg>
                </div>
                <span class="dash-stat__chip">Diproses</span>
            </div>
            <p class="dash-stat__value">{{ $stats['active_orders'] }}</p>
            <p class="dash-stat__label">Pesanan Aktif</p>
            <p class="dash-stat__hint">Menunggu bayar &amp; diproses</p>
        </div>

        {{-- Kunjungan Selesai — mint/emerald --}}
        <div class="dash-stat dash-stat--mint">
            <div aria-hidden="true" class="dash-stat__glow"></div>
            <div class="dash-stat__head">
                <div class="dash-stat__icon">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <span class="dash-stat__chip">Total</span>
            </div>
            <p class="dash-stat__value">{{ $stats['total_rekam_medis'] }}</p>
            <p class="dash-stat__label">Kunjungan Selesai</p>
            <p class="dash-stat__hint">Total riwayat perawatan</p>
        </div>
    </div>

    <style>
        /* ─────────  STATS CARDS  ───────── */
        .dash-stats {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 0.875rem;
        }
        @media (min-width: 768px) {
            .dash-stats { grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 1rem; }
        }

        .dash-stat {
            position: relative;
            overflow: hidden;
            padding: 1.25rem;
            border-radius: 20px;
            background:
                linear-gradient(180deg, rgba(255, 255, 255, 0.85) 0%, rgba(255, 255, 255, 0.6) 100%),
                var(--stat-tint, #FAF8F5);
            border: 1px solid var(--stat-border, rgba(232, 224, 216, 0.7));
            box-shadow:
                0 1px 2px rgba(26, 26, 26, 0.04),
                0 10px 24px -16px var(--stat-glow, rgba(184, 149, 111, 0.2));
            transition: transform 250ms cubic-bezier(0.34, 1.4, 0.64, 1),
                        box-shadow 250ms ease,
                        border-color 250ms ease;
        }
        .dash-stat:hover {
            transform: translateY(-3px);
            border-color: var(--stat-border-hover, rgba(184, 149, 111, 0.5));
            box-shadow:
                0 1px 2px rgba(26, 26, 26, 0.05),
                0 18px 32px -14px var(--stat-glow, rgba(184, 149, 111, 0.32));
        }

        .dash-stat__glow {
            position: absolute;
            top: -50px; right: -50px;
            width: 160px; height: 160px;
            border-radius: 9999px;
            background: var(--stat-glow, rgba(184, 149, 111, 0.18));
            filter: blur(50px);
            opacity: 0.7;
            pointer-events: none;
        }

        .dash-stat__head {
            position: relative;
            z-index: 1;
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1rem;
        }
        .dash-stat__icon {
            width: 40px; height: 40px;
            border-radius: 12px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: var(--stat-icon-bg, rgba(184, 149, 111, 0.14));
            color: var(--stat-icon-color, #B8956F);
            box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.6);
        }
        .dash-stat__icon svg { width: 20px; height: 20px; stroke-width: 2; }
        .dash-stat__chip {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 0.625rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.16em;
            padding: 0.25rem 0.5rem;
            border-radius: 9999px;
            background: var(--stat-chip-bg, rgba(184, 149, 111, 0.12));
            color: var(--stat-chip-color, #B8956F);
        }

        .dash-stat__value {
            position: relative;
            z-index: 1;
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 2.75rem;
            line-height: 1;
            font-weight: 900;
            letter-spacing: -0.04em;
            color: #1A1A1A;
        }
        @media (min-width: 768px) { .dash-stat__value { font-size: 3.25rem; } }

        .dash-stat__label {
            position: relative;
            z-index: 1;
            margin-top: 0.625rem;
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 0.9375rem;
            font-weight: 800;
            letter-spacing: -0.005em;
            color: #1A1A1A;
        }
        .dash-stat__hint {
            position: relative;
            z-index: 1;
            margin-top: 0.25rem;
            font-size: 0.75rem;
            color: #94A3B8;
            font-weight: 500;
        }

        /* Variants — gold / amber / mint */
        .dash-stat--gold {
            --stat-tint: linear-gradient(180deg, #FFFCF3 0%, #FFFFFF 100%);
            --stat-border: rgba(207, 175, 83, 0.28);
            --stat-border-hover: rgba(207, 175, 83, 0.55);
            --stat-glow: rgba(207, 175, 83, 0.22);
            --stat-icon-bg: linear-gradient(135deg, rgba(207, 175, 83, 0.18) 0%, rgba(184, 149, 111, 0.18) 100%);
            --stat-icon-color: #B8956F;
            --stat-chip-bg: rgba(207, 175, 83, 0.14);
            --stat-chip-color: #8C7A63;
        }
        .dash-stat--amber {
            --stat-tint: linear-gradient(180deg, #FFF8EC 0%, #FFFFFF 100%);
            --stat-border: rgba(245, 158, 11, 0.22);
            --stat-border-hover: rgba(245, 158, 11, 0.5);
            --stat-glow: rgba(245, 158, 11, 0.22);
            --stat-icon-bg: linear-gradient(135deg, rgba(245, 158, 11, 0.16) 0%, rgba(217, 119, 6, 0.16) 100%);
            --stat-icon-color: #D97706;
            --stat-chip-bg: rgba(245, 158, 11, 0.14);
            --stat-chip-color: #B45309;
        }
        .dash-stat--mint {
            --stat-tint: linear-gradient(180deg, #F1FBF6 0%, #FFFFFF 100%);
            --stat-border: rgba(16, 185, 129, 0.22);
            --stat-border-hover: rgba(16, 185, 129, 0.5);
            --stat-glow: rgba(16, 185, 129, 0.22);
            --stat-icon-bg: linear-gradient(135deg, rgba(16, 185, 129, 0.16) 0%, rgba(5, 150, 105, 0.16) 100%);
            --stat-icon-color: #047857;
            --stat-chip-bg: rgba(16, 185, 129, 0.14);
            --stat-chip-color: #047857;
        }

        @media (max-width: 767px) {
            .dash-stat--mint { grid-column: span 2; }
        }

        @media (prefers-reduced-motion: reduce) {
            .dash-stat { transition: none; }
            .dash-stat:hover { transform: none; }
        }
    </style>

    {{-- Jadwal Mendatang --}}
    <div class="mt-8">
        <div class="dash-section">
            <div class="dash-section__head">
                <div>
                    <p class="dash-section__eyebrow">Jadwal Mendatang</p>
                    <p class="dash-section__sub">Janji temu aktif dan yang akan datang.</p>
                </div>
                <a href="{{ route('appointment.select') }}" class="dash-section__link">
                    + Janji Baru
                </a>
            </div>

            @if($upcomingAppointments->isEmpty())
                <div class="dash-empty">
                    <div aria-hidden="true" class="dash-empty__orb"></div>
                    <div class="dash-empty__icon">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <p class="dash-empty__title">Belum ada janji temu</p>
                    <p class="dash-empty__desc">Jadwalkan kunjungan Anda dan rasakan pengalaman perawatan premium di Aishi Beauty.</p>
                    <a href="{{ route('appointment.select') }}" class="dash-empty__cta">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                        </svg>
                        Buat Janji Pertama
                    </a>
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
                    @foreach($upcomingAppointments as $upcoming)
                        @php
                            $treatmentNames = $upcoming->items->map(fn($i) => $i->treatment?->nama)->filter()->implode(', ');
                            $isDisetujui = strtolower($upcoming->status) === 'disetujui';
                            $statusLabel = ucfirst(strtolower($upcoming->status));
                        @endphp
                        <div class="group relative flex flex-col overflow-hidden rounded-2xl border border-[#E8E0D8] bg-white p-4 sm:p-5 shadow-sm hover:shadow-md hover:border-[#CFAF53]/60 transition-all cursor-pointer"
                             @click="activeTab = 'appointment'">
                            {{-- Side accent --}}
                            <div class="absolute top-0 left-0 w-1 h-full {{ $isDisetujui ? 'bg-emerald-400' : 'bg-amber-400' }} group-hover:w-1.5 transition-all"></div>

                            <div class="flex gap-4 pl-2">
                                {{-- Calendar Date Box --}}
                                <div class="w-14 h-[68px] sm:w-16 sm:h-[72px] shrink-0 bg-[#FAF8F5] rounded-xl border border-[#E8E0D8] overflow-hidden flex flex-col shadow-sm group-hover:border-[#CFAF53]/40 transition-colors">
                                    <div class="bg-[#1A1A1A] py-1 text-center">
                                        <span class="text-[10px] font-bold text-[#CFAF53] uppercase tracking-widest">{{ $upcoming->tanggal_jam->translatedFormat('M') }}</span>
                                    </div>
                                    <div class="flex-1 flex items-center justify-center bg-white">
                                        <span class="text-2xl font-black text-[#1A1A1A] leading-none">{{ $upcoming->tanggal_jam->translatedFormat('d') }}</span>
                                    </div>
                                </div>

                                {{-- Details --}}
                                <div class="flex-1 min-w-0">
                                    <h4 class="font-bold text-[15px] text-[#1A1A1A] line-clamp-1 group-hover:text-[#CFAF53] transition-colors mb-1.5"
                                        title="{{ $treatmentNames ?: ucfirst($upcoming->jenis) }}">
                                        {{ $treatmentNames ?: ucfirst($upcoming->jenis) }}
                                    </h4>
                                    <div class="space-y-1 text-xs text-slate-500 font-medium">
                                        <div class="flex items-center gap-1.5">
                                            <svg class="w-3.5 h-3.5 text-[#CFAF53] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                            <span class="whitespace-nowrap">{{ $upcoming->tanggal_jam->translatedFormat('H:i') }} WIB</span>
                                        </div>
                                        <div class="flex items-center gap-1.5 min-w-0">
                                            <svg class="w-3.5 h-3.5 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                            </svg>
                                            <span class="truncate" title="{{ $upcoming->dokter->name ?? 'Dokter' }}">{{ $upcoming->dokter->name ?? 'Dokter' }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Footer: status + CTA, full row, jarak konsisten --}}
                            <div class="mt-4 pt-3 pl-2 border-t border-[#E8E0D8]/40 flex items-center justify-between gap-3">
                                <span class="shrink-0 px-2.5 py-1 rounded-md text-[10px] font-extrabold uppercase tracking-widest border whitespace-nowrap {{ $isDisetujui ? 'text-emerald-700 bg-emerald-50 border-emerald-200' : 'text-amber-700 bg-amber-50 border-amber-200' }}">
                                    {{ $statusLabel }}
                                </span>

                                <button type="button" class="shrink-0 inline-flex items-center justify-center gap-1.5 rounded-full bg-[#FAF8F5] border border-[#E8E0D8] hover:bg-white hover:border-[#CFAF53] px-3.5 py-1.5 text-xs font-bold text-[#1A1A1A] transition-colors shadow-sm whitespace-nowrap cursor-pointer">
                                    Lihat Detail
                                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    {{-- Main: Aktivitas Terkini --}}
    <div class="mt-8">
        <div class="dash-section">
            <div class="dash-section__head">
                <div>
                    <p class="dash-section__eyebrow">Aktivitas Terkini</p>
                    <p class="dash-section__sub">Update terbaru dari konsultasi dan pesanan Anda.</p>
                </div>
            </div>

            <div class="space-y-4">
            @php $latestPraKonsul = $praKonsuls->first(); @endphp
            @php $latestOrder = $orders->first(); @endphp

            {{-- Card: Pra Konsultasi Terbaru --}}
            @if($latestPraKonsul)
                @php
                    $praStatusKey = strtolower($latestPraKonsul->status ?? 'baru');
                    $isReviewed = in_array($praStatusKey, ['reviewed', 'direkomendasikan', 'selesai']);
                    $isInReview = in_array($praStatusKey, ['ditinjau', 'in_review', 'diproses']);
                    $praStatusLabel = match ($praStatusKey) {
                        'baru', 'submitted', 'menunggu' => 'Menunggu Respon',
                        'ditinjau', 'in_review', 'diproses', 'reviewed' => 'Sedang Ditinjau',
                        'direkomendasikan', 'selesai' => 'Direkomendasikan',
                        'ditutup' => 'Ditutup',
                        default => ucfirst($praStatusKey),
                    };
                    $praStatusClass = match ($praStatusKey) {
                        'baru', 'submitted', 'menunggu' => 'bg-amber-100 text-amber-800 border-amber-300',
                        'ditinjau', 'in_review', 'diproses', 'reviewed' => 'bg-blue-100 text-blue-800 border-blue-300',
                        'direkomendasikan', 'selesai' => 'bg-blue-600 text-white border-blue-700 shadow-sm shadow-blue-600/20',
                        'ditutup' => 'bg-rose-100 text-rose-800 border-rose-300',
                        default => 'bg-slate-100 text-slate-700 border-slate-300',
                    };
                @endphp
                <div class="bg-[#FAF8F5] rounded-2xl border {{ $isReviewed ? 'border-blue-200 ring-4 ring-blue-50' : 'border-[#E8E0D8]/60' }} p-5 relative overflow-hidden transition-all">
                    @if($isReviewed)
                        <div class="absolute top-0 right-0 w-32 h-32 bg-blue-500/5 rounded-full -mr-10 -mt-10 blur-2xl"></div>
                    @endif
                    <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4 relative z-10">
                        <div class="flex items-start gap-4 w-full">
                            <div class="w-10 h-10 rounded-xl {{ $isReviewed ? 'bg-blue-100 text-blue-600' : 'bg-[#CFAF53]/10 text-[#CFAF53]' }} flex items-center justify-center shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex flex-col sm:flex-row sm:items-center gap-1.5 sm:gap-3 mb-1.5">
                                    <h4 class="font-bold text-[#1A1A1A] text-base truncate">{{ $isReviewed ? 'Hasil Konsultasi Tersedia' : 'Tinjauan Konsultasi' }}</h4>
                                    <span class="w-fit shrink-0 px-2.5 py-0.5 rounded-full text-[10px] font-extrabold border tracking-wider {{ $praStatusClass }} uppercase">
                                        {{ $praStatusLabel }}
                                    </span>
                                </div>
                                <p class="text-xs text-slate-400 flex items-center gap-1">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    Diajukan pada {{ $latestPraKonsul->created_at->translatedFormat('d M Y, H:i') }} WIB
                                </p>

                                @if($isReviewed)
                                    <div class="mt-3 bg-white px-3.5 py-2.5 rounded-xl border border-blue-100 shadow-sm border-l-4 border-l-blue-500">
                                        <p class="text-sm font-medium text-slate-700">
                                            Dokter telah memberikan rekomendasi untuk kondisi kamu.
                                        </p>
                                    </div>
                                @else
                                    <p class="text-sm text-slate-500 mt-2 line-clamp-2">{{ $latestPraKonsul->keluhan_utama ?? 'Sedang ditinjau oleh tim dokter.' }}</p>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div x-data="{ showLatestDetail: false }" class="relative z-10">
                        <div class="mt-4 pt-4 border-t border-[#E8E0D8]/40 flex justify-end">
                            @if($isReviewed)
                                <button @click="showLatestDetail = true" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold rounded-full transition-colors shadow-lg shadow-blue-600/20">
                                    Lihat Rekomendasi
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                                </button>
                            @else
                                <button @click="showLatestDetail = true" class="text-sm font-bold text-[#8C7A63] hover:text-[#1A1A1A] transition-colors">Lihat Detail →</button>
                            @endif
                        </div>

                        {{-- Modal: Detail Pra-Konsultasi Terbaru --}}
                        <template x-teleport="body">
                            <div x-show="showLatestDetail" 
                                 x-transition:enter="transition ease-out duration-300"
                                 x-transition:enter-start="opacity-0"
                                 x-transition:enter-end="opacity-100"
                                 x-transition:leave="transition ease-in duration-200"
                                 x-transition:leave-start="opacity-100"
                                 x-transition:leave-end="opacity-0"
                                 class="fixed inset-0 z-[999] flex items-center justify-center p-4 bg-[#1A1A1A]/40 backdrop-blur-sm"
                                 style="display: none;">

                                <div @click.away="showLatestDetail = false" 
                                     x-transition:enter="transition ease-out duration-300"
                                     x-transition:enter-start="opacity-0 scale-95 translate-y-4"
                                     x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                                     class="bg-white w-full max-w-2xl max-h-[90vh] rounded-3xl shadow-2xl overflow-hidden flex flex-col">

                                    {{-- Modal Header --}}
                                    <div class="px-6 py-5 border-b border-[#E8E0D8]/60 flex items-center justify-between shrink-0 bg-white">
                                        <div>
                                            <h3 class="text-xl font-extrabold text-[#1A1A1A]">Detail Pra-Konsultasi</h3>
                                            <p class="text-xs text-slate-400 mt-0.5">#PK-{{ $latestPraKonsul->patient_form_id }} • {{ $latestPraKonsul->created_at->translatedFormat('d F Y, H:i') }} WIB</p>
                                        </div>
                                        <button @click="showLatestDetail = false" class="w-10 h-10 rounded-full bg-slate-50 flex items-center justify-center text-slate-400 hover:bg-slate-100 hover:text-slate-600 transition-colors">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                        </button>
                                    </div>

                                    {{-- Modal Body --}}
                                    <div class="p-6 overflow-y-auto custom-scrollbar space-y-6">
                                        {{-- Row 1: Vital Stats --}}
                                        <div class="grid grid-cols-2 gap-4">
                                            <div class="bg-[#FAF8F5] rounded-2xl border border-[#E8E0D8]/60 p-4">
                                                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Golongan Darah</p>
                                                <p class="text-lg font-extrabold text-[#1A1A1A]">{{ $latestPraKonsul->golongan_darah ?? '-' }}</p>
                                            </div>
                                            <div class="bg-[#FAF8F5] rounded-2xl border border-[#E8E0D8]/60 p-4">
                                                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Jenis Kulit</p>
                                                <p class="text-lg font-extrabold text-[#1A1A1A]">{{ $latestPraKonsul->jenis_kulit ?? '-' }}</p>
                                            </div>
                                        </div>

                                        {{-- Skincare --}}
                                        <div class="bg-[#FAF8F5] rounded-2xl border border-[#E8E0D8]/60 p-5">
                                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2">Skincare Sebelumnya</p>
                                            <p class="text-sm text-[#1A1A1A] font-medium leading-relaxed">{{ $latestPraKonsul->skincare_sebelumnya ?? '-' }}</p>
                                        </div>

                                        {{-- Riwayat Perawatan --}}
                                        <div>
                                            <h4 class="text-[10px] font-bold text-[#8C7A63] uppercase tracking-[0.2em] mb-3">Riwayat Perawatan</h4>
                                            <div class="grid grid-cols-2 gap-3">
                                                @php
                                                    $perawatan = [
                                                        ['label' => 'Facial', 'value' => $latestPraKonsul->pernah_facial],
                                                        ['label' => 'Skin Booster', 'value' => $latestPraKonsul->pernah_skin_booster],
                                                        ['label' => 'Laser', 'value' => $latestPraKonsul->pernah_laser],
                                                        ['label' => 'Inject/Infus', 'value' => $latestPraKonsul->pernah_inject_infus],
                                                    ];
                                                @endphp
                                                @foreach($perawatan as $p)
                                                    <div class="flex items-center gap-3 px-4 py-3 rounded-xl border border-[#E8E0D8]/60 {{ $p['value'] ? 'bg-emerald-50/50 border-emerald-100' : 'bg-slate-50/50' }}">
                                                        <div class="w-5 h-5 rounded-full flex items-center justify-center {{ $p['value'] ? 'bg-emerald-500 text-white' : 'bg-slate-200 text-slate-400' }}">
                                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                @if($p['value'])
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                                                                @else
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"/>
                                                                @endif
                                                            </svg>
                                                        </div>
                                                        <span class="text-sm font-bold {{ $p['value'] ? 'text-emerald-700' : 'text-slate-400' }}">{{ $p['label'] }}</span>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>

                                        {{-- Foto Wajah --}}
                                        <div>
                                            <h4 class="text-[10px] font-bold text-[#8C7A63] uppercase tracking-[0.2em] mb-3">📸 Foto Wajah</h4>
                                            <div class="grid grid-cols-3 gap-3">
                                                @foreach(['foto_depan_path' => 'Depan', 'foto_kiri_path' => 'Kiri', 'foto_kanan_path' => 'Kanan'] as $field => $pos)
                                                    @php $path = $latestPraKonsul->$field; @endphp
                                                    <div class="text-center">
                                                        <div class="aspect-square rounded-2xl border border-[#E8E0D8]/60 overflow-hidden bg-slate-50 mb-1.5 shadow-sm">
                                                            @if($path)
                                                                <img src="{{ asset('storage/' . $path) }}" class="w-full h-full object-cover">
                                                            @else
                                                                <div class="w-full h-full flex flex-col items-center justify-center text-slate-300">
                                                                    <svg class="w-8 h-8 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                                                    <span class="text-[10px] font-bold uppercase tracking-wider">No Image</span>
                                                                </div>
                                                            @endif
                                                        </div>
                                                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">{{ $pos }}</span>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>

                                        {{-- Diagnosa & Rekomendasi Dokter --}}
                                        @if($latestPraKonsul->diagnosa || $latestPraKonsul->rekomendasiItems->isNotEmpty())
                                            <div class="space-y-4 border-t border-[#E8E0D8]/60 mt-4 pt-4">
                                                
                                                {{-- Dokter's General Note --}}
                                                @if($latestPraKonsul->diagnosa)
                                                    <div class="bg-white border border-[#E8E0D8] rounded-xl p-4 shadow-sm relative overflow-hidden group">
                                                        <div class="absolute left-0 top-0 bottom-0 w-1 bg-[#CFAF53] rounded-l-xl"></div>
                                                        <div class="flex items-center gap-2 mb-2 pl-2">
                                                            <svg class="w-4 h-4 text-[#CFAF53]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
                                                            <p class="text-xs font-bold text-[#8C7A63] uppercase tracking-wider">Diagnosa Dokter</p>
                                                        </div>
                                                        <p class="text-sm text-[#1A1A1A] leading-relaxed pl-2">{{ $latestPraKonsul->diagnosa }}</p>
                                                    </div>
                                                @endif
    
                                                {{-- Produk/Treatment Iteration --}}
                                                @if($latestPraKonsul->rekomendasiItems->isNotEmpty())
                                                    @foreach($latestPraKonsul->rekomendasiItems as $item)
                                                        @php 
                                                            $tipe = $item->item_tipe;
                                                            $obj = $tipe === 'Produk' ? $item->product : ($tipe === 'Treatment' ? $item->treatment : $item->bundle);
                                                        @endphp
                                                        <div class="bg-white border border-[#E8E0D8] rounded-xl p-4 shadow-sm hover:border-[#CFAF53]/40 transition-colors relative overflow-hidden">
                                                            <div class="flex items-center gap-2 mb-4 border-b border-[#E8E0D8]/60 pb-3">
                                                                @if($tipe === 'Treatment')
                                                                    <svg class="w-4 h-4 text-[#CFAF53]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                                    <p class="text-xs font-bold text-[#8C7A63] uppercase tracking-wider">Treatment Rekomendasi</p>
                                                                @elseif($tipe === 'Bundle')
                                                                    <svg class="w-4 h-4 text-[#CFAF53]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                                    <p class="text-xs font-bold text-[#8C7A63] uppercase tracking-wider">Bundle Rekomendasi</p>
                                                                @else
                                                                    <svg class="w-4 h-4 text-[#CFAF53]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                                                                    <p class="text-xs font-bold text-[#8C7A63] uppercase tracking-wider">Produk Rekomendasi</p>
                                                                @endif
                                                            </div>
    
                                                            <div class="flex flex-col sm:flex-row items-start gap-4">
                                                                {{-- Image --}}
                                                                <div class="w-20 h-20 rounded-lg bg-[#FAF8F5] border border-[#E8E0D8] shrink-0 overflow-hidden flex items-center justify-center">
                                                                    @if($obj && isset($obj->foto_path) && $obj->foto_path)
                                                                        <img src="{{ asset('storage/' . $obj->foto_path) }}" alt="{{ $item->item_nama }}" class="w-full h-full object-cover">
                                                                    @else
                                                                        <svg class="w-8 h-8 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                                                    @endif
                                                                </div>
    
                                                                {{-- Details --}}
                                                                <div class="flex-1 min-w-0 flex flex-col justify-between h-full w-full">
                                                                    <div>
                                                                        <p class="font-bold text-[#1A1A1A] text-base leading-tight mb-1">{{ $item->item_nama }}</p>
                                                                        
                                                                        @if($item->catatan)
                                                                            <p class="text-sm text-slate-600 mb-2">{{ $item->catatan }}</p>
                                                                        @endif
    
                                                                        @if($tipe === 'Treatment' && isset($obj->durasi_menit))
                                                                            <p class="text-xs font-medium text-slate-500 mb-2">{{ $obj->durasi_menit }} menit</p>
                                                                        @endif
                                                                        
                                                                        @if($obj && (isset($obj->harga) || isset($obj->harga_promo) || isset($obj->harga_normal)))
                                                                            <p class="text-sm font-bold text-[#CFAF53]">Rp {{ number_format($obj->harga ?? $obj->harga_promo ?? $obj->harga_normal ?? 0, 0, ',', '.') }}</p>
                                                                        @endif
                                                                    </div>
    
                                                                    {{-- Actions --}}
                                                                    <div class="mt-4 flex flex-wrap items-center gap-2">
                                                                        @if($tipe === 'Treatment')
                                                                            <a href="{{ route('appointment.select') }}" class="inline-flex items-center justify-center px-5 py-2 text-xs font-bold text-white bg-[#1A1A1A] rounded-lg hover:bg-[#333] hover:shadow-lg hover:shadow-[#1A1A1A]/20 transition-all w-full sm:w-auto text-center">Buat Janji Temu</a>
                                                                        @else
                                                                            <form action="{{ $tipe === 'Bundle' ? route('cart.add_bundle', $obj->bundle_id ?? 0) : route('cart.add', $obj->product_id ?? 0) }}" method="POST" class="inline-block flex-1 sm:flex-none">
                                                                                @csrf
                                                                                <button type="submit" class="w-full inline-flex items-center justify-center px-5 py-2 text-xs font-bold text-[#1A1A1A] bg-[#FAF8F5] border border-[#E8E0D8] rounded-lg hover:bg-white hover:border-[#CFAF53] transition-all shadow-sm">+ Keranjang</button>
                                                                            </form>
                                                                            <a href="{{ route('cart.index') }}" class="inline-flex items-center justify-center px-5 py-2 text-xs font-bold text-white bg-[#1A1A1A] rounded-lg hover:bg-[#333] hover:shadow-lg hover:shadow-[#1A1A1A]/20 transition-all flex-1 sm:flex-none text-center">Beli Sekarang</a>
                                                                        @endif
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                @endif
                                            </div>
                                        @endif
                                    </div>

                                    {{-- Modal Footer --}}
                                    <div class="p-6 border-t border-[#E8E0D8]/60 bg-slate-50/50 shrink-0">
                                        <button @click="showLatestDetail = false" class="w-full bg-[#1A1A1A] text-white font-bold py-4 rounded-2xl hover:bg-black transition-colors shadow-lg shadow-black/10">
                                            Tutup
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            @endif

            {{-- Card: Pesanan Terbaru --}}
            @if($latestOrder)
                <div class="bg-[#FAF8F5] rounded-2xl border border-[#E8E0D8]/60 p-5">
                    @php
                        // Deteksi apakah order ini terkait janji temu (treatment/konsultasi)
                        $linkedAppointmentId = $latestOrder->items->firstWhere('appointment_id', '!=', null)?->appointment_id;
                        $isAppointmentBased = $linkedAppointmentId
                            || in_array($latestOrder->order_type, ['treatment', 'konsultasi']);

                        $shipIcon = $isAppointmentBased
                            ? '<svg class="w-5 h-5 text-[#CFAF53]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>'
                            : match ($latestOrder->shipping_status) {
                                'dikirim' => '<svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10l2-1 2 1 2-1 2 1m0 0V6a1 1 0 011-1h2a1 1 0 011 1v4l4 2v6h-3m-7 0h7m-7 0a2 2 0 11-4 0m11 0a2 2 0 11-4 0"/></svg>',
                                'selesai' => '<svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>',
                                default => '<svg class="w-5 h-5 text-[#CFAF53]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>',
                            };
                        $orderStatusLabel = match ($latestOrder->status) {
                            'menunggu_pembayaran' => 'Belum Bayar',
                            'sudah_dibayar' => match ($latestOrder->shipping_status) {
                                    'dikirim' => 'Dikirim',
                                    'selesai' => 'Selesai',
                                    default => 'Dikemas',
                                },
                            'batal', 'dibatalkan' => 'Dibatalkan',
                            default => ucfirst($latestOrder->status),
                        };
                        $orderStatusBadge = match ($orderStatusLabel) {
                            'Belum Bayar' => 'bg-amber-50 text-amber-700 border-amber-200',
                            'Dikemas' => 'bg-blue-50 text-blue-700 border-blue-200',
                            'Dikirim' => 'bg-indigo-50 text-indigo-700 border-indigo-200',
                            'Selesai' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                            'Dibatalkan' => 'bg-rose-50 text-rose-700 border-rose-200',
                            default => 'bg-slate-50 text-slate-600 border-slate-200',
                        };
                    @endphp
                    <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4">
                        <div class="flex items-start gap-4 w-full">
                            <div class="w-10 h-10 rounded-xl bg-slate-50 flex items-center justify-center shrink-0">
                                {!! $shipIcon !!}
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex flex-col sm:flex-row sm:items-center gap-1.5 sm:gap-3 mb-1.5">
                                    <h4 class="font-bold text-[#1A1A1A] text-base truncate">{{ $latestOrder->items->first()?->product?->nama ?? $latestOrder->items->first()?->treatment?->nama ?? 'Pesanan Aishi Beauty' }}</h4>
                                    <span class="w-fit shrink-0 px-2.5 py-0.5 rounded-full text-[10px] font-extrabold border tracking-wider {{ $orderStatusBadge }} uppercase">
                                        {{ $orderStatusLabel }}
                                    </span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="text-xs text-slate-400 font-mono">{{ $latestOrder->kode_order }}</span>
                                    <span class="text-slate-300">•</span>
                                    <span class="text-xs text-slate-400">{{ $latestOrder->created_at->translatedFormat('d M Y') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="mt-4 pt-4 border-t border-[#E8E0D8]/40 flex justify-end">
                        @if($isAppointmentBased)
                            <button
                                @click="activeTab = 'appointment'; @if($linkedAppointmentId) highlightId = '{{ $linkedAppointmentId }}'; setTimeout(() => highlightId = null, 4000); @endif"
                                class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-full font-bold text-sm text-[#1A1A1A] bg-white border border-[#E8E0D8] hover:border-[#CFAF53] hover:bg-[#CFAF53]/5 transition-colors shadow-sm cursor-pointer">
                                Lihat Janji Temu
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                                </svg>
                            </button>
                        @else
                            <button @click="activeTab = 'orders'" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-full font-bold text-sm text-[#1A1A1A] bg-white border border-[#E8E0D8] hover:border-[#CFAF53] hover:bg-[#CFAF53]/5 transition-colors shadow-sm cursor-pointer">
                                Lihat Pesanan
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                                </svg>
                            </button>
                        @endif
                    </div>
                </div>
            @endif

            @if(!$latestPraKonsul && !$latestOrder)
                <div class="dash-empty">
                    <div aria-hidden="true" class="dash-empty__orb"></div>
                    <div class="dash-empty__icon">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                        </svg>
                    </div>
                    <p class="dash-empty__title">Belum ada aktivitas terkini</p>
                    <p class="dash-empty__desc">Riwayat konsultasi, pesanan, dan rekam perawatan Anda akan tampil di sini.</p>
                </div>
            @endif
            </div>
        </div>
    </div>
</div>

<style>
    /* ─────────  SHARED SECTION CARD + EMPTY STATE  ───────── */
    .dash-section {
        position: relative;
        overflow: hidden;
        background:
            radial-gradient(140% 90% at 100% 0%, rgba(207, 175, 83, 0.05) 0%, transparent 55%),
            #ffffff;
        border: 1px solid rgba(232, 224, 216, 0.7);
        border-radius: 22px;
        padding: 1.5rem;
        box-shadow:
            0 1px 2px rgba(26, 26, 26, 0.03),
            0 8px 24px -18px rgba(184, 149, 111, 0.18);
    }
    @media (min-width: 768px) {
        .dash-section { padding: 1.75rem; }
    }

    .dash-section__head {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 1rem;
        margin-bottom: 1.25rem;
    }
    .dash-section__eyebrow {
        font-family: 'Plus Jakarta Sans', sans-serif;
        font-size: 0.6875rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.22em;
        color: #8C7A63;
    }
    .dash-section__sub {
        margin-top: 0.25rem;
        font-size: 0.75rem;
        color: #94A3B8;
        font-weight: 500;
    }
    .dash-section__link {
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
        padding: 0.4375rem 0.875rem;
        border-radius: 9999px;
        background: rgba(207, 175, 83, 0.1);
        color: #B8956F;
        font-family: 'Plus Jakarta Sans', sans-serif;
        font-size: 0.75rem;
        font-weight: 700;
        text-decoration: none;
        white-space: nowrap;
        transition: background 200ms ease, color 200ms ease, transform 200ms ease;
        cursor: pointer;
    }
    .dash-section__link:hover {
        background: linear-gradient(135deg, #CFAF53 0%, #B8956F 100%);
        color: #fff;
        transform: translateY(-1px);
    }

    /* Empty state — premium dashed card */
    .dash-empty {
        position: relative;
        overflow: hidden;
        padding: 2.5rem 1.5rem;
        text-align: center;
        background:
            radial-gradient(80% 100% at 50% 0%, rgba(207, 175, 83, 0.06) 0%, transparent 60%),
            linear-gradient(180deg, #FDFCFA 0%, #FAF8F5 100%);
        border: 1.5px dashed rgba(184, 149, 111, 0.3);
        border-radius: 20px;
    }
    .dash-empty__orb {
        position: absolute;
        top: -60px; right: -50px;
        width: 180px; height: 180px;
        border-radius: 9999px;
        background: rgba(207, 175, 83, 0.18);
        filter: blur(60px);
        pointer-events: none;
        opacity: 0.7;
    }
    .dash-empty__icon {
        position: relative;
        z-index: 1;
        margin: 0 auto 1rem;
        width: 60px; height: 60px;
        border-radius: 18px;
        background: linear-gradient(135deg, #FFFFFF 0%, #FAF8F5 100%);
        border: 1px solid rgba(232, 224, 216, 0.8);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: #B8956F;
        box-shadow:
            0 1px 2px rgba(26, 26, 26, 0.04),
            0 8px 18px -8px rgba(184, 149, 111, 0.3);
    }
    .dash-empty__icon svg { width: 28px; height: 28px; stroke-width: 1.6; }
    .dash-empty__title {
        position: relative;
        z-index: 1;
        font-family: 'Plus Jakarta Sans', sans-serif;
        font-size: 0.9375rem;
        font-weight: 800;
        color: #1A1A1A;
        letter-spacing: -0.01em;
    }
    .dash-empty__desc {
        position: relative;
        z-index: 1;
        margin-top: 0.375rem;
        max-width: 28rem;
        margin-left: auto;
        margin-right: auto;
        font-size: 0.8125rem;
        line-height: 1.55;
        color: #64748B;
    }
    .dash-empty__cta {
        position: relative;
        z-index: 1;
        margin-top: 1.25rem;
        display: inline-flex;
        align-items: center;
        gap: 0.4375rem;
        padding: 0.625rem 1.25rem;
        border-radius: 9999px;
        background: linear-gradient(135deg, #CFAF53 0%, #B8956F 100%);
        color: #fff;
        font-family: 'Plus Jakarta Sans', sans-serif;
        font-size: 0.8125rem;
        font-weight: 700;
        text-decoration: none;
        box-shadow:
            0 1px 2px rgba(184, 149, 111, 0.25),
            0 10px 22px -10px rgba(184, 149, 111, 0.55);
        transition: transform 200ms ease, box-shadow 200ms ease, filter 200ms ease;
        cursor: pointer;
    }
    .dash-empty__cta:hover {
        transform: translateY(-2px);
        filter: brightness(1.05);
        box-shadow:
            0 1px 2px rgba(184, 149, 111, 0.3),
            0 14px 26px -10px rgba(184, 149, 111, 0.6);
    }
    .dash-empty__cta:active { transform: translateY(0); }

    @media (prefers-reduced-motion: reduce) {
        .dash-section__link,
        .dash-empty__cta { transition: none; }
        .dash-section__link:hover,
        .dash-empty__cta:hover { transform: none; filter: none; }
    }
</style>

