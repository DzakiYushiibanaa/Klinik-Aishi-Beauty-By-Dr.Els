<x-guest-layout title="Beranda">
    <div class="flex flex-col bg-[#FAF8F5]">

        {{-- SECTION 1: HERO (Image Slider Background) --}}
        <section x-data="{ 
                    activeSlide: 0, 
                    slides: [
                        '{{ asset('assets/img/hero1.jpg') }}',
                        '{{ asset('assets/img/hero2.jpg') }}'
                    ],
                    init() {
                        setInterval(() => {
                            this.activeSlide = this.activeSlide === this.slides.length - 1 ? 0 : this.activeSlide + 1;
                        }, 5000);
                    }
                 }" class="relative w-full h-[600px] lg:h-[800px] overflow-hidden bg-black">

            {{-- Background Images Slider --}}
            <template x-for="(slide, index) in slides" :key="index">
                <img :src="slide" alt="Aishi Beauty Hero"
                    class="absolute inset-0 w-full h-full object-cover object-center transition-opacity duration-1000 ease-in-out"
                    :class="activeSlide === index ? 'opacity-100 z-0' : 'opacity-0 -z-10'" />
            </template>

            {{-- Global Dark Overlay for Readability --}}
            <div class="absolute inset-0 bg-black/75 z-10 pointer-events-none"></div>

            {{-- Gradient Overlay (Stronger on the left for text) --}}
            <div
                class="absolute inset-0 bg-gradient-to-r from-black/80 via-black/50 to-transparent z-10 pointer-events-none">
            </div>

            <div class="relative z-20 mx-auto h-full max-w-7xl px-4 sm:px-6 lg:px-8 flex items-center">
                <div class="max-w-2xl text-white">
                    <h1
                        class="text-4xl sm:text-5xl lg:text-[4.5rem] font-bold font-serif mb-6 leading-tight drop-shadow-lg">
                        Konsultasikan Masalah Kulitmu,<br />
                        Cukup dari Rumah.
                    </h1>
                    <p
                        class="text-[#D6C5AD] text-base md:text-lg leading-relaxed mb-10 max-w-lg font-light drop-shadow">
                        Tak perlu bingung salah pilih produk. Konsultasikan masalah kulitmu secara online dan biarkan dokter spesialis kami memberikan solusi yang tepat untukmu secara <b>GRATIS.</b>
                    </p>

                    <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4">
                        <a href="{{ route('appointment.select') }}"
                            class="inline-flex items-center justify-center rounded-full bg-white text-[#1A1A1A] hover:bg-gray-200 px-8 py-3.5 text-sm font-bold tracking-wider transition-colors shadow-lg">
                            Buat Janji Temu
                        </a>
                        <a href="{{ route('pra.index') }}"
                            class="inline-flex items-center justify-center rounded-full bg-black/40 backdrop-blur-md border border-white/20 text-white hover:bg-black/60 px-8 py-3.5 text-sm font-bold tracking-wider transition-colors">
                            Konsultasi Online
                        </a>
                    </div>
                </div>
            </div>

            {{-- Social floating (Right Center) --}}
            <div class="hidden lg:flex absolute right-8 top-1/2 -translate-y-1/2 flex-col gap-4 z-20">
                {{-- Instagram --}}
                <a href="https://www.instagram.com/aishibeauty_bydr.els" target="_blank"
                    class="w-10 h-10 rounded-full border border-white/20 flex items-center justify-center text-white hover:bg-white hover:text-black transition-colors backdrop-blur-md bg-black/20">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                        <path
                            d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z" />
                    </svg>
                </a>
                {{-- TikTok --}}
                <a href="https://www.tiktok.com/@aishibeauty.id_official" target="_blank"
                    class="w-10 h-10 rounded-full border border-white/20 flex items-center justify-center text-white hover:bg-white hover:text-black transition-colors backdrop-blur-md bg-black/20">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                        <path
                            d="M19.59 6.69a4.83 4.83 0 01-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 01-2.88 2.5 2.89 2.89 0 01-2.89-2.89 2.89 2.89 0 012.89-2.89c.28 0 .54.04.79.1v-3.5a6.37 6.37 0 00-.79-.05A6.34 6.34 0 003.15 15.2a6.34 6.34 0 0010.86 4.48 6.3 6.3 0 001.86-4.49V8.74a8.18 8.18 0 004.72 1.5v-3.4a4.85 4.85 0 01-1-.15z" />
                    </svg>
                </a>
            </div>

            {{-- Slider Indicators (Bottom Right) --}}
            <div
                class="absolute bottom-8 right-8 lg:bottom-16 lg:right-16 z-20 flex items-center gap-4 text-white text-[10px] font-bold tracking-widest uppercase">
                <button @click="activeSlide = activeSlide === 0 ? slides.length - 1 : activeSlide - 1"
                    class="w-6 h-6 flex items-center justify-center hover:text-[#D6C5AD] transition-colors">
                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </button>

                <div class="flex items-center gap-3">
                    <span x-text="String(activeSlide + 1).padStart(2, '0')"></span>
                    <span class="text-white/50">/</span>
                    <span class="text-white/50" x-text="String(slides.length).padStart(2, '0')"></span>
                    <div class="w-16 h-px bg-white/20 relative mx-2">
                        <div class="absolute top-0 left-0 h-full bg-white transition-all duration-300"
                            :style="'width: ' + ((activeSlide + 1) / slides.length * 100) + '%'"></div>
                    </div>
                </div>

                <button @click="activeSlide = activeSlide === slides.length - 1 ? 0 : activeSlide + 1"
                    class="w-6 h-6 flex items-center justify-center hover:text-[#D6C5AD] transition-colors">
                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </button>
            </div>
        </section>

        {{-- SECTION 2: STATS (Dark block attached below hero) --}}
        <section class="w-full bg-[#2D2A26] py-10 relative z-20">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-8 text-center divide-x divide-[#4A453F]" x-data="{ countTo(max, key) { 
                        let start = 0; 
                        const duration = 2000; 
                        const stepTime = Math.abs(Math.floor(duration / max)); 
                        let timer = setInterval(() => { 
                            start += Math.ceil(max / (duration/30)); 
                            if(start >= max) { start = max; clearInterval(timer); } 
                            this[key] = start; 
                        }, 30); 
                     }, c1: 0, c2: 0, c3: 0, c4: 0 }" x-init="
                        setTimeout(() => countTo(15000, 'c1'), 500);
                        setTimeout(() => countTo(50, 'c2'), 500);
                        setTimeout(() => countTo(20, 'c3'), 500);
                        setTimeout(() => countTo(2018, 'c4'), 500);
                     ">

                    <div class="flex flex-col items-center justify-center px-4">
                        <div
                            class="h-10 w-10 mb-4 rounded-full border border-[#8C7A63] flex items-center justify-center text-[#8C7A63]">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                        </div>
                        <h3 class="text-3xl font-serif text-white mb-1"><span
                                x-text="c1.toLocaleString('id-ID')">0</span></h3>
                        <p class="text-[10px] font-semibold tracking-widest text-[#8C7A63] uppercase">Pasien Terlayani
                        </p>
                    </div>

                    <div class="flex flex-col items-center justify-center px-4">
                        <div
                            class="h-10 w-10 mb-4 rounded-full border border-[#8C7A63] flex items-center justify-center text-[#8C7A63]">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                            </svg>
                        </div>
                        <h3 class="text-3xl font-serif text-white mb-1"><span x-text="c2">0</span></h3>
                        <p class="text-[10px] font-semibold tracking-widest text-[#8C7A63] uppercase">Jenis Perawatan
                        </p>
                    </div>

                    <div class="flex flex-col items-center justify-center px-4">
                        <div
                            class="h-10 w-10 mb-4 rounded-full border border-[#8C7A63] flex items-center justify-center text-[#8C7A63]">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                            </svg>
                        </div>
                        <h3 class="text-3xl font-serif text-white mb-1"><span x-text="c3">0</span></h3>
                        <p class="text-[10px] font-semibold tracking-widest text-[#8C7A63] uppercase">Ahli Kecantikan
                        </p>
                    </div>

                    <div class="flex flex-col items-center justify-center px-4">
                        <div
                            class="h-10 w-10 mb-4 rounded-full border border-[#8C7A63] flex items-center justify-center text-[#8C7A63]">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                        </div>
                        <h3 class="text-3xl font-serif text-white mb-1"><span x-text="c4">0</span></h3>
                        <p class="text-[10px] font-semibold tracking-widest text-[#8C7A63] uppercase">Tahun Berdiri</p>
                    </div>
                </div>
            </div>
        </section>

        {{-- SECTION 3: TREATMENTS --}}
        <section class="max-w-7xl mx-auto px-4 py-20 w-full">
            <div class="text-center mb-12">
                <h2 class="text-3xl sm:text-4xl font-serif text-[#1A1A1A] mb-4">
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
                            <h3 class="font-serif text-lg font-bold text-[#1A1A1A] mb-2">{{ $treatment->nama }}</h3>
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
                            <h3 class="font-serif text-lg font-bold text-[#1A1A1A] mb-2">Skin Booster</h3>
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
                            <h3 class="font-serif text-lg font-bold text-[#1A1A1A] mb-2">Acne Clear</h3>
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
                            <h3 class="font-serif text-lg font-bold text-[#1A1A1A] mb-2">Laser Rejuvenation</h3>
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

        {{-- SECTION 4: PRODUCTS --}}
        <section class="max-w-7xl mx-auto px-4 py-16 w-full border-t border-gray-200">
            <div class="text-center mb-12">
                <h2 class="text-3xl sm:text-4xl font-serif text-[#1A1A1A] mb-4">
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
                            <h3 class="font-serif text-lg font-bold text-[#1A1A1A] mb-2 line-clamp-1">{{ $p->nama }}</h3>
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
                                class="absolute text-white font-bold tracking-widest z-10 text-xl font-serif">Serum
                                Sinar</span></div>
                        <div class="p-6">
                            <h3 class="font-serif text-lg font-bold text-[#1A1A1A] mb-2">Serum Skincare Pencerah</h3>
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
                                class="absolute text-white font-bold tracking-widest z-10 text-xl font-serif">Moisture</span>
                        </div>
                        <div class="p-6">
                            <h3 class="font-serif text-lg font-bold text-[#1A1A1A] mb-2">Moisture Tekstur Krim Gel</h3>
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
                                class="absolute text-white font-bold tracking-widest z-10 text-xl font-serif">Sun
                                Shield</span></div>
                        <div class="p-6">
                            <h3 class="font-serif text-lg font-bold text-[#1A1A1A] mb-2">Sunblock Pencerah Wajah</h3>
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

        {{-- SECTION 5: FAQ --}}
        <section class="max-w-7xl mx-auto px-4 py-20 w-full">
            <div class="flex flex-col lg:flex-row gap-12 lg:gap-24">
                <div class="lg:w-1/3">
                    <h2 class="text-3xl sm:text-4xl font-serif text-[#1A1A1A] mb-6">
                        Pertanyaan yang Sering<br /><span class="italic text-[#8C7A63]">Diajukan</span>
                    </h2>
                    <p class="text-gray-500 text-sm mb-8">
                        Temukan jawaban dari pertanyaan seputar layanan dan produk kami. Jangan ragu menghubungi CS jika
                        masih ada yang membingungkan.
                    </p>
                    <a href="https://wa.me/+6285780050007"
                        class="inline-flex items-center justify-center rounded-full bg-[#8C7A63] text-white hover:bg-[#6D5A42] px-6 py-3 text-sm font-bold transition-colors">
                        Hubungi Kami
                    </a>
                </div>

                <div class="lg:w-2/3" x-data="{ active: 0 }">
                    <div class="space-y-4">
                        {{-- FAQ 1 --}}
                        <div class="border border-[#E5E0D8] rounded-xl bg-[#F8F6F3] overflow-hidden">
                            <button @click="active = active === 1 ? 0 : 1"
                                class="w-full px-6 py-5 flex items-center justify-between text-left font-bold text-[#1A1A1A]">
                                Apakah konsultasi sebelum treatment gratis?
                                <svg class="w-5 h-5 text-[#8C7A63] transform transition-transform"
                                    :class="{ 'rotate-180': active === 1 }" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>
                            <div x-show="active === 1" x-collapse>
                                <div
                                    class="px-6 pb-5 text-sm text-gray-600 leading-relaxed border-t border-[#E5E0D8] pt-4 mt-2">
                                    Ya, kami memberikan konsultasi pra-treatment secara gratis. Dokter kami akan
                                    memeriksa kondisi kulit Anda untuk merekomendasikan perawatan dan produk skincare
                                    yang paling tepat sesuai kebutuhan kulit Anda.
                                </div>
                            </div>
                        </div>

                        {{-- FAQ 2 --}}
                        <div class="border border-[#E5E0D8] rounded-xl bg-[#F8F6F3] overflow-hidden">
                            <button @click="active = active === 2 ? 0 : 2"
                                class="w-full px-6 py-5 flex items-center justify-between text-left font-bold text-[#1A1A1A]">
                                Berapa lama pengerjaan treatment facial?
                                <svg class="w-5 h-5 text-[#8C7A63] transform transition-transform"
                                    :class="{ 'rotate-180': active === 2 }" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>
                            <div x-show="active === 2" x-collapse>
                                <div
                                    class="px-6 pb-5 text-sm text-gray-600 leading-relaxed border-t border-[#E5E0D8] pt-4 mt-2">
                                    Waktu pengerjaan bervariasi antara 45 menit hingga 90 menit, tergantung pada jenis
                                    facial dan kebutuhan kulit yang dianalisis oleh dokter kami saat sesi konsultasi.
                                </div>
                            </div>
                        </div>

                        {{-- FAQ 3 --}}
                        <div class="border border-[#E5E0D8] rounded-xl bg-[#F8F6F3] overflow-hidden">
                            <button @click="active = active === 3 ? 0 : 3"
                                class="w-full px-6 py-5 flex items-center justify-between text-left font-bold text-[#1A1A1A]">
                                Apakah produk di klinik aman untuk ibu hamil?
                                <svg class="w-5 h-5 text-[#8C7A63] transform transition-transform"
                                    :class="{ 'rotate-180': active === 3 }" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>
                            <div x-show="active === 3" x-collapse>
                                <div
                                    class="px-6 pb-5 text-sm text-gray-600 leading-relaxed border-t border-[#E5E0D8] pt-4 mt-2">
                                    Beberapa produk kami diformulasikan khusus dan aman untuk ibu hamil maupun menyusui.
                                    Harap informasikan kondisi kehamilan Anda saat konsultasi agar dokter dapat
                                    meresepkan produk yang tepat.
                                </div>
                            </div>
                        </div>

                        {{-- FAQ 4 --}}
                        <div class="border border-[#E5E0D8] rounded-xl bg-[#F8F6F3] overflow-hidden">
                            <button @click="active = active === 4 ? 0 : 4"
                                class="w-full px-6 py-5 flex items-center justify-between text-left font-bold text-[#1A1A1A]">
                                Berapa lama hasil treatment mulai terlihat?
                                <svg class="w-5 h-5 text-[#8C7A63] transform transition-transform"
                                    :class="{ 'rotate-180': active === 4 }" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>
                            <div x-show="active === 4" x-collapse>
                                <div
                                    class="px-6 pb-5 text-sm text-gray-600 leading-relaxed border-t border-[#E5E0D8] pt-4 mt-2">
                                    Hasil langsung bisa terlihat sejak treatment pertama, namun untuk hasil yang optimal
                                    dan permanen, disarankan untuk melakukan treatment secara rutin sesuai jadwal yang
                                    direkomendasikan dokter.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- SECTION 6: CTA --}}
        <section class="max-w-6xl mx-auto px-4 py-10 w-full mb-10">
            <div class="bg-[#8C7A63] rounded-[2rem] p-12 md:p-20 text-center relative overflow-hidden shadow-xl">
                <div
                    class="absolute inset-0 opacity-10 bg-[url('https://www.transparenttextures.com/patterns/stardust.png')] mix-blend-overlay">
                </div>
                <div class="relative z-10">
                    <h2 class="text-3xl sm:text-4xl lg:text-5xl font-serif text-white mb-6">
                        Siap untuk <span class="italic font-light">Mengubah</span><br />Penampilan Anda?
                    </h2>
                    <p class="text-[#E5E0D8] max-w-2xl mx-auto mb-10">
                        Bila Anda sedang mencari perawatan kulit untuk wajah cantik yang bersinar maupun produk skincare
                        yang efektif atasi masalah kulit, kami siap membantu wujudkan impian Anda di sini bersama kami.
                    </p>
                    <a href="{{ route('appointment.create') }}"
                        class="inline-flex items-center justify-center rounded-full bg-white text-[#8C7A63] hover:bg-gray-100 px-8 py-3.5 text-sm font-bold uppercase tracking-wider transition shadow-md">
                        Buat Janji Temu
                    </a>
                </div>
            </div>
        </section>

        {{-- SECTION 7: LOCATION (Moved from Guest Layout/Footer to here) --}}
        <section class="w-full bg-[#FAF8F5] py-20 border-t border-gray-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center mb-12">
                <h2 class="text-3xl sm:text-4xl font-serif text-[#1A1A1A] mb-4">
                    Kunjungi <span class="italic text-[#8C7A63]">Klinik</span> Kami
                </h2>
                <p class="text-gray-500 max-w-2xl mx-auto text-sm">
                    Fasilitas klinik yang nyaman dengan protokol kesehatan yang ketat untuk keamanan dan kenyamanan
                    Anda.
                </p>
            </div>

            <div class="max-w-6xl mx-auto px-4 sm:px-6 flex flex-col lg:flex-row gap-8 items-stretch">
                {{-- Address Card --}}
                <div class="lg:w-1/3 bg-[#F2EFE9] rounded-3xl p-8 border border-[#E5E0D8] shadow-sm flex flex-col">
                    <h3 class="font-serif text-xl font-bold text-[#1A1A1A] mb-6">Aishi Beauty Cabang Beji</h3>

                    <div class="space-y-6 flex-1">
                        <div class="flex gap-4">
                            <div class="mt-1 text-[#8C7A63]"><svg class="w-5 h-5" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg></div>
                            <p class="text-sm text-gray-600 leading-relaxed">
                                Jl. Nusantara Raya No.99b, Beji, Kec. Beji, Kota Depok, Jawa Barat 16421
                            </p>
                        </div>

                        <div>
                            <h4 class="font-bold text-[#1A1A1A] text-sm mb-2">Jam Operasional</h4>
                            <div class="flex gap-4 text-sm text-gray-600">
                                <div class="mt-0.5 text-[#8C7A63]"><svg class="w-5 h-5" fill="none" viewBox="0 0 24 24"
                                        stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg></div>
                                <p>Senin - Minggu : 09:00 - 18:30 WIB</p>
                            </div>
                        </div>

                        <h4 class="font-bold text-[#1A1A1A] text-sm mb-2">Kontak</h4>
                        <div class="flex gap-4 text-sm text-gray-600">
                            <div class="mt-0.5 text-[#8C7A63]"><svg class="w-5 h-5" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                </svg></div>
                            <p>+62 857 8005 0007</p>
                        </div>
                    </div>

                    <a href="https://maps.google.com/?q=Klinik+Aishi+Beauty+By+DR.+ELS" target="_blank"
                        class="mt-8 w-full inline-flex items-center justify-center rounded-xl bg-[#C1A589] text-white hover:bg-[#8C7A63] px-6 py-3.5 text-sm font-bold transition">
                        Buka Petunjuk Arah
                    </a>
                </div>

                {{-- Map --}}
                <div
                    class="lg:w-2/3 h-[400px] lg:h-auto rounded-3xl overflow-hidden border border-[#E5E0D8] shadow-sm bg-gray-100">
                    <iframe class="w-full h-full grayscale hover:grayscale-0 transition-all duration-700"
                        frameborder="0" scrolling="no" marginheight="0" marginwidth="0"
                        src="https://maps.google.com/maps?q=Klinik+Aishi+Beauty+By+DR.+ELS&t=&z=15&ie=UTF8&iwloc=&output=embed">
                    </iframe>
                </div>
            </div>
        </section>

    </div>
</x-guest-layout>