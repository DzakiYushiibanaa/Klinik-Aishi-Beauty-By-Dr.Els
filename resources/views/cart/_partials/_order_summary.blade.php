{{-- Partial: Order Summary --}}
<div class="lg:col-span-4 sticky top-24">
    <div class="rounded-3xl border border-[#E8E0D8] bg-white p-6 shadow-sm">
        <h3 class="text-lg font-bold text-[#1A1A1A] mb-5 flex items-center gap-2">
            Ringkasan Belanja
        </h3>

        {{-- Detail item --}}
        <div class="mb-5">
            <div class="flex items-center justify-between mb-3">
                <span class="text-[10px] font-bold uppercase tracking-widest text-[#8C7A63]">Detail Item</span>
                <span class="text-[10px] font-bold uppercase tracking-widest text-slate-400">{{ count($cart) }} produk</span>
            </div>
            <ul class="space-y-2.5 max-h-64 overflow-y-auto custom-scrollbar pr-1">
                @foreach($cart as $key => $item)
                    <li class="flex items-start justify-between gap-3">
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-semibold text-[#1A1A1A] line-clamp-2 leading-snug">{{ $item['nama'] }}</p>
                            <span class="inline-flex items-center justify-center mt-1 rounded-md bg-[#CFAF53]/10 text-[#CFAF53] text-[11px] font-extrabold px-1.5 h-5">
                                {{ (int) $item['qty'] }}×
                            </span>
                        </div>
                        <span class="shrink-0 text-sm font-bold text-[#1A1A1A] tabular-nums">
                            Rp {{ number_format((int) $item['harga'] * (int) $item['qty'], 0, ',', '.') }}
                        </span>
                    </li>
                @endforeach
            </ul>
        </div>

        <div class="space-y-3 mb-6 border-t border-[#E8E0D8]/60 pt-5">
            <div class="flex items-center justify-between text-sm">
                <span class="text-slate-500 font-medium">Total Item</span>
                <span class="font-bold text-[#1A1A1A]">{{ array_reduce($cart, function($carry, $item) { return $carry + $item['qty']; }, 0) }} Item</span>
            </div>
            <div class="flex items-center justify-between text-sm">
                <span class="text-slate-500 font-medium">Subtotal</span>
                <span class="font-bold text-[#1A1A1A]">Rp {{ number_format((int)$subtotal, 0, ',', '.') }}</span>
            </div>
        </div>

        <div class="flex flex-col border-t border-[#E8E0D8]/60 pt-5 mb-6">
            <div class="flex items-center justify-between mb-1">
                <span class="font-bold text-[#1A1A1A]">Total Harga</span>
                <span class="text-2xl font-bold text-[#CFAF53]">Rp {{ number_format((int)$subtotal, 0, ',', '.') }}</span>
            </div>
            <p class="text-[10px] font-medium text-slate-400 text-right uppercase tracking-wider">Belum Termasuk Ongkir</p>
        </div>

        <a href="{{ route('checkout.index') }}"
           class="group flex w-full items-center justify-center gap-2 rounded-xl bg-[#1A1A1A] px-4 py-4 text-sm font-bold text-white transition-all hover:bg-[#333] hover:shadow-lg hover:shadow-[#1A1A1A]/20">
            Lanjut ke Pembayaran
            <svg class="h-4 w-4 transition-transform group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" /></svg>
        </a>
        
        <div class="mt-5 flex items-center justify-center gap-2 text-[10px] font-bold uppercase tracking-wider text-slate-400">
            <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
            Transaksi Aman & Terenkripsi
        </div>
    </div>
</div>
