<x-guest-layout :title="$bundle->nama">
    <div class="bg-white min-h-screen">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 sm:py-16">
            
            {{-- BREADCRUMB --}}
            <nav class="mb-8 flex items-center text-sm text-slate-500 font-medium">
                <a href="{{ route('landing') }}" class="hover:text-[#CFAF53] transition-colors">Home</a>
                <svg class="h-4 w-4 mx-2 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                <a href="{{ route('bundle.index') }}" class="hover:text-[#CFAF53] transition-colors">Daftar Paket</a>
                <svg class="h-4 w-4 mx-2 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                <span class="text-slate-900">{{ $bundle->nama }}</span>
            </nav>

            {{-- MAIN CONTENT --}}
            <div class="lg:grid lg:grid-cols-2 lg:gap-x-12 xl:gap-x-16">
                
                {{-- KOLOM KIRI: FOTO BUNDLE --}}
                <div class="mb-10 lg:mb-0">
                    <div class="relative w-full aspect-[4/5] sm:aspect-square md:aspect-[4/3] lg:aspect-square bg-[#F5F5F3] rounded-3xl overflow-hidden group border border-slate-100 shadow-sm flex items-center justify-center p-8">
                        @if ($bundle->foto_path)
                            <img src="{{ asset('storage/' . $bundle->foto_path) }}" 
                                 alt="{{ $bundle->nama }}" 
                                 class="h-full w-full rounded-2xl object-cover transition-transform duration-700 ease-in-out group-hover:scale-105 shadow-sm ring-1 ring-slate-100" />
                        @else
                            {{-- Placeholder jika tidak ada gambar --}}
                            <div class="flex flex-col items-center justify-center text-slate-300">
                                <svg class="h-20 w-20 mb-4 text-slate-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                <span class="text-sm font-medium text-slate-400">Belum ada foto paket</span>
                            </div>
                        @endif
                        
                        {{-- Badge Tipe --}}
                        @php
                            $tipeLabel = match($bundle->jenis) {
                                'product' => 'Paket Produk',
                                'treatment' => 'Paket Treatment',
                                'mixed' => 'Mix (Produk + Treatment)',
                                default => 'Paket'
                            };
                            $tipeColor = match($bundle->jenis) {
                                'product' => 'bg-[#CFAF53] text-slate-900',
                                'treatment' => 'bg-rose-500 text-white',
                                'mixed' => 'bg-purple-600 text-white',
                                default => 'bg-slate-800 text-white'
                            };
                        @endphp
                        <span class="absolute left-6 top-6 rounded-full {{ $tipeColor }} px-4 py-1.5 text-xs font-bold uppercase tracking-wide shadow-lg">
                            {{ $tipeLabel }}
                        </span>
                        
                        {{-- Overlay Gradient (Subtle) --}}
                        <div class="absolute inset-0 bg-gradient-to-t from-black/5 to-transparent opacity-0 transition-opacity duration-500 group-hover:opacity-100 pointer-events-none rounded-3xl"></div>
                    </div>
                </div>

                {{-- KOLOM KANAN: DETAIL INFO --}}
                <div class="px-2 sm:px-0 flex flex-col pt-2">
                    
                    {{-- Header: Kode Paket --}}
                    <div class="flex items-center justify-between mb-4">
                        <span class="text-xs font-bold tracking-widest text-[#CFAF53] uppercase flex items-center gap-1.5">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" /></svg>
                            KODE: {{ $bundle->kode }}
                        </span>
                    </div>

                    {{-- Judul & Harga --}}
                    <div class="mb-8">
                        <h1 class="text-3xl sm:text-4xl font-extrabold text-slate-900 tracking-tight leading-tight mb-4">
                            {{ $bundle->nama }}
                        </h1>
                        
                        <div class="flex flex-wrap items-end gap-3">
                            <p class="text-3xl font-bold text-[#CFAF53]">
                                Rp {{ number_format($bundle->harga_promo > 0 ? $bundle->harga_promo : $bundle->harga_normal, 0, ',', '.') }}
                            </p>
                            @if($bundle->harga_normal > $bundle->harga_promo && $bundle->harga_promo > 0)
                                <span class="text-lg text-slate-400 line-through font-medium mb-1 border-l border-slate-200 pl-3 ml-1">
                                    Rp {{ number_format($bundle->harga_normal, 0, ',', '.') }}
                                </span>
                                @php
                                    $hemat = $bundle->harga_normal - $bundle->harga_promo;
                                    $persen = round(($hemat / $bundle->harga_normal) * 100);
                                @endphp
                                <span class="mb-1.5 rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-bold text-red-600 ring-1 ring-inset ring-red-500/20">
                                    Hemat {{ $persen }}%
                                </span>
                            @endif
                        </div>
                    </div>

                    {{-- Deskripsi Paket --}}
                    <div class="mb-10 border-y border-slate-100 py-6">
                        <h3 class="text-xs font-bold tracking-widest text-slate-400 uppercase mb-4">Deskripsi Paket</h3>
                        <div class="prose prose-slate prose-sm sm:prose-base text-slate-600 leading-relaxed text-justify">
                            {!! nl2br(e($bundle->deskripsi ?? 'Belum ada deskripsi untuk paket ini.')) !!}
                        </div>
                    </div>

                    {{-- ISI PAKET (ITEMS) --}}
                    <div class="mb-10">
                        <h3 class="flex items-center gap-2 text-xs font-bold tracking-widest text-[#CFAF53] uppercase mb-5">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg>
                            Apa yang kamu dapatkan?
                        </h3>
                        
                        <ul class="space-y-3">
                            {{-- List Produk --}}
                            @foreach($bundle->productItems as $item)
                                <li class="flex items-center gap-4 bg-[#F8F9FA] p-3.5 rounded-xl border border-slate-100 transition-colors hover:bg-white hover:border-slate-200">
                                    <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-50 text-xs font-bold text-emerald-600 ring-1 ring-inset ring-emerald-500/20">
                                        {{ $item->qty }}x
                                    </span>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-semibold text-slate-800 truncate">{{ $item->product->nama ?? 'Produk Dihapus' }}</p>
                                        @if($item->product)
                                            <p class="text-xs text-slate-500 truncate mt-0.5">Stok tersedia: {{ $item->product->stok }} item</p>
                                        @endif
                                    </div>
                                </li>
                            @endforeach

                            {{-- List Treatment --}}
                            @foreach($bundle->treatmentItems as $item)
                                <li class="flex items-center gap-4 bg-[#F8F9FA] p-3.5 rounded-xl border border-slate-100 transition-colors hover:bg-white hover:border-slate-200">
                                    <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-rose-50 text-xs font-bold text-rose-600 ring-1 ring-inset ring-rose-500/20">
                                        {{ $item->qty }}x
                                    </span>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-semibold text-slate-800 truncate">{{ $item->treatment->nama ?? 'Treatment Dihapus' }}</p>
                                        <p class="text-xs text-slate-500 truncate mt-0.5">Layanan Treatment Pilihan</p>
                                    </div>
                                </li>
                            @endforeach

                            {{-- Pesan jika bundle kosong --}}
                            @if($bundle->productItems->isEmpty() && $bundle->treatmentItems->isEmpty())
                                <li class="flex items-center justify-center gap-2 text-sm text-slate-400 italic bg-slate-50 p-6 rounded-xl border border-dashed border-slate-200">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                    Paket ini belum memiliki item di dalamnya.
                                </li>
                            @endif
                        </ul>
                    </div>

                    {{-- Action Area (Add to Cart / Booking) --}}
                    <div class="mt-auto border-t border-slate-100 pt-8">
                        @if($bundle->jenis === 'treatment')
                            {{-- Tombol Booking jika hanya treatment --}}
                            <div class="flex flex-col sm:flex-row gap-4">
                                <a href="{{ route('appointment.create', ['bundle_id' => $bundle->bundle_id]) }}" 
                                   class="mx-auto w-full max-w-[300px] flex h-14 items-center justify-center gap-2 rounded-xl bg-slate-900 px-8 font-bold text-white transition-all duration-300 hover:bg-[#CFAF53] hover:shadow-lg focus:outline-none focus:ring-4 focus:ring-[#CFAF53]/30">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                    <span>Booking Paket Ini Sekarang</span>
                                </a>
                            </div>
                        @else
                            {{-- Add to Cart Form jika ada produk --}}
                            <form action="{{ route('cart.add_bundle', $bundle) }}" method="POST" class="flex flex-col sm:flex-row items-center gap-4 justify-center" x-data="{ qty: 1 }">
                                @csrf
                                
                                {{-- Input Qty Custom --}}
                                <div class="flex items-center justify-between rounded-xl border border-slate-200 bg-white p-1 shadow-sm w-full sm:w-36 h-14">
                                    <button type="button" @click="qty > 1 ? qty-- : null" class="flex h-full w-10 items-center justify-center rounded-lg text-slate-400 hover:bg-slate-50 hover:text-slate-900 transition focus:outline-none">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" /></svg>
                                    </button>
                                    
                                    <input type="number" name="qty" id="qty" x-model="qty" min="1" max="10" readonly
                                        class="h-full w-12 border-none bg-transparent text-center font-bold text-slate-900 p-0 focus:ring-0">
                                    
                                    <button type="button" @click="qty < 10 ? qty++ : null" class="flex h-full w-10 items-center justify-center rounded-lg text-slate-400 hover:bg-slate-50 hover:text-slate-900 transition focus:outline-none">
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
                        @endif
                    </div>

                    {{-- Action Tambahan (Back to Catalog) --}}
                    <div class="mt-8 flex justify-center items-center gap-6">
                        <a href="{{ route('bundle.index') }}" class="group flex items-center text-sm font-medium text-slate-500 hover:text-slate-900 transition-colors">
                            <svg class="mr-2 h-4 w-4 transition-transform group-hover:-translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                            </svg>
                            Kembali ke Daftar Paket
                        </a>
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-guest-layout>