<x-guest-layout :title="'Pembayaran Belum Selesai'">
    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
        <div class="text-center">
            {{-- Warning Icon --}}
            <div class="mx-auto w-24 h-24 bg-yellow-100 rounded-full flex items-center justify-center mb-6">
                <svg class="w-12 h-12 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                </svg>
            </div>

            {{-- Title --}}
            <h1 class="text-3xl font-bold text-slate-900 mb-3">Pembayaran Belum Selesai</h1>
            <p class="text-slate-600 mb-8">Sepertinya Anda menutup halaman pembayaran sebelum menyelesaikan transaksi.</p>

            {{-- Order Info --}}
            @isset($order)
            <div class="bg-slate-50 rounded-xl border border-slate-200 p-6 mb-6 text-left">
                <div class="grid gap-3">
                    <div class="flex justify-between">
                        <span class="text-sm text-slate-600">Kode Order:</span>
                        <span class="font-bold">{{ $order->kode_order }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-slate-600">Total Pembayaran:</span>
                        <span class="font-bold text-yellow-600">Rp {{ number_format($order->grand_total, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-slate-600">Status:</span>
                        <span class="px-3 py-1 bg-yellow-100 text-yellow-700 text-xs font-medium rounded-full">Menunggu Pembayaran</span>
                    </div>
                </div>
            </div>

            <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 mb-6">
                <p class="text-sm text-blue-800">
                    💡 Jangan khawatir! Pesanan Anda masih tersimpan. <br>
                    Silakan lanjutkan pembayaran dari dashboard Anda.
                </p>
            </div>
            @endisset

            {{-- Actions --}}
            <div class="flex flex-col sm:flex-row gap-3 justify-center">
                @isset($order)
                <a href="{{ route('payment.show', $order->order_id) }}" 
                   class="bg-[#CFAF53] text-white font-bold py-3 px-6 rounded-xl hover:bg-yellow-600 transition">
                    Lanjutkan Pembayaran
                </a>
                @endisset
                
                <a href="{{ route('dashboard') }}" 
                   class="bg-white border border-slate-300 text-slate-700 font-medium py-3 px-6 rounded-xl hover:bg-slate-50 transition">
                    Kembali ke Dashboard
                </a>
            </div>
        </div>
    </div>
</x-guest-layout>