{{-- Partial: Empty State --}}
<div class="rounded-3xl border border-[#E8E0D8] bg-white p-12 sm:p-16 text-center shadow-sm flex flex-col items-center justify-center">
    <div class="w-24 h-24 bg-[#FAF8F5] rounded-full flex items-center justify-center mb-6">
        <svg class="h-10 w-10 text-[#CFAF53]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
        </svg>
    </div>
    <h2 class="text-xl font-bold text-[#1A1A1A] mb-2">Keranjang masih kosong</h2>
    <p class="text-slate-500 mb-8 max-w-md mx-auto">Anda belum menambahkan produk atau paket apa pun ke dalam keranjang belanja.</p>
    <a class="inline-flex items-center justify-center px-8 py-3.5 text-sm font-bold text-[#1A1A1A] bg-[#FAF8F5] border border-[#E8E0D8] rounded-xl hover:bg-white hover:border-[#CFAF53] transition-all shadow-sm" href="{{ route('shop.index') }}">
        Mulai Belanja Sekarang
    </a>
</div>
