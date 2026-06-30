{{-- ====== TAB: PRA-KONSULTASI ====== --}}
<section class="prakonsul-section">
    <div aria-hidden="true" class="prakonsul-section__glow"></div>
    <div aria-hidden="true" class="prakonsul-section__pattern"></div>

    <div class="prakonsul-section__inner">
        {{-- Header --}}
        <div class="prakonsul-section__head">
            <div class="prakonsul-section__heading">
                <p class="prakonsul-section__eyebrow">
                    <span class="prakonsul-section__dot"></span>
                    Konsultasi
                </p>
                <h2 class="prakonsul-section__title">Riwayat Pra-Konsultasi</h2>
                <p class="prakonsul-section__sub">Lihat kembali data konsultasi awal yang pernah Anda isi dan pantau status responnya.</p>
            </div>
            <a href="{{ route('pra.create') }}" class="prakonsul-section__cta">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                </svg>
                <span>Buat Baru</span>
            </a>
        </div>

    @if($praKonsuls->isEmpty())
        <div class="bg-[#FAF8F5] rounded-2xl border border-dashed border-[#E8E0D8] p-12 text-center">
            <div class="w-16 h-16 bg-[#CFAF53]/10 rounded-full flex items-center justify-center mb-4 mx-auto">
                <svg class="w-8 h-8 text-[#CFAF53]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
            </div>
            <h3 class="font-bold text-[#1A1A1A]">Belum ada pra-konsultasi</h3>
            <p class="text-sm text-slate-400 mt-1">Isi formulir untuk konsultasi awal dengan dokter.</p>
            <a href="{{ route('pra.create') }}" class="mt-4 inline-block text-sm font-bold text-[#CFAF53]">Isi Formulir Sekarang →</a>
        </div>
    @else
        @php $praCount = $praKonsuls->count(); @endphp
        <div class="prakonsul-grid">
            @foreach($praKonsuls as $form)
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
                                Dokter kami akan meninjau data Anda dalam <strong>1×24 jam</strong>. Setelah selesai, rekomendasi treatment dan produk akan muncul di halaman ini.
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

                    {{-- ══════ MODAL DETAIL PRA-KONSULTASI ══════ --}}
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
                                                        $obj = $tipe === 'Produk' ? $item->product : ($tipe === 'Treatment' ? $item->treatment : $item->bundle);
                                                    @endphp
                                                    <div class="bg-white border border-[#E8E0D8] rounded-xl p-4 shadow-sm hover:border-[#CFAF53]/40 transition-colors relative overflow-hidden">
                                                        <div class="flex items-center gap-2 mb-4 border-b border-[#E8E0D8]/60 pb-3">
                                                            @if($tipe === 'Treatment')
                                                                <svg class="w-4 h-4 text-[#CFAF53]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                                <p class="text-xs font-bold text-[#8C7A63] uppercase tracking-wider">Treatment Rekomendasi</p>
                                                            @elseif($tipe === 'Bundle')
                                                                <svg class="w-4 h-4 text-[#CFAF53]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                                <p class="text-xs font-bold text-[#8C7A63] uppercase tracking-wider">Bundle Rekomendasi</p>
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
                                                                        <form action="{{ $tipe === 'Bundle' ? route('cart.add_bundle', $obj->bundle_id ?? 0) : route('cart.add', $obj->product_id ?? 0) }}" method="POST" class="inline-block flex-1 sm:flex-none">
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
            @endforeach
        </div>
    @endif
    </div>
</section>


@once
    <style>
        /* ─────────  SECTION CONTAINER  ───────── */
        .prakonsul-section {
            position: relative;
            overflow: hidden;
            background:
                radial-gradient(140% 90% at 100% 0%, rgba(207, 175, 83, 0.08) 0%, transparent 60%),
                radial-gradient(120% 90% at 0% 100%, rgba(184, 149, 111, 0.06) 0%, transparent 55%),
                #ffffff;
            border: 1px solid rgba(232, 224, 216, 0.7);
            border-radius: 22px;
            box-shadow:
                0 1px 2px rgba(26, 26, 26, 0.03),
                0 12px 28px -18px rgba(184, 149, 111, 0.2);
        }
        .prakonsul-section__glow {
            position: absolute;
            top: -120px; right: -80px;
            width: 280px; height: 280px;
            border-radius: 9999px;
            background: rgba(207, 175, 83, 0.14);
            filter: blur(80px);
            pointer-events: none;
            opacity: 0.7;
        }
        .prakonsul-section__pattern {
            position: absolute; inset: 0;
            background-image: radial-gradient(rgba(184, 149, 111, 0.14) 1px, transparent 1px);
            background-size: 22px 22px;
            mask-image: linear-gradient(135deg, transparent 30%, black 60%, transparent 100%);
            -webkit-mask-image: linear-gradient(135deg, transparent 30%, black 60%, transparent 100%);
            opacity: 0.4;
            pointer-events: none;
        }
        .prakonsul-section__inner {
            position: relative;
            z-index: 1;
            padding: 1.5rem 1.25rem;
        }
        @media (min-width: 768px) {
            .prakonsul-section__inner { padding: 1.75rem 1.75rem; }
        }
        @media (min-width: 1024px) {
            .prakonsul-section__inner { padding: 2rem 2rem; }
        }

        /* Section header */
        .prakonsul-section__head {
            display: flex;
            flex-direction: column;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        @media (min-width: 640px) {
            .prakonsul-section__head {
                flex-direction: row;
                align-items: center;
                justify-content: space-between;
                gap: 1.5rem;
            }
        }
        .prakonsul-section__heading { min-width: 0; }
        .prakonsul-section__eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            margin: 0 0 0.5rem 0;
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 0.625rem;
            font-weight: 800;
            letter-spacing: 0.28em;
            text-transform: uppercase;
            color: #B8956F;
        }
        .prakonsul-section__dot {
            width: 5px; height: 5px;
            border-radius: 9999px;
            background: #CFAF53;
            box-shadow: 0 0 0 3px rgba(207, 175, 83, 0.18);
        }
        .prakonsul-section__title {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 1.25rem;
            font-weight: 900;
            letter-spacing: -0.015em;
            color: #1A1A1A;
            line-height: 1.2;
        }
        @media (min-width: 768px) {
            .prakonsul-section__title { font-size: 1.5rem; }
        }
        .prakonsul-section__sub {
            margin-top: 0.375rem;
            max-width: 36rem;
            font-size: 0.8125rem;
            line-height: 1.55;
            color: #64748B;
        }
        @media (min-width: 768px) {
            .prakonsul-section__sub { font-size: 0.875rem; }
        }
        .prakonsul-section__cta {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 0.625rem 1.125rem;
            border-radius: 9999px;
            background: linear-gradient(135deg, #CFAF53 0%, #B8956F 100%);
            color: #ffffff;
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 0.8125rem;
            font-weight: 700;
            text-decoration: none;
            cursor: pointer;
            white-space: nowrap;
            self-align: flex-start;
            box-shadow:
                0 1px 2px rgba(184, 149, 111, 0.25),
                0 8px 18px -8px rgba(184, 149, 111, 0.55);
            transition: transform 200ms ease, box-shadow 200ms ease, filter 200ms ease;
        }
        .prakonsul-section__cta svg { width: 14px; height: 14px; }
        .prakonsul-section__cta:hover {
            transform: translateY(-2px);
            filter: brightness(1.05);
            box-shadow:
                0 1px 2px rgba(184, 149, 111, 0.3),
                0 14px 24px -8px rgba(184, 149, 111, 0.6);
        }
        .prakonsul-section__cta:active { transform: translateY(0); }

        /* Grid (single centered card or auto-fill) */
        .prakonsul-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1rem;
            max-width: 820px;
            margin: 0 auto;
        }
        @media (min-width: 768px) {
            .prakonsul-grid { gap: 1.25rem; }
        }
        @media (min-width: 1024px) {
            .prakonsul-grid {
                grid-template-columns: repeat(auto-fill, minmax(360px, 1fr));
                max-width: 100%;
            }
        }
        /* When only 1 card, keep it centered max-width 820px on desktop */
        .prakonsul-grid:has(> .prakonsul-card:only-child) {
            grid-template-columns: 1fr;
            max-width: 820px;
        }

        /* ─────────  PRA-KONSUL UNIFIED CARD  ───────── */
        .prakonsul-card {
            position: relative;
            overflow: hidden;
            background:
                radial-gradient(120% 80% at 100% 0%, rgba(207, 175, 83, 0.06) 0%, transparent 60%),
                #FDFCFA;
            border: 1px solid rgba(232, 224, 216, 0.7);
            border-radius: 20px;
            box-shadow:
                0 1px 2px rgba(26, 26, 26, 0.03),
                0 8px 22px -16px rgba(184, 149, 111, 0.2);
        }
        .prakonsul-card__glow {
            position: absolute;
            top: -60px; right: -50px;
            width: 200px; height: 200px;
            border-radius: 9999px;
            background: rgba(207, 175, 83, 0.13);
            filter: blur(60px);
            pointer-events: none;
            opacity: 0.7;
        }
        .prakonsul-card__inner {
            position: relative;
            z-index: 1;
            padding: 1.5rem;
            display: flex;
            flex-direction: column;
            gap: 1.25rem;
        }
        @media (min-width: 768px) {
            .prakonsul-card__inner { padding: 1.75rem; gap: 1.5rem; }
        }

        /* Header — date left, status badge right */
        .prakonsul-card__header {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 0.75rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid rgba(232, 224, 216, 0.65);
        }
        .prakonsul-card__date {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            min-width: 0;
        }
        .prakonsul-card__date-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 40px; height: 40px;
            border-radius: 12px;
            background: linear-gradient(135deg, rgba(207, 175, 83, 0.18) 0%, rgba(184, 149, 111, 0.22) 100%);
            color: #B8956F;
            border: 1px solid rgba(184, 149, 111, 0.22);
            flex-shrink: 0;
        }
        .prakonsul-card__date-icon svg { width: 18px; height: 18px; stroke-width: 1.8; }
        .prakonsul-card__date-text {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 1rem;
            font-weight: 800;
            color: #1A1A1A;
            line-height: 1.2;
            letter-spacing: -0.01em;
        }
        .prakonsul-card__date-time {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 0.8125rem;
            font-weight: 500;
            color: #94A3B8;
            line-height: 1.2;
            margin-top: 4px;
        }

        /* Section */
        .prakonsul-card__section {
            display: flex;
            flex-direction: column;
            gap: 0.625rem;
        }
        .prakonsul-card__label {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 0.6875rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.18em;
            color: #94A3B8;
            margin: 0;
        }

        /* Keluhan */
        .prakonsul-card__quote {
            padding: 1rem 1.125rem;
            background: #FAF8F5;
            border: 1px solid rgba(232, 224, 216, 0.7);
            border-left: 3px solid #CFAF53;
            border-radius: 14px;
        }
        .prakonsul-card__keluhan {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 0.9375rem;
            line-height: 1.7;
            color: #1A1A1A;
            font-weight: 500;
            margin: 0;
            text-align: left;
            white-space: pre-wrap;
            word-break: break-word;
            overflow-wrap: anywhere;
        }
        .prakonsul-card__keluhan--clamp {
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        .prakonsul-card__more {
            margin-top: 0.5rem;
            background: transparent;
            border: 0;
            padding: 0;
            color: #B8956F;
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 0.8125rem;
            font-weight: 700;
            cursor: pointer;
            transition: color 160ms ease;
        }
        .prakonsul-card__more:hover {
            color: #8C7A63;
            text-decoration: underline;
        }

        /* Status badge — calmer, smaller */
        .prakonsul-card__status {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.4375rem 0.75rem;
            border-radius: 9999px;
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 0.75rem;
            font-weight: 700;
            line-height: 1;
            border: 1px solid transparent;
            white-space: nowrap;
        }
        .prakonsul-card__status-dot {
            width: 6px;
            height: 6px;
            border-radius: 9999px;
            display: inline-block;
            flex-shrink: 0;
        }
        .prakonsul-card__status--pending {
            background: #FFF8EB;
            color: #92400E;
            border-color: rgba(245, 158, 11, 0.25);
        }
        .prakonsul-card__status--pending .prakonsul-card__status-dot { background: #D97706; }
        .prakonsul-card__status--review {
            background: #EFF6FF;
            color: #1E40AF;
            border-color: rgba(37, 99, 235, 0.25);
        }
        .prakonsul-card__status--review .prakonsul-card__status-dot { background: #1D4ED8; }
        .prakonsul-card__status--done {
            background: #ECFDF5;
            color: #065F46;
            border-color: rgba(16, 185, 129, 0.25);
        }
        .prakonsul-card__status--done .prakonsul-card__status-dot { background: #047857; }
        .prakonsul-card__status--closed {
            background: #FEF2F2;
            color: #9F1239;
            border-color: rgba(244, 63, 94, 0.25);
        }
        .prakonsul-card__status--closed .prakonsul-card__status-dot { background: #E11D48; }

        /* Progress timeline */
        .prakonsul-timeline {
            list-style: none;
            margin: 0;
            padding: 1rem 1.125rem;
            background: #ffffff;
            border: 1px solid rgba(232, 224, 216, 0.7);
            border-radius: 14px;
            display: flex;
            flex-direction: column;
            gap: 1rem;
            position: relative;
        }
        .prakonsul-timeline__step {
            position: relative;
            display: flex;
            align-items: flex-start;
            gap: 0.875rem;
            padding-left: 0;
        }
        /* Connector line behind dots */
        .prakonsul-timeline__step:not(:last-child)::before {
            content: '';
            position: absolute;
            left: 11px;
            top: 26px;
            bottom: -1rem;
            width: 1.5px;
            background: #E2E8F0;
        }
        .prakonsul-timeline__step--done:not(:last-child)::before {
            background: linear-gradient(180deg, #B8956F 0%, #E2E8F0 100%);
        }
        .prakonsul-timeline__dot {
            position: relative;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 22px;
            height: 22px;
            border-radius: 9999px;
            background: #ffffff;
            border: 1.5px solid #E2E8F0;
            color: transparent;
            flex-shrink: 0;
            margin-top: 1px;
        }
        .prakonsul-timeline__dot svg { width: 12px; height: 12px; }
        .prakonsul-timeline__step--done .prakonsul-timeline__dot {
            background: linear-gradient(135deg, #CFAF53 0%, #B8956F 100%);
            border-color: transparent;
            color: #ffffff;
            box-shadow: 0 2px 6px -2px rgba(184, 149, 111, 0.5);
        }
        .prakonsul-timeline__step--active .prakonsul-timeline__dot {
            background: rgba(245, 158, 11, 0.12);
            border-color: rgba(245, 158, 11, 0.55);
        }
        .prakonsul-timeline__pulse {
            width: 8px;
            height: 8px;
            border-radius: 9999px;
            background: #D97706;
            box-shadow: 0 0 0 4px rgba(217, 119, 6, 0.18);
            animation: prakonsulPulse 2s ease-in-out infinite;
        }
        .prakonsul-timeline__body { flex: 1; min-width: 0; }
        .prakonsul-timeline__title {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 0.875rem;
            font-weight: 700;
            color: #1A1A1A;
            line-height: 1.3;
            margin: 0;
        }
        .prakonsul-timeline__step--done .prakonsul-timeline__title { color: #475569; }
        .prakonsul-timeline__step--idle .prakonsul-timeline__title { color: #94A3B8; }
        .prakonsul-timeline__sub {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 0.75rem;
            color: #94A3B8;
            line-height: 1.45;
            margin: 3px 0 0 0;
        }

        /* Info text */
        .prakonsul-card__info {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 0.8125rem;
            line-height: 1.6;
            color: #64748B;
            margin: 0;
            padding: 0.75rem 1rem;
            background: #FAF8F5;
            border-radius: 10px;
            border: 1px solid rgba(232, 224, 216, 0.55);
        }
        .prakonsul-card__info strong {
            color: #1A1A1A;
            font-weight: 700;
        }

        /* Footer */
        .prakonsul-card__footer {
            margin-top: 0.25rem;
            padding-top: 1.125rem;
            border-top: 1px solid rgba(232, 224, 216, 0.65);
        }
        .prakonsul-card__cta {
            width: 100%;
            min-height: 46px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 0.75rem 1.25rem;
            border-radius: 9999px;
            background: rgba(207, 175, 83, 0.08);
            border: 1px solid rgba(184, 149, 111, 0.4);
            color: #8C7A63;
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 0.875rem;
            font-weight: 700;
            cursor: pointer;
            transition: background 200ms ease, color 200ms ease, border-color 200ms ease, transform 200ms ease, box-shadow 200ms ease;
        }
        .prakonsul-card__cta svg {
            width: 14px; height: 14px;
            transition: transform 200ms ease;
        }
        .prakonsul-card__cta:hover {
            background: linear-gradient(135deg, #CFAF53 0%, #B8956F 100%);
            border-color: transparent;
            color: #ffffff;
            transform: translateY(-1px);
            box-shadow: 0 8px 18px -8px rgba(184, 149, 111, 0.55);
        }
        .prakonsul-card__cta:hover svg { transform: translateX(2px); }
        .prakonsul-card__cta:active { transform: translateY(0); }

        /* Primary CTA — gold gradient solid */
        .prakonsul-card__cta--primary {
            background: linear-gradient(135deg, #CFAF53 0%, #B8956F 100%);
            border-color: transparent;
            color: #ffffff;
            box-shadow:
                0 1px 2px rgba(184, 149, 111, 0.25),
                0 8px 18px -8px rgba(184, 149, 111, 0.55);
        }
        .prakonsul-card__cta--primary:hover {
            filter: brightness(1.05);
            box-shadow:
                0 1px 2px rgba(184, 149, 111, 0.3),
                0 12px 22px -8px rgba(184, 149, 111, 0.6);
        }

        /* Waiting CTA — disabled-soft state */
        .prakonsul-card__cta--waiting {
            background: #F8FAFC;
            border-color: rgba(226, 232, 240, 0.85);
            color: #94A3B8;
            cursor: pointer;
        }
        .prakonsul-card__cta--waiting:hover {
            background: #F8FAFC;
            border-color: rgba(184, 149, 111, 0.3);
            color: #8C7A63;
            transform: none;
            box-shadow: 0 4px 10px -6px rgba(0, 0, 0, 0.06);
        }
        .prakonsul-card__cta--waiting:hover svg { transform: none; }
        .prakonsul-card__cta--waiting svg { width: 14px; height: 14px; }

        @keyframes prakonsulPulse {
            0%, 100% { transform: scale(1);   opacity: 1;   box-shadow: 0 0 0 4px rgba(217, 119, 6, 0.18); }
            50%      { transform: scale(1.2); opacity: 0.85; box-shadow: 0 0 0 6px rgba(217, 119, 6, 0.05); }
        }

        /* ─────────  COMPANION ASIDE PANEL  ───────── */
        .prakonsul-aside {
            position: relative;
            overflow: hidden;
            background:
                radial-gradient(140% 100% at 100% 0%, rgba(207, 175, 83, 0.1) 0%, transparent 60%),
                linear-gradient(180deg, #FAF8F5 0%, #F5F0EB 100%);
            border: 1px solid rgba(232, 224, 216, 0.7);
            border-radius: 18px;
            box-shadow: 0 1px 2px rgba(26, 26, 26, 0.03);
        }
        .prakonsul-aside__glow {
            position: absolute;
            top: -60px; right: -40px;
            width: 200px; height: 200px;
            border-radius: 9999px;
            background: rgba(207, 175, 83, 0.18);
            filter: blur(60px);
            pointer-events: none;
        }
        .prakonsul-aside__inner {
            position: relative;
            z-index: 1;
            padding: 1.5rem;
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }
        @media (min-width: 1024px) {
            .prakonsul-aside__inner { padding: 1.75rem; }
        }
        .prakonsul-aside__icon {
            width: 44px; height: 44px;
            border-radius: 12px;
            background: linear-gradient(135deg, #CFAF53 0%, #B8956F 100%);
            color: #ffffff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            box-shadow:
                inset 0 0 0 1px rgba(255, 255, 255, 0.25),
                0 6px 14px -4px rgba(184, 149, 111, 0.5);
        }
        .prakonsul-aside__icon svg { width: 22px; height: 22px; }
        .prakonsul-aside__title {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 1rem;
            font-weight: 800;
            color: #1A1A1A;
            letter-spacing: -0.01em;
            line-height: 1.3;
        }
        .prakonsul-aside__desc {
            font-size: 0.8125rem;
            line-height: 1.6;
            color: #64748B;
            margin: 0;
        }
        .prakonsul-aside__strong {
            font-weight: 700;
            color: #1A1A1A;
        }

        /* Step list */
        .prakonsul-aside__steps {
            list-style: none;
            margin: 0.25rem 0 0 0;
            padding: 0.875rem;
            background: #ffffff;
            border: 1px solid rgba(232, 224, 216, 0.7);
            border-radius: 12px;
            display: flex;
            flex-direction: column;
            gap: 0.875rem;
        }
        .prakonsul-aside__step {
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
        }
        .prakonsul-aside__step-dot {
            position: relative;
            width: 22px; height: 22px;
            border-radius: 9999px;
            background: #ffffff;
            border: 1.5px solid #E2E8F0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: transparent;
            flex-shrink: 0;
            margin-top: 1px;
        }
        .prakonsul-aside__step-dot svg { width: 12px; height: 12px; }
        .prakonsul-aside__step--done .prakonsul-aside__step-dot {
            background: linear-gradient(135deg, #CFAF53 0%, #B8956F 100%);
            border-color: transparent;
            color: #ffffff;
            box-shadow: 0 2px 6px -2px rgba(184, 149, 111, 0.5);
        }
        .prakonsul-aside__step--active .prakonsul-aside__step-dot {
            background: rgba(245, 158, 11, 0.12);
            border-color: rgba(245, 158, 11, 0.5);
        }
        .prakonsul-aside__step-pulse {
            width: 8px; height: 8px;
            border-radius: 9999px;
            background: #D97706;
            box-shadow: 0 0 0 4px rgba(217, 119, 6, 0.2);
            animation: prakonsulPulse 2s ease-in-out infinite;
        }
        .prakonsul-aside__step-title {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 0.8125rem;
            font-weight: 700;
            color: #1A1A1A;
            line-height: 1.3;
            margin: 0;
        }
        .prakonsul-aside__step--done .prakonsul-aside__step-title { color: #475569; }
        .prakonsul-aside__step:not(.prakonsul-aside__step--done):not(.prakonsul-aside__step--active) .prakonsul-aside__step-title {
            color: #94A3B8;
        }
        .prakonsul-aside__step-sub {
            font-size: 0.6875rem;
            color: #94A3B8;
            line-height: 1.4;
            margin: 2px 0 0 0;
        }

        /* Aside link */
        .prakonsul-aside__link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.4375rem;
            margin-top: 0.25rem;
            padding: 0.625rem 1rem;
            border-radius: 9999px;
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            border: 1px solid rgba(184, 149, 111, 0.3);
            color: #8C7A63;
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 0.8125rem;
            font-weight: 700;
            text-decoration: none;
            transition: background 200ms ease, color 200ms ease, transform 200ms ease, box-shadow 200ms ease, border-color 200ms ease;
        }
        .prakonsul-aside__link svg { width: 14px; height: 14px; flex-shrink: 0; }
        .prakonsul-aside__link-arrow { transition: transform 200ms ease; }
        .prakonsul-aside__link:hover {
            background: linear-gradient(135deg, #CFAF53 0%, #B8956F 100%);
            color: #ffffff;
            border-color: transparent;
            transform: translateY(-1px);
            box-shadow: 0 8px 18px -8px rgba(184, 149, 111, 0.55);
        }
        .prakonsul-aside__link:hover .prakonsul-aside__link-arrow { transform: translateX(2px); }

        @keyframes prakonsulPulse {
            0%, 100% { transform: scale(1);   opacity: 1;   box-shadow: 0 0 0 4px rgba(217, 119, 6, 0.2); }
            50%      { transform: scale(1.2); opacity: 0.85; box-shadow: 0 0 0 6px rgba(217, 119, 6, 0.05); }
        }

        @media (prefers-reduced-motion: reduce) {
            .prakonsul-card,
            .prakonsul-card__cta,
            .prakonsul-card__cta svg,
            .prakonsul-card__more,
            .prakonsul-section__cta,
            .prakonsul-aside__link,
            .prakonsul-aside__link-arrow,
            .prakonsul-aside__step-pulse,
            .prakonsul-modal__close,
            .prakonsul-modal__close-btn,
            .prakonsul-modal__photo,
            .prakonsul-modal__photo img,
            .prakonsul-modal__photo-overlay { transition: none; animation: none; }
            .prakonsul-card:hover,
            .prakonsul-card__cta:hover,
            .prakonsul-section__cta:hover,
            .prakonsul-aside__link:hover,
            .prakonsul-modal__photo:hover,
            .prakonsul-modal__close-btn:hover { transform: none; filter: none; }
            .prakonsul-card__cta:hover svg,
            .prakonsul-aside__link:hover .prakonsul-aside__link-arrow,
            .prakonsul-modal__photo:hover img { transform: none; }
        }

        /* ─────────  MODAL DETAIL PRA-KONSULTASI  ───────── */
        .prakonsul-modal {
            position: fixed;
            inset: 0;
            z-index: 9999;
            display: flex;
            align-items: flex-end;
            justify-content: center;
            background: rgba(26, 26, 26, 0.55);
            backdrop-filter: blur(4px);
            -webkit-backdrop-filter: blur(4px);
            padding: 0;
        }
        @media (min-width: 640px) {
            .prakonsul-modal {
                align-items: center;
                padding: 1.5rem;
            }
        }

        .prakonsul-modal__panel {
            position: relative;
            display: flex;
            flex-direction: column;
            width: 100%;
            max-height: 92vh;
            background:
                radial-gradient(120% 60% at 100% 0%, rgba(207, 175, 83, 0.08) 0%, transparent 60%),
                #FFFFFF;
            border: 1px solid rgba(232, 224, 216, 0.7);
            border-radius: 24px 24px 0 0;
            box-shadow:
                0 -2px 4px rgba(0, 0, 0, 0.04),
                0 -32px 60px -12px rgba(26, 26, 26, 0.32);
            overflow: hidden;
        }
        @media (min-width: 640px) {
            .prakonsul-modal__panel {
                max-width: 760px;
                max-height: 88vh;
                border-radius: 24px;
                box-shadow:
                    0 1px 4px rgba(0, 0, 0, 0.04),
                    0 32px 60px -12px rgba(26, 26, 26, 0.32),
                    0 18px 32px -12px rgba(184, 149, 111, 0.18);
            }
        }
        .prakonsul-modal__glow {
            position: absolute;
            top: -100px; right: -80px;
            width: 280px; height: 280px;
            border-radius: 9999px;
            background: rgba(207, 175, 83, 0.16);
            filter: blur(80px);
            pointer-events: none;
            opacity: 0.65;
            z-index: 0;
        }

        /* Drag handle (mobile only) */
        .prakonsul-modal__handle {
            display: flex;
            justify-content: center;
            padding: 0.5rem 0 0.25rem;
            flex-shrink: 0;
            position: relative;
            z-index: 1;
        }
        .prakonsul-modal__handle span {
            width: 42px;
            height: 4px;
            border-radius: 9999px;
            background: rgba(184, 149, 111, 0.35);
        }
        @media (min-width: 640px) {
            .prakonsul-modal__handle { display: none; }
        }

        /* Header */
        .prakonsul-modal__header {
            position: relative;
            z-index: 1;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.875rem;
            padding: 1rem 1.25rem 1rem;
            border-bottom: 1px solid rgba(232, 224, 216, 0.6);
            flex-shrink: 0;
        }
        @media (min-width: 640px) {
            .prakonsul-modal__header { padding: 1.25rem 1.5rem; }
        }
        .prakonsul-modal__header-left {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            min-width: 0;
            flex: 1;
        }
        .prakonsul-modal__header-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 40px; height: 40px;
            border-radius: 12px;
            background: linear-gradient(135deg, #CFAF53 0%, #B8956F 100%);
            color: #fff;
            box-shadow:
                inset 0 0 0 1px rgba(255, 255, 255, 0.25),
                0 4px 10px -4px rgba(184, 149, 111, 0.5);
            flex-shrink: 0;
        }
        .prakonsul-modal__header-icon svg { width: 20px; height: 20px; }
        .prakonsul-modal__title {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 1rem;
            font-weight: 800;
            color: #1A1A1A;
            letter-spacing: -0.01em;
            line-height: 1.25;
        }
        @media (min-width: 640px) {
            .prakonsul-modal__title { font-size: 1.125rem; }
        }
        .prakonsul-modal__subtitle {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 0.75rem;
            color: #94A3B8;
            margin-top: 2px;
            font-weight: 500;
        }
        .prakonsul-modal__close {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 36px; height: 36px;
            border-radius: 9999px;
            background: #FAF8F5;
            color: #64748B;
            border: 1px solid rgba(232, 224, 216, 0.7);
            cursor: pointer;
            transition: background 200ms ease, color 200ms ease, transform 200ms ease, border-color 200ms ease;
            flex-shrink: 0;
        }
        .prakonsul-modal__close svg { width: 16px; height: 16px; }
        .prakonsul-modal__close:hover {
            background: #ffffff;
            border-color: rgba(184, 149, 111, 0.4);
            color: #1A1A1A;
            transform: rotate(90deg);
        }

        /* Body */
        .prakonsul-modal__body {
            position: relative;
            z-index: 1;
            flex: 1;
            overflow-y: auto;
            padding: 1.25rem 1.25rem 1.5rem;
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }
        @media (min-width: 640px) {
            .prakonsul-modal__body { padding: 1.5rem 1.75rem 1.75rem; gap: 1.75rem; }
        }

        /* Meta badges */
        .prakonsul-modal__meta {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 0.5rem;
        }
        .prakonsul-modal__badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.4375rem 0.75rem;
            border-radius: 9999px;
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 0.75rem;
            font-weight: 700;
            line-height: 1;
            border: 1px solid transparent;
        }
        .prakonsul-modal__badge-dot {
            width: 6px; height: 6px;
            border-radius: 9999px;
            display: inline-block;
            flex-shrink: 0;
        }
        .prakonsul-modal__badge--pending {
            background: #FFF8EB;
            color: #92400E;
            border-color: rgba(245, 158, 11, 0.28);
        }
        .prakonsul-modal__badge--pending .prakonsul-modal__badge-dot { background: #D97706; }
        .prakonsul-modal__badge--review {
            background: #EFF6FF;
            color: #1E40AF;
            border-color: rgba(37, 99, 235, 0.28);
        }
        .prakonsul-modal__badge--review .prakonsul-modal__badge-dot { background: #1D4ED8; }
        .prakonsul-modal__badge--done {
            background: #ECFDF5;
            color: #065F46;
            border-color: rgba(16, 185, 129, 0.28);
        }
        .prakonsul-modal__badge--done .prakonsul-modal__badge-dot { background: #047857; }
        .prakonsul-modal__badge--closed {
            background: #FEF2F2;
            color: #9F1239;
            border-color: rgba(244, 63, 94, 0.28);
        }
        .prakonsul-modal__badge--closed .prakonsul-modal__badge-dot { background: #E11D48; }

        .prakonsul-modal__chip {
            display: inline-flex;
            align-items: center;
            gap: 0.375rem;
            padding: 0.375rem 0.75rem;
            border-radius: 9999px;
            background: rgba(184, 149, 111, 0.08);
            color: #8C7A63;
            border: 1px solid rgba(184, 149, 111, 0.2);
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 0.6875rem;
            font-weight: 700;
            line-height: 1;
        }
        .prakonsul-modal__chip svg { width: 12px; height: 12px; }

        /* Section */
        .prakonsul-modal__section {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }
        .prakonsul-modal__section-head {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .prakonsul-modal__section-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 28px; height: 28px;
            border-radius: 9px;
            background: rgba(207, 175, 83, 0.12);
            color: #B8956F;
            flex-shrink: 0;
        }
        .prakonsul-modal__section-icon svg { width: 15px; height: 15px; }
        .prakonsul-modal__section-title {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 0.75rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.16em;
            color: #1A1A1A;
            margin: 0;
        }

        /* Quote (Keluhan) */
        .prakonsul-modal__quote {
            position: relative;
            padding: 1rem 1.125rem;
            background: linear-gradient(180deg, #FAF8F5 0%, #F5F0EB 100%);
            border: 1px solid rgba(232, 224, 216, 0.7);
            border-left: 3px solid #CFAF53;
            border-radius: 14px;
        }
        .prakonsul-modal__quote-text {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 0.9375rem;
            line-height: 1.7;
            color: #1A1A1A;
            font-weight: 500;
            margin: 0;
            white-space: pre-wrap;
            word-break: break-word;
            overflow-wrap: anywhere;
        }

        /* Note (Riwayat Penyakit) */
        .prakonsul-modal__note {
            padding: 0.875rem 1rem;
            background: #FAF8F5;
            border: 1px solid rgba(232, 224, 216, 0.7);
            border-radius: 12px;
        }
        .prakonsul-modal__note p {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 0.875rem;
            line-height: 1.6;
            color: #475569;
            margin: 0;
            white-space: pre-wrap;
            word-break: break-word;
        }

        /* Info Grid */
        .prakonsul-modal__info-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 0.75rem;
        }
        .prakonsul-modal__info {
            background: #FDFCFA;
            border: 1px solid rgba(232, 224, 216, 0.6);
            border-radius: 12px;
            padding: 0.875rem 1rem;
        }
        .prakonsul-modal__info--wide { grid-column: 1 / -1; }
        .prakonsul-modal__info-label {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 0.625rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.16em;
            color: #94A3B8;
            margin: 0 0 0.375rem 0;
        }
        .prakonsul-modal__info-value {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 0.9375rem;
            font-weight: 700;
            color: #1A1A1A;
            line-height: 1.45;
            margin: 0;
            word-break: break-word;
        }
        .prakonsul-modal__info-value--empty {
            color: #94A3B8;
            font-weight: 500;
            font-style: italic;
        }
        .prakonsul-modal__info-hint {
            margin-top: 0.5rem;
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 0.75rem;
            color: #64748B;
        }
        .prakonsul-modal__info-hint strong { color: #1A1A1A; font-weight: 700; }

        /* Treatment chips */
        .prakonsul-modal__chips-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 0.5rem;
        }
        @media (min-width: 640px) {
            .prakonsul-modal__chips-grid { grid-template-columns: repeat(4, minmax(0, 1fr)); }
        }
        .prakonsul-modal__pill {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.4375rem;
            padding: 0.5625rem 0.875rem;
            border-radius: 12px;
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 0.8125rem;
            font-weight: 700;
            border: 1px solid transparent;
            line-height: 1;
        }
        .prakonsul-modal__pill svg { width: 14px; height: 14px; flex-shrink: 0; }
        .prakonsul-modal__pill--active {
            background: linear-gradient(135deg, #ECFDF5 0%, #D1FAE5 100%);
            color: #047857;
            border-color: rgba(16, 185, 129, 0.32);
        }
        .prakonsul-modal__pill--inactive {
            background: #F8FAFC;
            color: #94A3B8;
            border-color: rgba(226, 232, 240, 0.8);
        }

        /* Photos */
        .prakonsul-modal__photos {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 0.75rem;
        }
        .prakonsul-modal__photo {
            position: relative;
            display: block;
            aspect-ratio: 3 / 4;
            border-radius: 14px;
            overflow: hidden;
            background: #FAF8F5;
            border: 1px solid rgba(232, 224, 216, 0.7);
            cursor: pointer;
            transition: transform 220ms ease, box-shadow 220ms ease, border-color 220ms ease;
        }
        .prakonsul-modal__photo img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
            transition: transform 320ms ease;
        }
        .prakonsul-modal__photo:hover {
            transform: translateY(-2px);
            border-color: rgba(184, 149, 111, 0.5);
            box-shadow: 0 12px 24px -10px rgba(184, 149, 111, 0.4);
        }
        .prakonsul-modal__photo:hover img { transform: scale(1.05); }
        .prakonsul-modal__photo-overlay {
            position: absolute;
            inset: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(180deg, rgba(26, 26, 26, 0) 50%, rgba(26, 26, 26, 0.55) 100%);
            opacity: 0;
            transition: opacity 220ms ease;
        }
        .prakonsul-modal__photo:hover .prakonsul-modal__photo-overlay { opacity: 1; }
        .prakonsul-modal__photo-overlay svg {
            width: 24px; height: 24px;
            color: #fff;
            margin-bottom: 30%;
            filter: drop-shadow(0 2px 4px rgba(0,0,0,0.3));
        }
        .prakonsul-modal__photo-label {
            position: absolute;
            left: 0; right: 0; bottom: 0;
            text-align: center;
            padding: 0.4375rem 0.5rem;
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 0.6875rem;
            font-weight: 700;
            color: #ffffff;
            background: linear-gradient(180deg, rgba(26, 26, 26, 0) 0%, rgba(26, 26, 26, 0.7) 100%);
            letter-spacing: 0.04em;
        }
        .prakonsul-modal__photo--empty {
            cursor: default;
            border-style: dashed;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            color: #CBD5E1;
        }
        .prakonsul-modal__photo--empty:hover {
            transform: none;
            border-color: rgba(232, 224, 216, 0.7);
            box-shadow: none;
        }
        .prakonsul-modal__photo--empty svg { width: 24px; height: 24px; }
        .prakonsul-modal__photo--empty .prakonsul-modal__photo-label {
            position: static;
            background: transparent;
            color: #94A3B8;
            padding: 0;
        }

        /* Footer */
        .prakonsul-modal__footer {
            position: relative;
            z-index: 1;
            padding: 1rem 1.25rem;
            border-top: 1px solid rgba(232, 224, 216, 0.6);
            flex-shrink: 0;
            background: #FFFFFF;
            display: flex;
            justify-content: flex-end;
        }
        @media (min-width: 640px) {
            .prakonsul-modal__footer { padding: 1.125rem 1.75rem; }
        }
        .prakonsul-modal__close-btn {
            min-height: 44px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.625rem 1.5rem;
            border-radius: 9999px;
            background: #FAF8F5;
            color: #475569;
            border: 1px solid rgba(232, 224, 216, 0.85);
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 0.875rem;
            font-weight: 700;
            cursor: pointer;
            transition: background 200ms ease, color 200ms ease, border-color 200ms ease, transform 200ms ease, box-shadow 200ms ease;
        }
        .prakonsul-modal__close-btn:hover {
            background: #ffffff;
            color: #1A1A1A;
            border-color: rgba(184, 149, 111, 0.4);
            transform: translateY(-1px);
            box-shadow: 0 8px 18px -10px rgba(0, 0, 0, 0.15);
        }
        @media (max-width: 639px) {
            .prakonsul-modal__footer { justify-content: stretch; }
            .prakonsul-modal__close-btn { width: 100%; }
        }
    </style>
@endonce
