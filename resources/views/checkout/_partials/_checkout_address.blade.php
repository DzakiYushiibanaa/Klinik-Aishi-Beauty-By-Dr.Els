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