<!-- STEP 5: Ringkasan -->
<div x-show="step === 5" x-cloak x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0 translate-x-4" x-transition:enter-end="opacity-100 translate-x-0">
    <div class="space-y-4">
        <!-- Info Banner -->
        <div class="flex items-start gap-3 rounded-xl border border-[#E8E0D8] bg-[#FAF8F5] px-5 py-4">
            <div
                class="w-6 h-6 rounded-full border-2 border-[#8C7A63] flex items-center justify-center shrink-0 mt-0.5">
                <svg class="w-3.5 h-3.5 text-[#8C7A63]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
            </div>
            <p class="text-sm text-gray-600 leading-relaxed">Hampir selesai! Silakan periksa kembali kelengkapan dan
                kebenaran data Anda di bawah ini sebelum mengirimkan form ke tim medis kami.</p>
        </div>

        <!-- Section 1: Kondisi Kulit -->
        <section class="praprev-card">
            <div class="praprev-card__head">
                <div class="praprev-card__heading">
                    <span class="praprev-card__step">1</span>
                    <h3 class="praprev-card__title">Kondisi Kulit</h3>
                </div>
                <button type="button" @click="goToStep(1)" class="praprev-card__edit">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                    </svg>
                    Ubah
                </button>
            </div>

            <dl class="praprev-card__list">
                <div class="praprev-row" x-data="{ expanded: false, longText: false }"
                    x-init="$nextTick(() => { longText = $refs.val.scrollHeight - $refs.val.clientHeight > 1 })">
                    <dt class="praprev-row__label">Keluhan Utama</dt>
                    <dd class="praprev-row__value">
                        <p class="praprev-row__text" :class="{ 'praprev-row__text--clamp': !expanded }" x-ref="val"
                            x-text="keluhan_utama || '-'"></p>
                        <button type="button" x-show="longText" @click="expanded = !expanded" class="praprev-row__more"
                            x-text="expanded ? 'Tampilkan lebih sedikit' : 'Lihat selengkapnya'"></button>
                    </dd>
                </div>

                <div class="praprev-row">
                    <dt class="praprev-row__label">Jenis Kulit</dt>
                    <dd class="praprev-row__value">
                        <p class="praprev-row__text" x-text="jenis_kulit || '-'"></p>
                    </dd>
                </div>

                <div class="praprev-row">
                    <dt class="praprev-row__label">Golongan Darah</dt>
                    <dd class="praprev-row__value">
                        <p class="praprev-row__text" x-text="golongan_darah || '-'"></p>
                    </dd>
                </div>
            </dl>
        </section>

        <!-- Section 2: Riwayat Produk -->
        <section class="praprev-card">
            <div class="praprev-card__head">
                <div class="praprev-card__heading">
                    <span class="praprev-card__step">2</span>
                    <h3 class="praprev-card__title">Riwayat Skincare dan Treatment</h3>
                </div>
                <button type="button" @click="goToStep(2)" class="praprev-card__edit">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                    </svg>
                    Ubah
                </button>
            </div>

            <dl class="praprev-card__list">
                <div class="praprev-row" x-data="{ expanded: false, longText: false }"
                    x-init="$nextTick(() => { longText = $refs.val.scrollHeight - $refs.val.clientHeight > 1 })">
                    <dt class="praprev-row__label">Produk Saat Ini</dt>
                    <dd class="praprev-row__value">
                        <p class="praprev-row__text" :class="{ 'praprev-row__text--clamp': !expanded }" x-ref="val"
                            x-text="skincare_sebelumnya || '-'"></p>
                        <button type="button" x-show="longText" @click="expanded = !expanded" class="praprev-row__more"
                            x-text="expanded ? 'Tampilkan lebih sedikit' : 'Lihat selengkapnya'"></button>
                    </dd>
                </div>

                <div class="praprev-row">
                    <dt class="praprev-row__label">Lama Pemakaian</dt>
                    <dd class="praprev-row__value">
                        <p class="praprev-row__text" x-text="(periode_skincare_bulan || '0') + ' Bulan'"></p>
                    </dd>
                </div>

                <div class="praprev-row" x-data="{ expanded: false, longText: false }"
                    x-init="$nextTick(() => { longText = $refs.val.scrollHeight - $refs.val.clientHeight > 1 })">
                    <dt class="praprev-row__label">Treatment</dt>
                    <dd class="praprev-row__value">
                        <p class="praprev-row__text" :class="{ 'praprev-row__text--clamp': !expanded }" x-ref="val"
                            x-text="treatmentList"></p>
                        <button type="button" x-show="longText" @click="expanded = !expanded" class="praprev-row__more"
                            x-text="expanded ? 'Tampilkan lebih sedikit' : 'Lihat selengkapnya'"></button>
                    </dd>
                </div>
            </dl>
        </section>

        <!-- Section 3: Riwayat Medis -->
        <section class="praprev-card">
            <div class="praprev-card__head">
                <div class="praprev-card__heading">
                    <span class="praprev-card__step">3</span>
                    <h3 class="praprev-card__title">Riwayat Medis</h3>
                </div>
                <button type="button" @click="goToStep(3)" class="praprev-card__edit">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                    </svg>
                    Ubah
                </button>
            </div>

            <dl class="praprev-card__list">
                <div class="praprev-row" x-data="{ expanded: false, longText: false }"
                    x-init="$nextTick(() => { longText = $refs.val.scrollHeight - $refs.val.clientHeight > 1 })">
                    <dt class="praprev-row__label">Penyakit Khusus</dt>
                    <dd class="praprev-row__value">
                        <p class="praprev-row__text" :class="{ 'praprev-row__text--clamp': !expanded }" x-ref="val"
                            x-text="riwayat_penyakit || '-'"></p>
                        <button type="button" x-show="longText" @click="expanded = !expanded" class="praprev-row__more"
                            x-text="expanded ? 'Tampilkan lebih sedikit' : 'Lihat selengkapnya'"></button>
                    </dd>
                </div>

                <div class="praprev-row" x-data="{ expanded: false, longText: false }"
                    x-init="$nextTick(() => { longText = $refs.val.scrollHeight - $refs.val.clientHeight > 1 })">
                    <dt class="praprev-row__label">Riwayat Alergi</dt>
                    <dd class="praprev-row__value">
                        <p class="praprev-row__text" :class="{ 'praprev-row__text--clamp': !expanded }" x-ref="val"
                            x-text="riwayat_alergi || '-'"></p>
                        <button type="button" x-show="longText" @click="expanded = !expanded" class="praprev-row__more"
                            x-text="expanded ? 'Tampilkan lebih sedikit' : 'Lihat selengkapnya'"></button>
                    </dd>
                </div>
            </dl>
        </section>

        <!-- Section 4: Dokumentasi Visual -->
        <section class="praprev-card">
            <div class="praprev-card__head">
                <div class="praprev-card__heading">
                    <span class="praprev-card__step">4</span>
                    <h3 class="praprev-card__title">Dokumentasi Visual</h3>
                </div>
                <button type="button" @click="goToStep(4)" class="praprev-card__edit">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                    </svg>
                    Ubah
                </button>
            </div>

            <!-- Photo Previews Grid -->
            <div class="grid grid-cols-3 gap-3 mb-4">
                <!-- Depan -->
                <div class="text-center">
                    <div class="aspect-[3/4] rounded-xl overflow-hidden border border-gray-100 bg-[#FAF8F5]">
                        <template x-if="foto_depan_preview">
                            <img :src="foto_depan_preview" class="w-full h-full object-cover" alt="Foto Depan">
                        </template>
                        <template x-if="!foto_depan_preview">
                            <div class="w-full h-full flex flex-col items-center justify-center">
                                <img src="{{ asset('assets/img/wajah hadap depan.svg') }}" alt=""
                                    class="w-10 h-10 opacity-20">
                                <span class="text-[10px] text-rose-400 font-medium mt-1">Belum ada</span>
                            </div>
                        </template>
                    </div>
                    <p class="text-[10px] text-gray-500 mt-1">Depan</p>
                </div>
                <!-- Kanan -->
                <div class="text-center">
                    <div class="aspect-[3/4] rounded-xl overflow-hidden border border-gray-100 bg-[#FAF8F5]">
                        <template x-if="foto_kanan_preview">
                            <img :src="foto_kanan_preview" class="w-full h-full object-cover" alt="Foto Kanan">
                        </template>
                        <template x-if="!foto_kanan_preview">
                            <div class="w-full h-full flex flex-col items-center justify-center">
                                <img src="{{ asset('assets/img/wajah samping kanan.svg') }}" alt=""
                                    class="w-10 h-10 opacity-20">
                                <span class="text-[10px] text-rose-400 font-medium mt-1">Belum ada</span>
                            </div>
                        </template>
                    </div>
                    <p class="text-[10px] text-gray-500 mt-1">Kanan</p>
                </div>
                <!-- Kiri -->
                <div class="text-center">
                    <div class="aspect-[3/4] rounded-xl overflow-hidden border border-gray-100 bg-[#FAF8F5]">
                        <template x-if="foto_kiri_preview">
                            <img :src="foto_kiri_preview" class="w-full h-full object-cover" alt="Foto Kiri">
                        </template>
                        <template x-if="!foto_kiri_preview">
                            <div class="w-full h-full flex flex-col items-center justify-center">
                                <img src="{{ asset('assets/img/wajah samping kiri.svg') }}" alt=""
                                    class="w-10 h-10 opacity-20">
                                <span class="text-[10px] text-rose-400 font-medium mt-1">Belum ada</span>
                            </div>
                        </template>
                    </div>
                    <p class="text-[10px] text-gray-500 mt-1">Kiri</p>
                </div>
            </div>

            <!-- Status Text -->
            <dl class="praprev-card__list praprev-card__list--bordered">
                <div class="praprev-row">
                    <dt class="praprev-row__label">Foto Wajah Depan</dt>
                    <dd class="praprev-row__value">
                        <p class="praprev-row__text praprev-row__text--status"
                            :class="foto_depan_name ? 'praprev-row__text--ok' : 'praprev-row__text--missing'"
                            x-text="foto_depan_name ? '✓ Diunggah' : 'Belum Diunggah'"></p>
                    </dd>
                </div>
                <div class="praprev-row">
                    <dt class="praprev-row__label">Foto Wajah Kanan</dt>
                    <dd class="praprev-row__value">
                        <p class="praprev-row__text praprev-row__text--status"
                            :class="foto_kanan_name ? 'praprev-row__text--ok' : 'praprev-row__text--missing'"
                            x-text="foto_kanan_name ? '✓ Diunggah' : 'Belum Diunggah'"></p>
                    </dd>
                </div>
                <div class="praprev-row">
                    <dt class="praprev-row__label">Foto Wajah Kiri</dt>
                    <dd class="praprev-row__value">
                        <p class="praprev-row__text praprev-row__text--status"
                            :class="foto_kiri_name ? 'praprev-row__text--ok' : 'praprev-row__text--missing'"
                            x-text="foto_kiri_name ? '✓ Diunggah' : 'Belum Diunggah'"></p>
                    </dd>
                </div>
            </dl>
        </section>
    </div>
