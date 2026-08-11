@if($order->shipping_address)
    <section class="pay-section pay-section--bordered">
        <p class="pay-shipping__label">Dikirim ke</p>
        <p class="pay-shipping__address">{{ $order->shipping_address }}</p>
        @if($order->shipping_method)
            <p class="pay-shipping__courier">
                Kurir: <strong>{{ strtoupper($order->shipping_method) }}</strong>
            </p>
        @endif
    </section>
@endif
