<x-guest-layout :title="'Pembayaran'">
    {{-- Background Decoration --}}
    <div class="absolute top-0 left-0 w-full h-64 bg-gradient-to-b from-[#CFAF53]/10 to-transparent -z-10"></div>

    <div class="max-w-xl mx-auto px-4 py-10">
        
        {{-- HEADER ICON --}}
        <div class="flex justify-center mb-6">
            <div class="w-16 h-16 bg-white rounded-full shadow-lg flex items-center justify-center border-4 border-[#CFAF53]/20">
                <svg class="w-8 h-8 text-[#CFAF53]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                </svg>
            </div>
        </div>

        <div class="text-center mb-8">
            <h1 class="text-2xl font-bold text-slate-800">Selesaikan Pembayaran</h1>
            <p class="text-slate-500 text-sm mt-1">Lakukan pembayaran untuk memproses pesanan Anda.</p>
        </div>

        {{-- MAIN CARD --}}
        <div class="bg-white rounded-3xl shadow-xl shadow-slate-200/50 overflow-hidden border border-slate-100 relative">
            
            {{-- Pita Kode Order --}}
            <div class="bg-slate-50 border-b border-slate-100 py-4 px-6 flex justify-between items-center">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Kode Order</span>
                <div class="flex items-center gap-2">
                    <span class="font-mono font-bold text-slate-700 text-lg">#{{ $order->kode_order }}</span>
                    <button onclick="navigator.clipboard.writeText('{{ $order->kode_order }}'); alert('Kode disalin!');" class="text-slate-400 hover:text-[#CFAF53] transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                    </button>
                </div>
            </div>

            <div class="p-6 md:p-8">
                {{-- DETAIL ITEM --}}
                <h3 class="text-sm font-bold text-slate-900 mb-4 flex items-center gap-2">
                    <svg class="w-4 h-4 text-[#CFAF53]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                    Detail Belanja
                </h3>
                
                <div class="space-y-3 mb-6">
                    @foreach($order->items as $item)
                        <div class="flex justify-between items-start text-sm group">
                            <div class="pr-4">
                                <p class="text-slate-700 font-medium group-hover:text-[#CFAF53] transition">{{ $item->description }}</p>
                                <p class="text-xs text-slate-400">x{{ $item->qty }}</p>
                            </div>
                            <span class="font-semibold text-slate-700 whitespace-nowrap">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</span>
                        </div>
                    @endforeach
                </div>

                {{-- ALAMAT (Jika Ada) --}}
                @if($order->shipping_address)
                    <div class="mb-6 p-4 rounded-xl bg-blue-50/50 border border-blue-100">
                        <h3 class="text-xs font-bold text-blue-800 uppercase mb-2 flex items-center gap-1">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            Dikirim Ke
                        </h3>
                        <p class="text-sm text-slate-600 leading-relaxed">{{ $order->shipping_address }}</p>
                        @if($order->shipping_method)
                            <div class="mt-2 inline-flex items-center gap-1 text-[10px] font-bold text-blue-600 bg-blue-100 px-2 py-0.5 rounded">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg>
                                {{ strtoupper($order->shipping_method) }}
                            </div>
                        @endif
                    </div>
                @endif

                {{-- DASHED SEPARATOR --}}
                <div class="border-t border-dashed border-slate-200 my-6 relative">
                    <div class="absolute -left-10 -top-3 w-6 h-6 bg-slate-50 rounded-full"></div>
                    <div class="absolute -right-10 -top-3 w-6 h-6 bg-slate-50 rounded-full"></div>
                </div>

                {{-- TOTAL SECTION --}}
                <div class="space-y-3">
                    <div class="flex justify-between text-sm text-slate-500">
                        <span>Subtotal</span>
                        <span>Rp {{ number_format($order->subtotal, 0, ',', '.') }}</span>
                    </div>

                    @if($order->diskon_total > 0)
                        <div class="flex justify-between text-sm text-emerald-600">
                            <span class="flex items-center gap-1">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                                Diskon Promo
                            </span>
                            <span>- Rp {{ number_format($order->diskon_total, 0, ',', '.') }}</span>
                        </div>
                    @endif

                    <div class="flex justify-between text-sm text-slate-500">
                        <span>Biaya Pengiriman</span>
                        <span>Rp {{ number_format($order->ongkir, 0, ',', '.') }}</span>
                    </div>

                    <div class="pt-4 flex justify-between items-end">
                        <span class="text-sm font-bold text-slate-700">Total Tagihan</span>
                        <span class="text-3xl font-extrabold text-[#CFAF53]">
                            <span class="text-lg font-bold text-slate-400 mr-1">Rp</span>{{ number_format($order->grand_total, 0, ',', '.') }}
                        </span>
                    </div>
                </div>

                {{-- BUTTON --}}
                <div class="mt-8">
                    <button id="pay-button" 
                            class="w-full bg-gradient-to-r from-[#CFAF53] to-[#B59B48] hover:from-[#dcb95a] hover:to-[#c4a950] text-white font-bold py-4 px-6 rounded-2xl shadow-lg shadow-yellow-200 transform transition hover:-translate-y-1 active:scale-95 flex items-center justify-center gap-2">
                        <span>Bayar Sekarang</span>
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                    </button>
                </div>

                <div class="mt-4 flex items-center justify-center gap-2 text-[10px] text-slate-400 uppercase tracking-widest font-bold">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                    Secure SSL Payment
                </div>

            </div>
        </div>

        <div class="text-center mt-8">
            <a href="{{ route('dashboard') }}" class="text-sm font-medium text-slate-500 hover:text-[#CFAF53] transition flex items-center justify-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Kembali ke Dashboard
            </a>
        </div>
    </div>

    {{-- Midtrans Snap Script --}}
    <script src="https://app.{{ config('midtrans.is_production') ? '' : 'sandbox.' }}midtrans.com/snap/snap.js" 
            data-client-key="{{ config('midtrans.client_key') }}"></script>

    <script type="text/javascript">
        const payButton = document.getElementById('pay-button');
        
        payButton.addEventListener('click', function () {
            // Disable button saat loading
            payButton.disabled = true;
            payButton.classList.add('opacity-75', 'cursor-not-allowed');
            payButton.innerHTML = `
                <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Memproses...
            `;

            window.snap.pay('{{ $snapToken }}', {
                onSuccess: function(result) {
                    console.log('Payment success:', result);
                    window.location.href = "{{ route('payment.success') }}?order_id={{ $order->kode_order }}";
                },
                onPending: function(result) {
                    console.log('Payment pending:', result);
                    alert('Pembayaran tertunda. Silakan cek dashboard.');
                    window.location.href = "{{ route('dashboard') }}?status=pending";
                },
                onError: function(result) {
                    console.log('Payment error:', result);
                    alert('Terjadi kesalahan saat memproses pembayaran.');
                    resetButton();
                },
                onClose: function() {
                    console.log('Payment popup closed');
                    resetButton();
                }
            });
        });

        function resetButton() {
            payButton.disabled = false;
            payButton.classList.remove('opacity-75', 'cursor-not-allowed');
            payButton.innerHTML = `<span>Bayar Sekarang</span><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>`;
        }
    </script>
</x-guest-layout>