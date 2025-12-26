<x-guest-layout title="Beranda">
    {{-- Main Container --}}
    <div class="flex flex-col gap-12 pb-12">

        {{-- SECTION 1: HERO (Value Proposition) --}}
        <section class="mx-auto w-full max-w-6xl px-4 pt-8">
            <div class="rounded-3xl border border-slate-200 bg-white p-6 sm:p-10 shadow-sm relative overflow-hidden">
                {{-- Decorative Background Blob --}}
                <div class="absolute -top-20 -right-20 h-64 w-64 rounded-full bg-[#CFAF53]/5 blur-3xl"></div>

                <div class="relative grid gap-10 lg:grid-cols-2 lg:items-center">
                    {{-- Left: Text Content --}}
                    <div>
                        <div class="inline-flex items-center gap-2 rounded-full border border-[#CFAF53]/30 bg-[#CFAF53]/10 px-4 py-2 text-xs font-bold uppercase tracking-widest text-[#8B6F1B]">
                            <span class="relative flex h-2 w-2">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-[#CFAF53] opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-2 w-2 bg-[#8B6F1B]"></span>
                            </span>
                            Klinik & Skincare
                        </div>
                        
                        <h1 class="mt-6 text-4xl font-bold tracking-tight text-slate-900 sm:text-5xl leading-tight">
                            Solusi Kulit Sehat, <br>
                            <span class="text-[#CFAF53]">Terpercaya & Praktis.</span>
                        </h1>
                        
                        <p class="mt-4 text-lg text-slate-600 leading-relaxed max-w-lg">
                            Dapatkan rekomendasi dokter ahli melalui pra-konsultasi online, lalu belanja produk perawatan yang tepat tanpa ribet.
                        </p>

                        <div class="mt-8 flex flex-col sm:flex-row gap-4">
                            @if (\Illuminate\Support\Facades\Route::has('shop.index'))
                                <a href="{{ route('shop.index') }}"
                                   class="inline-flex items-center justify-center rounded-xl bg-[#CFAF53] px-6 py-3.5 text-sm font-bold text-slate-900 shadow-lg shadow-[#CFAF53]/20 transition-all hover:bg-[#CFAF53]/90 hover:-translate-y-0.5">
                                    Mulai Belanja
                                </a>
                            @endif

                            @auth
                                @if (\Illuminate\Support\Facades\Route::has('pra.index'))
                                    <a href="{{ route('pra.index') }}"
                                       class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-6 py-3.5 text-sm font-bold text-slate-700 transition-all hover:border-[#CFAF53] hover:text-[#CFAF53]">
                                        Isi Pra Konsul
                                    </a>
                                @endif
                            @else
                                @if (\Illuminate\Support\Facades\Route::has('login'))
                                    <a href="{{ route('login') }}"
                                       class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-6 py-3.5 text-sm font-bold text-slate-700 transition-all hover:border-[#CFAF53] hover:text-[#CFAF53]">
                                        Login Pasien
                                    </a>
                                @endif
                            @endauth
                        </div>

                        {{-- Trust Signals --}}
                        <div class="mt-8 flex items-center gap-6 border-t border-slate-100 pt-6">
                            <div>
                                <p class="text-2xl font-bold text-slate-900">1k+</p>
                                <p class="text-xs text-slate-500">Pasien Puas</p>
                            </div>
                            <div class="h-8 w-px bg-slate-200"></div>
                            <div>
                                <p class="text-2xl font-bold text-slate-900">100%</p>
                                <p class="text-xs text-slate-500">Produk Ori</p>
                            </div>
                        </div>
                    </div>

                    {{-- Right: Visual Steps --}}
                    <div class="relative">
                        <div class="rounded-3xl border border-[#CFAF53]/20 bg-[#FFFDF5] p-6 sm:p-8">
                            <h3 class="mb-6 text-sm font-bold uppercase tracking-wide text-slate-400">Cara Kerja</h3>
                            <div class="space-y-4">
                                {{-- Step 1 --}}
                                <div class="flex items-start gap-4 rounded-2xl bg-white p-4 shadow-sm border border-slate-100">
                                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-[#CFAF53]/10 text-[#CFAF53] font-bold">1</div>
                                    <div>
                                        <p class="font-bold text-slate-900">Daftar / Login</p>
                                        <p class="text-sm text-slate-500">Buat akun untuk mulai konsultasi.</p>
                                    </div>
                                </div>
                                {{-- Step 2 --}}
                                <div class="flex items-start gap-4 rounded-2xl bg-white p-4 shadow-sm border border-slate-100">
                                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-[#CFAF53]/10 text-[#CFAF53] font-bold">2</div>
                                    <div>
                                        <p class="font-bold text-slate-900">Pra-Konsultasi</p>
                                        <p class="text-sm text-slate-500">Isi form keluhan & upload foto wajah.</p>
                                    </div>
                                </div>
                                {{-- Step 3 --}}
                                <div class="flex items-start gap-4 rounded-2xl bg-white p-4 shadow-sm border border-slate-100">
                                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-[#CFAF53]/10 text-[#CFAF53] font-bold">3</div>
                                    <div>
                                        <p class="font-bold text-slate-900">Dapat Rekomendasi</p>
                                        <p class="text-sm text-slate-500">Dokter akan menyarankan produk terbaik.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- SECTION 2: FEATURED PRODUCTS --}}
        <section id="produk" class="mx-auto w-full max-w-6xl px-4">
            <div class="flex items-end justify-between gap-4 mb-6">
                <div>
                    <h2 class="text-2xl font-bold text-slate-900">Produk Pilihan</h2>
                    <p class="mt-1 text-slate-600">Perawatan kulit terbaik yang paling diminati.</p>
                </div>

                @if (\Illuminate\Support\Facades\Route::has('shop.index'))
                    <a href="{{ route('shop.index') }}" class="group flex items-center gap-1 text-sm font-semibold text-[#CFAF53] hover:text-[#b59b48]">
                        Lihat semua
                        <svg class="w-4 h-4 transition-transform group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" /></svg>
                    </a>
                @endif
            </div>

            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @forelse (($featuredProducts ?? collect()) as $p)
                    <a href="{{ \Illuminate\Support\Facades\Route::has('shop.show') ? route('shop.show', $p) : '#' }}"
                       class="group relative flex flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white transition-all hover:-translate-y-1 hover:shadow-lg hover:shadow-slate-200/50">
                        {{-- Image Aspect Ratio Wrapper --}}
                        <div class="aspect-[4/3] w-full overflow-hidden bg-slate-100 relative">
                            @if (!empty($p?->foto_path))
                                <img src="{{ asset('storage/' . $p->foto_path) }}" 
                                     alt="{{ $p->nama ?? 'Produk' }}" 
                                     class="h-full w-full object-cover transition duration-500 group-hover:scale-105" />
                            @else
                                <div class="flex h-full w-full items-center justify-center text-slate-400">
                                    <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                </div>
                            @endif
                        </div>
                        
                        {{-- Content --}}
                        <div class="flex flex-1 flex-col p-5">
                            <h3 class="font-bold text-slate-900 group-hover:text-[#CFAF53] transition-colors">
                                {{ $p->nama ?? 'Nama Produk' }}
                            </h3>
                            <div class="mt-auto pt-4 flex items-center justify-between">
                                <p class="text-lg font-bold text-[#CFAF53]">
                                    Rp {{ number_format((int)($p->harga ?? 0), 0, ',', '.') }}
                                </p>
                                <span class="rounded-full bg-slate-50 p-2 text-slate-400 group-hover:bg-[#CFAF53] group-hover:text-white transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                                </span>
                            </div>
                        </div>
                    </a>
                @empty
                    <div class="col-span-full rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-10 text-center text-slate-500">
                        <p>Belum ada produk yang ditampilkan.</p>
                    </div>
                @endforelse
            </div>
        </section>

        {{-- SECTION 3: LOCATION (Google Maps & Contact) --}}
        <section class="mx-auto w-full max-w-6xl px-4">
            <div class="grid gap-8 lg:grid-cols-3">
                {{-- Text Info --}}
                <div class="rounded-3xl bg-slate-900 p-8 text-white lg:col-span-1 flex flex-col justify-center">
                    <p class="text-[#CFAF53] font-bold uppercase tracking-widest text-xs mb-2">Lokasi & Kontak</p>
                    <h2 class="text-2xl font-bold mb-4">Kunjungi Klinik</h2>
                    <p class="text-slate-400 mb-6 text-sm leading-relaxed">
                        Kami berlokasi di tempat yang strategis dan nyaman. Datang langsung atau hubungi kami daring.
                    </p>
                    
                    <div class="space-y-5">
                        <div class="flex items-start gap-3">
                            <svg class="w-5 h-5 text-[#CFAF53] mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                            <div>
                                <p class="font-bold text-sm">Aishi Beauty By DR. ELS</p>
                                <p class="text-xs text-slate-500 mt-1">Jl. Nusantara Raya No.99b, Beji, Kecamatan Beji, Kota Depok, Jawa Barat 16421</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-3">
                            <svg class="w-5 h-5 text-[#CFAF53] mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            <div>
                                <p class="font-bold text-sm">Jam Operasional</p>
                                <p class="text-xs text-slate-500 mt-1">Setiap Hari: 09:00 - 18:30 WIB</p>
                            </div>
                        </div>

                        {{-- SOCIAL MEDIA LINKS (NEW ADDITION) --}}
                        <div class="pt-5 mt-2 border-t border-slate-800">
                            <p class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-3">Hubungi Kami</p>
                            <div class="flex gap-3">
                                {{-- WhatsApp --}}
                                <a href="https://wa.me/+6285780050007" target="_blank" class="flex items-center justify-center w-10 h-10 rounded-xl bg-slate-800 text-slate-400 transition-colors hover:bg-slate-700 hover:text-[#CFAF53]">
                                    <span class="sr-only">WhatsApp</span>
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>
                                </a>
                                {{-- Instagram --}}
                                <a href="https://www.instagram.com/aishibeauty_bydr.els/" target="_blank" class="flex items-center justify-center w-10 h-10 rounded-xl bg-slate-800 text-slate-400 transition-colors hover:bg-slate-700 hover:text-[#CFAF53]">
                                    <span class="sr-only">Instagram</span>
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>
                                </a>
                            </div>
                        </div>

                    </div>
                </div>

                {{-- Map Embed --}}
                <div class="lg:col-span-2 h-80 lg:h-auto rounded-3xl overflow-hidden shadow-lg border border-slate-200 relative bg-slate-100">
                    <div class="embed-map-responsive">
                        <div class="embed-map-container">
                            <iframe class="embed-map-frame" 
                                frameborder="0" 
                                scrolling="no" 
                                marginheight="0" 
                                marginwidth="0" 
                                src="https://maps.google.com/maps?q=Aishi+Beauty+by+DR.ELS+Depok&t=&z=15&ie=UTF8&iwloc=&output=embed">
                            </iframe>
                            <a href="https://classicjoy.games" style="font-size:2px!important;color:gray!important;position:absolute;bottom:0;left:0;z-index:1;max-height:1px;overflow:hidden">Retro Games Online</a>
                        </div>
                        <style>
                            .embed-map-responsive{position:relative;text-align:right;width:100%;height:0;padding-bottom:66.66666666666666%;}
                            .embed-map-container{overflow:hidden;background:none!important;width:100%;height:100%;position:absolute;top:0;left:0;}
                            .embed-map-frame{width:100%!important;height:100%!important;position:absolute;top:0;left:0;}
                        </style>
                    </div>
                </div>
            </div>
        </section>

        {{-- SECTION 4: CTA (Call To Action) --}}
        <section class="mx-auto w-full max-w-6xl px-4">
            <div class="relative overflow-hidden rounded-3xl bg-[#CFAF53] px-6 py-12 text-center shadow-xl sm:px-12 sm:py-16">
                {{-- Pattern Background --}}
                <div class="absolute inset-0 opacity-10" style="background-image: radial-gradient(#000 1px, transparent 1px); background-size: 20px 20px;"></div>
                
                <div class="relative z-10 max-w-2xl mx-auto">
                    <h2 class="text-3xl font-bold text-slate-900 sm:text-4xl">Siap untuk Kulit Lebih Sehat?</h2>
                    <p class="mx-auto mt-4 max-w-xl text-lg text-slate-800/80">
                        Jangan tunda lagi. Konsultasikan keluhanmu sekarang dan dapatkan produk yang benar-benar cocok untukmu.
                    </p>
                    <div class="mt-8 flex flex-col items-center justify-center gap-4 sm:flex-row">
                        @auth
                            <a href="{{ route('pra.index') }}" class="w-full sm:w-auto rounded-xl bg-slate-900 px-8 py-4 text-sm font-bold text-white shadow-lg transition hover:bg-slate-800 hover:-translate-y-1">
                                Mulai Konsultasi
                            </a>
                        @else
                            <a href="{{ route('login') }}" class="w-full sm:w-auto rounded-xl bg-slate-900 px-8 py-4 text-sm font-bold text-white shadow-lg transition hover:bg-slate-800 hover:-translate-y-1">
                                Buat Akun Sekarang
                            </a>
                        @endauth
                        
                        <a href="{{ route('shop.index') }}" class="w-full sm:w-auto rounded-xl bg-white/50 px-8 py-4 text-sm font-bold text-slate-900 backdrop-blur-sm transition hover:bg-white/80">
                            Lihat Katalog
                        </a>
                    </div>
                </div>
            </div>
        </section>

    </div>
</x-guest-layout>