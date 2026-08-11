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
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
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