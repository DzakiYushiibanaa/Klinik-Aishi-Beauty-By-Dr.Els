<x-guest-layout :title="$product->nama">
    <div class="bg-white min-h-screen">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 sm:py-16">
            
            {{-- BREADCRUMB --}}
            <nav class="mb-8 flex items-center text-sm text-slate-500 font-medium">
                <a href="{{ route('landing') }}" class="hover:text-[#CFAF53] transition-colors">Home</a>
                <svg class="h-4 w-4 mx-2 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                <a href="{{ route('shop.index') }}" class="hover:text-[#CFAF53] transition-colors">Katalog Produk</a>
                <svg class="h-4 w-4 mx-2 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                <span class="text-slate-900">{{ $product->nama }}</span>
            </nav>

            {{-- MAIN CONTENT --}}
            <div class="lg:grid lg:grid-cols-2 lg:gap-x-12 xl:gap-x-16">
                
                {{-- KOLOM KIRI: FOTO PRODUK --}}
                <div class="mb-10 lg:mb-0">
                    <div class="relative w-full aspect-[4/5] sm:aspect-square md:aspect-[4/3] lg:aspect-square bg-gradient-to-br from-[#FAF8F5] to-[#F2EFE9] rounded-3xl overflow-hidden group border border-slate-100 shadow-sm">
                        @if ($product->foto_path)
                            <img src="{{ asset('storage/' . $product->foto_path) }}" 
                                 alt="{{ $product->nama }}" 
                                 class="absolute inset-0 h-full w-full object-contain p-10 transition-transform duration-700 ease-in-out group-hover:scale-105" />
                        @else
                            {{-- Placeholder jika tidak ada gambar --}}
                            <div class="absolute inset-0 flex flex-col items-center justify-center bg-[#F5F5F3] text-slate-300">
                                <svg class="h-20 w-20 mb-4 text-slate-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                <span class="text-sm font-medium text-slate-400">Belum ada foto produk</span>
                            </div>
                        @endif
                        
                        {{-- Overlay Gradient (Subtle) --}}
                        <div class="absolute inset-0 bg-gradient-to-t from-black/10 to-transparent opacity-0 transition-opacity duration-500 group-hover:opacity-100 pointer-events-none"></div>
                    </div>
                </div>

                {{-- KOLOM KANAN: DETAIL INFO --}}
                <div class="px-2 sm:px-0 flex flex-col pt-2">
                    
                    {{-- Header: Kategori & Status Stok --}}
                    <div class="flex items-center justify-between mb-4">
                        <span class="text-xs font-bold tracking-widest text-[#CFAF53] uppercase">
                            {{ $product->category->nama ?? 'Produk' }}
                        </span>
                        
                        @if($product->stok > 0)
                            <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-2.5 py-1 text-[11px] font-bold uppercase tracking-wider text-emerald-600 ring-1 ring-inset ring-emerald-500/20">
                                <span class="h-1.5 w-1.5 rounded-full bg-emerald-500 animate-pulse"></span> Tersedia
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1.5 rounded-full bg-rose-50 px-2.5 py-1 text-[11px] font-bold uppercase tracking-wider text-rose-600 ring-1 ring-inset ring-rose-500/20">
                                <span class="h-1.5 w-1.5 rounded-full bg-rose-500"></span> Habis
                            </span>
                        @endif
                    </div>

                    {{-- Judul & Harga --}}
                    <div class="mb-6">
                        <h1 class="text-3xl sm:text-4xl font-extrabold text-slate-900 tracking-tight leading-tight mb-4">
                            {{ $product->nama }}
                        </h1>
                        <p class="text-3xl font-bold text-[#CFAF53]">
                            Rp {{ number_format((int)$product->harga, 0, ',', '.') }}
                        </p>
                    </div>

                    {{-- Spesifikasi Ringkas (Optional, jika ada data tambahan di masa depan, bisa ditaruh di sini) --}}
                    @if($product->berat || $product->tanggal_kadaluarsa)
                        <div class="mb-8 flex flex-wrap gap-4 border-y border-slate-100 py-4">
                            @if($product->berat)
                            <div class="flex items-center gap-2 text-sm text-slate-600">
                                <svg class="h-5 w-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3" /></svg>
                                <span><span class="font-medium text-slate-900">{{ $product->berat }}</span> gram</span>
                            </div>
                            @endif
                            @if($product->tanggal_kadaluarsa)
                            <div class="flex items-center gap-2 text-sm text-slate-600">
                                <svg class="h-5 w-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                <span>Exp: <span class="font-medium text-slate-900">{{ \Carbon\Carbon::parse($product->tanggal_kadaluarsa)->format('d MMM Y') }}</span></span>
                            </div>
                            @endif
                        </div>
                    @endif

                    {{-- Deskripsi Produk --}}
                    <div class="mb-10 flex-1">
                        <h3 class="text-xs font-bold tracking-widest text-slate-400 uppercase mb-4">Informasi Produk</h3>
                        <div class="prose prose-slate prose-sm sm:prose-base text-slate-600 leading-relaxed text-justify">
                            {!! nl2br(e($product->deskripsi ?? 'Belum ada deskripsi untuk produk ini.')) !!}
                        </div>
                    </div>

                    {{-- Action Area (Add to Cart) --}}
                    <div class="mt-auto border-t border-slate-100 pt-8">
                        @if($product->stok > 0)
                            <form method="POST"
                                  action="{{ route('cart.add', $product) }}"
                                  data-cart-form
                                  data-cart-name="{{ $product->nama }}"
                                  class="flex flex-col sm:flex-row items-center gap-4 justify-center"
                                  x-data="{ qty: 1 }">
                                @csrf
                                
                                {{-- Input Qty Custom --}}
                                <div class="flex items-center justify-between rounded-xl border border-slate-200 bg-white p-1 shadow-sm w-full sm:w-36 h-14">
                                    <button type="button" @click="qty > 1 ? qty-- : null" class="flex h-full w-10 items-center justify-center rounded-lg text-slate-400 hover:bg-slate-50 hover:text-slate-900 transition focus:outline-none">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" /></svg>
                                    </button>
                                    
                                    <input type="number" name="qty" id="qty" x-model="qty" min="1" max="{{ $product->stok }}" readonly
                                        class="h-full w-12 border-none bg-transparent text-center font-bold text-slate-900 p-0 focus:ring-0">
                                    
                                    <button type="button" @click="qty < {{ $product->stok }} ? qty++ : null" class="flex h-full w-10 items-center justify-center rounded-lg text-slate-400 hover:bg-slate-50 hover:text-slate-900 transition focus:outline-none">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                                    </button>
                                </div>

                                {{-- Tombol Submit --}}
                                <button type="submit" 
                                    class="w-full sm:flex-1 flex h-14 items-center justify-center gap-2 rounded-xl bg-slate-900 px-8 font-bold text-white transition-all duration-300 hover:bg-[#CFAF53] hover:shadow-lg focus:outline-none focus:ring-4 focus:ring-[#CFAF53]/30">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                                    </svg>
                                    <span>Tambah ke Keranjang</span>
                                </button>
                            </form>
                            <p class="mt-3 text-center w-full text-xs text-slate-400">
                                Sisa stok: <span class="font-semibold text-slate-600">{{ $product->stok }} item</span>
                            </p>
                        @else
                            {{-- State Stok Habis --}}
                            <div class="flex h-14 items-center justify-center rounded-xl bg-slate-100 px-8 font-bold text-slate-400 cursor-not-allowed border border-slate-200 w-full">
                                Stok Habis
                            </div>
                        @endif
                    </div>

                    {{-- Action Tambahan (Back to Catalog dll) --}}
                    <div class="mt-8 flex justify-center items-center gap-6">
                        <a href="{{ route('shop.index') }}" class="group flex items-center text-sm font-medium text-slate-500 hover:text-slate-900 transition-colors">
                            <svg class="mr-2 h-4 w-4 transition-transform group-hover:-translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                            </svg>
                            Kembali ke Katalog
                        </a>
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-guest-layout>