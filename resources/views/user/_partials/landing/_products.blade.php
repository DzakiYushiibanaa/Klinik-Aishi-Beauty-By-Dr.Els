{{-- Section: Featured Products --}}
        {{-- SECTION 4: PRODUCTS --}}
        <section class="max-w-7xl mx-auto px-4 py-16 w-full border-t border-gray-200">
            <div class="text-center mb-12">
                <h2 class="text-3xl sm:text-4xl font-sans text-[#1A1A1A] mb-4">
                    Katalog <span class="italic text-[#8C7A63]">Produk</span> Kami
                </h2>
                <p class="text-gray-500 max-w-xl mx-auto text-sm">
                    Rangkaian produk skincare eksklusif yang diformulasikan secara medis untuk menjaga kesehatan kulit
                    Anda di rumah.
                </p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
                @forelse (($featuredProducts ?? collect()) as $p)
                    <div class="bg-[#F2EFE9] rounded-2xl overflow-hidden shadow-sm hover:shadow-md transition">
                        <div class="aspect-square bg-white flex items-center justify-center p-8 relative">
                            @if (!empty($p->foto_path))
                                <img src="{{ asset('storage/' . $p->foto_path) }}" alt="{{ $p->nama }}"
                                    class="w-full h-full object-contain" />
                            @else
                                <img src="https://images.unsplash.com/photo-1620916566398-39f1143ab7be?w=400&h=400&fit=crop"
                                    class="w-full h-full object-contain" />
                            @endif
                        </div>
                        <div class="p-6">
                            <h3 class="font-sans text-lg font-bold text-[#1A1A1A] mb-2 line-clamp-1">{{ $p->nama }}</h3>
                            <p class="text-sm text-gray-500 line-clamp-2 mb-4">Rangkaian perawatan kulit yang menutrisi dan
                                menjaga kelembapan kulit secara optimal setiap saat.</p>
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-[#8C7A63]">Rp
                                    {{ number_format((int) ($p->harga ?? 0), 0, ',', '.') }}</span>
                                <button
                                    class="w-8 h-8 rounded-full border border-[#D6C5AD] flex items-center justify-center text-[#8C7A63] hover:bg-[#8C7A63] hover:text-white transition"><svg
                                        class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 4v16m8-8H4" />
                                    </svg></button>
                            </div>
                        </div>
                    </div>
                @empty
                    {{-- Dummy products --}}
                    <div class="bg-[#F2EFE9] rounded-2xl overflow-hidden shadow-sm hover:shadow-md transition">
                        <div class="aspect-square bg-[#812328] flex items-center justify-center p-8 relative"><img
                                src="https://images.unsplash.com/photo-1620916566398-39f1143ab7be?w=400&h=400&fit=crop"
                                class="w-full h-full object-cover mix-blend-multiply opacity-50 rounded" /><span
                                class="absolute text-white font-bold tracking-widest z-10 text-xl font-sans">Serum
                                Sinar</span></div>
                        <div class="p-6">
                            <h3 class="font-sans text-lg font-bold text-[#1A1A1A] mb-2">Serum Skincare Pencerah</h3>
                            <p class="text-sm text-gray-500 mb-4 line-clamp-2">Serum yang diformulasikan dengan niacinamide
                                untuk melembapkan kulit secara maksimal dan meratakan warna kulit.</p>
                            <div class="flex items-center justify-between"><span class="font-bold text-[#8C7A63]">Rp
                                    150.000</span><button
                                    class="w-8 h-8 rounded-full border border-[#D6C5AD] flex items-center justify-center text-[#8C7A63]"><svg
                                        class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 4v16m8-8H4" />
                                    </svg></button></div>
                        </div>
                    </div>
                    <div class="bg-[#F2EFE9] rounded-2xl overflow-hidden shadow-sm hover:shadow-md transition">
                        <div class="aspect-square bg-[#B34125] flex items-center justify-center p-8 relative"><img
                                src="https://images.unsplash.com/photo-1620916566398-39f1143ab7be?w=400&h=400&fit=crop"
                                class="w-full h-full object-cover mix-blend-multiply opacity-50 rounded" /><span
                                class="absolute text-white font-bold tracking-widest z-10 text-xl font-sans">Moisture</span>
                        </div>
                        <div class="p-6">
                            <h3 class="font-sans text-lg font-bold text-[#1A1A1A] mb-2">Moisture Tekstur Krim Gel</h3>
                            <p class="text-sm text-gray-500 mb-4 line-clamp-2">Krim pelembap dengan tekstur ringan yang
                                menyerap cepat, memberikan kelembapan maksimal sepanjang hari.</p>
                            <div class="flex items-center justify-between"><span class="font-bold text-[#8C7A63]">Rp
                                    180.000</span><button
                                    class="w-8 h-8 rounded-full border border-[#D6C5AD] flex items-center justify-center text-[#8C7A63]"><svg
                                        class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 4v16m8-8H4" />
                                    </svg></button></div>
                        </div>
                    </div>
                    <div class="bg-[#F2EFE9] rounded-2xl overflow-hidden shadow-sm hover:shadow-md transition">
                        <div class="aspect-square bg-[#3E3836] flex items-center justify-center p-8 relative"><img
                                src="https://images.unsplash.com/photo-1620916566398-39f1143ab7be?w=400&h=400&fit=crop"
                                class="w-full h-full object-cover mix-blend-multiply opacity-50 rounded" /><span
                                class="absolute text-white font-bold tracking-widest z-10 text-xl font-sans">Sun
                                Shield</span></div>
                        <div class="p-6">
                            <h3 class="font-sans text-lg font-bold text-[#1A1A1A] mb-2">Sunblock Pencerah Wajah</h3>
                            <p class="text-sm text-gray-500 mb-4 line-clamp-2">Perlindungan optimal dari paparan sinar UV
                                dengan SPF 50+, sekaligus membantu mencerahkan noda hitam pada wajah.</p>
                            <div class="flex items-center justify-between"><span class="font-bold text-[#8C7A63]">Rp
                                    120.000</span><button
                                    class="w-8 h-8 rounded-full border border-[#D6C5AD] flex items-center justify-center text-[#8C7A63]"><svg
                                        class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 4v16m8-8H4" />
                                    </svg></button></div>
                        </div>
                    </div>
                @endforelse
            </div>

            <div class="mt-12 text-center">
                <a href="{{ route('shop.index') }}"
                    class="inline-flex items-center justify-center rounded-full border border-[#8C7A63] text-[#8C7A63] hover:bg-[#8C7A63] hover:text-white px-8 py-3 text-sm font-bold tracking-wider uppercase transition-colors">
                    Lihat Semua Produk
                </a>
            </div>
        </section>
