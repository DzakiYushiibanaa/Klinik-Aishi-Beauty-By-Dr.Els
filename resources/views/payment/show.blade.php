<x-guest-layout :title="'Pembayaran'">

<div class="pay-page">
    <div class="pay-page__container">

        <header class="pay-header">
            <h1 class="pay-header__title">Selesaikan Pembayaran</h1>
            <p class="pay-header__subtitle">Pesanan Anda siap diproses setelah pembayaran berhasil.</p>
            @include('payment._partials._stepper', ['activeStep' => 3])
        </header>

        <article class="pay-card" aria-labelledby="pay-section-title">
            @include('payment._partials._order_id')
            @include('payment._partials._order_items')
            @include('payment._partials._shipping_summary')
            @include('payment._partials._cost_summary')
        </article>

        <a href="{{ route('dashboard') }}" class="pay-back">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            <span>Kembali ke Dashboard</span>
        </a>
    </div>
</div>

<style>
    /* ════════ PAGE ════════ */
    .pay-page {
        background: #FAF8F5;
        min-height: 100vh;
        padding: 2rem 1rem 3rem;
    }
    @media (min-width: 640px) {
        .pay-page { padding: 3rem 1.5rem 4rem; }
    }

    .pay-page__container {
        max-width: 560px;
        margin: 0 auto;
    }

    /* ════════ HEADER ════════ */
    .pay-header {
        text-align: center;
        margin-bottom: 1.75rem;
    }
    .pay-header__title {
        font-family: 'Plus Jakarta Sans', sans-serif;
        font-size: 1.5rem;
        font-weight: 800;
        color: #1A1A1A;
        line-height: 1.2;
        letter-spacing: -0.01em;
        margin: 0;
    }
    @media (min-width: 640px) {
        .pay-header__title { font-size: 1.75rem; }
    }
    .pay-header__subtitle {
        margin-top: 0.5rem;
        font-family: 'Plus Jakarta Sans', sans-serif;
        font-size: 0.9375rem;
        color: #64748B;
        line-height: 1.55;
    }

    /* ════════ STEPPER ════════ */
    .checkout-stepper {
        list-style: none;
        margin: 1.25rem auto 0;
        padding: 0.625rem 0.875rem;
        display: inline-flex;
        align-items: center;
        gap: 0.625rem;
        background: #ffffff;
        border: 1px solid rgba(232, 224, 216, 0.7);
        border-radius: 9999px;
        box-shadow: 0 1px 2px rgba(26, 26, 26, 0.03);
        overflow-x: auto;
        scrollbar-width: none;
        max-width: 100%;
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

    /* ════════ MAIN CARD ════════ */
    .pay-card {
        background:
            radial-gradient(120% 60% at 100% 0%, rgba(207, 175, 83, 0.05) 0%, transparent 60%),
            #ffffff;
        border: 1px solid rgba(232, 224, 216, 0.7);
        border-radius: 20px;
        padding: 1.5rem;
        box-shadow:
            0 1px 2px rgba(26, 26, 26, 0.03),
            0 16px 36px -18px rgba(184, 149, 111, 0.2);
    }
    @media (min-width: 640px) {
        .pay-card { padding: 1.75rem; border-radius: 22px; }
    }

    /* ════════ ORDER ID ════════ */
    .pay-orderid {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        padding-bottom: 1.125rem;
        border-bottom: 1px dashed rgba(232, 224, 216, 0.85);
        margin-bottom: 1.25rem;
    }
    .pay-orderid__label {
        font-family: 'Plus Jakarta Sans', sans-serif;
        font-size: 0.6875rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.18em;
        color: #94A3B8;
    }
    .pay-orderid__value {
        display: inline-flex;
        align-items: center;
        gap: 0.4375rem;
        font-family: 'JetBrains Mono', 'SFMono-Regular', ui-monospace, Menlo, monospace;
        font-size: 0.875rem;
        font-weight: 600;
        color: #1A1A1A;
        letter-spacing: 0.005em;
    }
    .pay-orderid__copy {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 26px; height: 26px;
        border-radius: 8px;
        background: transparent;
        border: 0;
        color: #94A3B8;
        cursor: pointer;
        transition: background 180ms ease, color 180ms ease;
    }
    .pay-orderid__copy svg { width: 14px; height: 14px; }
    .pay-orderid__copy:hover {
        background: rgba(184, 149, 111, 0.1);
        color: #B8956F;
    }

    /* ════════ SECTION ════════ */
    .pay-section { margin-bottom: 1.25rem; }
    .pay-section--bordered {
        padding-top: 1rem;
        border-top: 1px solid rgba(232, 224, 216, 0.65);
    }
    .pay-section--totals {
        padding-top: 1.125rem;
        border-top: 1px solid rgba(232, 224, 216, 0.65);
    }
    .pay-section__title {
        font-family: 'Plus Jakarta Sans', sans-serif;
        font-size: 0.6875rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.18em;
        color: #94A3B8;
        margin: 0 0 0.875rem 0;
    }

    /* ════════ ITEMS LIST ════════ */
    .pay-items {
        list-style: none;
        margin: 0;
        padding: 0;
        display: flex;
        flex-direction: column;
    }
    .pay-item {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 0.625rem 0;
    }
    .pay-item + .pay-item {
        border-top: 1px solid rgba(232, 224, 216, 0.5);
    }
    .pay-item__thumb {
        width: 44px;
        height: 44px;
        border-radius: 10px;
        background: #FAF8F5;
        border: 1px solid rgba(232, 224, 216, 0.7);
        flex-shrink: 0;
        overflow: hidden;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: #CBD5E1;
    }
    .pay-item__thumb img { width: 100%; height: 100%; object-fit: cover; }
    .pay-item__thumb svg { width: 18px; height: 18px; }
    .pay-item__body { flex: 1; min-width: 0; }
    .pay-item__name {
        font-family: 'Plus Jakarta Sans', sans-serif;
        font-size: 0.875rem;
        font-weight: 700;
        color: #1A1A1A;
        line-height: 1.35;
        margin: 0;
        word-break: break-word;
    }
    .pay-item__qty {
        font-family: 'Plus Jakarta Sans', sans-serif;
        font-size: 0.75rem;
        color: #94A3B8;
        margin: 2px 0 0 0;
    }
    .pay-item__price {
        font-family: 'Plus Jakarta Sans', sans-serif;
        font-size: 0.875rem;
        font-weight: 800;
        color: #1A1A1A;
        margin: 0;
        white-space: nowrap;
        text-align: right;
    }

    /* ════════ SHIPPING ════════ */
    .pay-shipping__label {
        font-family: 'Plus Jakarta Sans', sans-serif;
        font-size: 0.6875rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.18em;
        color: #94A3B8;
        margin: 0 0 0.4375rem 0;
    }
    .pay-shipping__address {
        font-family: 'Plus Jakarta Sans', sans-serif;
        font-size: 0.875rem;
        line-height: 1.6;
        color: #1A1A1A;
        margin: 0 0 0.375rem 0;
        word-break: break-word;
    }
    .pay-shipping__courier {
        font-family: 'Plus Jakarta Sans', sans-serif;
        font-size: 0.8125rem;
        color: #64748B;
        margin: 0;
    }
    .pay-shipping__courier strong {
        color: #1A1A1A;
        font-weight: 700;
        letter-spacing: 0.02em;
    }

    /* ════════ TOTALS ════════ */
    .pay-totals {
        margin: 0 0 1.25rem 0;
        padding: 0;
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }
    .pay-totals > div {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .pay-totals dt {
        font-family: 'Plus Jakarta Sans', sans-serif;
        font-size: 0.875rem;
        color: #64748B;
    }
    .pay-totals dd {
        font-family: 'Plus Jakarta Sans', sans-serif;
        font-size: 0.875rem;
        font-weight: 700;
        color: #1A1A1A;
        margin: 0;
    }
    .pay-totals__discount dt,
    .pay-totals__discount dd { color: #047857; }
    .pay-totals__discount dt { font-weight: 600; }

    .pay-grand {
        display: flex;
        align-items: baseline;
        justify-content: space-between;
        gap: 0.75rem;
        padding-top: 1rem;
        border-top: 1px dashed rgba(232, 224, 216, 0.85);
    }
    .pay-grand__label {
        font-family: 'Plus Jakarta Sans', sans-serif;
        font-size: 0.9375rem;
        font-weight: 700;
        color: #1A1A1A;
    }
    .pay-grand__value {
        font-family: 'Plus Jakarta Sans', sans-serif;
        font-size: 1.625rem;
        font-weight: 900;
        letter-spacing: -0.015em;
        color: #B8956F;
        line-height: 1;
    }
    @media (min-width: 640px) {
        .pay-grand__value { font-size: 1.875rem; }
    }
    .pay-grand__currency {
        font-size: 0.75rem;
        font-weight: 700;
        color: #94A3B8;
        margin-right: 0.25rem;
        letter-spacing: 0.02em;
    }

    /* ════════ CTA ════════ */
    .pay-cta {
        margin-top: 1.5rem;
        width: 100%;
        min-height: 48px;
        padding: 0.75rem 1.25rem;
        border-radius: 14px;
        background: linear-gradient(135deg, #CFAF53 0%, #B8956F 100%);
        color: #ffffff;
        border: 0;
        font-family: 'Plus Jakarta Sans', sans-serif;
        font-size: 0.9375rem;
        font-weight: 700;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        box-shadow:
            0 1px 2px rgba(184, 149, 111, 0.25),
            0 10px 22px -8px rgba(184, 149, 111, 0.5);
        transition: transform 200ms ease, box-shadow 200ms ease, filter 200ms ease;
    }
    .pay-cta:hover:not(:disabled) {
        transform: translateY(-1px);
        filter: brightness(1.05);
        box-shadow:
            0 1px 2px rgba(184, 149, 111, 0.3),
            0 14px 26px -8px rgba(184, 149, 111, 0.55);
    }
    .pay-cta:active:not(:disabled) { transform: translateY(0); }
    .pay-cta:disabled { opacity: 0.7; cursor: not-allowed; }

    .pay-secure {
        margin: 0.75rem 0 0 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.375rem;
        width: 100%;
        font-family: 'Plus Jakarta Sans', sans-serif;
        font-size: 0.75rem;
        color: #94A3B8;
    }
    .pay-secure svg { width: 13px; height: 13px; color: #B8956F; }

    /* ════════ BACK LINK ════════ */
    .pay-back {
        display: inline-flex;
        align-items: center;
        gap: 0.375rem;
        margin: 1.25rem auto 0;
        padding: 0.5rem 0.875rem;
        font-family: 'Plus Jakarta Sans', sans-serif;
        font-size: 0.8125rem;
        font-weight: 600;
        color: #8C7A63;
        text-decoration: none;
        border-radius: 9999px;
        transition: color 180ms ease, background 180ms ease;
        width: fit-content;
        position: relative;
        left: 50%;
        transform: translateX(-50%);
    }
    .pay-back svg { width: 14px; height: 14px; }
    .pay-back:hover {
        color: #1A1A1A;
        background: rgba(184, 149, 111, 0.1);
    }

    @media (prefers-reduced-motion: reduce) {
        .pay-cta,
        .pay-orderid__copy,
        .pay-back { transition: none; }
        .pay-cta:hover:not(:disabled) { transform: none; filter: none; }
    }
</style>

{{-- Midtrans Snap Script --}}
<script src="https://app.{{ config('midtrans.is_production') ? '' : 'sandbox.' }}midtrans.com/snap/snap.js"
        data-client-key="{{ config('midtrans.client_key') }}"></script>

<script type="text/javascript">
    (function () {
        const payButton = document.getElementById('pay-button');
        if (!payButton) return;

        const defaultLabel = '<span>Bayar Sekarang</span>';
        const loadingLabel = '<svg class="animate-spin" style="width:18px;height:18px;margin-right:6px;" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" style="opacity:0.25"></circle><path fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" style="opacity:0.75"></path></svg><span>Memproses...</span>';

        function resetButton() {
            payButton.disabled = false;
            payButton.innerHTML = defaultLabel;
        }

        payButton.addEventListener('click', function () {
            payButton.disabled = true;
            payButton.innerHTML = loadingLabel;

            window.snap.pay('{{ $snapToken }}', {
                onSuccess: function (result) {
                    console.log('Payment success:', result);
                    window.location.href = "{{ route('payment.success') }}?order_id={{ $order->kode_order }}";
                },
                onPending: function (result) {
                    console.log('Payment pending:', result);
                    window.showToast && window.showToast('warning', 'Pembayaran Anda masih tertunda. Silakan cek halaman dashboard.', 'Pembayaran Tertunda');
                    setTimeout(() => { window.location.href = "{{ route('dashboard') }}?status=pending"; }, 1200);
                },
                onError: function (result) {
                    console.log('Payment error:', result);
                    window.showToast && window.showToast('error', 'Terjadi kesalahan saat memproses pembayaran. Silakan coba lagi.', 'Pembayaran Gagal');
                    resetButton();
                },
                onClose: function () {
                    console.log('Payment popup closed');
                    resetButton();
                }
            });
        });
    })();
</script>

</x-guest-layout>
