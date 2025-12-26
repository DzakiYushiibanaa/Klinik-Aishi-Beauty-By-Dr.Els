<x-guest-layout :title="'Detail Pra Konsul'">

<div class="max-w-7xl mx-auto">
    {{-- HEADER --}}
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <p class="text-xs font-semibold uppercase tracking-widest text-[#CFAF53]">
                Detail Pra Konsultasi
            </p>
            <h1 class="text-2xl font-semibold text-slate-900">
                Form #{{ $nomor }}
            </h1>
            <p class="mt-1 text-sm text-slate-600">
                Dikirim {{ $form->created_at?->format('d M Y H:i') }} ·
                Status:
                <span class="font-medium text-slate-800">{{ ucfirst($form->status) }}</span>
            </p>
        </div>

        <a href="{{ route('pra.index') }}"
           class="inline-flex items-center justify-center rounded-xl border border-slate-300
                  bg-white px-4 py-2 text-sm font-medium text-slate-700
                  hover:bg-slate-50">
            ← Kembali
        </a>
    </div>

    <div class="grid gap-6 lg:grid-cols-3">

        {{-- ================= LEFT CONTENT ================= --}}
        <div class="lg:col-span-2 space-y-6">

            {{-- KELUHAN --}}
            <section class="rounded-2xl border border-slate-200 bg-white p-5">
                <h2 class="font-semibold text-slate-900 mb-2">Keluhan Utama</h2>
                <p class="text-sm text-slate-700 whitespace-pre-line">
                    {{ $form->keluhan_utama }}
                </p>

                <div class="mt-4 grid gap-3 sm:grid-cols-3 text-sm">
                    <x-info label="Jenis Kulit" :value="$form->jenis_kulit" />
                    <x-info label="Gol. Darah" :value="$form->golongan_darah" />
                    <x-info label="Periode Skincare"
                            :value="$form->periode_skincare_bulan ? $form->periode_skincare_bulan.' bulan' : null" />
                </div>
            </section>

            {{-- RIWAYAT --}}
            <section class="rounded-2xl border border-slate-200 bg-white p-5 space-y-4">
                <h2 class="font-semibold text-slate-900">Riwayat & Skincare</h2>

                <div class="grid gap-4 sm:grid-cols-2 text-sm">
                    <x-box label="Skincare Sebelumnya" :value="$form->skincare_sebelumnya" />
                    <x-box label="Riwayat Penyakit" :value="$form->riwayat_penyakit" />
                </div>

                <div class="grid gap-2 sm:grid-cols-2 text-sm">
                    <x-boolean label="Pernah Facial" :value="$form->pernah_facial" />
                    <x-boolean label="Pernah Laser" :value="$form->pernah_laser" />
                    <x-boolean label="Pernah Skin Booster" :value="$form->pernah_skin_booster" />
                    <x-boolean label="Pernah Inject / Infus" :value="$form->pernah_inject_infus" />
                </div>
            </section>

            {{-- ================= REKOMENDASI ================= --}}
            <section class="rounded-2xl border border-slate-200 bg-white p-5 space-y-5">
                <div>
                    <h2 class="font-semibold text-slate-900">Rekomendasi Dokter</h2>
                    <p class="text-sm text-slate-600">
                        {{ $form->dokter ? 'Oleh '.$form->dokter->name : 'Belum direview dokter' }}
                    </p>
                </div>

                @if ($rekomProduk->isEmpty() && $rekomTreatment->isEmpty())
                    <div class="rounded-xl border border-dashed border-slate-300 bg-slate-50 p-4 text-sm text-slate-600">
                        Belum ada rekomendasi yang diberikan.
                    </div>
                @endif

                {{-- ===== PRODUK ===== --}}
                @if ($rekomProduk->count())
                    <div>
                        <h3 class="font-semibold text-slate-800 mb-3">Produk Rekomendasi</h3>

                        <div class="grid gap-4 sm:grid-cols-2">
                            @foreach ($rekomProduk as $it)
                                <div class="rounded-xl border border-slate-200 bg-slate-50 p-4 flex flex-col justify-between">
                                    <div>
                                        <p class="font-medium text-slate-900">
                                            {{ $it->product->nama }}
                                        </p>
                                        <p class="mt-1 text-sm text-slate-600">
                                            Rp {{ number_format($it->product->harga,0,',','.') }}
                                        </p>

                                        @if ($it->catatan)
                                            <p class="mt-2 text-sm text-slate-700">
                                                {{ $it->catatan }}
                                            </p>
                                        @endif
                                    </div>

                                    {{-- ACTION --}}
                                    <form method="POST"
                                          action="{{ route('cart.add', $it->product) }}"
                                          class="mt-4">
                                        @csrf
                                        <input type="hidden" name="qty" value="1">

                                        <button
                                            class="w-full rounded-xl bg-[#CFAF53] px-4 py-2
                                                   text-sm font-medium text-slate-900
                                                   hover:bg-[#CFAF53]/90">
                                            + Tambah ke Keranjang
                                        </button>
                                    </form>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- ===== TREATMENT ===== --}}
                @if ($rekomTreatment->count())
                    <div>
                        <h3 class="font-semibold text-slate-800 mb-3">Rekomendasi Treatment</h3>

                        <div class="grid gap-4 sm:grid-cols-2">
                            @foreach ($rekomTreatment as $it)
                                <div class="rounded-xl border border-slate-200 bg-slate-50 p-4 flex flex-col justify-between">
                                    <div>
                                        <p class="font-medium text-slate-900">
                                            {{ $it->treatment->nama }}
                                        </p>
                                        <p class="mt-1 text-sm text-slate-600">
                                            Rp {{ number_format($it->treatment->harga,0,',','.') }}
                                        </p>

                                        @if ($it->catatan)
                                            <div class="mt-2 rounded-lg bg-yellow-50 p-2 text-xs text-yellow-800">
                                                <strong>Catatan Dokter:</strong><br>
                                                {{ $it->catatan }}
                                            </div>
                                        @endif
                                    </div>

                                    <a href="{{ route('appointment.create', [
                                            'treatment_id' => $it->treatment->treatment_id,
                                            'patient_form_id' => $form->patient_form_id
                                        ]) }}"
                                       class="mt-4 block w-full rounded-xl border border-[#CFAF53]
                                              bg-white px-4 py-2 text-center text-sm font-medium
                                              text-[#CFAF53] hover:bg-[#CFAF53]/10">
                                        Buat Janji Temu
                                    </a>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </section>
        </div>

        {{-- ================= RIGHT SIDEBAR ================= --}}
        <aside class="space-y-4">
            <section class="rounded-2xl border border-slate-200 bg-white p-5">
                <h2 class="font-semibold text-slate-900 mb-3">Foto Wajah</h2>

                @foreach (['Depan'=>'foto_depan_path','Kiri'=>'foto_kiri_path','Kanan'=>'foto_kanan_path'] as $label=>$field)
                    <div class="mb-3">
                        <p class="text-sm font-medium text-slate-700">{{ $label }}</p>

                        @if ($form->$field)
                            <img src="{{ asset('storage/'.$form->$field) }}"
                                 class="mt-2 h-40 w-full rounded-xl object-cover border">
                        @else
                            <p class="mt-1 text-sm text-slate-500">Tidak ada foto</p>
                        @endif
                    </div>
                @endforeach
            </section>
        </aside>
    </div>
</div>

</x-guest-layout>
