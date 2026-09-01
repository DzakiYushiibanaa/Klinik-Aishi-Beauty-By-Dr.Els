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
                        <p class="checkout-summary__item-qty">{{ $item['qty'] }} × Rp
                            {{ number_format($item['harga'], 0, ',', '.') }}
                        </p>
                    </div>
                    <p class="checkout-summary__item-price">Rp
                        {{ number_format($item['harga'] * $item['qty'], 0, ',', '.') }}
                    </p>
                </li>
            @endforeach
        </ul>

        <div class="checkout-summary__divider"></div>

        {{-- Promo --}}
        <div class="checkout-promo">
            <p class="checkout-promo__label">Punya kode promo?</p>
            <div class="checkout-promo__row">
                <input type="text" x-model="promoCodeInput" placeholder="MASUKKAN KODE" class="checkout-promo__input"
                    :disabled="appliedPromoCode !== null">
                <button type="button" x-show="!appliedPromoCode" @click="checkPromo()"
                    :disabled="!promoCodeInput || isLoadingPromo" class="checkout-promo__btn">
                    <span x-show="!isLoadingPromo">Pakai</span>
                    <span x-show="isLoadingPromo">...</span>
                </button>
                <button type="button" x-show="appliedPromoCode" @click="cancelPromo()" class="checkout-promo__cancel"
                    aria-label="Batal pakai promo">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.4"
                            d="M6 18L18 6M6 6l12 12" />
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
                <dd
                    x-text="selectedShippingCost > 0 ? 'Rp ' + parseInt(selectedShippingCost).toLocaleString('id-ID') : '—'">
                </dd>
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
        <button type="submit" class="checkout-cta"
            :class="{ 'checkout-cta--disabled': isLoadingOngkir || !selectedShippingCourier }"
            :disabled="isLoadingOngkir || !selectedShippingCourier">
            <span x-show="!isLoadingOngkir && selectedShippingCourier">
                <span>Lanjut ke Pembayaran</span>
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.4"
                        d="M14 5l7 7m0 0l-7 7m7-7H3" />
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