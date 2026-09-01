<footer class="bg-[#1C1A18] text-[#9CA3AF] py-16">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 grid grid-cols-1 md:grid-cols-4 gap-10">
            <div class="md:col-span-1">
                <a href="{{ route('landing') }}" class="flex flex-col mb-6">
                    <span class="font-serif text-2xl tracking-widest text-white">AISHI BEAUTY</span>
                    <span class="text-[8px] font-medium text-[#8C7A63] tracking-[0.3em] mt-1">BY DR. ELS</span>
                </a>
                <p class="text-sm leading-relaxed mb-6">
                    Klinik estetika terpercaya dengan pelayanan dokter dan terapis profesional untuk hasil kulit sehat
                    maksimal.
                </p>
                <div class="flex gap-4">
                    {{-- Instagram --}}
                    <a href="https://www.instagram.com/aishibeauty_bydr.els" target="_blank"
                        class="h-8 w-8 rounded-full border border-gray-700 flex items-center justify-center hover:border-[#8C7A63] hover:text-[#8C7A63] transition-colors"><svg
                            class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                            <path
                                d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z" />
                        </svg></a>
                    {{-- TikTok --}}
                    <a href="https://www.tiktok.com/@aishibeauty.id_official" target="_blank"
                        class="h-8 w-8 rounded-full border border-gray-700 flex items-center justify-center hover:border-[#8C7A63] hover:text-[#8C7A63] transition-colors"><svg
                            class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                            <path
                                d="M19.59 6.69a4.83 4.83 0 01-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 01-2.88 2.5 2.89 2.89 0 01-2.89-2.89 2.89 2.89 0 012.89-2.89c.28 0 .54.04.79.1v-3.5a6.37 6.37 0 00-.79-.05A6.34 6.34 0 003.15 15.2a6.34 6.34 0 0010.86 4.48 6.3 6.3 0 001.86-4.49V8.74a8.18 8.18 0 004.72 1.5v-3.4a4.85 4.85 0 01-1-.15z" />
                        </svg></a>
                </div>
            </div>
            <div>
                <h4 class="text-white font-bold mb-4 uppercase tracking-wider text-xs">Navigasi</h4>
                <ul class="space-y-3 text-sm">
                    <li><a href="{{ route('appointment.create') }}" class="hover:text-white transition">Buat Janji
                            Temu</a></li>
                    <li><a href="{{ route('treatment.index') }}" class="hover:text-white transition">Treatment</a></li>
                    <li><a href="{{ route('shop.index') }}" class="hover:text-white transition">Produk Skincare</a></li>
                    <li><a href="{{ route('forum.index') }}" class="hover:text-white transition">Forum</a></li>
                    <li><a href="{{ route('pra.create') }}" class="hover:text-white transition">Konsultasi Online</a>
                    </li>
                </ul>
            </div>
            <div>
                <h4 class="text-white font-bold mb-4 uppercase tracking-wider text-xs">Layanan</h4>
                <ul class="space-y-3 text-sm">
                    <li><a href="#" class="hover:text-white transition">Facial Treatment</a></li>
                    <li><a href="#" class="hover:text-white transition">Laser & Light</a></li>
                    <li><a href="#" class="hover:text-white transition">Anti Aging</a></li>
                    <li><a href="#" class="hover:text-white transition">Acne Solution</a></li>
                </ul>
            </div>
            <div>
                <h4 class="text-white font-bold mb-4 uppercase tracking-wider text-xs">Informasi</h4>
                <ul class="space-y-3 text-sm">
                    <li><a href="#" class="hover:text-white transition">Kebijakan Privasi</a></li>
                    <li><a href="#" class="hover:text-white transition">Syarat & Ketentuan</a></li>
                    <li><a href="#" class="hover:text-white transition">FAQ</a></li>
                </ul>
            </div>
        </div>
        <div
            class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 mt-12 pt-8 border-t border-gray-800 text-xs text-center md:text-left flex flex-col md:flex-row justify-between items-center">
            <p>&copy; {{ date('Y') }} Aishi Beauty By Dr.Els. All rights reserved.</p>
        </div>
    </footer>