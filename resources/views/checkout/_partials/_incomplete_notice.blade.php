            <section class="checkout-notice">
                <div aria-hidden="true" class="checkout-notice__glow"></div>

                <div class="checkout-notice__inner">
                    <div class="checkout-notice__icon">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>

                    <h2 class="checkout-notice__title">Lengkapi Alamat Pengiriman</h2>
                    <p class="checkout-notice__desc">
                        Untuk melanjutkan checkout, lengkapi nomor HP dan alamat pengiriman Anda terlebih dahulu agar pesanan dapat diproses dengan benar.
                    </p>

                    <div class="checkout-notice__actions">
                        <a href="{{ route('dashboard', ['tab' => 'profile']) }}" class="checkout-notice__btn checkout-notice__btn--primary">
                            <span>Lengkapi Alamat Pengiriman</span>
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