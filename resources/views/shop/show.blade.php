<x-guest-layout :title="$product->nama">
    <div class="grid gap-6 lg:grid-cols-2">
        <div class="rounded-2xl border border-slate-300 bg-white p-4 shadow-sm">
            <div class="aspect-[4/3] rounded-xl bg-slate-100 border border-slate-300 flex items-center justify-center overflow-hidden">
                @if ($product->foto_path)
                    <img src="{{ asset('storage/' . $product->foto_path) }}" alt="{{ $product->nama }}" class="h-full w-full object-cover" />
                @else
                    <span class="text-xs text-slate-400">Gambar</span>
                @endif
            </div>
        </div>

        <div class="rounded-2xl border border-slate-300 bg-white p-6 shadow-sm">
            <h1 class="text-2xl font-semibold text-slate-900">{{ $product->nama }}</h1>
            <p class="mt-2 text-slate-700">{{ $product->deskripsi }}</p>
            <p class="mt-4 text-xl font-semibold text-[#CFAF53]">
                Rp {{ number_format((int)$product->harga, 0, ',', '.') }}
            </p>

            <form method="POST" action="{{ route('cart.add', $product) }}" class="mt-6 flex gap-2">
                @csrf
                <input type="number" name="qty" value="1" min="1"
                       class="w-24 rounded-xl border border-slate-200 bg-white px-3 py-2 text-slate-900">
                <button class="flex-1 rounded-xl bg-[#CFAF53] px-5 py-3 font-medium text-zinc-900 hover:bg-[#CFAF53]/90">
                    Tambah ke Keranjang
                </button>
            </form>

            <div class="mt-4">
                <a href="{{ route('cart.index') }}" class="text-sm text-slate-600 hover:text-[#CFAF53]">Lihat keranjang -></a>
            </div>
        </div>
    </div>
</x-guest-layout>
