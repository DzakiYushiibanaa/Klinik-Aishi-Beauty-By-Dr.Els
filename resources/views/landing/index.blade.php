<x-guest-layout :title="'Landing'">
    <section class="rounded-2xl border border-slate-300 bg-white p-6 sm:p-10">
        <div class="grid gap-8 lg:grid-cols-2 lg:items-center">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-[#CFAF53]">Klinik Aishi Beauty By Dr.Els</p>
                <h1 class="mt-3 text-3xl sm:text-4xl font-semibold tracking-tight text-slate-900">
                    Klinik kecantikan modern, booking dan belanja jadi lebih mudah.
                </h1>
                <p class="mt-3 text-slate-700 leading-relaxed">
                    Jelajahi produk pilihan, simpan ke keranjang, lalu checkout dengan cepat.
                    Pembayaran online menyusul setelah tampilan dan alur checkout rapi.
                </p>

                <div class="mt-6 flex flex-col sm:flex-row gap-3">
                    <a href="{{ route('shop.index') }}"
                       class="inline-flex items-center justify-center rounded-xl bg-[#CFAF53] text-zinc-900 px-5 py-3 font-medium hover:bg-[#CFAF53]/90">
                        Mulai Belanja
                    </a>
                    <a href="/login"
                       class="inline-flex items-center justify-center rounded-xl border border-slate-300 bg-white px-5 py-3 font-medium text-slate-800 hover:border-slate-400 hover:text-slate-900">
                        Login Pasien
                    </a>
                </div>

                <div class="mt-8 grid grid-cols-2 gap-3 text-sm text-slate-700">
                    <div class="rounded-xl border border-slate-300 bg-white p-4 shadow-sm">
                        <p class="font-medium text-slate-900">Clean dan Responsif</p>
                        <p class="mt-1 text-slate-600">Nyaman di HP dan desktop.</p>
                    </div>
                    <div class="rounded-xl border border-slate-300 bg-white p-4 shadow-sm">
                        <p class="font-medium text-slate-900">Checkout Cepat</p>
                        <p class="mt-1 text-slate-600">Keranjang ke order.</p>
                    </div>
                </div>
            </div>

            <div class="rounded-2xl border border-slate-300 bg-[#FFF3C4] p-6">
                <div class="grid gap-4">
                    <div class="rounded-xl border border-[#CFAF53]/30 bg-white p-4 shadow-sm">
                        <p class="text-sm text-[#7A6114]">Step 1</p>
                        <p class="font-medium text-slate-900">Login / daftar sebagai pasien</p>
                    </div>
                    <div class="rounded-xl border border-[#CFAF53]/30 bg-white p-4 shadow-sm">
                        <p class="text-sm text-[#7A6114]">Step 2</p>
                        <p class="font-medium text-slate-900">Pilih produk dan masukkan keranjang</p>
                    </div>
                    <div class="rounded-xl border border-[#CFAF53]/30 bg-white p-4 shadow-sm">
                        <p class="text-sm text-[#7A6114]">Step 3</p>
                        <p class="font-medium text-slate-900">Checkout, order menunggu pembayaran</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="mt-10">
        <div class="flex items-end justify-between gap-4">
            <div>
                <h2 class="text-xl font-semibold text-slate-900">Produk Pilihan</h2>
                <p class="text-sm text-slate-700">Beberapa produk terbaru yang tersedia.</p>
            </div>
            <a href="{{ route('shop.index') }}" class="text-sm text-[#CFAF53] hover:text-[#CFAF53]/80">Lihat semua</a>
        </div>

        <div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @forelse ($featuredProducts as $p)
                <a href="{{ route('shop.show', $p) }}" class="group rounded-2xl border border-slate-300 bg-white p-4 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                    <div class="aspect-[4/3] rounded-xl bg-slate-100 border border-slate-300 overflow-hidden flex items-center justify-center">
                        @if ($p->foto_path)
                            <img src="{{ asset('storage/' . $p->foto_path) }}" alt="{{ $p->nama }}" class="h-full w-full object-cover" />
                        @else
                            <span class="text-xs text-slate-500">Gambar</span>
                        @endif
                    </div>
                    <div class="mt-3">
                        <p class="font-medium text-slate-900">{{ $p->nama }}</p>
                        <p class="text-sm text-slate-700 mt-1">Rp {{ number_format((int)$p->harga, 0, ',', '.') }}</p>
                    </div>
                </a>
            @empty
                <div class="rounded-2xl border border-slate-300 bg-white p-6 text-slate-700">
                    Belum ada produk aktif.
                </div>
            @endforelse
        </div>
    </section>
</x-guest-layout>
