{{-- Section: Location & Map --}}
        {{-- SECTION 7: LOCATION (Moved from Guest Layout/Footer to here) --}}
        <section class="w-full bg-[#FAF8F5] py-20 border-t border-gray-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center mb-12">
                <h2 class="text-3xl sm:text-4xl font-sans text-[#1A1A1A] mb-4">
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
                    <h3 class="font-sans text-xl font-bold text-[#1A1A1A] mb-6">Aishi Beauty Cabang Beji</h3>

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
