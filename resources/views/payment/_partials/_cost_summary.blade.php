<section class="pay-section pay-section--totals">
    <dl class="pay-totals">
        <div>
            <dt>Subtotal</dt>
            <dd>Rp {{ number_format($order->subtotal, 0, ',', '.') }}</dd>
        </div>
        @if($order->diskon_total > 0)
            <div class="pay-totals__discount">
                <dt>Diskon Promo</dt>
                <dd>− Rp {{ number_format($order->diskon_total, 0, ',', '.') }}</dd>
            </div>
        @endif
        <div>
            <dt>Biaya Pengiriman</dt>
            <dd>{{ $order->ongkir > 0 ? 'Rp ' . number_format($order->ongkir, 0, ',', '.') : '—' }}</dd>
        </div>
    </dl>

    <div class="pay-grand">
        <span class="pay-grand__label">Total Tagihan</span>
        <span class="pay-grand__value">
            <span class="pay-grand__currency">Rp</span>{{ number_format($order->grand_total, 0, ',', '.') }}
        </span>
    </div>
</section>

{{-- CTA --}}
<button id="pay-button" type="button" class="pay-cta">
    <span>Bayar Sekarang</span>
</button>

<p class="pay-secure">
    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
    </svg>
    Pembayaran aman dan terenkripsi.
</p>
