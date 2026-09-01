{{-- Section: Hero Image Slider --}}
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
                 }" class="relative w-full min-h-[600px] md:min-h-[calc(100vh-5rem)] md:max-h-[900px] overflow-hidden bg-black flex">

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

            <div class="relative z-20 mx-auto w-full max-w-7xl px-4 sm:px-6 lg:px-8 flex items-center py-16 sm:py-20">
                <div class="max-w-2xl text-white">
                    <h1
                        class="text-4xl sm:text-5xl lg:text-[4.5rem] font-bold font-sans mb-6 leading-tight drop-shadow-lg">
                        Solusi kulitmu,<br />
                        tanpa harus ke klinik
                    </h1>
                    <p
                        class="text-[#D6C5AD] text-base md:text-lg leading-relaxed mb-10 max-w-lg font-light drop-shadow">
                        Ceritakan kondisi kulitmu secara online, lalu dapatkan analisis dan rekomendasi perawatan langsung dari dokter kami tanpa harus datang ke klinik.
                    </p>

                    <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4">
                        <a href="{{ route('appointment.select') }}"
                            class="inline-flex items-center justify-center rounded-full bg-white text-[#1A1A1A] hover:bg-gray-200 px-8 py-3.5 text-sm font-bold tracking-wider transition-colors shadow-lg">
                            Buat Janji Temu
                        </a>
                        <a href="{{ route('pra.create') }}"
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
