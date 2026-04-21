<x-guest-layout :title="'Keranjang'">
    <h1 class="text-2xl font-semibold text-slate-900">Keranjang Belanja</h1>
    <p class="text-sm text-slate-700 mt-1">Periksa kembali item sebelum checkout.</p>

    @if (empty($cart))
        <div class="mt-6 rounded-2xl border border-slate-300 bg-white p-12 text-center text-slate-500">
            <svg class="mx-auto h-12 w-12 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
            </svg>
            <p class="mt-4">Keranjang masih kosong.</p>
            <a class="mt-4 inline-block font-medium text-[#CFAF53] hover:underline" href="{{ route('shop.index') }}">
                Mulai Belanja
            </a>
        </div>
    @else
        <form method="POST" action="{{ route('cart.update') }}" class="mt-6 grid gap-8 lg:grid-cols-3">
            @csrf

            {{-- LIST ITEM --}}
            <div class="lg:col-span-2 space-y-4">
                @foreach ($cart as $key => $item)
                    <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm transition hover:shadow-md">
                        
                        {{-- Foto --}}
                        <div class="h-20 w-20 shrink-0 overflow-hidden rounded-xl bg-slate-100 border border-slate-100">
                            <img src="{{ $item['foto_path'] ? asset('storage/' . $item['foto_path']) : 'https://via.placeholder.com/150' }}" 
                                 class="h-full w-full object-cover">
                        </div>

                        {{-- Info --}}
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 mb-1">
                                {{-- LOGIC LINK: Cek apakah Bundle atau Produk --}}
                                @if(isset($item['type']) && $item['type'] === 'bundle')
                                    <a href="{{ route('bundle.show', $item['id']) }}" class="font-bold text-slate-900 hover:text-[#CFAF53] truncate block">
                                        {{ $item['nama'] }}
                                    </a>
                                    <span class="shrink-0 rounded bg-purple-100 px-2 py-0.5 text-[10px] font-bold tracking-wide text-purple-700 uppercase">
                                        Paket
                                    </span>
                                @else
                                    <a href="{{ route('shop.show', $item['id']) }}" class="font-bold text-slate-900 hover:text-[#CFAF53] truncate block">
                                        {{ $item['nama'] }}
                                    </a>
                                @endif
                            </div>

                            <p class="text-sm font-semibold text-[#CFAF53]">
                                Rp {{ number_format((int)$item['harga'], 0, ',', '.') }}
                            </p>
                        </div>

                        {{-- Aksi (Qty & Hapus) --}}
                        <div class="flex items-center gap-3 mt-2 sm:mt-0">
                            
                            {{-- 🔥 INPUT QTY OTOMATIS UPDATE 🔥 --}}
                            {{-- Saya tambahkan onchange="this.form.submit()" --}}
                            <input type="number"
                                name="items[{{ $key }}]"
                                value="{{ $item['qty'] }}"
                                min="1"
                                onchange="this.form.submit()" 
                                class="w-16 rounded-lg border border-slate-300 bg-slate-50 px-2 py-2 text-center text-sm font-semibold text-slate-900 focus:border-[#CFAF53] focus:ring-[#CFAF53]" 
                            />

                            {{-- Tombol Hapus --}}
                            <button type="submit"
                                formaction="{{ route('cart.remove', $key) }}"
                                formmethod="POST"
                                class="rounded-lg p-2 text-slate-400 hover:bg-red-50 hover:text-red-500 transition"
                                title="Hapus Item">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                        </div>
                    </div>
                @endforeach

                {{-- TOMBOL UPDATE MANUAL DIHAPUS (SUDAH OTOMATIS) --}}
            </div>

            {{-- RINGKASAN BELANJA --}}
            <div class="h-fit space-y-4">
                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <h3 class="font-bold text-slate-900">Ringkasan Belanja</h3>
                    
                    <div class="mt-4 flex items-center justify-between border-t border-slate-100 pt-4">
                        <span class="text-slate-600">Total Harga</span>
                        <span class="text-lg font-bold text-slate-900">Rp {{ number_format((int)$subtotal, 0, ',', '.') }}</span>
                    </div>
                    <p class="mt-1 text-xs text-slate-400 text-right">Belum termasuk ongkir</p>

                    <a href="{{ route('checkout.index') }}"
                       class="mt-6 flex w-full items-center justify-center gap-2 rounded-xl bg-slate-900 px-4 py-3.5 text-sm font-bold text-white transition hover:bg-[#CFAF53] hover:shadow-lg">
                        Lanjut Checkout
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" /></svg>
                    </a>
                </div>
            </div>
        </form>
    @endif
</x-guest-layout>