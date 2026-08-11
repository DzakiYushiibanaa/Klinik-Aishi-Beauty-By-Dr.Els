{{-- Partial: Product Card --}}
                        @php
                            $inStock = (int) $p->stok > 0;
                            $isLow   = $inStock && (int) $p->stok <= 5;
                        @endphp
                        <article class="group/card relative flex flex-col rounded-2xl bg-white border border-[#E8E0D8]/60 overflow-hidden transition-all duration-300 hover:-translate-y-1 hover:shadow-xl hover:shadow-[#CFAF53]/10 hover:border-[#CFAF53]/50">

                            {{-- Image Area --}}
                            <div class="relative aspect-square overflow-hidden bg-gradient-to-br from-[#FAF8F5] to-[#F2EFE9]">
                                {{-- Link ke detail (overlay seluruh image) --}}
                                <a href="{{ route('shop.show', $p) }}" class="absolute inset-0 z-0 block" aria-label="{{ $p->nama }}">
                                    @if($p->foto_path)
                                        <img src="{{ asset('storage/' . $p->foto_path) }}"
                                             alt="{{ $p->nama }}"
                                             loading="lazy"
                                             class="h-full w-full object-contain p-6 sm:p-8 transition duration-500 group-hover/card:scale-105">
                                    @else
                                        <div class="flex h-full w-full items-center justify-center">
                                            <svg class="h-16 w-16 text-slate-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                            </svg>
                                        </div>
                                    @endif
                                </a>

                                {{-- Stok ribbon (di atas link image) --}}
                                @if(!$inStock)
                                    <span class="absolute left-0 top-3 z-10 rounded-r-full bg-rose-500 pl-3 pr-4 py-1 text-[10px] font-extrabold tracking-widest text-white uppercase shadow-md pointer-events-none">
                                        Habis
                                    </span>
                                @elseif($isLow)
                                    <span class="absolute left-0 top-3 z-10 rounded-r-full bg-amber-500 pl-3 pr-4 py-1 text-[10px] font-extrabold tracking-widest text-white uppercase shadow-md pointer-events-none">
                                        Sisa {{ $p->stok }}
                                    </span>
                                @endif

                                {{-- Hover CTA (sibling, bukan nested di link) â€” tablet+ saja --}}
                                @if($inStock)
                                    <div class="hidden sm:block absolute inset-x-3 bottom-3 z-20 opacity-0 translate-y-3 group-hover/card:opacity-100 group-hover/card:translate-y-0 transition-all duration-300">
                                        <form method="POST"
                                              action="{{ route('cart.add', $p) }}"
                                              data-cart-form
                                              data-cart-name="{{ $p->nama }}">
                                            @csrf
                                            <input type="hidden" name="qty" value="1">
                                            <button type="submit"
                                                    class="w-full inline-flex items-center justify-center gap-1.5 rounded-full bg-[#1A1A1A] hover:bg-[#CFAF53] text-white text-[10px] sm:text-[11px] font-bold uppercase tracking-wider py-2.5 transition-colors cursor-pointer shadow-lg whitespace-nowrap">
                                                <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                                                </svg>
                                                Tambah Keranjang
                                            </button>
                                        </form>
                                    </div>
                                @endif
                            </div>

                            {{-- Mobile-only CTA bar (selalu tampil di sm bawah) --}}
                            @if($inStock)
                                <form method="POST"
                                      action="{{ route('cart.add', $p) }}"
                                      data-cart-form
                                      data-cart-name="{{ $p->nama }}"
                                      class="sm:hidden px-3 pt-2 pb-3">
                                    @csrf
                                    <input type="hidden" name="qty" value="1">
                                    <button type="submit"
                                            class="w-full inline-flex items-center justify-center gap-1.5 rounded-full bg-[#1A1A1A] hover:bg-[#CFAF53] text-white text-[11px] font-bold uppercase tracking-wider py-2.5 transition-colors cursor-pointer">
                                        <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                                        </svg>
                                        Tambah
                                    </button>
                                </form>
                            @endif

                            {{-- Body --}}
                            <div class="flex flex-col p-3 sm:p-4 border-t border-[#E8E0D8]/60">
                                {{-- Kategori --}}
                                <span class="text-[10px] sm:text-[11px] font-semibold tracking-wide text-[#8C7A63] mb-1 sm:mb-1.5">
                                    {{ $p->category->nama ?? 'Skincare' }}
                                </span>

                                {{-- Nama produk --}}
                                <h3 class="text-[13px] sm:text-base font-bold text-[#1A1A1A] line-clamp-2 leading-snug min-h-[2.25rem] sm:min-h-[2.5rem]">
                                    <a href="{{ route('shop.show', $p) }}" class="hover:text-[#8C7A63] transition-colors">{{ $p->nama }}</a>
                                </h3>

                                {{-- Harga --}}
                                <div class="mt-1.5 sm:mt-2">
                                    <span class="text-sm sm:text-lg font-extrabold text-[#CFAF53]">
                                        Rp {{ number_format((int)$p->harga, 0, ',', '.') }}
                                    </span>
                                </div>
                            </div>
                        </article>
