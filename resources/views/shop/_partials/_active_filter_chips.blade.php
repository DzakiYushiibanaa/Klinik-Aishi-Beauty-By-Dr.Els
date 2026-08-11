{{-- Partial: Active Filter Chips --}}
                @if(request('category') || request('availability') || request('search'))
                    <div class="flex flex-wrap items-center gap-2 mb-6">
                        <span class="text-xs font-bold uppercase tracking-widest text-slate-400">Aktif:</span>

                        @if(request('search'))
                            <span class="inline-flex items-center gap-1.5 rounded-full bg-[#1A1A1A] pl-3 pr-2 py-1 text-xs font-bold text-white">
                                "{{ request('search') }}"
                                <a href="{{ route('shop.index', request()->except(['search', 'page'])) }}" class="rounded-full hover:bg-white/10 p-0.5 transition-colors" aria-label="Hapus pencarian">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                                </a>
                            </span>
                        @endif

                        @foreach((array)request('category') as $cat_id)
                            @php $c = $categories->where('product_category_id', $cat_id)->first(); @endphp
                            @if($c)
                                <span class="inline-flex items-center gap-1.5 rounded-full bg-[#CFAF53]/15 text-[#1A1A1A] border border-[#CFAF53]/30 pl-3 pr-2 py-1 text-xs font-bold">
                                    {{ $c->nama }}
                                    <a href="{{ route('shop.index', array_merge(request()->except('page'), ['category' => array_diff((array)request('category'), [$cat_id])])) }}" class="rounded-full hover:bg-[#CFAF53]/20 p-0.5 transition-colors" aria-label="Hapus filter">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </a>
                                </span>
                            @endif
                        @endforeach

                        @foreach((array)request('availability') as $avail)
                            <span class="inline-flex items-center gap-1.5 rounded-full bg-[#CFAF53]/15 text-[#1A1A1A] border border-[#CFAF53]/30 pl-3 pr-2 py-1 text-xs font-bold">
                                {{ $avail === 'in_stock' ? 'Tersedia' : 'Stok Habis' }}
                                <a href="{{ route('shop.index', array_merge(request()->except('page'), ['availability' => array_diff((array)request('availability'), [$avail])])) }}" class="rounded-full hover:bg-[#CFAF53]/20 p-0.5 transition-colors" aria-label="Hapus filter">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                                </a>
                            </span>
                        @endforeach
                    </div>
                @endif
