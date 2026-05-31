<x-guest-layout :title="'Checkout'">
    <div class="checkout-page">
        <div class="checkout-page__container">

            {{-- Header --}}
            <header class="checkout-header">
                <div class="checkout-header__heading">
                    <p class="checkout-header__eyebrow">
                        <span class="checkout-header__dot"></span>
                        Pembayaran
                    </p>
                    <h1 class="checkout-header__title">Checkout</h1>
                    <p class="checkout-header__subtitle">Lengkapi pengiriman dan pilih metode pengiriman pesanan Anda.</p>
                </div>

                {{-- Stepper --}}
                <ol class="checkout-stepper" aria-label="Progress checkout">
                    <li class="checkout-stepper__step checkout-stepper__step--done">
                        <span class="checkout-stepper__dot">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                            </svg>
                        </span>
                        <span class="checkout-stepper__label">Keranjang</span>
                    </li>
                    <li class="checkout-stepper__connector" aria-hidden="true"></li>
                    <li class="checkout-stepper__step checkout-stepper__step--active">
                        <span class="checkout-stepper__dot">2</span>
                        <span class="checkout-stepper__label">Pengiriman</span>
                    </li>
                    <li class="checkout-stepper__connector" aria-hidden="true"></li>
                    <li class="checkout-stepper__step">
                        <span class="checkout-stepper__dot">3</span>
                        <span class="checkout-stepper__label">Pembayaran</span>
                    </li>
                </ol>
            </header>

            @php
                $userPhone = auth()->user()->phone ?? null;
                $checkoutFields = [
                    'phone'       => ['label' => 'Nomor HP',         'filled' => !empty($userPhone)],
                    'province'    => ['label' => 'Provinsi',          'filled' => !empty($pasien?->provinsi) || !empty($pasien?->province_id)],
                    'city'        => ['label' => 'Kota / Kabupaten',  'filled' => !empty($pasien?->kota) || !empty($pasien?->city_id)],
                    'district'    => ['label' => 'Kecamatan',         'filled' => !empty($pasien?->district_name) || !empty($pasien?->district_id)],
                    'address'     => ['label' => 'Alamat Lengkap',    'filled' => !empty($pasien?->alamat)],
                ];
                $checkoutMissing = collect($checkoutFields)->where('filled', false)->isNotEmpty();
            @endphp

        {{-- CEK: Apakah user sudah lengkapi profil (HP + alamat)? --}}
        @if($checkoutMissing)
            {{-- BELUM LENGKAP: Tampilkan notice premium --}}
            <section class="checkout-notice">
                <div aria-hidden="true" class="checkout-notice__glow"></div>

                <div class="checkout-notice__inner">
                    <div class="checkout-notice__icon">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>

                    <h2 class="checkout-notice__title">Lengkapi Data Pengiriman</h2>
                    <p class="checkout-notice__desc">
                        Untuk melanjutkan checkout, lengkapi nomor HP dan alamat pengiriman Anda terlebih dahulu agar pesanan dapat diproses dengan benar.
                    </p>

                    <div class="checkout-notice__checklist-wrap">
                        <p class="checkout-notice__checklist-label">Data yang dibutuhkan</p>
                        <ul class="checkout-notice__checklist" role="list">
                            @foreach($checkoutFields as $key => $field)
                                <li class="checkout-notice__item {{ $field['filled'] ? 'checkout-notice__item--done' : 'checkout-notice__item--missing' }}">
                                    <span class="checkout-notice__item-dot">
                                        @if($field['filled'])
                                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                                            </svg>
                                        @else
                                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                <circle cx="12" cy="12" r="9" stroke-width="1.6"/>
                                            </svg>
                                        @endif
                                    </span>
                                    <span class="checkout-notice__item-label">{{ $field['label'] }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>

                    <div class="checkout-notice__actions">
                        <a href="{{ route('dashboard', ['tab' => 'profile']) }}" class="checkout-notice__btn checkout-notice__btn--primary">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                            <span>Lengkapi Profil</span>
                        </a>
                        <a href="{{ route('cart.index') }}" class="checkout-notice__back">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                            </svg>
                            <span>Kembali ke Keranjang</span>
                        </a>
                    </div>
                </div>
            </section>

            <style>
                /* ─────────  CHECKOUT PROFILE-INCOMPLETE NOTICE  ───────── */
                .checkout-notice {
                    position: relative;
                    overflow: hidden;
                    max-width: 620px;
                    margin: 0 auto;
                    background:
                        radial-gradient(120% 80% at 100% 0%, rgba(207, 175, 83, 0.1) 0%, transparent 60%),
                        linear-gradient(180deg, #FFFFFF 0%, #FAF8F5 100%);
                    border: 1px solid rgba(184, 149, 111, 0.28);
                    border-radius: 20px;
                    box-shadow:
                        0 1px 2px rgba(26, 26, 26, 0.04),
                        0 14px 32px -16px rgba(184, 149, 111, 0.28);
                }
                .checkout-notice__glow {
                    position: absolute;
                    top: -80px; right: -60px;
                    width: 240px; height: 240px;
                    border-radius: 9999px;
                    background: rgba(207, 175, 83, 0.16);
                    filter: blur(70px);
                    pointer-events: none;
                    opacity: 0.7;
                }
                .checkout-notice__inner {
                    position: relative;
                    z-index: 1;
                    padding: 1.75rem 1.5rem;
                    display: flex;
                    flex-direction: column;
                    align-items: center;
                    text-align: center;
                    gap: 1rem;
                }
                @media (min-width: 640px) {
                    .checkout-notice__inner { padding: 2rem 2.25rem; gap: 1.125rem; }
                }

                .checkout-notice__icon {
                    width: 52px; height: 52px;
                    border-radius: 14px;
                    display: inline-flex;
                    align-items: center;
                    justify-content: center;
                    background: linear-gradient(135deg, #FFF8EB 0%, #FFE9C2 100%);
                    color: #B45309;
                    border: 1px solid rgba(245, 158, 11, 0.28);
                    box-shadow:
                        inset 0 0 0 1px rgba(255, 255, 255, 0.6),
                        0 4px 10px -4px rgba(245, 158, 11, 0.4);
                }
                .checkout-notice__icon svg { width: 24px; height: 24px; stroke-width: 1.8; }

                .checkout-notice__title {
                    font-family: 'Plus Jakarta Sans', sans-serif;
                    font-size: 1.25rem;
                    font-weight: 800;
                    letter-spacing: -0.01em;
                    color: #1A1A1A;
                    line-height: 1.25;
                    margin: 0;
                }
                @media (min-width: 640px) {
                    .checkout-notice__title { font-size: 1.375rem; }
                }
                .checkout-notice__desc {
                    font-family: 'Plus Jakarta Sans', sans-serif;
                    font-size: 0.875rem;
                    line-height: 1.65;
                    color: #64748B;
                    max-width: 28rem;
                    margin: 0;
                }
                @media (min-width: 640px) {
                    .checkout-notice__desc { font-size: 0.9375rem; }
                }

                /* Checklist */
                .checkout-notice__checklist-wrap {
                    width: 100%;
                    margin-top: 0.25rem;
                    padding: 1rem 1.125rem;
                    background: #ffffff;
                    border: 1px solid rgba(232, 224, 216, 0.7);
                    border-radius: 14px;
                    text-align: left;
                }
                .checkout-notice__checklist-label {
                    font-family: 'Plus Jakarta Sans', sans-serif;
                    font-size: 0.6875rem;
                    font-weight: 700;
                    text-transform: uppercase;
                    letter-spacing: 0.16em;
                    color: #94A3B8;
                    margin: 0 0 0.625rem 0;
                }
                .checkout-notice__checklist {
                    list-style: none;
                    margin: 0;
                    padding: 0;
                    display: flex;
                    flex-direction: column;
                    gap: 0.5rem;
                }
                .checkout-notice__item {
                    display: flex;
                    align-items: center;
                    gap: 0.625rem;
                    font-family: 'Plus Jakarta Sans', sans-serif;
                    font-size: 0.875rem;
                    line-height: 1.4;
                }
                .checkout-notice__item-dot {
                    width: 20px;
                    height: 20px;
                    border-radius: 9999px;
                    display: inline-flex;
                    align-items: center;
                    justify-content: center;
                    flex-shrink: 0;
                    background: #ffffff;
                    border: 1.5px solid #E2E8F0;
                    color: transparent;
                }
                .checkout-notice__item-dot svg { width: 11px; height: 11px; }
                .checkout-notice__item--done .checkout-notice__item-dot {
                    background: linear-gradient(135deg, #10B981 0%, #047857 100%);
                    border-color: transparent;
                    color: #ffffff;
                    box-shadow: 0 2px 4px -2px rgba(16, 185, 129, 0.5);
                }
                .checkout-notice__item--done .checkout-notice__item-label {
                    color: #047857;
                    text-decoration: line-through;
                    text-decoration-color: rgba(16, 185, 129, 0.4);
                    font-weight: 600;
                }
                .checkout-notice__item--missing .checkout-notice__item-dot {
                    border-color: rgba(245, 158, 11, 0.5);
                    color: rgba(245, 158, 11, 0.5);
                }
                .checkout-notice__item--missing .checkout-notice__item-label {
                    color: #1A1A1A;
                    font-weight: 600;
                }

                /* Actions */
                .checkout-notice__actions {
                    margin-top: 0.5rem;
                    width: 100%;
                    display: flex;
                    flex-direction: column;
                    align-items: center;
                    gap: 0.5rem;
                }
                .checkout-notice__btn {
                    width: 100%;
                    max-width: 360px;
                    min-height: 46px;
                    display: inline-flex;
                    align-items: center;
                    justify-content: center;
                    gap: 0.5rem;
                    padding: 0.75rem 1.5rem;
                    border-radius: 9999px;
                    font-family: 'Plus Jakarta Sans', sans-serif;
                    font-size: 0.875rem;
                    font-weight: 700;
                    text-decoration: none;
                    cursor: pointer;
                    transition: transform 200ms ease, box-shadow 200ms ease, filter 200ms ease;
                }
                .checkout-notice__btn svg { width: 16px; height: 16px; }
                .checkout-notice__btn--primary {
                    background: linear-gradient(135deg, #CFAF53 0%, #B8956F 100%);
                    color: #ffffff;
                    border: 1px solid transparent;
                    box-shadow:
                        0 1px 2px rgba(184, 149, 111, 0.25),
                        0 8px 18px -8px rgba(184, 149, 111, 0.55);
                }
                .checkout-notice__btn--primary:hover {
                    transform: translateY(-1px);
                    filter: brightness(1.05);
                    box-shadow:
                        0 1px 2px rgba(184, 149, 111, 0.3),
                        0 14px 24px -8px rgba(184, 149, 111, 0.6);
                }
                .checkout-notice__btn--primary:active { transform: translateY(0); }

                .checkout-notice__back {
                    display: inline-flex;
                    align-items: center;
                    gap: 0.375rem;
                    font-family: 'Plus Jakarta Sans', sans-serif;
                    font-size: 0.8125rem;
                    font-weight: 600;
                    color: #8C7A63;
                    text-decoration: none;
                    padding: 0.5rem 0.75rem;
                    border-radius: 9999px;
                    transition: color 180ms ease, background 180ms ease;
                }
                .checkout-notice__back svg { width: 13px; height: 13px; }
                .checkout-notice__back:hover {
                    color: #1A1A1A;
                    background: rgba(184, 149, 111, 0.08);
                }

                @media (prefers-reduced-motion: reduce) {
                    .checkout-notice__btn { transition: none; }
                    .checkout-notice__btn:hover { transform: none; filter: none; }
                }
            </style>
        @else
            {{-- SUDAH LENGKAP: Lanjut checkout --}}
            <form method="POST" action="{{ route('checkout.store') }}"
                  class="checkout-grid"
                  x-data="checkoutData()">
                @csrf

                {{-- Hidden Inputs untuk Controller --}}
                <input type="hidden" name="shipping_cost" x-model="selectedShippingCost">
                <input type="hidden" name="shipping_courier" x-model="selectedShippingCourier">
                <input type="hidden" name="district_id" value="{{ $pasien->district_id }}">

                {{-- ═════════ KOLOM KIRI ═════════ --}}
                <div class="checkout-main">

                    {{-- Compact info chips --}}
                    <div class="checkout-chips">
                        <div class="checkout-chip">
                            <span class="checkout-chip__icon checkout-chip__icon--gold">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                </svg>
                            </span>
                            <div class="checkout-chip__body">
                                <p class="checkout-chip__label">Dikirim Dari</p>
                                <p class="checkout-chip__value">{{ config('app.clinic_name', 'Klinik Aishi Beauty') }}</p>
                                <p class="checkout-chip__hint">{{ config('app.clinic_district', 'Beji') }}, {{ config('app.clinic_city', 'Kota Depok') }}</p>
                            </div>
                        </div>
                    </div>

                    {{-- ALAMAT PENGIRIMAN (READ-ONLY, premium) --}}
                    <section class="checkout-card">
                        <div class="checkout-card__head">
                            <div>
                                <p class="checkout-card__eyebrow">Alamat Pengiriman</p>
                                <h2 class="checkout-card__title">Detail Penerima</h2>
                            </div>
                            <a href="{{ route('dashboard', ['tab' => 'profile_alamat']) }}" class="checkout-card__edit">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                </svg>
                                <span>Ubah Alamat</span>
                            </a>
                        </div>

                        <div class="checkout-address">
                            <div class="checkout-address__head">
                                <span class="checkout-address__name">{{ auth()->user()->name }}</span>
                                <span class="checkout-address__badge">Alamat Utama</span>
                            </div>
                            <p class="checkout-address__phone">{{ auth()->user()->phone ?? 'Belum ada nomor HP' }}</p>
                            <p class="checkout-address__line">{{ $pasien->alamat }}</p>
                            <p class="checkout-address__line checkout-address__line--muted">
                                {{ $pasien->district_name }}, {{ $pasien->kota }}, {{ $pasien->provinsi }}@if($pasien->kode_pos), {{ $pasien->kode_pos }}@endif
                            </p>
                        </div>
                    </section>

                    {{-- PILIHAN KURIR --}}
                    <section class="checkout-card">
                        <div class="checkout-card__head">
                            <div>
                                <p class="checkout-card__eyebrow">Pengiriman</p>
                                <h2 class="checkout-card__title">Pilih Kurir Pengiriman</h2>
                            </div>
                            <span class="checkout-card__chip" x-show="costs.length > 0 && !isLoadingOngkir">
                                <span x-text="costs.length"></span> opsi tersedia
                            </span>
                        </div>

                        {{-- Route info --}}
                        <div class="checkout-route">
                            <div class="checkout-route__path">
                                <span class="checkout-route__node">{{ config('app.clinic_district', 'Beji') }}</span>
                                <svg class="checkout-route__arrow" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                                </svg>
                                <span class="checkout-route__node">{{ $pasien->district_name ?? 'Tujuan' }}</span>
                            </div>
                            <span class="checkout-route__weight">Total Berat: <strong>~1 kg</strong></span>
                        </div>

                        {{-- LOADING --}}
                        <div x-show="isLoadingOngkir" class="checkout-loading">
                            <div class="checkout-loading__skeleton"></div>
                            <div class="checkout-loading__skeleton"></div>
                            <div class="checkout-loading__skeleton"></div>
                            <p class="checkout-loading__text">Menghitung ongkir...</p>
                        </div>

                        {{-- ERROR --}}
                        <div x-show="ongkirError && !isLoadingOngkir" class="checkout-error">
                            <span class="checkout-error__icon">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </span>
                            <p class="checkout-error__title">Gagal menghitung ongkir</p>
                            <p class="checkout-error__msg" x-text="ongkirError"></p>
                            <button type="button" @click="checkOngkir()" class="checkout-error__btn">Coba Lagi</button>
                        </div>

                        {{-- COURIER LIST (grouped by code) --}}
                        <div x-show="costs.length > 0 && !isLoadingOngkir" x-cloak class="checkout-couriers" x-data="{ openGroup: null }" x-init="$nextTick(() => { openGroup = Object.keys(courierGroups)[0] || null })">
                            <template x-for="(services, code) in courierGroups" :key="code">
                                <div class="checkout-courier-group" :class="{ 'checkout-courier-group--open': openGroup === code }">
                                    <button type="button"
                                            class="checkout-courier-group__head"
                                            @click="openGroup = (openGroup === code ? null : code)">
                                        <div class="checkout-courier-group__brand">
                                            <span class="checkout-courier-group__logo" x-text="code.toUpperCase()"></span>
                                            <div>
                                                <p class="checkout-courier-group__name" x-text="code.toUpperCase()"></p>
                                                <p class="checkout-courier-group__meta" x-text="services.length + ' layanan tersedia'"></p>
                                            </div>
                                        </div>
                                        <svg class="checkout-courier-group__caret" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                        </svg>
                                    </button>

                                    <div class="checkout-courier-group__body" x-show="openGroup === code" x-collapse>
                                        <template x-for="(cost, i) in services" :key="i">
                                            <label class="checkout-courier"
                                                   :class="{ 'checkout-courier--selected': selectedShippingCourier === (cost.courier_code + ' ' + cost.service) }">
                                                <input type="radio"
                                                       name="courier_radio"
                                                       class="sr-only"
                                                       @change="selectCourier(cost.cost, cost.courier_code + ' ' + cost.service)"
                                                       :checked="selectedShippingCourier === (cost.courier_code + ' ' + cost.service)">
                                                <span class="checkout-courier__radio" aria-hidden="true">
                                                    <span class="checkout-courier__radio-inner"></span>
                                                </span>
                                                <div class="checkout-courier__body">
                                                    <p class="checkout-courier__service" x-text="cost.service"></p>
                                                    <p class="checkout-courier__etd">
                                                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                        </svg>
                                                        <span x-text="'Estimasi ' + cost.etd"></span>
                                                    </p>
                                                </div>
                                                <p class="checkout-courier__cost" x-text="'Rp ' + parseInt(cost.cost).toLocaleString('id-ID')"></p>
                                            </label>
                                        </template>
                                    </div>
                                </div>
                            </template>
                        </div>

                        {{-- EMPTY --}}
                        <div x-show="costs.length === 0 && !isLoadingOngkir && !ongkirError" x-cloak class="checkout-empty">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                            </svg>
                            <p>Belum ada pilihan kurir tersedia.</p>
                        </div>
                    </section>

                    {{-- CATATAN PENGIRIMAN --}}
                    <section class="checkout-card">
                        <div class="checkout-card__head">
                            <div>
                                <p class="checkout-card__eyebrow">Catatan</p>
                                <h2 class="checkout-card__title">Catatan Pengiriman <span class="checkout-card__optional">(opsional)</span></h2>
                            </div>
                        </div>
                        <textarea name="shipping_notes"
                                  rows="3"
                                  placeholder="Contoh: Titip di satpam atau hubungi saat paket tiba."
                                  class="checkout-textarea"></textarea>
                        <p class="checkout-card__helper">Catatan ini membantu kurir menemukan alamat Anda dengan lebih mudah.</p>
                    </section>
                </div>

                {{-- ═════════ KOLOM KANAN (RINGKASAN) ═════════ --}}
                <aside class="checkout-summary">
                    <div class="checkout-summary__inner">
                        <div class="checkout-summary__head">
                            <h3 class="checkout-summary__title">Ringkasan Pesanan</h3>
                            <span class="checkout-summary__count">{{ count($cart) }} item</span>
                        </div>

                        {{-- Items list --}}
                        <ul class="checkout-summary__items">
                            @foreach($cart as $item)
                                <li class="checkout-summary__item">
                                    <div class="checkout-summary__item-info">
                                        <p class="checkout-summary__item-name">{{ $item['nama'] }}</p>
                                        <p class="checkout-summary__item-qty">{{ $item['qty'] }} × Rp {{ number_format($item['harga'], 0, ',', '.') }}</p>
                                    </div>
                                    <p class="checkout-summary__item-price">Rp {{ number_format($item['harga'] * $item['qty'], 0, ',', '.') }}</p>
                                </li>
                            @endforeach
                        </ul>

                        <div class="checkout-summary__divider"></div>

                        {{-- Promo --}}
                        <div class="checkout-promo">
                            <p class="checkout-promo__label">Punya kode promo?</p>
                            <div class="checkout-promo__row">
                                <input type="text"
                                       x-model="promoCodeInput"
                                       placeholder="MASUKKAN KODE"
                                       class="checkout-promo__input"
                                       :disabled="appliedPromoCode !== null">
                                <button type="button"
                                        x-show="!appliedPromoCode"
                                        @click="checkPromo()"
                                        :disabled="!promoCodeInput || isLoadingPromo"
                                        class="checkout-promo__btn">
                                    <span x-show="!isLoadingPromo">Pakai</span>
                                    <span x-show="isLoadingPromo">...</span>
                                </button>
                                <button type="button"
                                        x-show="appliedPromoCode"
                                        @click="cancelPromo()"
                                        class="checkout-promo__cancel"
                                        aria-label="Batal pakai promo">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.4" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            </div>
                            <p x-show="promoMessage" class="checkout-promo__msg"
                               :class="{ 'checkout-promo__msg--ok': promoValid, 'checkout-promo__msg--err': !promoValid }"
                               x-text="promoMessage"></p>
                            <input type="hidden" name="promo_code" x-model="appliedPromoCode">
                        </div>

                        <div class="checkout-summary__divider"></div>

                        {{-- Totals --}}
                        <dl class="checkout-summary__totals">
                            <div>
                                <dt>Subtotal Produk</dt>
                                <dd>Rp {{ number_format($subtotal, 0, ',', '.') }}</dd>
                            </div>
                            <div>
                                <dt>Ongkos Kirim</dt>
                                <dd x-text="selectedShippingCost > 0 ? 'Rp ' + parseInt(selectedShippingCost).toLocaleString('id-ID') : '—'"></dd>
                            </div>
                            <div x-show="discountAmount > 0" x-transition class="checkout-summary__totals-discount">
                                <dt>Diskon Promo</dt>
                                <dd x-text="'− Rp ' + parseInt(discountAmount).toLocaleString('id-ID')"></dd>
                            </div>
                        </dl>

                        <div class="checkout-summary__divider"></div>

                        <div class="checkout-summary__grand">
                            <span class="checkout-summary__grand-label">Total Bayar</span>
                            <span class="checkout-summary__grand-value"
                                  x-text="'Rp ' + ({{ $subtotal }} + parseInt(selectedShippingCost || 0) - parseInt(discountAmount || 0)).toLocaleString('id-ID')"></span>
                        </div>

                        {{-- CTA --}}
                        <button type="submit"
                                class="checkout-cta"
                                :class="{ 'checkout-cta--disabled': isLoadingOngkir || !selectedShippingCourier }"
                                :disabled="isLoadingOngkir || !selectedShippingCourier">
                            <span x-show="!isLoadingOngkir && selectedShippingCourier">
                                <span>Lanjut ke Pembayaran</span>
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.4" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                                </svg>
                            </span>
                            <span x-show="!isLoadingOngkir && !selectedShippingCourier">Pilih Kurir Terlebih Dahulu</span>
                            <span x-show="isLoadingOngkir">Memproses...</span>
                        </button>

                        <p class="checkout-summary__notice" x-show="!selectedShippingCourier && !isLoadingOngkir">
                            Pilih kurir di samping untuk melanjutkan.
                        </p>
                        <p class="checkout-summary__terms">
                            Dengan melanjutkan, Anda menyetujui syarat dan ketentuan kami.
                        </p>
                    </div>
                </aside>
            </form>
        @endif
    </div>
    </div>

    <style>
        /* ════════ PAGE CONTAINER & HEADER ════════ */
        .checkout-page {
            background: #FAF8F5;
            min-height: 100vh;
        }
        .checkout-page__container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 1.5rem 1rem 4rem;
        }
        @media (min-width: 768px) {
            .checkout-page__container { padding: 2rem 1.5rem 5rem; }
        }
        @media (min-width: 1024px) {
            .checkout-page__container { padding: 2.5rem 2rem 5rem; }
        }

        .checkout-header {
            margin-bottom: 1.75rem;
            display: flex;
            flex-direction: column;
            gap: 1.25rem;
        }
        @media (min-width: 768px) {
            .checkout-header { margin-bottom: 2.25rem; gap: 1.5rem; }
        }
        @media (min-width: 1024px) {
            .checkout-header {
                flex-direction: row;
                align-items: center;
                justify-content: space-between;
            }
        }
        .checkout-header__heading { min-width: 0; }
        .checkout-header__eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            margin: 0 0 0.5rem 0;
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 0.6875rem;
            font-weight: 800;
            letter-spacing: 0.28em;
            text-transform: uppercase;
            color: #B8956F;
        }
        .checkout-header__dot {
            width: 5px; height: 5px;
            border-radius: 9999px;
            background: #CFAF53;
            box-shadow: 0 0 0 3px rgba(207, 175, 83, 0.18);
        }
        .checkout-header__title {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 1.75rem;
            font-weight: 900;
            letter-spacing: -0.02em;
            color: #1A1A1A;
            line-height: 1.15;
            margin: 0;
        }
        @media (min-width: 768px) {
            .checkout-header__title { font-size: 2rem; }
        }
        .checkout-header__subtitle {
            margin-top: 0.5rem;
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 0.9375rem;
            line-height: 1.55;
            color: #64748B;
            max-width: 32rem;
        }

        /* ════════ STEPPER ════════ */
        .checkout-stepper {
            list-style: none;
            margin: 0;
            padding: 0.625rem 0.875rem;
            display: flex;
            align-items: center;
            gap: 0.625rem;
            background: #ffffff;
            border: 1px solid rgba(232, 224, 216, 0.7);
            border-radius: 9999px;
            box-shadow: 0 1px 2px rgba(26, 26, 26, 0.03);
            overflow-x: auto;
            scrollbar-width: none;
        }
        .checkout-stepper::-webkit-scrollbar { display: none; }
        .checkout-stepper__step {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            white-space: nowrap;
        }
        .checkout-stepper__dot {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 22px;
            height: 22px;
            border-radius: 9999px;
            background: #ffffff;
            color: #94A3B8;
            border: 1.5px solid #E2E8F0;
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 0.6875rem;
            font-weight: 800;
            flex-shrink: 0;
        }
        .checkout-stepper__dot svg { width: 12px; height: 12px; }
        .checkout-stepper__step--done .checkout-stepper__dot {
            background: linear-gradient(135deg, #CFAF53 0%, #B8956F 100%);
            border-color: transparent;
            color: #ffffff;
            box-shadow: 0 2px 6px -2px rgba(184, 149, 111, 0.5);
        }
        .checkout-stepper__step--active .checkout-stepper__dot {
            background: rgba(207, 175, 83, 0.14);
            border-color: rgba(184, 149, 111, 0.55);
            color: #B8956F;
        }
        .checkout-stepper__label {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 0.75rem;
            font-weight: 700;
            color: #94A3B8;
        }
        .checkout-stepper__step--done .checkout-stepper__label,
        .checkout-stepper__step--active .checkout-stepper__label { color: #1A1A1A; }
        .checkout-stepper__connector {
            flex: 0 0 24px;
            height: 1.5px;
            background: #E2E8F0;
            border-radius: 9999px;
        }
        @media (min-width: 768px) {
            .checkout-stepper__connector { flex-basis: 32px; }
        }

        /* ════════ GRID ════════ */
        .checkout-grid {
            display: grid;
            grid-template-columns: minmax(0, 1fr);
            gap: 1.25rem;
        }
        @media (min-width: 1024px) {
            .checkout-grid {
                grid-template-columns: minmax(0, 1fr) minmax(0, 380px);
                gap: 1.75rem;
                align-items: start;
            }
        }
        .checkout-main {
            display: flex;
            flex-direction: column;
            gap: 1.25rem;
        }
        @media (min-width: 768px) {
            .checkout-main { gap: 1.5rem; }
        }

        /* ════════ INFO CHIPS ════════ */
        .checkout-chips {
            display: grid;
            grid-template-columns: 1fr;
            gap: 0.75rem;
        }
        @media (min-width: 640px) {
            .checkout-chips { grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 1rem; }
        }
        .checkout-chip {
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
            padding: 0.875rem 1rem;
            background: #ffffff;
            border: 1px solid rgba(232, 224, 216, 0.65);
            border-radius: 14px;
            box-shadow: 0 1px 2px rgba(26, 26, 26, 0.02);
        }
        .checkout-chip__icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 36px; height: 36px;
            border-radius: 11px;
            flex-shrink: 0;
        }
        .checkout-chip__icon svg { width: 18px; height: 18px; }
        .checkout-chip__icon--gold {
            background: linear-gradient(135deg, rgba(207, 175, 83, 0.15) 0%, rgba(184, 149, 111, 0.18) 100%);
            color: #B8956F;
            border: 1px solid rgba(184, 149, 111, 0.22);
        }
        .checkout-chip__icon--success {
            background: linear-gradient(135deg, #ECFDF5 0%, #D1FAE5 100%);
            color: #047857;
            border: 1px solid rgba(16, 185, 129, 0.28);
        }
        .checkout-chip__body { min-width: 0; flex: 1; }
        .checkout-chip__label {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 0.625rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.18em;
            color: #94A3B8;
            margin: 0 0 2px 0;
        }
        .checkout-chip__value {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 0.875rem;
            font-weight: 800;
            color: #1A1A1A;
            line-height: 1.3;
            margin: 0;
        }
        .checkout-chip__hint {
            font-size: 0.75rem;
            color: #94A3B8;
            margin: 2px 0 0 0;
        }
        .checkout-chip__link {
            display: inline-block;
            margin-top: 4px;
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 0.75rem;
            font-weight: 700;
            color: #B8956F;
            text-decoration: none;
        }
        .checkout-chip__link:hover { color: #8C7A63; text-decoration: underline; }

        /* ════════ CARD ════════ */
        .checkout-card {
            background: #ffffff;
            border: 1px solid rgba(232, 224, 216, 0.65);
            border-radius: 18px;
            padding: 1.25rem;
            box-shadow: 0 1px 2px rgba(26, 26, 26, 0.03);
        }
        @media (min-width: 768px) {
            .checkout-card { padding: 1.5rem; }
        }
        .checkout-card__head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.75rem;
            margin-bottom: 1rem;
            flex-wrap: wrap;
        }
        .checkout-card__eyebrow {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 0.6875rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.18em;
            color: #94A3B8;
            margin: 0 0 4px 0;
        }
        .checkout-card__title {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 1.0625rem;
            font-weight: 800;
            letter-spacing: -0.01em;
            color: #1A1A1A;
            margin: 0;
        }
        .checkout-card__optional {
            color: #94A3B8;
            font-weight: 500;
            font-size: 0.875rem;
        }
        .checkout-card__chip {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 0.75rem;
            font-weight: 700;
            color: #B8956F;
            background: rgba(207, 175, 83, 0.1);
            border: 1px solid rgba(184, 149, 111, 0.22);
            padding: 0.3125rem 0.75rem;
            border-radius: 9999px;
        }
        .checkout-card__edit {
            display: inline-flex;
            align-items: center;
            gap: 0.375rem;
            padding: 0.4375rem 0.875rem;
            border-radius: 9999px;
            background: rgba(207, 175, 83, 0.08);
            color: #B8956F;
            border: 1px solid rgba(184, 149, 111, 0.25);
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 0.75rem;
            font-weight: 700;
            text-decoration: none;
            transition: background 200ms ease, color 200ms ease, transform 200ms ease;
        }
        .checkout-card__edit svg { width: 12px; height: 12px; }
        .checkout-card__edit:hover {
            background: linear-gradient(135deg, #CFAF53 0%, #B8956F 100%);
            color: #fff;
            border-color: transparent;
            transform: translateY(-1px);
        }
        .checkout-card__helper {
            margin: 0.625rem 0 0 0;
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 0.75rem;
            color: #94A3B8;
            line-height: 1.5;
        }

        /* ════════ ADDRESS ════════ */
        .checkout-address {
            padding: 1rem 1.125rem;
            background: linear-gradient(180deg, #FAF8F5 0%, #FFFFFF 100%);
            border: 1px solid rgba(232, 224, 216, 0.6);
            border-radius: 14px;
        }
        .checkout-address__head {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 0.5rem;
            margin-bottom: 4px;
        }
        .checkout-address__name {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 1rem;
            font-weight: 800;
            color: #1A1A1A;
            letter-spacing: -0.005em;
        }
        .checkout-address__badge {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 0.625rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.12em;
            padding: 0.25rem 0.5rem;
            border-radius: 9999px;
            background: rgba(207, 175, 83, 0.14);
            color: #8C7A63;
            border: 1px solid rgba(184, 149, 111, 0.22);
        }
        .checkout-address__phone {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 0.8125rem;
            color: #94A3B8;
            margin: 0 0 0.625rem 0;
        }
        .checkout-address__line {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 0.875rem;
            line-height: 1.6;
            color: #1A1A1A;
            margin: 0;
        }
        .checkout-address__line--muted { color: #64748B; margin-top: 2px; }

        /* ════════ ROUTE INFO ════════ */
        .checkout-route {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.75rem;
            margin-bottom: 0.875rem;
            padding: 0.625rem 0.875rem;
            background: #FAF8F5;
            border: 1px solid rgba(232, 224, 216, 0.6);
            border-radius: 12px;
            flex-wrap: wrap;
        }
        .checkout-route__path {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 0.8125rem;
            font-weight: 700;
            color: #1A1A1A;
        }
        .checkout-route__node { white-space: nowrap; }
        .checkout-route__arrow { width: 14px; height: 14px; color: #B8956F; flex-shrink: 0; }
        .checkout-route__weight {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 0.75rem;
            color: #94A3B8;
        }
        .checkout-route__weight strong { color: #1A1A1A; font-weight: 700; }

        /* ════════ COURIER GROUP ════════ */
        .checkout-couriers {
            display: flex;
            flex-direction: column;
            gap: 0.625rem;
        }
        .checkout-courier-group {
            background: #ffffff;
            border: 1px solid rgba(232, 224, 216, 0.7);
            border-radius: 14px;
            overflow: hidden;
            transition: border-color 200ms ease, box-shadow 200ms ease;
        }
        .checkout-courier-group--open {
            border-color: rgba(184, 149, 111, 0.4);
            box-shadow: 0 4px 14px -8px rgba(184, 149, 111, 0.25);
        }
        .checkout-courier-group__head {
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.875rem;
            padding: 0.875rem 1rem;
            background: transparent;
            border: 0;
            cursor: pointer;
            text-align: left;
            transition: background 180ms ease;
        }
        .checkout-courier-group__head:hover { background: #FAF8F5; }
        .checkout-courier-group__brand {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            min-width: 0;
        }
        .checkout-courier-group__logo {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 48px;
            height: 36px;
            padding: 0 0.625rem;
            border-radius: 8px;
            background: linear-gradient(135deg, #FAF8F5 0%, #F5F0EB 100%);
            border: 1px solid rgba(184, 149, 111, 0.18);
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 0.6875rem;
            font-weight: 900;
            letter-spacing: 0.05em;
            color: #8C7A63;
            flex-shrink: 0;
        }
        .checkout-courier-group__name {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 0.9375rem;
            font-weight: 800;
            color: #1A1A1A;
            line-height: 1.2;
            margin: 0;
            letter-spacing: -0.005em;
        }
        .checkout-courier-group__meta {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 0.75rem;
            color: #94A3B8;
            margin: 2px 0 0 0;
        }
        .checkout-courier-group__caret {
            width: 16px; height: 16px;
            color: #94A3B8;
            transition: transform 220ms ease;
            flex-shrink: 0;
        }
        .checkout-courier-group--open .checkout-courier-group__caret {
            transform: rotate(180deg);
            color: #B8956F;
        }
        .checkout-courier-group__body {
            padding: 0 0.625rem 0.625rem;
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        /* ════════ COURIER ITEM ════════ */
        .checkout-courier {
            display: flex;
            align-items: center;
            gap: 0.875rem;
            padding: 0.75rem 0.875rem;
            background: #FDFCFA;
            border: 1.5px solid rgba(232, 224, 216, 0.7);
            border-radius: 12px;
            cursor: pointer;
            transition: border-color 200ms ease, background 200ms ease, box-shadow 200ms ease;
        }
        .checkout-courier:hover {
            border-color: rgba(184, 149, 111, 0.5);
            background: #ffffff;
        }
        .checkout-courier--selected {
            border-color: #CFAF53;
            background: linear-gradient(135deg, rgba(207, 175, 83, 0.08) 0%, rgba(184, 149, 111, 0.05) 100%);
            box-shadow: 0 4px 14px -6px rgba(184, 149, 111, 0.32);
        }
        .checkout-courier__radio {
            width: 18px;
            height: 18px;
            border-radius: 9999px;
            border: 1.5px solid #CBD5E1;
            background: #ffffff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            transition: border-color 200ms ease, background 200ms ease;
        }
        .checkout-courier__radio-inner {
            width: 8px;
            height: 8px;
            border-radius: 9999px;
            background: transparent;
            transform: scale(0);
            transition: transform 200ms ease;
        }
        .checkout-courier--selected .checkout-courier__radio {
            border-color: #B8956F;
            background: linear-gradient(135deg, #CFAF53 0%, #B8956F 100%);
        }
        .checkout-courier--selected .checkout-courier__radio-inner {
            background: #ffffff;
            transform: scale(1);
        }
        .checkout-courier__body { flex: 1; min-width: 0; }
        .checkout-courier__service {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 0.875rem;
            font-weight: 700;
            color: #1A1A1A;
            margin: 0;
        }
        .checkout-courier__etd {
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 0.75rem;
            color: #94A3B8;
            margin: 2px 0 0 0;
        }
        .checkout-courier__etd svg { width: 12px; height: 12px; }
        .checkout-courier__cost {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 0.9375rem;
            font-weight: 800;
            color: #1A1A1A;
            margin: 0;
            white-space: nowrap;
        }
        .checkout-courier--selected .checkout-courier__cost { color: #B8956F; }

        /* ════════ LOADING / ERROR / EMPTY ════════ */
        .checkout-loading {
            padding: 0.625rem 0;
            display: flex;
            flex-direction: column;
            gap: 0.625rem;
            position: relative;
        }
        .checkout-loading__skeleton {
            height: 60px;
            border-radius: 12px;
            background: linear-gradient(90deg, #F5F0EB 0%, #FAF8F5 50%, #F5F0EB 100%);
            background-size: 200% 100%;
            animation: checkoutSkeleton 1.4s ease-in-out infinite;
        }
        .checkout-loading__text {
            position: absolute;
            inset: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 0.8125rem;
            font-weight: 700;
            color: #B8956F;
        }
        @keyframes checkoutSkeleton {
            0%   { background-position: -200% 0; }
            100% { background-position: 200% 0; }
        }

        .checkout-error {
            padding: 1.5rem 1rem;
            text-align: center;
            background: #FEF2F2;
            border: 1px solid rgba(244, 63, 94, 0.22);
            border-radius: 14px;
        }
        .checkout-error__icon {
            display: inline-flex;
            width: 36px; height: 36px;
            margin: 0 auto 0.625rem;
            align-items: center;
            justify-content: center;
            border-radius: 9999px;
            background: rgba(244, 63, 94, 0.12);
            color: #E11D48;
        }
        .checkout-error__icon svg { width: 18px; height: 18px; }
        .checkout-error__title {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 0.875rem;
            font-weight: 700;
            color: #9F1239;
            margin: 0 0 4px 0;
        }
        .checkout-error__msg {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 0.75rem;
            color: #BE123C;
            margin: 0 0 0.625rem 0;
        }
        .checkout-error__btn {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 0.75rem;
            font-weight: 700;
            color: #BE123C;
            background: rgba(244, 63, 94, 0.1);
            border: 1px solid rgba(244, 63, 94, 0.22);
            padding: 0.4375rem 0.875rem;
            border-radius: 9999px;
            cursor: pointer;
            transition: background 180ms ease;
        }
        .checkout-error__btn:hover { background: rgba(244, 63, 94, 0.18); }

        .checkout-empty {
            padding: 2rem 1rem;
            text-align: center;
            background: #FAF8F5;
            border: 1.5px dashed rgba(232, 224, 216, 0.85);
            border-radius: 14px;
        }
        .checkout-empty svg { width: 38px; height: 38px; color: #CBD5E1; margin: 0 auto 0.625rem; }
        .checkout-empty p {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 0.8125rem;
            color: #94A3B8;
            margin: 0;
        }

        /* ════════ TEXTAREA ════════ */
        .checkout-textarea {
            width: 100%;
            min-height: 100px;
            padding: 0.75rem 0.875rem;
            border-radius: 12px;
            border: 1px solid rgba(232, 224, 216, 0.85);
            background: #ffffff;
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 0.9375rem;
            line-height: 1.6;
            color: #1A1A1A;
            resize: vertical;
            transition: border-color 180ms ease, box-shadow 180ms ease;
        }
        .checkout-textarea::placeholder { color: #94A3B8; }
        .checkout-textarea:focus {
            outline: none;
            border-color: #CFAF53;
            box-shadow: 0 0 0 3px rgba(207, 175, 83, 0.18);
        }

        /* ════════ SUMMARY (RIGHT COLUMN) ════════ */
        .checkout-summary {
            position: relative;
        }
        @media (min-width: 1024px) {
            .checkout-summary {
                position: sticky;
                top: 1.5rem;
            }
        }
        .checkout-summary__inner {
            background:
                radial-gradient(120% 60% at 100% 0%, rgba(207, 175, 83, 0.06) 0%, transparent 60%),
                #ffffff;
            border: 1px solid rgba(232, 224, 216, 0.7);
            border-radius: 20px;
            padding: 1.25rem;
            box-shadow:
                0 1px 2px rgba(26, 26, 26, 0.03),
                0 12px 28px -16px rgba(184, 149, 111, 0.18);
        }
        @media (min-width: 768px) {
            .checkout-summary__inner { padding: 1.5rem; }
        }
        .checkout-summary__head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1rem;
            padding-bottom: 0.875rem;
            border-bottom: 1px solid rgba(232, 224, 216, 0.6);
        }
        .checkout-summary__title {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 1rem;
            font-weight: 800;
            color: #1A1A1A;
            margin: 0;
            letter-spacing: -0.005em;
        }
        .checkout-summary__count {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 0.75rem;
            font-weight: 700;
            color: #B8956F;
            background: rgba(207, 175, 83, 0.1);
            border: 1px solid rgba(184, 149, 111, 0.22);
            padding: 0.25rem 0.625rem;
            border-radius: 9999px;
        }

        .checkout-summary__items {
            list-style: none;
            margin: 0 0 0.5rem 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            gap: 0.625rem;
            max-height: 280px;
            overflow-y: auto;
            padding-right: 0.25rem;
        }
        .checkout-summary__items::-webkit-scrollbar { width: 4px; }
        .checkout-summary__items::-webkit-scrollbar-thumb { background: rgba(184, 149, 111, 0.28); border-radius: 4px; }
        .checkout-summary__item {
            display: flex;
            align-items: flex-start;
            gap: 0.625rem;
            padding: 0.625rem 0.75rem;
            background: #FAF8F5;
            border-radius: 10px;
            border: 1px solid rgba(232, 224, 216, 0.5);
        }
        .checkout-summary__item-info { flex: 1; min-width: 0; }
        .checkout-summary__item-name {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 0.8125rem;
            font-weight: 700;
            color: #1A1A1A;
            margin: 0;
            line-height: 1.35;
            word-break: break-word;
        }
        .checkout-summary__item-qty {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 0.6875rem;
            color: #94A3B8;
            margin: 2px 0 0 0;
        }
        .checkout-summary__item-price {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 0.8125rem;
            font-weight: 800;
            color: #1A1A1A;
            margin: 0;
            white-space: nowrap;
            text-align: right;
        }

        .checkout-summary__divider {
            height: 1px;
            background: rgba(232, 224, 216, 0.6);
            margin: 1rem 0;
        }

        /* ════════ PROMO ════════ */
        .checkout-promo__label {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 0.6875rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.16em;
            color: #94A3B8;
            margin: 0 0 0.5rem 0;
        }
        .checkout-promo__row {
            display: flex;
            gap: 0.5rem;
        }
        .checkout-promo__input {
            flex: 1;
            min-height: 40px;
            padding: 0.5rem 0.75rem;
            border-radius: 10px;
            border: 1px dashed rgba(184, 149, 111, 0.35);
            background: #FAF8F5;
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 0.8125rem;
            font-weight: 700;
            color: #1A1A1A;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            transition: border-color 180ms ease, background 180ms ease;
        }
        .checkout-promo__input::placeholder {
            color: #94A3B8;
            font-weight: 600;
            letter-spacing: 0.04em;
        }
        .checkout-promo__input:focus {
            outline: none;
            border-color: #CFAF53;
            border-style: solid;
            background: #ffffff;
        }
        .checkout-promo__input:disabled {
            background: #ECFDF5;
            border-color: rgba(16, 185, 129, 0.32);
            color: #047857;
        }
        .checkout-promo__btn {
            min-height: 40px;
            padding: 0.5rem 0.875rem;
            border-radius: 10px;
            background: linear-gradient(135deg, #1A1A1A 0%, #2C2622 100%);
            color: #ffffff;
            border: 0;
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 0.8125rem;
            font-weight: 700;
            cursor: pointer;
            transition: filter 180ms ease, transform 180ms ease;
        }
        .checkout-promo__btn:hover:not(:disabled) {
            filter: brightness(1.15);
            transform: translateY(-1px);
        }
        .checkout-promo__btn:disabled {
            opacity: 0.45;
            cursor: not-allowed;
        }
        .checkout-promo__cancel {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            border-radius: 10px;
            background: #FEF2F2;
            color: #BE123C;
            border: 1px solid rgba(244, 63, 94, 0.22);
            cursor: pointer;
            transition: background 180ms ease;
        }
        .checkout-promo__cancel svg { width: 14px; height: 14px; }
        .checkout-promo__cancel:hover { background: #FEE2E2; }

        .checkout-promo__msg {
            margin: 0.5rem 0 0 0;
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 0.75rem;
            font-weight: 600;
            line-height: 1.4;
        }
        .checkout-promo__msg--ok  { color: #047857; }
        .checkout-promo__msg--err { color: #BE123C; }

        /* ════════ TOTALS ════════ */
        .checkout-summary__totals {
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }
        .checkout-summary__totals > div {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .checkout-summary__totals dt {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 0.8125rem;
            color: #64748B;
        }
        .checkout-summary__totals dd {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 0.875rem;
            font-weight: 700;
            color: #1A1A1A;
            margin: 0;
        }
        .checkout-summary__totals-discount dt { color: #047857; font-weight: 600; }
        .checkout-summary__totals-discount dd { color: #047857; }

        .checkout-summary__grand {
            display: flex;
            align-items: baseline;
            justify-content: space-between;
            margin-bottom: 1rem;
        }
        .checkout-summary__grand-label {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 0.875rem;
            font-weight: 700;
            color: #1A1A1A;
        }
        .checkout-summary__grand-value {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 1.375rem;
            font-weight: 900;
            letter-spacing: -0.01em;
            background: linear-gradient(120deg, #1A1A1A 0%, #B8956F 100%);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        @media (min-width: 768px) {
            .checkout-summary__grand-value { font-size: 1.5rem; }
        }

        /* ════════ CTA ════════ */
        .checkout-cta {
            width: 100%;
            min-height: 50px;
            padding: 0.875rem 1.25rem;
            border-radius: 9999px;
            background: linear-gradient(135deg, #CFAF53 0%, #B8956F 100%);
            color: #ffffff;
            border: 0;
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 0.9375rem;
            font-weight: 800;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            box-shadow:
                0 1px 2px rgba(184, 149, 111, 0.25),
                0 12px 24px -8px rgba(184, 149, 111, 0.55);
            transition: transform 200ms ease, box-shadow 200ms ease, filter 200ms ease;
        }
        .checkout-cta > span {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }
        .checkout-cta svg { width: 16px; height: 16px; }
        .checkout-cta:hover:not(:disabled) {
            transform: translateY(-1px);
            filter: brightness(1.05);
            box-shadow:
                0 1px 2px rgba(184, 149, 111, 0.3),
                0 16px 30px -8px rgba(184, 149, 111, 0.6);
        }
        .checkout-cta--disabled,
        .checkout-cta:disabled {
            background: #E2E8F0;
            color: #94A3B8;
            box-shadow: none;
            cursor: not-allowed;
            transform: none;
        }

        .checkout-summary__notice {
            margin: 0.625rem 0 0 0;
            text-align: center;
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 0.75rem;
            font-weight: 600;
            color: #B45309;
        }
        .checkout-summary__terms {
            margin: 0.875rem 0 0 0;
            text-align: center;
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 0.6875rem;
            color: #94A3B8;
            line-height: 1.5;
        }

        @media (prefers-reduced-motion: reduce) {
            .checkout-loading__skeleton { animation: none; }
            .checkout-courier-group__caret,
            .checkout-courier__radio,
            .checkout-courier__radio-inner,
            .checkout-cta,
            .checkout-card__edit,
            .checkout-promo__btn { transition: none; }
            .checkout-cta:hover:not(:disabled),
            .checkout-card__edit:hover,
            .checkout-promo__btn:hover { transform: none; filter: none; }
        }

        [x-cloak] { display: none !important; }
    </style>

    {{-- ALPINE.JS SCRIPT --}}
    <script>
        function checkoutData() {
            return {
                // Data Ongkir
                costs: [],
                selectedShippingCost: 0,
                selectedShippingCourier: '',
                isLoadingOngkir: false,
                ongkirError: '',

                // [BARU] Data Promo
                promoCodeInput: '', // Apa yang diketik user
                appliedPromoCode: null, // Kode yang valid dan tersimpan
                discountAmount: 0, // Nominal diskon
                isLoadingPromo: false,
                promoMessage: '',
                promoValid: false,
                
                // Static Subtotal dari PHP
                subtotalDB: {{ $subtotal }}, 

                init() {
                    console.log('🚀 Checkout initialized');
                    @if($pasien && $pasien->district_id)
                        this.checkOngkir();
                    @endif
                },

                // --- FUNGSI ONGKIR (SAMA) ---
                async checkOngkir() {
                    const districtId = '{{ $pasien->district_id ?? "" }}';
                    if (!districtId) return;

                    this.isLoadingOngkir = true;
                    this.ongkirError = '';
                    this.costs = [];
                    
                    try {
                        const response = await fetch("{{ route('api.checkOngkir') }}", {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                            body: JSON.stringify({ district_id: districtId })
                        });

                        if (!response.ok) throw new Error('Gagal load ongkir');
                        const data = await response.json();
                        
                        if (Array.isArray(data) && data.length > 0) {
                            this.costs = data;
                        } else {
                            this.ongkirError = 'Kurir tidak tersedia';
                        }
                    } catch (error) {
                        this.ongkirError = 'Gagal menghitung ongkir';
                    } finally {
                        this.isLoadingOngkir = false;
                    }
                },

                selectCourier(price, name) {
                    this.selectedShippingCost = price;
                    this.selectedShippingCourier = name;
                },

                /** Group courier costs by `courier_code` for accordion UI */
                get courierGroups() {
                    const groups = {};
                    for (const c of this.costs) {
                        const code = (c.courier_code || 'lainnya').toLowerCase();
                        if (!groups[code]) groups[code] = [];
                        groups[code].push(c);
                    }
                    return groups;
                },

                // --- [BARU] FUNGSI PROMO ---
                async checkPromo() {
                    if(!this.promoCodeInput) return;

                    this.isLoadingPromo = true;
                    this.promoMessage = '';
                    
                    try {
                        const response = await fetch("{{ route('api.checkPromo') }}", {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                            // Kirim kode dan subtotal (buat validasi min pembelian)
                            body: JSON.stringify({ 
                                kode: this.promoCodeInput,
                                subtotal: this.subtotalDB 
                            })
                        });

                        const data = await response.json();

                        if (data.valid) {
                            // Sukses
                            this.promoValid = true;
                            this.promoMessage = data.message;
                            this.discountAmount = data.discount_amount;
                            this.appliedPromoCode = data.kode; // Kunci kode agar tidak bisa diedit
                        } else {
                            // Gagal (Syarat tidak penuhi / Salah kode)
                            this.promoValid = false;
                            this.promoMessage = data.message;
                            this.discountAmount = 0;
                            this.appliedPromoCode = null;
                        }
                    } catch (error) {
                        this.promoValid = false;
                        this.promoMessage = 'Terjadi kesalahan sistem.';
                    } finally {
                        this.isLoadingPromo = false;
                    }
                },

                cancelPromo() {
                    this.promoCodeInput = '';
                    this.appliedPromoCode = null;
                    this.discountAmount = 0;
                    this.promoMessage = '';
                    this.promoValid = false;
                }
            }
        }
    </script>

    {{-- Custom Styles --}}
    <style>
        [x-cloak] { display: none !important; }
    </style>
</x-guest-layout>