</div>

@once
    <style>
        /* ─────────  PRA-KONSULTASI PREVIEW CARDS  ───────── */
        .praprev-card {
            background: #ffffff;
            border: 1px solid rgba(232, 224, 216, 0.7);
            border-radius: 16px;
            padding: 1.125rem 1.25rem;
            box-shadow: 0 1px 2px rgba(26, 26, 26, 0.03);
            transition: border-color 200ms ease, box-shadow 200ms ease;
        }

        .praprev-card:hover {
            border-color: rgba(184, 149, 111, 0.35);
            box-shadow:
                0 1px 2px rgba(26, 26, 26, 0.04),
                0 6px 16px -10px rgba(184, 149, 111, 0.18);
        }

        .praprev-card__head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.75rem;
            margin-bottom: 1rem;
            padding-bottom: 0.75rem;
            border-bottom: 1px solid rgba(232, 224, 216, 0.6);
        }

        .praprev-card__heading {
            display: flex;
            align-items: center;
            gap: 0.625rem;
            min-width: 0;
        }

        .praprev-card__step {
            flex-shrink: 0;
            width: 26px;
            height: 26px;
            border-radius: 9999px;
            background: linear-gradient(135deg, #2C2622 0%, #1A1A1A 100%);
            color: #ffffff;
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 0.6875rem;
            font-weight: 800;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            box-shadow:
                inset 0 0 0 1px rgba(255, 255, 255, 0.08),
                0 2px 5px -2px rgba(26, 26, 26, 0.4);
        }

        .praprev-card__title {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 0.875rem;
            font-weight: 800;
            letter-spacing: -0.01em;
            color: #1A1A1A;
        }

        .praprev-card__edit {
            display: inline-flex;
            align-items: center;
            gap: 0.3125rem;
            padding: 0.375rem 0.75rem;
            border-radius: 9999px;
            background: transparent;
            color: #8C7A63;
            border: 1px solid transparent;
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 0.75rem;
            font-weight: 700;
            cursor: pointer;
            transition: background 180ms ease, color 180ms ease, border-color 180ms ease, transform 180ms ease;
        }

        .praprev-card__edit svg {
            width: 12px;
            height: 12px;
        }

        .praprev-card__edit:hover {
            background: rgba(207, 175, 83, 0.1);
            color: #B8956F;
            border-color: rgba(184, 149, 111, 0.25);
            transform: translateY(-1px);
        }

        .praprev-card__edit:active {
            transform: translateY(0);
        }

        .praprev-card__list {
            display: flex;
            flex-direction: column;
            gap: 0.875rem;
            margin: 0;
        }

        .praprev-card__list--bordered {
            border-top: 1px solid rgba(232, 224, 216, 0.6);
            padding-top: 0.875rem;
        }

        .praprev-row {
            display: grid;
            grid-template-columns: 1fr;
            gap: 0.25rem;
        }

        @media (min-width: 640px) {
            .praprev-row {
                grid-template-columns: 140px minmax(0, 1fr);
                gap: 1rem;
                align-items: start;
            }
        }

        .praprev-row__label {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 0.75rem;
            font-weight: 600;
            color: #94A3B8;
            line-height: 1.5;
            margin: 0;
        }

        @media (min-width: 640px) {
            .praprev-row__label {
                padding-top: 0.0625rem;
            }
        }

        .praprev-row__value {
            min-width: 0;
            margin: 0;
        }

        .praprev-row__text {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 0.875rem;
            font-weight: 700;
            color: #1A1A1A;
            line-height: 1.55;
            margin: 0;
            text-align: left;
            white-space: pre-wrap;
            word-break: break-word;
            overflow-wrap: anywhere;
        }

        .praprev-row__text--clamp {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .praprev-row__text--status {
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
            font-size: 0.8125rem;
        }

        .praprev-row__text--ok {
            color: #047857;
        }

        .praprev-row__text--missing {
            color: #E11D48;
            font-weight: 600;
        }

        .praprev-row__more {
            margin-top: 0.25rem;
            background: transparent;
            border: 0;
            padding: 0;
            color: #B8956F;
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 0.75rem;
            font-weight: 700;
            cursor: pointer;
            transition: color 160ms ease;
        }

        @media (min-width: 640px) {
            .praprev-row__more {
                display: inline-block;
            }
        }

        .praprev-row__more:hover {
            color: #8C7A63;
            text-decoration: underline;
        }

        @media (prefers-reduced-motion: reduce) {

            .praprev-card,
            .praprev-card__edit,
            .praprev-row__more {
                transition: none;
            }

            .praprev-card__edit:hover {
                transform: none;
            }
        }
    </style>
@endonce