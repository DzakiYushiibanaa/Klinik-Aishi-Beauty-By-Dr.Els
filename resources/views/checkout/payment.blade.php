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
                
                {{-- DETAIL ITEM --}}
                <h3 class="font-bold text-slate-800 mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                    Rincian Pesanan
                </h3>
                
                <div class="space-y-3 mb-6 bg-slate-50 p-4 rounded-xl border border-slate-100">
                    @foreach($order->items as $item)
                    <div class="flex justify-between text-sm group">
                        <div class="flex-1">
                            <span class="text-slate-700 font-medium">{{ $item->description }}</span>
                            <span class="text-slate-400 text-xs ml-1">x{{ $item->qty }}</span>
                        </div>
                        <span class="font-semibold text-slate-600">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</span>
                    </div>
                    @endforeach
                </div>

                <div class="space-y-2 text-sm border-t border-slate-100 pt-4 mb-6">
                    <div class="flex justify-between">
                        <span class="text-slate-500">Subtotal Produk</span>
                        <span class="font-medium text-slate-700">Rp {{ number_format($order->subtotal, 0, ',', '.') }}</span>
                    </div>

                    @if($order->diskon_total > 0)
                    <div class="flex justify-between text-green-600">
                        <span>Diskon</span>
                        <span>- Rp {{ number_format($order->diskon_total, 0, ',', '.') }}</span>
                    </div>
                    @endif

                    {{-- ONGKIR: Hanya tampil jika BUKAN membership --}}
                    @if($order->order_type !== 'membership')
                    <div class="flex justify-between">
                        <span class="text-slate-500">Biaya Pengiriman</span>
                        <span class="font-medium text-slate-700">
                            @if($order->ongkir > 0)
                                Rp {{ number_format($order->ongkir, 0, ',', '.') }}
                            @else
                                <span class="text-green-600">Gratis Ongkir</span>
                            @endif
                        </span>
                    </div>
                    @endif
                    
                    <div class="flex justify-between pt-2 border-t border-slate-100 mt-2 text-base">
                        <span class="font-bold text-slate-800">Grand Total</span>
                        <span class="font-bold text-[#CFAF53]">Rp {{ number_format($order->grand_total, 0, ',', '.') }}</span>
                    </div>
                </div>

                {{-- ALAMAT PENGIRIMAN (Hanya Tampil Jika BUKAN Membership) --}}
                @if($order->order_type !== 'membership' && $order->shipping_address)
                <div class="mb-8">
                    <h3 class="font-bold text-slate-800 mb-2 flex items-center gap-2">
                        <svg class="w-5 h-5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                        Dikirim ke:
                    </h3>
                    <div class="bg-blue-50 p-4 rounded-xl border border-blue-100 text-sm text-blue-900 leading-relaxed">
                        {{ $order->shipping_address }}
                        @if($order->shipping_method)
                        <div class="mt-2 text-xs font-semibold bg-blue-200 text-blue-800 inline-block px-2 py-1 rounded">
                            Kurir: {{ strtoupper($order->shipping_method) }}
                        </div>
                        @endif
                    </div>
                </div>
                @endif

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

    {{-- SCRIPT MIDTRANS --}}
    <script src="https://app.{{ config('midtrans.is_production') ? '' : 'sandbox.' }}midtrans.com/snap/snap.js" 
            data-client-key="{{ config('midtrans.client_key') }}"></script>

    <script type="text/javascript">
        const payButton = document.getElementById('pay-button');
        
        payButton.addEventListener('click', function () {
            // Disable tombol biar ga diklik 2x
            payButton.disabled = true;
            payButton.innerHTML = '⏳ Memproses...';
            payButton.classList.add('opacity-75', 'cursor-not-allowed');

            window.snap.pay('{{ $snapToken }}', {
                onSuccess: function(result) {
                    console.log('Payment success', result);
                    window.location.href = "{{ route('payment.success') }}?order_id={{ $order->kode_order }}";
                },
                onPending: function(result) {
                    console.log('Payment pending', result);
                    window.location.href = "{{ route('payment.unfinish') }}?order_id={{ $order->kode_order }}";
                },
                onError: function(result) {
                    console.log('Payment error', result);
                    window.location.href = "{{ route('payment.error') }}?order_id={{ $order->kode_order }}";
                },
                onClose: function() {
                    console.log('Popup closed');
                    window.showToast && window.showToast('info', 'Anda menutup popup pembayaran. Silakan lanjutkan transaksi kapan saja dari halaman pesanan.', 'Pembayaran Dibatalkan');
                    payButton.disabled = false;
                    payButton.innerHTML = '<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg> Bayar Sekarang';
                    payButton.classList.remove('opacity-75', 'cursor-not-allowed');
                }
            });
        });
    </script>
</x-guest-layout>
