<x-guest-layout :title="'Buat Pra Konsul'">
    <div class="rounded-3xl bg-gradient-to-br from-[#CFAF53]/15 via-white to-[#CFAF53]/10 p-5 sm:p-8 lg:p-10">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="text-center sm:text-left">
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-[#CFAF53]">Pra Konsultasi</p>
                <h1 class="text-2xl font-semibold text-slate-900 sm:text-3xl">Buat Pra Konsultasi</h1>
                <p class="text-sm text-slate-600 mt-1 sm:text-base">Isi data singkat agar dokter bisa review lebih cepat.</p>
            </div>
            <div class="w-full rounded-2xl border border-[#CFAF53]/20 bg-white px-4 py-3 text-center text-sm text-slate-700 sm:w-auto">
                Estimasi waktu isi: <span class="font-semibold text-slate-900">3-5 menit</span>
            </div>
        </div>

        @if ($errors->any())
            <div class="mt-5 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-rose-800">
                <ul class="list-disc pl-5">
                    @foreach ($errors->all() as $e)
                        <li>{{ $e }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('pra.store') }}" enctype="multipart/form-data"
              class="mt-6 grid gap-5 sm:gap-6 lg:grid-cols-3">
            @csrf

            <div class="lg:col-span-2 space-y-5">
                <section class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5">
                    <div>
                        <label class="text-sm font-medium text-slate-700">Keluhan Utama *</label>
                        <textarea name="keluhan_utama" rows="4" required
                            class="mt-2 w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 placeholder:text-slate-400 focus:border-[#CFAF53] focus:outline-none focus:ring-2 focus:ring-[#CFAF53]/20 sm:text-base"
                        >{{ old('keluhan_utama') }}</textarea>
                        <p class="mt-2 text-xs text-slate-500">Ceritakan keluhan yang paling ganggu sekarang, boleh tulis sejak kapan.</p>
                    </div>
                </section>

                <section class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5">
                    <h2 class="text-sm font-semibold text-slate-800">Profil Kulit</h2>
                    <div class="mt-4 grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="text-sm font-medium text-slate-700">Jenis Kulit</label>
                            <select name="jenis_kulit"
                                class="mt-2 w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 focus:border-[#CFAF53] focus:outline-none focus:ring-2 focus:ring-[#CFAF53]/20 sm:text-base">
                                @php($jk = old('jenis_kulit'))
                                <option value="" {{ $jk === null || $jk === '' ? 'selected' : '' }}>Pilih...</option>
                                <option value="Kering" {{ $jk === 'Kering' ? 'selected' : '' }}>Kering</option>
                                <option value="Berminyak" {{ $jk === 'Berminyak' ? 'selected' : '' }}>Berminyak</option>
                                <option value="Kombinasi" {{ $jk === 'Kombinasi' ? 'selected' : '' }}>Kombinasi</option>
                                <option value="Sensitif" {{ $jk === 'Sensitif' ? 'selected' : '' }}>Sensitif</option>
                                <option value="Normal" {{ $jk === 'Normal' ? 'selected' : '' }}>Normal</option>
                            </select>
                        </div>

                        <div>
                            <label class="text-sm font-medium text-slate-700">Golongan Darah</label>
                            <select name="golongan_darah"
                                class="mt-2 w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 focus:border-[#CFAF53] focus:outline-none focus:ring-2 focus:ring-[#CFAF53]/20 sm:text-base">
                                @php($gd = old('golongan_darah'))
                                <option value="" {{ $gd === null || $gd === '' ? 'selected' : '' }}>Pilih...</option>
                                <option value="A"  {{ $gd === 'A' ? 'selected' : '' }}>A</option>
                                <option value="B"  {{ $gd === 'B' ? 'selected' : '' }}>B</option>
                                <option value="AB" {{ $gd === 'AB' ? 'selected' : '' }}>AB</option>
                                <option value="O"  {{ $gd === 'O' ? 'selected' : '' }}>O</option>
                                <option value="-"  {{ $gd === '-' ? 'selected' : '' }}>Tidak tahu / Tidak ingin isi</option>
                            </select>
                        </div>
                    </div>
                </section>

                <section class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5">
                    <div class="grid gap-4">
                        <div>
                            <label class="text-sm font-medium text-slate-700">Skincare Sebelumnya</label>
                            <textarea name="skincare_sebelumnya" rows="3"
                                class="mt-2 w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 placeholder:text-slate-400 focus:border-[#CFAF53] focus:outline-none focus:ring-2 focus:ring-[#CFAF53]/20 sm:text-base"
                                placeholder="Contoh: facial wash A, toner B, serum C..."
                            >{{ old('skincare_sebelumnya') }}</textarea>
                            <p class="mt-2 text-xs text-slate-500">Tulis skincare yang rutin dipakai 1-2 bulan terakhir.</p>
                        </div>

                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <label class="text-sm font-medium text-slate-700">Periode Skincare (bulan)</label>
                                <input type="number" name="periode_skincare_bulan" min="0" max="240"
                                    value="{{ old('periode_skincare_bulan', 0) }}"
                                    class="mt-2 w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 placeholder:text-slate-400 focus:border-[#CFAF53] focus:outline-none focus:ring-2 focus:ring-[#CFAF53]/20 sm:text-base"
                                    placeholder="0" />
                                <p class="mt-1 text-xs text-slate-500">Perkiraan juga boleh, isi 0 kalau belum rutin.</p>
                            </div>
                            <div class="hidden sm:block"></div>
                        </div>

                        <div>
                            <label class="text-sm font-medium text-slate-700">Riwayat Penyakit</label>
                            <textarea name="riwayat_penyakit" rows="3"
                                class="mt-2 w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 placeholder:text-slate-400 focus:border-[#CFAF53] focus:outline-none focus:ring-2 focus:ring-[#CFAF53]/20 sm:text-base"
                            >{{ old('riwayat_penyakit') }}</textarea>
                            <p class="mt-2 text-xs text-slate-500">Contoh: alergi tertentu, masalah hormonal, atau penyakit kronis.</p>
                        </div>
                    </div>
                </section>

                <section class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5">
                    <h2 class="text-sm font-semibold text-slate-800">Riwayat Treatment</h2>
                    <p class="mt-1 text-xs text-slate-500">Centang yang pernah kamu lakukan dalam 12 bulan terakhir.</p>
                    <div class="mt-4 grid gap-3 sm:grid-cols-2">
                        <label class="flex items-center gap-2 text-sm text-slate-700">
                            <input type="checkbox" name="pernah_facial" value="1"
                                {{ old('pernah_facial') ? 'checked' : '' }}
                                class="h-4 w-4 rounded border-slate-300 text-[#CFAF53] focus:ring-2 focus:ring-[#CFAF53]/30">
                            Pernah Facial
                        </label>

                        <label class="flex items-center gap-2 text-sm text-slate-700">
                            <input type="checkbox" name="pernah_laser" value="1"
                                {{ old('pernah_laser') ? 'checked' : '' }}
                                class="h-4 w-4 rounded border-slate-300 text-[#CFAF53] focus:ring-2 focus:ring-[#CFAF53]/30">
                            Pernah Laser
                        </label>

                        <label class="flex items-center gap-2 text-sm text-slate-700">
                            <input type="checkbox" name="pernah_skin_booster" value="1"
                                {{ old('pernah_skin_booster') ? 'checked' : '' }}
                                class="h-4 w-4 rounded border-slate-300 text-[#CFAF53] focus:ring-2 focus:ring-[#CFAF53]/30">
                            Pernah Skin Booster
                        </label>

                        <label class="flex items-center gap-2 text-sm text-slate-700">
                            <input type="checkbox" name="pernah_inject_infus" value="1"
                                {{ old('pernah_inject_infus') ? 'checked' : '' }}
                                class="h-4 w-4 rounded border-slate-300 text-[#CFAF53] focus:ring-2 focus:ring-[#CFAF53]/30">
                            Pernah Inject/Infus
                        </label>
                    </div>
                </section>
            </div>

            <aside class="space-y-4">
                <section class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <h2 class="text-sm font-semibold text-slate-800">Foto Wajah</h2>
                            <p class="text-sm text-slate-500 mt-1">Opsional, tapi sangat membantu dokter.</p>
                        </div>
                        <span class="rounded-full bg-[#CFAF53]/10 px-3 py-1 text-xs font-semibold text-[#8B6F1B]">Opsional</span>
                    </div>

                    <div class="mt-4 space-y-4">
                        <div>
                            <label class="text-sm font-medium text-slate-700">Foto Depan</label>
                            <input type="file" name="foto_depan" accept="image/*"
                                class="mt-2 w-full text-xs text-slate-600 file:mr-3 file:rounded-lg file:border-0 file:bg-[#CFAF53]/10 file:px-3 file:py-2 file:text-xs file:font-semibold file:text-[#8B6F1B] hover:file:bg-[#CFAF53]/20 sm:text-sm sm:file:text-sm"/>
                        </div>

                        <div>
                            <label class="text-sm font-medium text-slate-700">Foto Kiri</label>
                            <input type="file" name="foto_kiri" accept="image/*"
                                class="mt-2 w-full text-xs text-slate-600 file:mr-3 file:rounded-lg file:border-0 file:bg-[#CFAF53]/10 file:px-3 file:py-2 file:text-xs file:font-semibold file:text-[#8B6F1B] hover:file:bg-[#CFAF53]/20 sm:text-sm sm:file:text-sm"/>
                        </div>

                        <div>
                            <label class="text-sm font-medium text-slate-700">Foto Kanan</label>
                            <input type="file" name="foto_kanan" accept="image/*"
                                class="mt-2 w-full text-xs text-slate-600 file:mr-3 file:rounded-lg file:border-0 file:bg-[#CFAF53]/10 file:px-3 file:py-2 file:text-xs file:font-semibold file:text-[#8B6F1B] hover:file:bg-[#CFAF53]/20 sm:text-sm sm:file:text-sm"/>
                        </div>
                    </div>
                </section>

                <section class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5">
                    <button class="w-full rounded-xl bg-[#CFAF53] px-4 py-3 text-sm font-semibold text-zinc-900 shadow-sm transition hover:bg-[#CFAF53]/90 focus:outline-none focus:ring-2 focus:ring-[#CFAF53]/30">
                        Kirim Pra Konsul
                    </button>
                    <a href="{{ route('pra.index') }}" class="mt-3 block text-center text-sm font-medium text-slate-600 transition hover:text-slate-900">
                        Kembali
                    </a>
                    <p class="mt-3 text-xs text-slate-500">Dengan mengirim, kamu setuju data digunakan untuk peninjauan dokter.</p>
                </section>
            </aside>
        </form>
    </div>
</x-guest-layout>
