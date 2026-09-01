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