<x-guest-layout :title="'Pembayaran'">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        
        {{-- HEADER --}}
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-slate-900 mb-2">Selesaikan Pembayaran</h1>
            <div class="inline-block bg-yellow-100 text-yellow-800 px-3 py-1 rounded-full text-sm font-semibold">
                Kode Order: {{ $order->kode_order }}
            </div>
        </div>

        {{-- CARD UTAMA --}}
        <div class="bg-white rounded-2xl border border-slate-200 shadow-xl overflow-hidden">
            
            {{-- Bagian Atas: Total Tagihan --}}
            <div class="bg-slate-50 p-6 text-center border-b border-slate-100">
                <p class="text-slate-500 mb-1 text-sm uppercase tracking-wide">Total yang harus dibayar</p>
                <h2 class="text-4xl font-extrabold text-[#CFAF53]">
                    Rp {{ number_format($order->grand_total, 0, ',', '.') }}
                </h2>
            </div>

            <div class="p-6 md:p-8">
                @include('checkout._partials._payment_order_detail')
                @include('checkout._partials._payment_shipping_address')

                {{-- TOMBOL AKSI --}}
                <div class="flex flex-col gap-3">
                    <button id="pay-button" 
                            class="w-full bg-[#CFAF53] hover:bg-yellow-600 text-white font-bold py-4 rounded-xl shadow-lg shadow-yellow-200 transition-all transform hover:scale-[1.01] flex justify-center items-center gap-2">
                        <span>💳</span> Bayar Sekarang
                    </button>
                    
                    <a href="{{ route('dashboard') }}" 
                       class="text-center py-3 text-sm text-slate-400 hover:text-slate-600 transition">
                        Bayar Nanti (Kembali ke Dashboard)
                    </a>
                </div>

                <div class="mt-6 text-center">
                    <p class="text-xs text-slate-400 flex items-center justify-center gap-1">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                        Pembayaran diamankan oleh Midtrans
                    </p>
                </div>
            </div>
        </div>
    </div>

    @include('checkout._partials._payment_script')
</x-guest-layout>
