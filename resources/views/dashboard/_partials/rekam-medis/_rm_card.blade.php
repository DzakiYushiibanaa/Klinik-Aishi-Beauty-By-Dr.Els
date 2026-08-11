{{-- Partial: RM Card --}}
                @php
                    /* â”€â”€ Layanan: ambil dari appointment items, pisahkan
                          consultation vs treatment, dedup nama. */
                    $items = $rm->appointment?->items ?? collect();

                    $consultationNames = $items
                        ->filter(fn ($i) => strtolower((string) $i->item_type) === 'consultation')
                        ->map(fn ($i) => $i->consultation?->nama)
                        ->filter()
                        ->unique()
                        ->values();

                    $treatmentNames = $items
                        ->filter(fn ($i) => in_array(strtolower((string) $i->item_type), ['treatment'], true))
                        ->map(fn ($i) => $i->treatment?->nama)
                        ->filter()
                        ->unique()
                        ->values();

                    /* â”€â”€ Produk rekomendasi: pecah jadi list kalau dipisah koma. */
                    $produkList = collect(explode(',', (string) ($rm->produk_rekomendasi ?? '')))
                        ->map(fn ($s) => trim($s))
                        ->filter()
                        ->values();

                    $searchableText = strtolower(
                        ($rm->diagnosa ?? '') . ' ' .
                        ($rm->tindakan ?? '') . ' ' .
                        ($rm->keluhan ?? '') . ' ' .
                        $treatmentNames->implode(', ') . ' ' .
                        $consultationNames->implode(', ') . ' ' .
                        $produkList->implode(', ')
                    );

                    $tanggalKunjungan = $rm->appointment?->tanggal_jam
                        ? \Illuminate\Support\Carbon::parse($rm->appointment->tanggal_jam)->translatedFormat('d F Y')
                        : $rm->created_at->translatedFormat('d F Y');

                    $fotoSebelum = $rm->foto_sebelum_path ? asset('storage/' . $rm->foto_sebelum_path) : null;
                    $fotoSesudah = $rm->foto_sesudah_path ? asset('storage/' . $rm->foto_sesudah_path) : null;
                @endphp

                <div x-data="{ expanded: false }"
                     x-show="!rmSearch.trim() || '{{ addslashes($searchableText) }}'.includes(rmSearch.toLowerCase())"
                     class="bg-white rounded-2xl border border-[#E8E0D8]/70 overflow-hidden">

                    @include('dashboard._partials.rekam-medis._rm_card_header')
                    @include('dashboard._partials.rekam-medis._rm_card_detail')
                </div>
