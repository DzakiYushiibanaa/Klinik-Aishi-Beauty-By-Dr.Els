<x-guest-layout :title="'Keranjang'">
    <h1 class="text-2xl font-semibold text-slate-900">Keranjang</h1>
    <p class="text-sm text-slate-700 mt-1">Perbarui qty lalu lanjut checkout.</p>

    @if (empty($cart))
        <div class="mt-6 rounded-2xl border border-slate-300 bg-white p-6 text-slate-700">
            Keranjang masih kosong. <a class="text-[#CFAF53] hover:text-[#CFAF53]/80" href="{{ route('shop.index') }}">Belanja sekarang</a>.
        </div>
    @else
        <form method="POST" action="{{ route('cart.update') }}" class="mt-6 grid gap-4 lg:grid-cols-3">
            @csrf

            <div class="lg:col-span-2 rounded-2xl border border-slate-300 bg-white p-4 shadow-sm">
                <div class="space-y-4">
                    @foreach ($cart as $id => $item)
                        <div class="flex items-center justify-between gap-4 rounded-xl border border-slate-300 bg-slate-50 p-4">
                            <div>
                                <p class="font-medium text-slate-900">{{ $item['nama'] }}</p>
                                <p class="text-sm text-slate-700">
                                    Rp {{ number_format((int)$item['harga'], 0, ',', '.') }}
                                </p>
                            </div>

                            <div class="flex items-center gap-3">
                                <input type="number"
                                    name="items[{{ $id }}]"
                                    value="{{ $item['qty'] }}"
                                    min="0"
                                    class="w-20 rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900" />

                                {{-- tombol hapus --}}
                                <button
                                    type="submit"
                                    formaction="{{ route('cart.remove', $item['product_id']) }}"
                                    formmethod="POST"
                                    class="rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-800 hover:border-slate-400 hover:text-slate-900"
                                >
                                    Hapus
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-4 flex justify-end">
                    <button class="rounded-xl bg-slate-900 text-white px-4 py-2 text-sm font-medium hover:bg-slate-800">
                        Simpan Perubahan
                    </button>
                </div>
            </div>


            <div class="rounded-2xl border border-slate-300 bg-white p-4 h-fit shadow-sm">
                <p class="text-sm text-slate-700">Ringkasan</p>
                <div class="mt-2 flex items-center justify-between">
                    <span class="text-slate-700">Subtotal</span>
                    <span class="font-semibold text-slate-900">Rp {{ number_format((int)$subtotal, 0, ',', '.') }}</span>
                </div>

                <a href="{{ route('checkout.index') }}"
                   class="mt-4 block text-center rounded-xl bg-[#CFAF53] px-4 py-3 font-medium text-zinc-900 hover:bg-[#CFAF53]/90">
                    Lanjut Checkout
                </a>
            </div>
        </form>
    @endif
</x-guest-layout>
