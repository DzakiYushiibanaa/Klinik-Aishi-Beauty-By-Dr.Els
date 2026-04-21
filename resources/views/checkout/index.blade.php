<x-guest-layout :title="'Checkout'">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <h1 class="text-3xl font-bold text-slate-900 mb-8">Checkout Pesanan</h1>

        {{-- CEK: Apakah user sudah lengkapi alamat? --}}
        @if(!$pasien || !$pasien->district_id)
            {{-- BELUM LENGKAP: Tampilkan warning & redirect --}}
            <div class="bg-rose-50 border-2 border-rose-300 rounded-2xl p-8 text-center max-w-2xl mx-auto">
                <svg class="w-16 h-16 text-rose-500 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                </svg>
                <h2 class="text-2xl font-bold text-rose-800 mb-3">Alamat Belum Lengkap!</h2>
                <p class="text-rose-700 mb-6">
                    Anda perlu melengkapi alamat pengiriman (termasuk <strong>Provinsi, Kota, dan Kecamatan</strong>) 
                    di profil sebelum melanjutkan ke proses checkout.
                </p>
                <a href="{{ route('dashboard') }}" 
                   class="inline-flex items-center gap-2 bg-[#CFAF53] text-white px-8 py-4 rounded-xl font-bold hover:bg-yellow-600 transition shadow-lg">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                    Lengkapi Profil Sekarang
                </a>
            </div>
        @else
            {{-- SUDAH LENGKAP: Lanjut checkout --}}
            <form method="POST" action="{{ route('checkout.store') }}" 
                  class="grid gap-8 lg:grid-cols-12"
                  x-data="checkoutData()">
                @csrf
                
                {{-- Hidden Inputs untuk Controller --}}
                <input type="hidden" name="shipping_cost" x-model="selectedShippingCost">
                <input type="hidden" name="shipping_courier" x-model="selectedShippingCourier">
                <input type="hidden" name="district_id" value="{{ $pasien->district_id }}">
                
                {{-- KOLOM KIRI --}}
                <div class="lg:col-span-8 space-y-8">
                    
                    {{-- INFO PENGIRIMAN & ALAMAT --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        {{-- Info Dikirim Dari (Origin) --}}
                        <div class="bg-blue-50 border border-blue-200 rounded-xl p-4">
                            <div class="flex items-start gap-3">
                                <svg class="w-5 h-5 text-blue-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                </svg>
                                <div class="flex-1">
                                    <p class="text-xs font-bold text-blue-800 uppercase mb-1">📦 Dikirim Dari</p>
                                    <p class="text-sm font-bold text-blue-900">{{ config('app.clinic_name', 'Klinik Kecantikan') }}</p>
                                    <p class="text-xs text-blue-600 mt-1">{{ config('app.clinic_district', 'Depok') }}, {{ config('app.clinic_city', 'Kota Depok') }}</p>
                                </div>
                            </div>
                        </div>

                        {{-- Info Alamat Lengkap --}}
                        <div class="bg-emerald-50 border border-emerald-200 rounded-xl p-4">
                            <div class="flex items-start gap-3">
                                <svg class="w-5 h-5 text-emerald-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <div class="flex-1">
                                    <p class="text-xs font-bold text-emerald-800 uppercase mb-1">✅ Alamat Sudah Lengkap</p>
                                    <p class="text-sm text-emerald-700">Ongkir dihitung otomatis dari profil Anda</p>
                                    <a href="{{ route('dashboard') }}" class="text-xs text-emerald-600 underline font-medium mt-1 inline-block">
                                        Update Profil →
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- ALAMAT PENGIRIMAN (READ-ONLY) --}}
                    <section class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm">
                        <div class="flex justify-between items-center mb-6">
                            <h2 class="text-lg font-bold text-slate-800">📍 Alamat Pengiriman</h2>
                            <a href="{{ route('dashboard') }}" 
                               class="text-xs font-medium text-[#CFAF53] hover:text-yellow-600 flex items-center gap-1">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                </svg>
                                Edit Alamat
                            </a>
                        </div>

                        <div class="bg-slate-50 rounded-xl p-5 space-y-3">
                            <div class="flex items-start gap-3">
                                <svg class="w-5 h-5 text-slate-400 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                                <div>
                                    <p class="text-sm font-bold text-slate-800">{{ auth()->user()->name }}</p>
                                    <p class="text-xs text-slate-500">{{ auth()->user()->phone ?? 'No HP belum diisi' }}</p>
                                </div>
                            </div>

                            <div class="flex items-start gap-3">
                                <svg class="w-5 h-5 text-slate-400 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                                <div class="text-sm text-slate-700 leading-relaxed">
                                    <p class="font-medium">{{ $pasien->alamat }}</p>
                                    <p class="text-slate-500 mt-1">
                                        {{ $pasien->district_name }}, {{ $pasien->kota }}, {{ $pasien->provinsi }}
                                        @if($pasien->kode_pos) - {{ $pasien->kode_pos }} @endif
                                    </p>
                                </div>
                            </div>
                        </div>
                    </section>

                    {{-- PILIHAN KURIR (Auto Load) --}}
                    <section class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm">
                        <div class="flex items-center justify-between mb-4">
                            <h2 class="text-lg font-bold text-slate-800">🚚 Pilih Kurir Pengiriman</h2>
                            <div class="text-xs text-slate-400 flex items-center gap-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <span>Estimasi: <span x-text="costs.length"></span> opsi tersedia</span>
                            </div>
                        </div>

                        {{-- Info Rute Pengiriman --}}
                        <div class="bg-slate-50 border border-slate-200 rounded-lg p-3 mb-4 flex items-center gap-3">
                            <div class="flex items-center gap-2 flex-1">
                                <div class="flex items-center gap-1 text-xs">
                                    <span class="font-semibold text-slate-700">{{ config('app.clinic_district', 'Depok') }}</span>
                                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path>
                                    </svg>
                                    <span class="font-semibold text-slate-700">{{ $pasien->district_name ?? 'Tujuan' }}</span>
                                </div>
                            </div>
                            <div class="text-xs text-slate-500">
                                Total Berat: <span class="font-semibold" x-data x-text="'~1 kg'"></span>
                            </div>
                        </div>

                        {{-- LOADING STATE --}}
                        <div x-show="isLoadingOngkir" class="flex flex-col items-center justify-center py-12">
                            <div class="animate-spin rounded-full h-12 w-12 border-4 border-yellow-500 border-t-transparent mb-4"></div>
                            <p class="text-sm text-slate-600 font-medium">Menghitung ongkir...</p>
                            <p class="text-xs text-slate-400 mt-1">Mohon tunggu sebentar</p>
                        </div>

                        {{-- ERROR STATE --}}
                        <div x-show="ongkirError && !isLoadingOngkir" class="bg-rose-50 border border-rose-200 rounded-xl p-4 text-center">
                            <svg class="w-8 h-8 text-rose-500 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <p class="text-sm text-rose-700 font-medium">Gagal menghitung ongkir</p>
                            <p class="text-xs text-rose-600 mt-1" x-text="ongkirError"></p>
                            <button type="button" @click="checkOngkir()" class="mt-3 text-xs text-rose-700 underline font-medium">
                                Coba Lagi
                            </button>
                        </div>

                        {{-- LIST KURIR --}}
                        <div x-show="costs.length > 0 && !isLoadingOngkir" x-cloak class="space-y-3">
                            <template x-for="(cost, index) in costs" :key="index">
                                <label class="flex justify-between items-center p-4 border-2 rounded-xl cursor-pointer hover:border-yellow-500 transition group"
                                       :class="selectedShippingCourier === (cost.courier_code + ' ' + cost.service) ? 'border-yellow-500 bg-yellow-50 shadow-md' : 'border-slate-200'">
                                    <div class="flex items-center gap-4 flex-1">
                                        <input type="radio" 
                                               name="courier_radio" 
                                               @change="selectCourier(cost.cost, cost.courier_code + ' ' + cost.service)"
                                               :checked="selectedShippingCourier === (cost.courier_code + ' ' + cost.service)"
                                               class="text-yellow-500 focus:ring-yellow-500 w-5 h-5">
                                        
                                        <div class="flex-1">
                                            <div class="font-bold text-base text-slate-900 mb-0.5" x-text="cost.courier_code.toUpperCase()"></div>
                                            <div class="text-sm text-slate-600" x-text="cost.service"></div>
                                            <div class="text-xs text-slate-400 mt-1" x-text="'Estimasi: ' + cost.etd"></div>
                                        </div>
                                    </div>
                                    <div class="font-bold text-lg text-yellow-600" x-text="'Rp ' + parseInt(cost.cost).toLocaleString('id-ID')"></div>
                                </label>
                            </template>
                        </div>

                        {{-- EMPTY STATE --}}
                        <div x-show="costs.length === 0 && !isLoadingOngkir && !ongkirError" x-cloak 
                             class="bg-slate-50 rounded-xl p-8 text-center border-2 border-dashed border-slate-200">
                            <svg class="w-12 h-12 text-slate-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                            </svg>
                            <p class="text-sm text-slate-500">Belum ada pilihan kurir</p>
                        </div>
                    </section>

                    {{-- CATATAN PENGIRIMAN --}}
                    <section class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm">
                        <h2 class="text-lg font-bold text-slate-800 mb-4">📝 Catatan Pengiriman (Opsional)</h2>
                        <textarea name="shipping_notes" 
                                  rows="3" 
                                  placeholder="Contoh: Titip di satpam, pagar hitam, rumah pojok..." 
                                  class="w-full rounded-xl border-slate-300 focus:border-yellow-500 focus:ring-yellow-500 text-sm"></textarea>
                        <p class="text-xs text-slate-400 mt-2">💡 Catatan ini akan membantu kurir menemukan alamat Anda dengan lebih mudah</p>
                    </section>
                </div>

                {{-- KOLOM KANAN (Ringkasan Pesanan) --}}
                {{-- KOLOM KANAN (Ringkasan Pesanan + PROMO) --}}
                <div class="lg:col-span-4">
                    <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-lg sticky top-8">
                        <h3 class="font-bold text-lg mb-4 flex items-center gap-2">
                            <svg class="w-5 h-5 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                            </svg>
                            Ringkasan Pesanan
                        </h3>
                        
                        {{-- Daftar Item --}}
                        <div class="space-y-2.5 mb-4 max-h-64 overflow-y-auto pr-2 custom-scrollbar">
                            @foreach($cart as $item)
                            <div class="flex justify-between text-sm bg-slate-50 p-2.5 rounded-lg">
                                <span class="text-slate-700 font-medium">
                                    {{ $item['nama'] }} 
                                    <span class="text-xs text-slate-400 ml-1">(x{{ $item['qty'] }})</span>
                                </span>
                                <span class="font-bold text-slate-900">Rp {{ number_format($item['harga'] * $item['qty'], 0, ',', '.') }}</span>
                            </div>
                            @endforeach
                        </div>

                        <hr class="my-4 border-slate-200">

                        {{-- [BARU] INPUT KODE PROMO --}}
                        <div class="mb-4">
                            <label class="text-xs font-bold text-slate-500 uppercase mb-1 block">Punya Kode Promo?</label>
                            <div class="flex gap-2">
                                <input type="text" 
                                       x-model="promoCodeInput"
                                       placeholder="Masukan kode" 
                                       class="w-full rounded-lg border-slate-300 text-sm focus:border-yellow-500 focus:ring-yellow-500 uppercase"
                                       :disabled="appliedPromoCode !== null"> {{-- Disable kalau sudah apply --}}
                                
                                {{-- Tombol Apply --}}
                                <button type="button" 
                                        @click="checkPromo()"
                                        x-show="!appliedPromoCode"
                                        class="bg-slate-800 text-white px-4 py-2 rounded-lg text-sm font-bold hover:bg-slate-700 transition disabled:opacity-50"
                                        :disabled="!promoCodeInput || isLoadingPromo">
                                    <span x-show="!isLoadingPromo">Pakai</span>
                                    <span x-show="isLoadingPromo">...</span>
                                </button>

                                {{-- Tombol Cancel (Muncul kalau sudah apply) --}}
                                <button type="button" 
                                        @click="cancelPromo()"
                                        x-show="appliedPromoCode"
                                        class="bg-rose-100 text-rose-600 px-3 py-2 rounded-lg hover:bg-rose-200 transition">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                </button>
                            </div>

                            {{-- Pesan Error/Sukses Promo --}}
                            <p x-show="promoMessage" 
                               class="text-xs mt-2 font-medium" 
                               :class="promoValid ? 'text-emerald-600' : 'text-rose-600'" 
                               x-text="promoMessage"></p>
                            
                            {{-- Input Hidden buat dikirim ke Controller --}}
                            <input type="hidden" name="promo_code" x-model="appliedPromoCode">
                        </div>
                        
                        <hr class="my-4 border-slate-200">

                        {{-- Subtotal --}}
                        <div class="flex justify-between mb-2 text-sm">
                            <span class="text-slate-600">Subtotal Produk</span>
                            <span class="font-semibold text-slate-900">Rp {{ number_format($subtotal, 0, ',', '.') }}</span>
                        </div>

                        {{-- Ongkir --}}
                        <div class="flex justify-between mb-2 text-sm">
                            <span class="text-slate-600">Ongkos Kirim</span>
                            <span class="font-semibold text-slate-900" x-text="selectedShippingCost > 0 ? 'Rp ' + parseInt(selectedShippingCost).toLocaleString('id-ID') : '-'"></span>
                        </div>

                        {{-- [BARU] Diskon Row --}}
                        <div class="flex justify-between mb-4 text-sm" x-show="discountAmount > 0" x-transition>
                            <span class="text-emerald-600 font-bold flex items-center gap-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path></svg>
                                Diskon Promo
                            </span>
                            <span class="font-bold text-emerald-600" x-text="'- Rp ' + parseInt(discountAmount).toLocaleString('id-ID')"></span>
                        </div>

                        <hr class="mb-4 border-slate-200">

                        {{-- Grand Total --}}
                        <div class="flex justify-between mb-6 text-lg">
                            <span class="font-bold text-slate-800">Total Bayar</span>
                            {{-- Rumus JS: Subtotal + Ongkir - Diskon --}}
                            <span class="font-bold text-yellow-600" x-text="'Rp ' + ({{ $subtotal }} + parseInt(selectedShippingCost || 0) - parseInt(discountAmount || 0)).toLocaleString('id-ID')"></span>
                        </div>

                        {{-- TOMBOL SUBMIT --}}
                        <button type="submit" 
                                class="w-full bg-[#CFAF53] text-white font-bold py-4 rounded-xl hover:bg-yellow-600 transition shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none"
                                :disabled="isLoadingOngkir || !selectedShippingCourier">
                            <span x-show="!isLoadingOngkir">
                                <span x-show="selectedShippingCourier">🛒 Buat Pesanan</span>
                                <span x-show="!selectedShippingCourier">⏳ Pilih Kurir Dulu</span>
                            </span>
                            <span x-show="isLoadingOngkir">Memproses...</span>
                        </button>

                        <p class="text-xs text-slate-400 text-center mt-3">
                            Dengan melanjutkan, Anda menyetujui syarat dan ketentuan kami
                        </p>
                    </div>
                </div>
            </form>
        @endif
    </div>

    {{-- ALPINE.JS SCRIPT --}}
    <script>
        function checkoutData() {
            return {
                // Data Ongkir
                costs: [],
                selectedShippingCost: 0,
                selectedShippingCourier: '',
                isLoadingOngkir: false,
                ongkirError: '',

                // [BARU] Data Promo
                promoCodeInput: '', // Apa yang diketik user
                appliedPromoCode: null, // Kode yang valid dan tersimpan
                discountAmount: 0, // Nominal diskon
                isLoadingPromo: false,
                promoMessage: '',
                promoValid: false,
                
                // Static Subtotal dari PHP
                subtotalDB: {{ $subtotal }}, 

                init() {
                    console.log('🚀 Checkout initialized');
                    @if($pasien && $pasien->district_id)
                        this.checkOngkir();
                    @endif
                },

                // --- FUNGSI ONGKIR (SAMA) ---
                async checkOngkir() {
                    const districtId = '{{ $pasien->district_id ?? "" }}';
                    if (!districtId) return;

                    this.isLoadingOngkir = true;
                    this.ongkirError = '';
                    this.costs = [];
                    
                    try {
                        const response = await fetch("{{ route('api.checkOngkir') }}", {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                            body: JSON.stringify({ district_id: districtId })
                        });

                        if (!response.ok) throw new Error('Gagal load ongkir');
                        const data = await response.json();
                        
                        if (Array.isArray(data) && data.length > 0) {
                            this.costs = data;
                        } else {
                            this.ongkirError = 'Kurir tidak tersedia';
                        }
                    } catch (error) {
                        this.ongkirError = 'Gagal menghitung ongkir';
                    } finally {
                        this.isLoadingOngkir = false;
                    }
                },

                selectCourier(price, name) {
                    this.selectedShippingCost = price;
                    this.selectedShippingCourier = name;
                },

                // --- [BARU] FUNGSI PROMO ---
                async checkPromo() {
                    if(!this.promoCodeInput) return;

                    this.isLoadingPromo = true;
                    this.promoMessage = '';
                    
                    try {
                        const response = await fetch("{{ route('api.checkPromo') }}", {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                            // Kirim kode dan subtotal (buat validasi min pembelian)
                            body: JSON.stringify({ 
                                kode: this.promoCodeInput,
                                subtotal: this.subtotalDB 
                            })
                        });

                        const data = await response.json();

                        if (data.valid) {
                            // Sukses
                            this.promoValid = true;
                            this.promoMessage = data.message;
                            this.discountAmount = data.discount_amount;
                            this.appliedPromoCode = data.kode; // Kunci kode agar tidak bisa diedit
                        } else {
                            // Gagal (Syarat tidak penuhi / Salah kode)
                            this.promoValid = false;
                            this.promoMessage = data.message;
                            this.discountAmount = 0;
                            this.appliedPromoCode = null;
                        }
                    } catch (error) {
                        this.promoValid = false;
                        this.promoMessage = 'Terjadi kesalahan sistem.';
                    } finally {
                        this.isLoadingPromo = false;
                    }
                },

                cancelPromo() {
                    this.promoCodeInput = '';
                    this.appliedPromoCode = null;
                    this.discountAmount = 0;
                    this.promoMessage = '';
                    this.promoValid = false;
                }
            }
        }
    </script>

    {{-- Custom Styles --}}
    <style>
        [x-cloak] { display: none !important; }
    </style>
</x-guest-layout>