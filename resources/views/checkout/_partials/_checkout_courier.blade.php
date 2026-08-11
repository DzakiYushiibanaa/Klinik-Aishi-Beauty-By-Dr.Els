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