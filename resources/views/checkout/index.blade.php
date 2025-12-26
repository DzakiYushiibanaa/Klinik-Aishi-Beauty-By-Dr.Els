<x-guest-layout :title="'Checkout'">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        
        {{-- HEADER --}}
        <div class="mb-8 border-b border-slate-100 pb-4">
            <h1 class="text-3xl font-bold text-slate-900">Checkout Pesanan</h1>
            <p class="text-sm text-slate-500 mt-2">Periksa kembali pesanan Anda sebelum melakukan pembayaran.</p>
        </div>

        {{-- FORM UTAMA --}}
        <form method="POST" action="{{ route('checkout.store') }}" 
              class="grid gap-8 lg:grid-cols-12"
              x-data="{ 
                  deliveryType: 'pickup', // Default: Ambil di Klinik
                  updateTotal() {
                      // Logic javascript simple kalau mau update total real-time (opsional)
                  }
              }">
            @csrf
            
            {{-- Hidden Input: Sumber Order (Sesuai Controller: Default Online) --}}
            <input type="hidden" name="sumber" value="online">

            {{-- KOLOM KIRI: Input Data (8 Kolom) --}}
            <div class="lg:col-span-8 space-y-8">
                
                {{-- 1. INFO PEMESAN (Read Only) --}}
                <section class="bg-white rounded-2xl border border-slate-200 p-6 shadow-[0_4px_20px_rgb(0,0,0,0.03)]">
                    <h2 class="text-lg font-bold text-slate-800 mb-6 flex items-center gap-3">
                        <span class="flex h-8 w-8 items-center justify-center rounded-full bg-[#CFAF53] text-sm font-bold text-slate-900">1</span>
                        Informasi Pemesan
                    </h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-xs font-bold text-slate-500 mb-2 uppercase tracking-wider">Nama Lengkap</label>
                            <input type="text" value="{{ Auth::user()->name }}" class="w-full rounded-xl border-slate-200 bg-slate-50 text-slate-700 font-medium cursor-not-allowed" readonly>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-500 mb-2 uppercase tracking-wider">Nomor WhatsApp</label>
                            <input type="text" value="{{ Auth::user()->phone ?? '-' }}" class="w-full rounded-xl border-slate-200 bg-slate-50 text-slate-700 font-medium cursor-not-allowed" readonly>
                        </div>
                    </div>
                </section>

                {{-- 2. METODE PENGIRIMAN --}}
                <section class="bg-white rounded-2xl border border-slate-200 p-6 shadow-[0_4px_20px_rgb(0,0,0,0.03)]">
                    <h2 class="text-lg font-bold text-slate-800 mb-6 flex items-center gap-3">
                        <span class="flex h-8 w-8 items-center justify-center rounded-full bg-[#CFAF53] text-sm font-bold text-slate-900">2</span>
                        Metode Pengiriman
                    </h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        {{-- Opsi: Ambil di Klinik --}}
                        <label class="relative cursor-pointer group">
                            <input type="radio" name="shipping_method" value="pickup" x-model="deliveryType" class="peer sr-only">
                            <div class="h-full rounded-2xl border-2 border-slate-100 p-5 transition-all hover:border-[#CFAF53]/50 peer-checked:border-[#CFAF53] peer-checked:bg-[#CFAF53]/5 peer-checked:shadow-sm">
                                <div class="flex items-start gap-4">
                                    <div class="rounded-full bg-slate-100 p-3 text-slate-500 peer-checked:bg-[#CFAF53] peer-checked:text-slate-900 transition-colors">
                                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                    </div>
                                    <div>
                                        <p class="font-bold text-slate-800">Ambil di Klinik</p>
                                        <p class="text-xs text-slate-500 mt-1">Datang langsung ke klinik setelah pesanan dikonfirmasi.</p>
                                    </div>
                                </div>
                            </div>
                        </label>

                        {{-- Opsi: Kirim Kurir --}}
                        <label class="relative cursor-pointer group">
                            {{-- Value bisa diubah jadi 'jne', 'jnt' dsb, tapi sementara general 'delivery' --}}
                            <input type="radio" name="shipping_method" value="delivery" x-model="deliveryType" class="peer sr-only">
                            <div class="h-full rounded-2xl border-2 border-slate-100 p-5 transition-all hover:border-[#CFAF53]/50 peer-checked:border-[#CFAF53] peer-checked:bg-[#CFAF53]/5 peer-checked:shadow-sm">
                                <div class="flex items-start gap-4">
                                    <div class="rounded-full bg-slate-100 p-3 text-slate-500 peer-checked:bg-[#CFAF53] peer-checked:text-slate-900 transition-colors">
                                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg>
                                    </div>
                                    <div>
                                        <p class="font-bold text-slate-800">Kirim ke Rumah</p>
                                        <p class="text-xs text-slate-500 mt-1">JNE / J&T / GoSend. Ongkir dihitung manual oleh Admin.</p>
                                    </div>
                                </div>
                            </div>
                        </label>
                    </div>

                    {{-- Form Alamat (Hanya Muncul Jika Pilih Delivery) --}}
                    <div x-show="deliveryType === 'delivery'" x-transition class="mt-6 pt-6 border-t border-slate-100 space-y-4">
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider">Alamat Lengkap Pengiriman</label>
                        {{-- Mengambil alamat dari PasienProfile --}}
                        <textarea name="shipping_address" rows="3" 
                            class="w-full rounded-xl border-slate-300 focus:border-[#CFAF53] focus:ring-[#CFAF53]/20 transition shadow-sm" 
                            placeholder="Nama Jalan, No. Rumah, RT/RW, Kecamatan, Kota, Kode Pos...">{{ Auth::user()->pasienProfile?->alamat }} {{ Auth::user()->pasienProfile?->kota }} {{ Auth::user()->pasienProfile?->provinsi }}</textarea>
                        <p class="text-[10px] text-slate-400 italic">*Pastikan alamat lengkap agar kurir tidak nyasar.</p>
                    </div>
                </section>
            </div>

            {{-- KOLOM KANAN: Ringkasan (4 Kolom) --}}
            <div class="lg:col-span-4">
                <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-[0_8px_30px_rgb(0,0,0,0.04)] sticky top-8">
                    <h3 class="text-lg font-bold text-slate-800 mb-6 pb-4 border-b border-slate-100">Ringkasan Pesanan</h3>

                    {{-- List Item Cart --}}
                    <div class="space-y-5 mb-8 max-h-[400px] overflow-y-auto pr-2 custom-scrollbar">
                        @foreach ($cart as $item)
                            <div class="flex gap-4">
                                {{-- Gambar Produk --}}
                                <div class="h-16 w-16 shrink-0 rounded-xl bg-slate-50 border border-slate-100 overflow-hidden flex items-center justify-center">
                                    {{-- Cek apakah di session cart ada key 'foto_path'. Jika tidak, pakai placeholder --}}
                                    @if(isset($item['foto_path']) && $item['foto_path'])
                                        <img src="{{ asset('storage/' . $item['foto_path']) }}" class="h-full w-full object-cover">
                                    @else
                                        {{-- Icon Placeholder --}}
                                        <svg class="w-6 h-6 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                    @endif
                                </div>
                                
                                <div class="flex-1 min-w-0">
                                    <h4 class="text-sm font-bold text-slate-800 truncate">{{ $item['nama'] }}</h4>
                                    <p class="text-xs text-slate-500 mb-1">Qty: {{ $item['qty'] }} pcs</p>
                                    <p class="text-sm font-semibold text-[#CFAF53]">Rp {{ number_format((int)$item['harga'] * (int)$item['qty'], 0, ',', '.') }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    {{-- Rincian Biaya --}}
                    <div class="space-y-3 pt-6 border-t border-slate-100 text-sm">
                        <div class="flex justify-between text-slate-600">
                            <span>Subtotal Produk</span>
                            <span class="font-medium">Rp {{ number_format((int)$subtotal, 0, ',', '.') }}</span>
                        </div>
                        
                        {{-- Ongkir --}}
                        <div class="flex justify-between text-slate-600">
                            <span>Biaya Pengiriman</span>
                            {{-- Logic Alpine JS untuk ubah teks ongkir --}}
                            <span x-text="deliveryType === 'pickup' ? 'Gratis' : 'Menunggu Admin'" class="font-medium italic text-slate-400"></span>
                        </div>
                    </div>

                    {{-- Grand Total --}}
                    <div class="mt-6 pt-6 border-t border-dashed border-slate-300">
                        <div class="flex justify-between items-end">
                            <span class="text-base font-bold text-slate-800">Total Pembayaran</span>
                            <span class="text-2xl font-bold text-[#CFAF53]">Rp {{ number_format((int)$subtotal, 0, ',', '.') }}</span>
                        </div>
                        <p class="text-[10px] text-right text-slate-400 mt-1" x-show="deliveryType === 'delivery'">*Belum termasuk ongkir</p>
                    </div>

                    {{-- Button Submit --}}
                    <button type="submit" class="w-full mt-6 bg-[#CFAF53] hover:bg-[#b59b48] text-slate-900 font-bold py-4 rounded-xl shadow-lg shadow-[#CFAF53]/20 transition transform hover:-translate-y-1 flex items-center justify-center gap-2">
                        <span>Buat Pesanan</span>
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                    </button>
                    
                    <p class="text-[10px] text-center text-slate-400 mt-4 px-4 leading-relaxed">
                        Dengan melanjutkan, pesanan akan dibuat dengan status <span class="font-bold text-slate-500">Menunggu Pembayaran</span>.
                    </p>
                </div>
            </div>

        </form>
    </div>
</x-guest-layout>