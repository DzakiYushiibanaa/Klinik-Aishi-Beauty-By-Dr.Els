<x-guest-layout :title="'Pembayaran Gagal'">
    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
        <div class="text-center">
            {{-- Error Icon --}}
            <div class="mx-auto w-24 h-24 bg-red-100 rounded-full flex items-center justify-center mb-6">
                <svg class="w-12 h-12 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </div>

            {{-- Title --}}
            <h1 class="text-3xl font-bold text-slate-900 mb-3">Pembayaran Gagal</h1>
            <p class="text-slate-600 mb-8">Maaf, terjadi kesalahan saat memproses pembayaran Anda.</p>

            {{-- Error Message --}}
            <div class="bg-red-50 border border-red-200 rounded-xl p-4 mb-6">
                <p class="text-sm text-red-800">
                    ⚠️ Transaksi tidak dapat diselesaikan. <br>
                    Silakan coba lagi atau hubungi customer service kami.
                </p>
            </div>

            {{-- Actions --}}
            <div class="flex flex-col sm:flex-row gap-3 justify-center">
                <a href="{{ route('dashboard') }}" 
                   class="bg-[#CFAF53] text-white font-bold py-3 px-6 rounded-xl hover:bg-yellow-600 transition">
                    Kembali ke Dashboard
                </a>
                <a href="{{ route('shop.index') }}" 
                   class="bg-white border border-slate-300 text-slate-700 font-medium py-3 px-6 rounded-xl hover:bg-slate-50 transition">
                    Belanja Lagi
                </a>
            </div>

            {{-- Support Info --}}
            <div class="mt-8 text-sm text-slate-600">
                <p>Butuh bantuan?</p>
                <p class="mt-2">
                    Hubungi kami di 
                    <a href="mailto:support@aishibeauty.com" class="text-yellow-600 hover:underline">support@aishibeauty.com</a>
                    atau WhatsApp 
                    <a href="https://wa.me/628123456789" class="text-yellow-600 hover:underline">0812-3456-7890</a>
                </p>
            </div>
        </div>
    </div>
</x-guest-layout>
