{{-- Partial: Order Filters --}}
    {{-- Filter Pills --}}
    <div class="flex gap-2 overflow-x-auto pb-2 mb-5 scrollbar-hide">
        <template x-for="f in getFiltersForTab()" :key="f">
            <button @click="orderFilter = f"
                :class="orderFilter === f ? 'bg-[#CFAF53] text-white border-[#CFAF53]' : 'bg-white text-slate-600 border-[#E8E0D8]/60 hover:border-[#CFAF53]/40'"
                class="shrink-0 px-4 py-1.5 rounded-full text-sm font-bold border whitespace-nowrap transition" x-text="filterLabel(f)"></button>
        </template>
    </div>

    {{-- Search --}}
    <div class="relative mb-4">
        <svg class="w-4 h-4 text-slate-400 absolute left-4 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
        <input x-model="orderSearch" type="text" placeholder="Cari kode pesanan atau nama produk..." class="w-full pl-11 pr-4 py-3 rounded-xl bg-white border border-[#E8E0D8]/60 text-sm focus:border-[#CFAF53] focus:ring-[#CFAF53]/20 focus:ring-2 outline-none transition">
    </div>
