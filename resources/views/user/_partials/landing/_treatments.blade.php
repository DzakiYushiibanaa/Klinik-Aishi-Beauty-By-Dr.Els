{{-- Section: Featured Treatments --}}
        {{-- SECTION 3: TREATMENTS --}}
        <section class="max-w-7xl mx-auto px-4 py-20 w-full">
            <div class="text-center mb-12">
                <h2 class="text-3xl sm:text-4xl font-sans text-[#1A1A1A] mb-4">
                    Jelajahi <span class="italic text-[#8C7A63]">Perawatan</span> Kecantikan<br />Kami
                </h2>
                <p class="text-gray-500 max-w-2xl mx-auto text-sm">
                    Kami menawarkan berbagai perawatan estetika yang disesuaikan dengan kebutuhan unik setiap pasien,
                    memberikan hasil optimal dan alami yang meningkatkan kepercayaan diri Anda.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                @forelse(($featuredTreatments ?? collect()) as $index => $treatment)
                    <div
                        class="bg-[#F2EFE9] rounded-2xl p-6 flex items-start gap-4 transition hover:-translate-y-1 hover:shadow-lg">
                        <div class="w-20 h-20 shrink-0 rounded-xl overflow-hidden bg-gray-200">
                            @if(!empty($treatment->foto_path))
                                <img src="{{ asset('storage/' . $treatment->foto_path) }}" alt="{{ $treatment->nama }}"
                                    class="w-full h-full object-cover">
                            @else
                                <img src="https://images.unsplash.com/photo-1616394584738-1d44bc40e4e7?w=200&h=200&fit=crop"
                                    class="w-full h-full object-cover" />
                            @endif
                        </div>
                        <div>
                            <h3 class="font-sans text-lg font-bold text-[#1A1A1A] mb-2">{{ $treatment->nama }}</h3>
                            <p class="text-sm text-gray-600 line-clamp-3 leading-relaxed">
                                {{ $treatment->deskripsi ?? 'Perawatan optimal untuk mengembalikan kesehatan kulit dan memberikan tampilan yang lebih cerah.' }}
                            </p>
                        </div>
                    </div>
                @empty
                    {{-- Dummy content for preview --}}
                    <div
                        class="bg-[#F2EFE9] rounded-2xl p-6 flex items-start gap-4 hover:-translate-y-1 transition shadow-sm">
                        <div class="w-20 h-20 shrink-0 rounded-xl overflow-hidden"><img
                                src="https://images.unsplash.com/photo-1590159763121-7c913ad8fac6?w=200&h=200&fit=crop"
                                class="w-full h-full object-cover"></div>
                        <div>
                            <h3 class="font-sans text-lg font-bold text-[#1A1A1A] mb-2">Skin Booster</h3>
                            <p class="text-sm text-gray-600">Injeksi vitamin dan hyaluronic acid untuk melembapkan kulit
                                dari dalam dan memberikan efek glowing bercahaya.</p>
                        </div>
                    </div>
                    <div
                        class="bg-[#F2EFE9] rounded-2xl p-6 flex items-start gap-4 hover:-translate-y-1 transition shadow-sm">
                        <div class="w-20 h-20 shrink-0 rounded-xl overflow-hidden"><img
                                src="https://images.unsplash.com/photo-1556228578-0d85b1a4d571?w=200&h=200&fit=crop"
                                class="w-full h-full object-cover"></div>
                        <div>
                            <h3 class="font-sans text-lg font-bold text-[#1A1A1A] mb-2">Acne Clear</h3>
                            <p class="text-sm text-gray-600">Perawatan komprehensif untuk mengatasi jerawat membandel dan
                                mencegah timbulnya kembali bekas jerawat.</p>
                        </div>
                    </div>
                    <div
                        class="bg-[#F2EFE9] rounded-2xl p-6 flex items-start gap-4 hover:-translate-y-1 transition shadow-sm">
                        <div class="w-20 h-20 shrink-0 rounded-xl overflow-hidden"><img
                                src="https://images.unsplash.com/photo-1570172619644-dfd03ed5d881?w=200&h=200&fit=crop"
                                class="w-full h-full object-cover"></div>
                        <div>
                            <h3 class="font-sans text-lg font-bold text-[#1A1A1A] mb-2">Laser Rejuvenation</h3>
                            <p class="text-sm text-gray-600">Terapi laser tingkat lanjut untuk merangsang kolagen, meratakan
                                warna kulit, dan memudarkan flek hitam.</p>
                        </div>
                    </div>
                @endforelse
            </div>

            <div class="mt-12 flex justify-center gap-4">
                <button
                    class="w-10 h-10 rounded-full border border-[#D6C5AD] flex items-center justify-center text-[#8C7A63] hover:bg-[#8C7A63] hover:text-white transition"><svg
                        class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg></button>
                <button
                    class="w-10 h-10 rounded-full border border-[#D6C5AD] flex items-center justify-center text-[#8C7A63] hover:bg-[#8C7A63] hover:text-white transition"><svg
                        class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg></button>
            </div>
        </section>
