<x-guest-layout :title="'Produk'">
    <div class="flex items-end justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold text-slate-900">Produk</h1>
            <p class="text-sm text-slate-700">Pilih produk, masukkan keranjang, lalu checkout.</p>
        </div>
    </div>

    <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        @foreach ($products as $p)
            <div class="rounded-2xl border border-slate-300 bg-white p-4 shadow-sm">
                <a href="{{ route('shop.show', $p) }}" class="block">
                    <div class="aspect-[4/3] rounded-xl bg-slate-100 border border-slate-300 overflow-hidden flex items-center justify-center">
                        @if ($p->foto_path)
                            <img src="{{ asset('storage/' . $p->foto_path) }}" alt="{{ $p->nama }}" class="h-full w-full object-cover" />
                        @else
                            <span class="text-xs text-slate-400">Gambar</span>
                        @endif
                    </div>
                    <div class="mt-3">
                        <p class="font-medium text-slate-900">{{ $p->nama }}</p>
                        <p class="text-sm text-slate-700 mt-1">Rp {{ number_format((int)$p->harga, 0, ',', '.') }}</p>
                    </div>
                </a>

                <form method="POST" action="{{ route('cart.add', $p) }}" class="mt-4 flex gap-2">
                    @csrf
                    <input type="number" name="qty" value="1" min="1"
                           class="w-20 rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900">
                    <button class="flex-1 rounded-xl bg-[#CFAF53] px-4 py-2 text-sm font-medium text-zinc-900 hover:bg-[#CFAF53]/90">
                        Tambah
                    </button>
                </form>
            </div>
        @endforeach
    </div>

    <div class="mt-6">
        {{ $products->links() }}
    </div>
</x-guest-layout>
