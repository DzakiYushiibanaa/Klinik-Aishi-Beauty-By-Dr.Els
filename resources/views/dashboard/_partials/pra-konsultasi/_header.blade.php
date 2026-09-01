{{-- Partial: Pra-Konsultasi Header --}}
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
