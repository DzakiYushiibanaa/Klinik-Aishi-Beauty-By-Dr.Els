<x-guest-layout title="Dashboard">
    
    {{-- BACKGROUND ACCENT --}}
    <div class="absolute top-0 left-0 w-full h-64 bg-[#CFAF53]/10 -z-10"></div>

    {{-- VALIDASI ERROR CHECKER (Biar ketahuan kalau ada error) --}}
    @if ($errors->any())
        <div class="max-w-6xl mx-auto px-4 mt-4">
            <div class="bg-rose-100 border border-rose-400 text-rose-700 px-4 py-3 rounded relative">
                <strong class="font-bold">Gagal Menyimpan!</strong>
                <ul class="list-disc list-inside text-sm">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    <div class="mx-auto max-w-6xl px-4 py-8" x-data="{ activeTab: 'appointment' }">
        
        {{-- 1. HEADER PROFILE (REVISI TAMPILAN) --}}
        <div class="bg-white rounded-3xl p-6 md:p-8 shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-slate-100 mb-10 relative overflow-hidden">
            
            {{-- Dekorasi Background Abstrak (Opsional, biar cantik) --}}
            <div class="absolute top-0 right-0 w-64 h-64 bg-[#CFAF53]/5 rounded-full blur-3xl -mr-16 -mt-16 pointer-events-none"></div>

            <div class="flex flex-col md:flex-row items-center gap-6 relative z-10">
                
                {{-- FOTO PROFIL --}}
                <div class="shrink-0 relative group">
                    <div class="h-24 w-24 md:h-28 md:w-28 rounded-full bg-slate-50 border-4 border-white shadow-lg flex items-center justify-center text-3xl font-bold text-[#CFAF53] overflow-hidden">
                        @if($user->pasienProfile && $user->pasienProfile->foto_profil_path)
                            <img src="{{ asset('storage/' . $user->pasienProfile->foto_profil_path) }}" class="h-full w-full object-cover transition transform group-hover:scale-105 duration-500">
                        @else
                            {{ substr($user->name, 0, 1) }}
                        @endif
                    </div>
                    
                    {{-- Badge Member (Posisi Melayang di Foto) --}}
                    @if($user->pasienProfile?->is_member)
                        <div class="absolute -bottom-2 -right-2 bg-gradient-to-r from-yellow-400 to-[#CFAF53] text-white text-[10px] font-bold px-3 py-1 rounded-full shadow-md border-2 border-white flex items-center gap-1">
                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                            MEMBER
                        </div>
                    @endif
                </div>

                {{-- INFO TEKS --}}
                <div class="flex-1 text-center md:text-left space-y-2">
                    <div>
                        <p class="text-sm text-slate-400 font-medium tracking-wide uppercase mb-1">Selamat Datang,</p>
                        <h1 class="text-2xl md:text-4xl font-extrabold text-slate-800 tracking-tight leading-tight">
                            {{ $user->name }}
                        </h1>
                    </div>
                    
                    {{-- Baris Metadata (Email & RM) - Dibuat Badge biar rapi --}}
                    <div class="flex flex-wrap items-center justify-center md:justify-start gap-3 mt-2">
                        
                        {{-- Email Badge --}}
                        <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-slate-50 border border-slate-200 text-slate-500 text-xs font-medium">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                            {{ $user->email }}
                        </div>

                        {{-- RM Badge (Highlight Warna Emas) --}}
                        @if($user->pasienProfile?->no_rekam_medis)
                            <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-[#CFAF53]/10 border border-[#CFAF53]/30 text-[#8B6F1B] text-xs font-bold tracking-wide">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"/></svg>
                                RM: {{ $user->pasienProfile->no_rekam_medis }}
                            </div>
                        @else
                            <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-slate-100 border border-slate-200 text-slate-400 text-xs font-medium">
                                Belum ada RM
                            </div>
                        @endif
                    </div>
                </div>

                {{-- TOMBOL ACTION (Di Desktop geser ke kanan) --}}
                <div class="mt-2 md:mt-0">
                    <a href="{{ route('appointment.create') }}" class="group flex items-center gap-3 rounded-2xl bg-[#CFAF53] px-8 py-4 text-sm font-bold text-slate-900 shadow-xl shadow-[#CFAF53]/20 transition transform hover:bg-[#b59b48] hover:-translate-y-1 hover:shadow-2xl">
                        <span class="flex flex-col items-start leading-none">
                            <span class="text-[10px] opacity-70 font-normal mb-1">Butuh Perawatan?</span>
                            <span>Buat Janji Baru</span>
                        </span>
                        <div class="bg-black/10 rounded-full p-1 group-hover:translate-x-1 transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        </div>
                    </a>
                </div>

            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
            
            {{-- 2. SIDEBAR MENU --}}
            <div class="lg:col-span-1">
                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden sticky top-6">
                    <nav class="flex flex-col p-2 space-y-1">
                        <button @click="activeTab = 'appointment'" 
                            :class="activeTab === 'appointment' ? 'bg-[#CFAF53]/10 text-[#8B6F1B] font-bold' : 'text-slate-600 hover:bg-slate-50'"
                            class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all text-sm text-left">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            Riwayat Janji Temu
                        </button>

                        <button @click="activeTab = 'prakonsul'" 
                            :class="activeTab === 'prakonsul' ? 'bg-[#CFAF53]/10 text-[#8B6F1B] font-bold' : 'text-slate-600 hover:bg-slate-50'"
                            class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all text-sm text-left">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                            Riwayat Pra-Konsul
                        </button>

                        <button @click="activeTab = 'orders'" 
                            :class="activeTab === 'orders' ? 'bg-[#CFAF53]/10 text-[#8B6F1B] font-bold' : 'text-slate-600 hover:bg-slate-50'"
                            class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all text-sm text-left">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                            Pesanan Produk
                        </button>

                        <div class="my-2 border-t border-slate-50"></div>

                        <button @click="activeTab = 'profile'" 
                            :class="activeTab === 'profile' ? 'bg-[#CFAF53]/10 text-[#8B6F1B] font-bold' : 'text-slate-600 hover:bg-slate-50'"
                            class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all text-sm text-left">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                            Profil Saya
                        </button>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="w-full flex items-center gap-3 px-4 py-3 rounded-xl text-rose-600 hover:bg-rose-50 transition-all text-sm text-left font-medium">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                                Keluar Aplikasi
                            </button>
                        </form>
                    </nav>
                </div>
            </div>

            {{-- 3. CONTENT AREA --}}
            <div class="lg:col-span-3">
                
                {{-- TAB 1: APPOINTMENTS --}}
                <div x-show="activeTab === 'appointment'" x-transition.opacity.duration.300ms>
                    <h2 class="text-xl font-bold text-slate-800 mb-4 flex items-center gap-2">📅 Janji Temu Saya</h2>
                    @if($appointments->isEmpty())
                        <div class="bg-white rounded-2xl border border-dashed border-slate-300 p-10 text-center">
                            <p class="text-slate-500 text-sm">Belum ada janji temu yang dibuat.</p>
                        </div>
                    @else
                        <div class="space-y-4">
                            @foreach($appointments as $apt)
                                <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm hover:shadow-md transition">
                                    <div class="flex flex-col md:flex-row justify-between gap-4">
                                        <div class="flex items-start gap-4">
                                            <div class="flex flex-col items-center justify-center bg-[#CFAF53]/10 text-[#8B6F1B] w-14 h-14 rounded-xl shrink-0">
                                                <span class="text-xs font-bold uppercase">{{ $apt->tanggal_jam->format('M') }}</span>
                                                <span class="text-xl font-bold">{{ $apt->tanggal_jam->format('d') }}</span>
                                            </div>
                                            <div>
                                                <p class="font-bold text-slate-900 text-lg">{{ ucfirst($apt->jenis) }}</p>
                                                <p class="text-sm text-slate-500 flex items-center gap-1 mt-1">{{ $apt->tanggal_jam->format('H:i') }} WIB</p>
                                                <p class="text-xs text-slate-400 mt-1">
                                                    Dokter: {{ $apt->dokter->name ?? '-' }} 
                                                    @if($apt->terapis) | Terapis: {{ $apt->terapis->name }} @endif
                                                </p>
                                            </div>
                                        </div>
                                        <div class="flex flex-col items-end justify-center gap-2">
                                            @php
                                                $statusClass = match($apt->status) {
                                                    'diajukan' => 'bg-yellow-100 text-yellow-700',
                                                    'disetujui' => 'bg-blue-100 text-blue-700',
                                                    'selesai' => 'bg-emerald-100 text-emerald-700',
                                                    'batal' => 'bg-rose-100 text-rose-700',
                                                    default => 'bg-slate-100 text-slate-700'
                                                };
                                            @endphp
                                            <span class="px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wide {{ $statusClass }}">{{ $apt->status }}</span>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- TAB 2: PRA KONSUL --}}
                <div x-show="activeTab === 'prakonsul'" x-transition.opacity.duration.300ms style="display: none;">
                    <h2 class="text-xl font-bold text-slate-800 mb-4 flex items-center gap-2">📋 Riwayat Pra-Konsul</h2>
                    @if($praKonsuls->isEmpty())
                        <div class="bg-white rounded-2xl border border-dashed border-slate-300 p-10 text-center">
                            <p class="text-slate-500 text-sm">Belum ada data pra-konsultasi.</p>
                            <a href="{{ route('pra.index') }}" class="text-[#CFAF53] font-bold text-sm mt-2 inline-block">Isi Formulir Sekarang</a>
                        </div>
                    @else
                        <div class="grid gap-4">
                            @foreach($praKonsuls as $form)
                                <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm">
                                    <div class="flex justify-between items-start">
                                        <div>
                                            <p class="text-xs text-slate-400">ID: #PK-{{ $form->patient_form_id }} • {{ $form->created_at->format('d M Y') }}</p>
                                            <p class="font-bold text-slate-900 mt-1 line-clamp-1">{{ $form->keluhan_utama ?? 'Tidak ada judul' }}</p>
                                        </div>
                                        <span class="px-2 py-1 rounded text-[10px] font-bold bg-slate-100 text-slate-600 uppercase">{{ $form->status ?? 'Submitted' }}</span>
                                    </div>
                                    <div class="mt-4 pt-4 border-t border-slate-50 flex justify-between items-center">
                                        <p class="text-xs text-slate-500">Dokter: {{ $form->dokter->name ?? 'Menunggu...' }}</p>
                                        <a href="{{ route('pra.show', $form->patient_form_id) }}" class="text-sm font-bold text-[#CFAF53] hover:text-[#8B6F1B]">Lihat Detail &rarr;</a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- TAB 3: ORDERS --}}
                <div x-show="activeTab === 'orders'" x-transition.opacity.duration.300ms style="display: none;">
                    <h2 class="text-xl font-bold text-slate-800 mb-4 flex items-center gap-2">🛍️ Pesanan Produk</h2>
                    @if(empty($orders))
                        <div class="bg-white rounded-2xl border border-dashed border-slate-300 p-10 text-center">
                            <div class="mx-auto w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center mb-3 text-slate-300">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                            </div>
                            <p class="text-slate-500 text-sm">Belum ada riwayat pesanan.</p>
                            <a href="{{ route('shop.index') }}" class="text-[#CFAF53] font-bold text-sm mt-2 inline-block">Mulai Belanja</a>
                        </div>
                    @endif
                </div>

                {{-- TAB 4: PROFIL SAYA (FIXED) --}}
                <div x-show="activeTab === 'profile'" x-transition.opacity.duration.300ms style="display: none;">
                    <h2 class="text-xl font-bold text-slate-800 mb-4 flex items-center gap-2">
                        ⚙️ Edit Profil Saya
                    </h2>

                    <form action="{{ route('pasien.profile.update') }}" method="POST" enctype="multipart/form-data" class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200 shadow-sm">
                        @csrf
                        @method('PATCH')

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            
                            {{-- Data Tabel Users --}}
                            <div class="col-span-2">
                                <label class="block text-xs font-bold text-slate-500 mb-2">Ganti Foto Profil</label>
                                <input type="file" name="foto" class="block w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-[#CFAF53]/10 file:text-[#CFAF53] hover:file:bg-[#CFAF53]/20"/>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-500 mb-1">Nama Lengkap</label>
                                <input type="text" name="name" value="{{ old('name', $user->name) }}" class="w-full rounded-xl border-slate-200 text-sm focus:border-[#CFAF53] focus:ring-[#CFAF53]/20" required>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-500 mb-1">Email (Login)</label>
                                {{-- PERBAIKAN DISINI: Saya tambahkan name="email" agar lolos validasi --}}
                                <input type="email" name="email" value="{{ $user->email }}" class="w-full rounded-xl border-slate-200 bg-slate-50 text-slate-400 text-sm cursor-not-allowed" readonly>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-500 mb-1">Nomor WhatsApp</label>
                                <input type="text" name="phone" value="{{ old('phone', $user->phone) }}" class="w-full rounded-xl border-slate-200 text-sm focus:border-[#CFAF53] focus:ring-[#CFAF53]/20" placeholder="081xxx">
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-500 mb-1">Tanggal Lahir</label>
                                <input type="date" name="tanggal_lahir" value="{{ old('tanggal_lahir', $user->tanggal_lahir) }}" class="w-full rounded-xl border-slate-200 text-sm focus:border-[#CFAF53] focus:ring-[#CFAF53]/20">
                            </div>

                            <div class="col-span-2">
                                <label class="block text-xs font-bold text-slate-500 mb-2">Jenis Kelamin</label>
                                <div class="flex gap-4">
                                    <label class="flex items-center gap-2 cursor-pointer">
                                        <input type="radio" name="jenis_kelamin" value="L" {{ old('jenis_kelamin', $user->jenis_kelamin) == 'L' ? 'checked' : '' }} class="text-[#CFAF53] focus:ring-[#CFAF53]">
                                        <span class="text-sm text-slate-700">Laki-laki</span>
                                    </label>
                                    <label class="flex items-center gap-2 cursor-pointer">
                                        <input type="radio" name="jenis_kelamin" value="P" {{ old('jenis_kelamin', $user->jenis_kelamin) == 'P' ? 'checked' : '' }} class="text-[#CFAF53] focus:ring-[#CFAF53]">
                                        <span class="text-sm text-slate-700">Perempuan</span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="border-t border-slate-100 my-6"></div>
                        <h3 class="text-sm font-bold text-slate-400 uppercase tracking-wider mb-4">Alamat & Domisili</h3>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            {{-- Data Tabel Pasien (Gunakan pasienProfile sesuai controller) --}}
                            <div class="col-span-2">
                                <label class="block text-xs font-bold text-slate-500 mb-1">Alamat Jalan</label>
                                <textarea name="alamat" rows="2" class="w-full rounded-xl border-slate-200 text-sm focus:border-[#CFAF53] focus:ring-[#CFAF53]/20" placeholder="Nama jalan, RT/RW, No. Rumah">{{ old('alamat', $user->pasienProfile?->alamat) }}</textarea>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-500 mb-1">Kota</label>
                                <input type="text" name="kota" value="{{ old('kota', $user->pasienProfile?->kota) }}" class="w-full rounded-xl border-slate-200 text-sm focus:border-[#CFAF53] focus:ring-[#CFAF53]/20">
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-500 mb-1">Provinsi</label>
                                <input type="text" name="provinsi" value="{{ old('provinsi', $user->pasienProfile?->provinsi) }}" class="w-full rounded-xl border-slate-200 text-sm focus:border-[#CFAF53] focus:ring-[#CFAF53]/20">
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-500 mb-1">Kode Pos</label>
                                <input type="text" name="kode_pos" value="{{ old('kode_pos', $user->pasienProfile?->kode_pos) }}" class="w-full rounded-xl border-slate-200 text-sm focus:border-[#CFAF53] focus:ring-[#CFAF53]/20">
                            </div>
                        </div>

                        <div class="mt-8 flex justify-end">
                            <button type="submit" class="bg-[#CFAF53] text-slate-900 font-bold px-8 py-3 rounded-xl hover:bg-[#b59b48] shadow-lg transition transform hover:-translate-y-1">
                                Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>

            </div>
        </div>
    </div>
</x-guest-layout>