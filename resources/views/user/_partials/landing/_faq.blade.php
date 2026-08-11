{{-- Section: FAQ Accordion --}}
        {{-- SECTION 5: FAQ --}}
        <section class="max-w-7xl mx-auto px-4 py-20 w-full">
            <div class="flex flex-col lg:flex-row gap-12 lg:gap-24">
                <div class="lg:w-1/3">
                    <h2 class="text-3xl sm:text-4xl font-sans text-[#1A1A1A] mb-6">
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
