{{-- Partial: Pra-Konsultasi Card --}}
                @php
                    $statusKey = strtolower($form->status ?? 'baru');
                    if (in_array($statusKey, ['baru', 'menunggu', 'submitted'])) {
                        $statusVariant = 'pending';
                        $statusText    = 'Menunggu Review';
                        $stepActive    = 'submitted'; // current step indicator
                    } elseif (in_array($statusKey, ['ditinjau', 'in_review', 'diproses', 'reviewed'])) {
                        $statusVariant = 'review';
                        $statusText    = 'Sedang Ditinjau';
                        $stepActive    = 'reviewing';
                    } elseif (in_array($statusKey, ['direkomendasikan', 'selesai'])) {
                        $statusVariant = 'done';
                        $statusText    = 'Rekomendasi Tersedia';
                        $stepActive    = 'done';
                    } elseif (in_array($statusKey, ['ditutup'])) {
                        $statusVariant = 'closed';
                        $statusText    = 'Ditutup';
                        $stepActive    = 'closed';
                    } else {
                        $statusVariant = 'pending';
                        $statusText    = ucfirst($statusKey);
                        $stepActive    = 'submitted';
                    }

                    $isDone = $stepActive === 'done';
                @endphp
                <div x-data="{ showModal: false, expanded: false, longText: false }"
                     x-init="$nextTick(() => { longText = $refs.keluhan && $refs.keluhan.scrollHeight - $refs.keluhan.clientHeight > 1 })"
                     class="prakonsul-card">
                    <div aria-hidden="true" class="prakonsul-card__glow"></div>

                    <div class="prakonsul-card__inner">
                        {{-- 1. HEADER : date + status badge --}}
                        <header class="prakonsul-card__header">
                            <div class="prakonsul-card__date">
                                <span class="prakonsul-card__date-icon">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                </span>
                                <div class="min-w-0">
                                    <p class="prakonsul-card__date-text">{{ $form->created_at->translatedFormat('d F Y') }}</p>
                                    <p class="prakonsul-card__date-time">{{ $form->created_at->translatedFormat('H:i') }} WIB</p>
                                </div>
                            </div>
                            <span class="prakonsul-card__status prakonsul-card__status--{{ $statusVariant }}">
                                <span aria-hidden="true" class="prakonsul-card__status-dot"></span>
                                {{ $statusText }}
                            </span>
                        </header>

                        {{-- 2. KELUHAN --}}
                        <section class="prakonsul-card__section">
                            <p class="prakonsul-card__label">Keluhan</p>
                            <div class="prakonsul-card__quote">
                                <p class="prakonsul-card__keluhan"
                                   :class="{ 'prakonsul-card__keluhan--clamp': !expanded }"
                                   x-ref="keluhan">{{ $form->keluhan_utama ?? 'Tidak ada keluhan' }}</p>
                                <button type="button"
                                        x-show="longText"
                                        @click="expanded = !expanded"
                                        class="prakonsul-card__more"
                                        x-text="expanded ? 'Tampilkan lebih sedikit' : 'Lihat selengkapnya'"></button>
                            </div>
                        </section>

                        {{-- 3. PROGRESS TIMELINE --}}
                        <section class="prakonsul-card__section">
                            <p class="prakonsul-card__label">Status Progres</p>
                            <ol class="prakonsul-timeline" data-active="{{ $stepActive }}">
                                <li class="prakonsul-timeline__step prakonsul-timeline__step--done">
                                    <span class="prakonsul-timeline__dot">
                                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                                        </svg>
                                    </span>
                                    <div class="prakonsul-timeline__body">
                                        <p class="prakonsul-timeline__title">Form Terkirim</p>
                                        <p class="prakonsul-timeline__sub">Data Anda sudah masuk ke tim kami.</p>
                                    </div>
                                </li>

                                <li class="prakonsul-timeline__step prakonsul-timeline__step--{{ in_array($stepActive, ['reviewing', 'done']) ? 'done' : ($stepActive === 'submitted' ? 'active' : 'idle') }}">
                                    <span class="prakonsul-timeline__dot">
                                        @if(in_array($stepActive, ['reviewing', 'done']))
                                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                                            </svg>
                                        @elseif($stepActive === 'submitted')
                                            <span class="prakonsul-timeline__pulse"></span>
                                        @endif
                                    </span>
                                    <div class="prakonsul-timeline__body">
                                        <p class="prakonsul-timeline__title">Sedang Ditinjau Dokter</p>
                                        <p class="prakonsul-timeline__sub">Rata-rata respon &lt; 24 jam.</p>
                                    </div>
                                </li>

                                <li class="prakonsul-timeline__step prakonsul-timeline__step--{{ $isDone ? 'done' : ($stepActive === 'reviewing' ? 'active' : 'idle') }}">
                                    <span class="prakonsul-timeline__dot">
                                        @if($isDone)
                                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                                            </svg>
                                        @elseif($stepActive === 'reviewing')
                                            <span class="prakonsul-timeline__pulse"></span>
                                        @endif
                                    </span>
                                    <div class="prakonsul-timeline__body">
                                        <p class="prakonsul-timeline__title">Rekomendasi Treatment</p>
                                        <p class="prakonsul-timeline__sub">{{ $isDone ? 'Tersedia di halaman ini.' : 'Anda akan dapat notifikasi.' }}</p>
                                    </div>
                                </li>
                            </ol>
                        </section>

                        {{-- 4. INFO DOKTER --}}
                        @if(!$isDone && $stepActive !== 'closed')
                            <p class="prakonsul-card__info">
                                Dokter kami akan meninjau data Anda dalam <strong>1x24 jam</strong>. Setelah selesai, rekomendasi treatment dan produk akan muncul di halaman ini.
                            </p>
                        @elseif($isDone)
                            <p class="prakonsul-card__info">
                                Dokter telah memberikan rekomendasi untuk Anda. Klik tombol di bawah untuk melihat detail lengkap dan rekomendasi treatment.
                            </p>
                        @else
                            <p class="prakonsul-card__info">
                                Pra-konsultasi ini telah ditutup oleh tim klinik. Hubungi admin jika butuh keterangan lebih lanjut.
                            </p>
                        @endif

                        {{-- 5. ACTION BUTTON --}}
                        <footer class="prakonsul-card__footer">
                            @if($isDone)
                                <button type="button" @click="showModal = true" class="prakonsul-card__cta prakonsul-card__cta--primary">
                                    <span>Lihat Rekomendasi</span>
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.4" d="M9 5l7 7-7 7"/>
                                    </svg>
                                </button>
                            @elseif($stepActive === 'closed')
                                <button type="button" @click="showModal = true" class="prakonsul-card__cta">
                                    <span>Lihat Detail</span>
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.4" d="M9 5l7 7-7 7"/>
                                    </svg>
                                </button>
                            @else
                                <button type="button" @click="showModal = true" class="prakonsul-card__cta prakonsul-card__cta--waiting">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    <span>Menunggu Rekomendasi</span>
                                </button>
                            @endif
                        </footer>
                    </div>

                    {{-- ΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉ MODAL DETAIL PRA-KONSULTASI ΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉ --}}
                    <template x-teleport="body">
                        <div x-show="showModal" style="display:none"
                             class="prakonsul-modal"
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0"
                             x-transition:enter-end="opacity-100"
                             x-transition:leave="transition ease-in duration-150"
                             x-transition:leave-start="opacity-100"
                             x-transition:leave-end="opacity-0">

                            <div class="prakonsul-modal__panel"
                                 @click.away="showModal = false"
                                 x-transition:enter="transition ease-out duration-300"
                                 x-transition:enter-start="opacity-0 translate-y-8 sm:translate-y-0 sm:scale-95"
                                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                                 x-transition:leave="transition ease-in duration-200"
                                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                                 x-transition:leave-end="opacity-0 translate-y-8 sm:translate-y-0 sm:scale-95">

                                <div aria-hidden="true" class="prakonsul-modal__glow"></div>

                                {{-- Drag Handle (Mobile) --}}
                                <div class="prakonsul-modal__handle">
                                    <span></span>
                                </div>

                                {{-- Header --}}
                                <div class="prakonsul-modal__header">
                                    <div class="prakonsul-modal__header-left">
                                        <span class="prakonsul-modal__header-icon">
                                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                            </svg>
                                        </span>
                                        <div class="min-w-0">
                                            <h3 class="prakonsul-modal__title">Detail Pra-Konsultasi</h3>
                                            <p class="prakonsul-modal__subtitle">{{ $form->created_at->translatedFormat('d F Y, H:i') }} WIB</p>
                                        </div>
                                    </div>
                                    <button @click="showModal = false" type="button" class="prakonsul-modal__close" aria-label="Tutup">
                                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                    </button>
                                </div>

                                {{-- Body --}}
                                <div class="prakonsul-modal__body">
                                    {{-- Status & meta badges --}}
                                    <div class="prakonsul-modal__meta">
                                        @php
                                            $statusKeyM = strtolower($form->status ?? 'baru');
                                            if (in_array($statusKeyM, ['baru', 'menunggu', 'submitted'])) {
                                                $statusVariantM = 'pending';
                                                $statusTextM    = 'Menunggu Respon';
                                            } elseif (in_array($statusKeyM, ['ditinjau', 'in_review', 'diproses', 'reviewed'])) {
                                                $statusVariantM = 'review';
                                                $statusTextM    = 'Sedang Ditinjau';
                                            } elseif (in_array($statusKeyM, ['direkomendasikan', 'selesai'])) {
                                                $statusVariantM = 'done';
                                                $statusTextM    = 'Rekomendasi Tersedia';
                                            } elseif (in_array($statusKeyM, ['ditutup'])) {
                                                $statusVariantM = 'closed';
                                                $statusTextM    = 'Ditutup';
                                            } else {
                                                $statusVariantM = 'pending';
                                                $statusTextM    = ucfirst($statusKeyM);
                                            }
                                        @endphp
                                        <span class="prakonsul-modal__badge prakonsul-modal__badge--{{ $statusVariantM }}">
                                            <span class="prakonsul-modal__badge-dot"></span>
                                            {{ $statusTextM }}
                                        </span>
                                        <span class="prakonsul-modal__chip">
                                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                                            </svg>
                                            Pra-Konsultasi
                                        </span>
                                        @php $reviewDoctor = $form->reviewer ?: $form->dokter; @endphp
                                        @if($reviewDoctor)
                                            <span class="prakonsul-modal__chip">
                                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                                </svg>
                                                Direview oleh Dr. {{ $reviewDoctor->name }}
                                            </span>
                                        @endif
                                    </div>

                                    {{-- Keluhan Utama --}}
                                    @if($form->keluhan_utama)
                                        <section class="prakonsul-modal__section">
                                            <header class="prakonsul-modal__section-head">
                                                <span class="prakonsul-modal__section-icon">
                                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.86 9.86 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                                                    </svg>
                                                </span>
                                                <h4 class="prakonsul-modal__section-title">Keluhan Utama</h4>
                                            </header>
                                            <div class="prakonsul-modal__quote">
                                                <p class="prakonsul-modal__quote-text">{{ $form->keluhan_utama }}</p>
                                            </div>
                                        </section>
                                    @endif

                                    {{-- Informasi Kulit --}}
                                    <section class="prakonsul-modal__section">
                                        <header class="prakonsul-modal__section-head">
                                            <span class="prakonsul-modal__section-icon">
                                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
                                                </svg>
                                            </span>
                                            <h4 class="prakonsul-modal__section-title">Informasi Kulit &amp; Riwayat</h4>
                                        </header>
                                        <div class="prakonsul-modal__info-grid">
                                            <div class="prakonsul-modal__info">
                                                <p class="prakonsul-modal__info-label">Golongan Darah</p>
                                                <p class="prakonsul-modal__info-value {{ $form->golongan_darah ? '' : 'prakonsul-modal__info-value--empty' }}">
                                                    {{ $form->golongan_darah ?: 'Belum diisi' }}
                                                </p>
                                            </div>
                                            <div class="prakonsul-modal__info">
                                                <p class="prakonsul-modal__info-label">Jenis Kulit</p>
                                                <p class="prakonsul-modal__info-value {{ $form->jenis_kulit ? '' : 'prakonsul-modal__info-value--empty' }}">
                                                    {{ $form->jenis_kulit ? ucfirst($form->jenis_kulit) : 'Belum diisi' }}
                                                </p>
                                            </div>
                                            <div class="prakonsul-modal__info prakonsul-modal__info--wide">
                                                <p class="prakonsul-modal__info-label">Skincare Sebelumnya</p>
                                                <p class="prakonsul-modal__info-value {{ $form->skincare_sebelumnya ? '' : 'prakonsul-modal__info-value--empty' }}">
                                                    {{ $form->skincare_sebelumnya ?: 'Belum pernah memakai skincare' }}
                                                </p>
                                                @if($form->periode_skincare_bulan)
                                                    <p class="prakonsul-modal__info-hint">Periode pemakaian: <strong>{{ $form->periode_skincare_bulan }} bulan</strong></p>
                                                @endif
                                            </div>
                                        </div>
                                    </section>

                                    {{-- Riwayat Penyakit --}}
                                    @if($form->riwayat_penyakit)
                                        <section class="prakonsul-modal__section">
                                            <header class="prakonsul-modal__section-head">
                                                <span class="prakonsul-modal__section-icon">
                                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                                    </svg>
                                                </span>
                                                <h4 class="prakonsul-modal__section-title">Riwayat Penyakit</h4>
                                            </header>
                                            <div class="prakonsul-modal__note">
                                                <p>{{ $form->riwayat_penyakit }}</p>
                                            </div>
                                        </section>
                                    @endif

                                    {{-- Riwayat Alergi --}}
                                    @if($form->riwayat_alergi)
                                        <section class="prakonsul-modal__section">
                                            <header class="prakonsul-modal__section-head">
                                                <span class="prakonsul-modal__section-icon">
                                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                                    </svg>
                                                </span>
                                                <h4 class="prakonsul-modal__section-title">Riwayat Alergi (Obat / Zat Kimia)</h4>
                                            </header>
                                            <div class="prakonsul-modal__note">
                                                <p>{{ $form->riwayat_alergi }}</p>
                                            </div>
                                        </section>
                                    @endif

                                    {{-- Riwayat Perawatan --}}
                                    <section class="prakonsul-modal__section">
                                        <header class="prakonsul-modal__section-head">
                                            <span class="prakonsul-modal__section-icon">
                                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                </svg>
                                            </span>
                                            <h4 class="prakonsul-modal__section-title">Riwayat Perawatan</h4>
                                        </header>
                                        <div class="prakonsul-modal__chips-grid">
                                            @php
                                                $treatments = [
                                                    ['label' => 'Facial', 'val' => $form->pernah_facial],
                                                    ['label' => 'Skin Booster', 'val' => $form->pernah_skin_booster],
                                                    ['label' => 'Laser', 'val' => $form->pernah_laser],
                                                    ['label' => 'Inject/Infus', 'val' => $form->pernah_inject_infus],
                                                ];
                                            @endphp
                                            @foreach($treatments as $t)
                                                <span class="prakonsul-modal__pill {{ $t['val'] ? 'prakonsul-modal__pill--active' : 'prakonsul-modal__pill--inactive' }}">
                                                    @if($t['val'])
                                                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.4" d="M5 13l4 4L19 7"/>
                                                        </svg>
                                                    @else
                                                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                            <circle cx="12" cy="12" r="9" stroke-width="1.6"/>
                                                        </svg>
                                                    @endif
                                                    {{ $t['label'] }}
                                                </span>
                                            @endforeach
                                        </div>
                                    </section>

                                    {{-- Foto Wajah --}}
                                    @if($form->foto_depan_path || $form->foto_kiri_path || $form->foto_kanan_path)
                                        <section class="prakonsul-modal__section">
                                            <header class="prakonsul-modal__section-head">
                                                <span class="prakonsul-modal__section-icon">
                                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                    </svg>
                                                </span>
                                                <h4 class="prakonsul-modal__section-title">Foto Wajah</h4>
                                            </header>
                                            <div class="prakonsul-modal__photos">
                                                @foreach(['foto_depan_path' => 'Depan', 'foto_kiri_path' => 'Kiri', 'foto_kanan_path' => 'Kanan'] as $field => $label)
                                                    @if($form->$field)
                                                        <a href="{{ asset('storage/' . $form->$field) }}" target="_blank" rel="noopener" class="prakonsul-modal__photo">
                                                            <img src="{{ asset('storage/' . $form->$field) }}" alt="Foto {{ $label }}">
                                                            <span class="prakonsul-modal__photo-overlay">
                                                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 11a6 6 0 11-12 0 6 6 0 0112 0zm-6-3v6m-3-3h6"/>
                                                                </svg>
                                                            </span>
                                                            <span class="prakonsul-modal__photo-label">{{ $label }}</span>
                                                        </a>
                                                    @else
                                                        <div class="prakonsul-modal__photo prakonsul-modal__photo--empty" aria-disabled="true">
                                                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                                            </svg>
                                                            <span class="prakonsul-modal__photo-label">{{ $label }}</span>
                                                        </div>
                                                    @endif
                                                @endforeach
                                            </div>
                                        </section>
                                    @endif

                                    {{-- Diagnosa & Rekomendasi Dokter --}}
                                    @if($form->diagnosa || $form->rekomendasiItems->isNotEmpty())
                                        <div class="space-y-4 border-t border-[#E8E0D8]/60 mt-4 pt-4">
                                            
                                            {{-- Dokter's General Note --}}
                                            @if($form->diagnosa)
                                                <div class="bg-white border border-[#E8E0D8] rounded-xl p-4 shadow-sm relative overflow-hidden group">
                                                    <div class="absolute left-0 top-0 bottom-0 w-1 bg-[#CFAF53] rounded-l-xl"></div>
                                                    <div class="flex items-center gap-2 mb-2 pl-2">
                                                        <svg class="w-4 h-4 text-[#CFAF53]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
                                                        <p class="text-xs font-bold text-[#8C7A63] uppercase tracking-wider">Diagnosa Dokter</p>
                                                    </div>
                                                    <p class="text-sm text-[#1A1A1A] leading-relaxed pl-2">{{ $form->diagnosa }}</p>
                                                </div>
                                            @endif

                                            {{-- Produk/Treatment Iteration --}}
                                            @if($form->rekomendasiItems->isNotEmpty())
                                                @foreach($form->rekomendasiItems as $item)
                                                    @php 
                                                        $tipe = $item->item_tipe;
                                                        $obj = $tipe === 'Produk' ? $item->product : $item->treatment;
                                                    @endphp
                                                    <div class="bg-white border border-[#E8E0D8] rounded-xl p-4 shadow-sm hover:border-[#CFAF53]/40 transition-colors relative overflow-hidden">
                                                        <div class="flex items-center gap-2 mb-4 border-b border-[#E8E0D8]/60 pb-3">
                                                            @if($tipe === 'Treatment')
                                                                <svg class="w-4 h-4 text-[#CFAF53]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                                <p class="text-xs font-bold text-[#8C7A63] uppercase tracking-wider">Treatment Rekomendasi</p>

                                                            @else
                                                                <svg class="w-4 h-4 text-[#CFAF53]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                                                                <p class="text-xs font-bold text-[#8C7A63] uppercase tracking-wider">Produk Rekomendasi</p>
                                                            @endif
                                                        </div>

                                                        <div class="flex flex-col sm:flex-row items-start gap-4">
                                                            {{-- Image --}}
                                                            <div class="w-20 h-20 rounded-lg bg-[#FAF8F5] border border-[#E8E0D8] shrink-0 overflow-hidden flex items-center justify-center">
                                                                @if($obj && isset($obj->foto_path) && $obj->foto_path)
                                                                    <img src="{{ asset('storage/' . $obj->foto_path) }}" alt="{{ $item->item_nama }}" class="w-full h-full object-cover">
                                                                @else
                                                                    <svg class="w-8 h-8 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                                                @endif
                                                            </div>

                                                            {{-- Details --}}
                                                            <div class="flex-1 min-w-0 flex flex-col justify-between h-full">
                                                                <div>
                                                                    <p class="font-bold text-[#1A1A1A] text-base leading-tight mb-1">{{ $item->item_nama }}</p>
                                                                    
                                                                    @if($item->catatan)
                                                                        <p class="text-sm text-slate-600 mb-2">{{ $item->catatan }}</p>
                                                                    @endif

                                                                    @if($tipe === 'Treatment' && isset($obj->durasi_menit))
                                                                        <p class="text-xs font-medium text-slate-500 mb-2">{{ $obj->durasi_menit }} menit</p>
                                                                    @endif
                                                                    
                                                                    @if($obj && (isset($obj->harga) || isset($obj->harga_promo) || isset($obj->harga_normal)))
                                                                        <p class="text-sm font-bold text-[#CFAF53]">Rp {{ number_format($obj->harga ?? $obj->harga_promo ?? $obj->harga_normal ?? 0, 0, ',', '.') }}</p>
                                                                    @endif
                                                                </div>

                                                                {{-- Actions --}}
                                                                <div class="mt-4 flex flex-wrap items-center gap-2">
                                                                    @if($tipe === 'Treatment')
                                                                        <a href="{{ route('appointment.select') }}" class="inline-flex items-center justify-center px-5 py-2 text-xs font-bold text-white bg-[#1A1A1A] rounded-lg hover:bg-[#333] hover:shadow-lg hover:shadow-[#1A1A1A]/20 transition-all w-full sm:w-auto text-center">Buat Janji Temu</a>
                                                                    @else
                                                                        <form action="{{ route('cart.add', $obj->product_id ?? 0) }}" method="POST" class="inline-block flex-1 sm:flex-none">
                                                                            @csrf
                                                                            <button type="submit" class="w-full inline-flex items-center justify-center px-5 py-2 text-xs font-bold text-[#1A1A1A] bg-[#FAF8F5] border border-[#E8E0D8] rounded-lg hover:bg-white hover:border-[#CFAF53] transition-all shadow-sm">+ Keranjang</button>
                                                                        </form>
                                                                        <a href="{{ route('cart.index') }}" class="inline-flex items-center justify-center px-5 py-2 text-xs font-bold text-white bg-[#1A1A1A] rounded-lg hover:bg-[#333] hover:shadow-lg hover:shadow-[#1A1A1A]/20 transition-all flex-1 sm:flex-none text-center">Beli Sekarang</a>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            @endif
                                        </div>
                                    @endif

                                    @if($form->catatan_tambahan)
                                        <section class="prakonsul-modal__section">
                                            <header class="prakonsul-modal__section-head">
                                                <span class="prakonsul-modal__section-icon">
                                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M7 8h10M7 12h7m-9 8l3.5-3.5H19a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v8.5a2 2 0 002 2z"/>
                                                    </svg>
                                                </span>
                                                <h4 class="prakonsul-modal__section-title">Komentar Dokter</h4>
                                            </header>
                                            <div class="prakonsul-modal__note">
                                                <p>{{ $form->catatan_tambahan }}</p>
                                            </div>
                                        </section>
                                    @endif
                                </div>

                                {{-- Footer --}}
                                <div class="prakonsul-modal__footer">
                                    <button @click="showModal = false" type="button" class="prakonsul-modal__close-btn">Tutup</button>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
