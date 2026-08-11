@php($activeStep = $activeStep ?? 3)
<ol class="checkout-stepper" aria-label="Progress checkout">
    {{-- Step 1 --}}
    <li class="checkout-stepper__step {{ $activeStep > 1 ? 'checkout-stepper__step--done' : ($activeStep === 1 ? 'checkout-stepper__step--active' : '') }}">
        <span class="checkout-stepper__dot">
            @if($activeStep > 1)
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                </svg>
            @else
                1
            @endif
        </span>
        <span class="checkout-stepper__label">Keranjang</span>
    </li>
    <li class="checkout-stepper__connector" aria-hidden="true"></li>
    
    {{-- Step 2 --}}
    <li class="checkout-stepper__step {{ $activeStep > 2 ? 'checkout-stepper__step--done' : ($activeStep === 2 ? 'checkout-stepper__step--active' : '') }}">
        <span class="checkout-stepper__dot">
            @if($activeStep > 2)
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                </svg>
            @else
                2
            @endif
        </span>
        <span class="checkout-stepper__label">Pengiriman</span>
    </li>
    <li class="checkout-stepper__connector" aria-hidden="true"></li>
    
    {{-- Step 3 --}}
    <li class="checkout-stepper__step {{ $activeStep > 3 ? 'checkout-stepper__step--done' : ($activeStep === 3 ? 'checkout-stepper__step--active' : '') }}">
        <span class="checkout-stepper__dot">
            @if($activeStep > 3)
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                </svg>
            @else
                3
            @endif
        </span>
        <span class="checkout-stepper__label">Pembayaran</span>
    </li>
</ol>
