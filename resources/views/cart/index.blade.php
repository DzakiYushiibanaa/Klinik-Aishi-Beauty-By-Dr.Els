<x-guest-layout :title="'Keranjang Belanja'">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        {{-- Header Section --}}
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-[#1A1A1A] tracking-tight">Keranjang Belanja</h1>
            <p class="text-sm text-slate-500 mt-2">Periksa kembali item Anda sebelum melanjutkan ke pembayaran.</p>
        </div>

        @if (empty($cart))
            {{-- Empty State --}}
            <div class="rounded-3xl border border-[#E8E0D8] bg-white p-12 sm:p-16 text-center shadow-sm flex flex-col items-center justify-center">
                <div class="w-24 h-24 bg-[#FAF8F5] rounded-full flex items-center justify-center mb-6">
                    <svg class="h-10 w-10 text-[#CFAF53]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                    </svg>
                </div>
                <h2 class="text-xl font-bold text-[#1A1A1A] mb-2">Keranjang masih kosong</h2>
                <p class="text-slate-500 mb-8 max-w-md mx-auto">Anda belum menambahkan produk atau paket apa pun ke dalam keranjang belanja.</p>
                <a class="inline-flex items-center justify-center px-8 py-3.5 text-sm font-bold text-[#1A1A1A] bg-[#FAF8F5] border border-[#E8E0D8] rounded-xl hover:bg-white hover:border-[#CFAF53] transition-all shadow-sm" href="{{ route('shop.index') }}">
                    Mulai Belanja Sekarang
                </a>
            </div>
        @else
            <form method="POST" action="{{ route('cart.update') }}" class="grid gap-8 lg:grid-cols-12 items-start">
                @csrf

                {{-- LIST ITEM --}}
                <div class="lg:col-span-8 space-y-4">
                    @foreach ($cart as $key => $item)
                        <div class="flex items-start gap-3 sm:gap-5 rounded-2xl border border-[#E8E0D8] bg-white p-3 sm:p-5 shadow-sm hover:shadow-md hover:border-[#CFAF53]/40 transition-all group">
                            
                            {{-- Foto --}}
                            <div class="h-20 w-20 sm:h-24 sm:w-24 shrink-0 overflow-hidden rounded-xl bg-gradient-to-br from-[#FAF8F5] to-[#F2EFE9] border border-[#E8E0D8] flex items-center justify-center relative">
                                @if(isset($item['type']) && $item['type'] === 'bundle')
                                    <div class="absolute top-0 left-0 w-full h-1.5 bg-[#CFAF53] z-10"></div>
                                @endif
                                @if(!empty($item['foto_path']))
                                    <img src="{{ asset('storage/' . $item['foto_path']) }}"
                                         alt="{{ $item['nama'] ?? 'Item' }}"
                                         loading="lazy"
                                         class="h-full w-full object-contain p-2 transition-transform duration-500 group-hover:scale-105">
                                @else
                                    <svg class="h-8 w-8 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                @endif
                            </div>

                            {{-- Info --}}
                            <div class="flex-1 min-w-0 w-full">
                                <div class="flex flex-col sm:flex-row sm:justify-between sm:items-start gap-2 mb-2">
                                    <div class="flex-1 pr-4">
                                        <div class="flex items-center gap-2 mb-1.5">
                                            @if(isset($item['type']) && $item['type'] === 'bundle')
                                                <span class="shrink-0 rounded-md bg-amber-50 px-2 py-0.5 text-[10px] font-bold tracking-wider text-amber-700 uppercase border border-amber-200">
                                                    Paket Bundle
                                                </span>
                                            @else
                                                <span class="shrink-0 rounded-md bg-slate-50 px-2 py-0.5 text-[10px] font-bold tracking-wider text-slate-500 uppercase border border-slate-200">
                                                    Produk
                                                </span>
                                            @endif
                                        </div>
                                        <a href="{{ isset($item['type']) && $item['type'] === 'bundle' ? route('bundle.show', $item['id']) : route('shop.show', $item['id']) }}" class="font-bold text-lg text-[#1A1A1A] hover:text-[#CFAF53] transition-colors line-clamp-2">
                                            {{ $item['nama'] }}
                                        </a>
                                        <p class="text-sm font-bold text-[#CFAF53] mt-1">
                                            Rp {{ number_format((int)$item['harga'], 0, ',', '.') }}
                                        </p>
                                    </div>
                                    
                                    {{-- Subtotal per item (Desktop) --}}
                                    <div class="hidden sm:block text-right shrink-0">
                                        <p class="text-[10px] text-slate-400 font-bold uppercase tracking-wider mb-1">Subtotal Item</p>
                                        <p class="text-base font-bold text-[#1A1A1A]">
                                            Rp {{ number_format((int)$item['harga'] * (int)$item['qty'], 0, ',', '.') }}
                                        </p>
                                    </div>
                                </div>

                                <div class="flex flex-wrap items-center justify-between mt-4 border-t border-[#E8E0D8]/50 pt-4 gap-4">
                                    {{-- Aksi (Qty) --}}
                                    <div class="flex items-center gap-3">
                                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider hidden sm:inline-block">Kuantitas</span>
                                        <div class="flex items-center bg-[#FAF8F5] border border-[#E8E0D8] rounded-lg overflow-hidden h-10 shadow-sm">
                                            <button type="button" onclick="this.parentNode.querySelector('input[type=number]').stepDown(); this.form.submit()" class="px-3 h-full text-slate-500 hover:text-[#1A1A1A] hover:bg-slate-100 transition-colors flex items-center justify-center">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20 12H4" /></svg>
                                            </button>
                                            <input type="number"
                                                name="items[{{ $key }}]"
                                                value="{{ $item['qty'] }}"
                                                min="1"
                                                onchange="this.form.submit()" 
                                                class="w-12 h-full border-x border-[#E8E0D8] border-y-0 bg-white px-1 text-center text-sm font-bold text-[#1A1A1A] focus:ring-[#CFAF53] p-0 [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none" 
                                            />
                                            <button type="button" onclick="this.parentNode.querySelector('input[type=number]').stepUp(); this.form.submit()" class="px-3 h-full text-slate-500 hover:text-[#1A1A1A] hover:bg-slate-100 transition-colors flex items-center justify-center">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" /></svg>
                                            </button>
                                        </div>
                                    </div>

                                    {{-- Tombol Hapus --}}
                                    <button type="submit"
                                        formaction="{{ route('cart.remove', $key) }}"
                                        formmethod="POST"
                                        class="flex items-center gap-1.5 rounded-lg px-3 py-2 text-xs font-bold text-slate-400 hover:bg-red-50 hover:text-red-600 transition-colors"
                                        title="Hapus Item">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                        <span class="hidden sm:inline-block">Hapus</span>
                                    </button>
                                </div>
                                
                                {{-- Subtotal per item (Mobile Only) --}}
                                <div class="sm:hidden mt-4 bg-slate-50 rounded-lg p-3 border border-slate-100 flex items-center justify-between">
                                    <p class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">Subtotal Item</p>
                                    <p class="text-sm font-bold text-[#1A1A1A]">
                                        Rp {{ number_format((int)$item['harga'] * (int)$item['qty'], 0, ',', '.') }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- RINGKASAN BELANJA --}}
                <div class="lg:col-span-4 sticky top-24">
                    <div class="rounded-3xl border border-[#E8E0D8] bg-white p-6 shadow-sm">
                        <h3 class="text-lg font-bold text-[#1A1A1A] mb-5 flex items-center gap-2">
                            Ringkasan Belanja
                        </h3>
                        
                        <div class="space-y-3 mb-6">
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-slate-500 font-medium">Total Item</span>
                                <span class="font-bold text-[#1A1A1A]">{{ array_reduce($cart, function($carry, $item) { return $carry + $item['qty']; }, 0) }} Item</span>
                            </div>
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-slate-500 font-medium">Subtotal</span>
                                <span class="font-bold text-[#1A1A1A]">Rp {{ number_format((int)$subtotal, 0, ',', '.') }}</span>
                            </div>
                        </div>

                        <div class="flex flex-col border-t border-[#E8E0D8]/60 pt-5 mb-6">
                            <div class="flex items-center justify-between mb-1">
                                <span class="font-bold text-[#1A1A1A]">Total Harga</span>
                                <span class="text-2xl font-bold text-[#CFAF53]">Rp {{ number_format((int)$subtotal, 0, ',', '.') }}</span>
                            </div>
                            <p class="text-[10px] font-medium text-slate-400 text-right uppercase tracking-wider">Belum Termasuk Ongkir</p>
                        </div>

                        <a href="{{ route('checkout.index') }}"
                           class="group flex w-full items-center justify-center gap-2 rounded-xl bg-[#1A1A1A] px-4 py-4 text-sm font-bold text-white transition-all hover:bg-[#333] hover:shadow-lg hover:shadow-[#1A1A1A]/20">
                            Lanjut ke Pembayaran
                            <svg class="h-4 w-4 transition-transform group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" /></svg>
                        </a>
                        
                        <div class="mt-5 flex items-center justify-center gap-2 text-[10px] font-bold uppercase tracking-wider text-slate-400">
                            <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                            Transaksi Aman & Terenkripsi
                        </div>
                    </div>
                </div>
            </form>
        @endif
    </div>
</x-guest-layout>