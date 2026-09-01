<x-guest-layout :title="'Checkout'">
    <div class="checkout-page">
        <div class="checkout-page__container">
            @include('checkout._partials._checkout_header')
            @php
                $u = auth()->user()->phone ?? null; $p = $pasien;
                $checkoutFields = [
                    ['label'=>'Nomor HP','filled'=>!empty($u)],
                    ['label'=>'Provinsi','filled'=>!empty($p?->provinsi)||!empty($p?->province_id)],
                    ['label'=>'Kota','filled'=>!empty($p?->kota)||!empty($p?->city_id)],
                    ['label'=>'Kecamatan','filled'=>!empty($p?->district_name)||!empty($p?->district_id)],
                    ['label'=>'Alamat','filled'=>!empty($p?->alamat)],
                ];
                $checkoutMissing = collect($checkoutFields)->where('filled', false)->isNotEmpty();
            @endphp
            @if($checkoutMissing)
                @include('checkout._partials._incomplete_notice')
            @else
                <form method="POST" action="{{ route('checkout.store') }}" class="checkout-grid" x-data="checkoutData()">
                    @csrf
                    <input type="hidden" name="shipping_cost" x-model="selectedShippingCost">
                    <input type="hidden" name="shipping_courier" x-model="selectedShippingCourier">
                    <input type="hidden" name="district_id" value="{{ $pasien->district_id }}">
                    <div class="checkout-main">
                        @include('checkout._partials._checkout_address')
                        @include('checkout._partials._checkout_courier')
                        <section class="checkout-card">
                            <div class="checkout-card__head">
                                <div>
                                    <p class="checkout-card__eyebrow">Catatan</p>
                                    <h2 class="checkout-card__title">Catatan Pengiriman <span class="checkout-card__optional">(opsional)</span></h2>
                                </div>
                            </div>
                            <textarea name="shipping_notes" rows="3" placeholder="Contoh: Titip di satpam atau hubungi saat paket tiba." class="checkout-textarea"></textarea>
                            <p class="checkout-card__helper">Catatan ini membantu kurir menemukan alamat Anda dengan lebih mudah.</p>
                        </section>
                    </div>
                    @include('checkout._partials._checkout_summary')
                </form>
            @endif
        </div>
    </div>
    @include('checkout._partials._checkout_styles')
    @include('checkout._partials._checkout_script')
</x-guest-layout>
