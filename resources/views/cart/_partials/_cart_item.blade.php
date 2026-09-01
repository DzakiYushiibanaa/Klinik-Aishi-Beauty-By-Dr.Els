{{-- Partial: Cart Item --}}
<div class="flex items-start gap-3 sm:gap-5 rounded-2xl border border-[#E8E0D8] bg-white p-3 sm:p-5 shadow-sm hover:shadow-md hover:border-[#CFAF53]/40 transition-all group">
    
    {{-- Foto --}}
    <div class="h-20 w-20 sm:h-24 sm:w-24 shrink-0 overflow-hidden rounded-xl bg-gradient-to-br from-[#FAF8F5] to-[#F2EFE9] border border-[#E8E0D8] flex items-center justify-center relative">

        @if(!empty($item['foto_path']))
            <img src="{{ asset('storage/' . $item['foto_path']) }}"
                 alt="{{ $item['nama'] ?? 'Item' }}"
                 loading="lazy"
                 class="h-full w-full object-contain p-2 transition-transform duration-500 group-hover:scale-105">
        @else
            <svg class="h-8 w-8 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 00-2-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
        @endif
    </div>

    {{-- Info --}}
    <div class="flex-1 min-w-0 w-full">
        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-start gap-2 mb-2">
            <div class="flex-1 pr-4">
                <a href="{{ route('shop.show', $item['id']) }}" class="font-bold text-lg text-[#1A1A1A] hover:text-[#CFAF53] transition-colors line-clamp-2">
                    {{ $item['nama'] }}
                </a>
                <p class="text-sm font-bold text-[#CFAF53] mt-1">
                    Rp {{ number_format((int)$item['harga'], 0, ',', '.') }}
                </p>


            </div>
            
            {{-- Subtotal per item (Desktop) --}}
            <div class="hidden sm:block text-right shrink-0">
                <p class="text-[10px] text-slate-400 font-bold uppercase tracking-wider mb-1">Subtotal Item</p>
                <p class="text-base font-bold text-[#1A1A1A]">
                    Rp {{ number_format((int)$item['harga'] * (int)$item['qty'], 0, ',', '.') }}
                </p>
            </div>
        </div>

        <div class="flex flex-wrap items-center justify-between mt-4 border-t border-[#E8E0D8]/50 pt-4 gap-4">
            {{-- Aksi (Qty) --}}
            <div class="flex items-center gap-3">
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider hidden sm:inline-block">Kuantitas</span>
                <div class="flex items-center bg-[#FAF8F5] border border-[#E8E0D8] rounded-lg overflow-hidden h-10 shadow-sm">
                    <button type="button" onclick="this.parentNode.querySelector('input[type=number]').stepDown(); this.form.submit()" class="px-3 h-full text-slate-500 hover:text-[#1A1A1A] hover:bg-slate-100 transition-colors flex items-center justify-center">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20 12H4" /></svg>
                    </button>
                    <input type="number"
                        name="items[{{ $key }}]"
                        value="{{ $item['qty'] }}"
                        min="1"
                        onchange="this.form.submit()" 
                        class="w-12 h-full border-x border-[#E8E0D8] border-y-0 bg-white px-1 text-center text-sm font-bold text-[#1A1A1A] focus:ring-[#CFAF53] p-0 [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none" 
                    />
                    <button type="button" onclick="this.parentNode.querySelector('input[type=number]').stepUp(); this.form.submit()" class="px-3 h-full text-slate-500 hover:text-[#1A1A1A] hover:bg-slate-100 transition-colors flex items-center justify-center">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" /></svg>
                    </button>
                </div>
            </div>

            {{-- Tombol Hapus --}}
            <button type="submit"
                formaction="{{ route('cart.remove', $key) }}"
                formmethod="POST"
                class="flex items-center gap-1.5 rounded-lg px-3 py-2 text-xs font-bold text-slate-400 hover:bg-red-50 hover:text-red-600 transition-colors"
                title="Hapus Item">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
                <span class="hidden sm:inline-block">Hapus</span>
            </button>
        </div>
        
        {{-- Subtotal per item (Mobile Only) --}}
        <div class="sm:hidden mt-4 bg-slate-50 rounded-lg p-3 border border-slate-100 flex items-center justify-between">
            <p class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">Subtotal Item</p>
            <p class="text-sm font-bold text-[#1A1A1A]">
                Rp {{ number_format((int)$item['harga'] * (int)$item['qty'], 0, ',', '.') }}
            </p>
        </div>
    </div>
</div>
