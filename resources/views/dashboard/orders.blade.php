{{-- ====== TAB: PESANAN SAYA ====== --}}
<div x-data="{
    orderTab: 'produk',
    orderFilter: 'semua',
    orderSearch: '',
    showInvoiceModal: false,
    selectedOrder: null,
    orders: @js($orders->map(fn($o) => [
        'id' => $o->order_id,
        'kode' => $o->kode_order,
        'type' => $o->order_type,
        'status' => $o->status,
        'shipping' => $o->shipping_status,
        'expired_at' => $o->expired_at ? $o->expired_at->toIso8601String() : null,
        'cancel_reason' => $o->cancel_reason ?? null,
        'total' => $o->grand_total,
        'subtotal' => $o->subtotal ?? 0,
        'discount' => $o->diskon_total ?? 0,
        'shipping_cost' => $o->ongkir ?? 0,
        'payment_method' => $o->payment_channel ?? $o->payment_method ?? '-',
        'date' => $o->created_at->translatedFormat('d M Y'),
        'method' => $o->shipping_method,
        'waybill' => $o->shipping_waybill,
        'items' => $o->items->map(fn($i) => [
            'name' => $i->product?->nama ?? $i->treatment?->nama ?? $i->bundle?->nama ?? $i->description ?? 'Item',
            'qty' => $i->qty,
            'price' => $i->harga_satuan ?? 0,
            'foto' => $i->product?->foto_path ?? $i->treatment?->foto_path ?? null,
            'apt_id' => $i->appointment_id,
        ]),
    ])),
    getFilteredOrders() {
        return this.orders.filter(o => {
            if (this.orderTab !== 'semua' && o.type !== this.orderTab) return false;
            if (this.orderFilter !== 'semua') {
                const combo = this.getComboStatus(o);
                if (combo !== this.orderFilter) return false;
            }
            if (this.orderSearch.trim()) {
                const q = this.orderSearch.toLowerCase();
                const match = o.kode.toLowerCase().includes(q) || o.items.some(i => i.name.toLowerCase().includes(q));
                if (!match) return false;
            }
            return true;
        });
    },
    getComboStatus(o) {
        if (['batal','dibatalkan'].includes(o.status) || o.shipping === 'dibatalkan') return 'dibatalkan';
        if ((o.status === 'menunggu_pembayaran' || o.status === 'draft') && o.expired_at) {
            const now = new Date();
            const expired = new Date(o.expired_at);
            if (now > expired) {
                return 'dibatalkan';
            }
        }
        if (o.status === 'menunggu_pembayaran' || o.status === 'draft') return 'belum_bayar';
        if (o.shipping === 'selesai') return 'selesai';
        if (o.shipping === 'dikirim') return 'dikirim';
        return 'dikemas';
    },
    getCancelReason(o) {
        if ((o.status === 'menunggu_pembayaran' || o.status === 'draft') && o.expired_at) {
            const now = new Date();
            const expired = new Date(o.expired_at);
            if (now > expired) {
                return 'Batas waktu pembayaran habis';
            }
        }
        return o.cancel_reason || 'Dibatalkan oleh sistem';
    },
    getFiltersForTab() {
        if (this.orderTab === 'produk') return ['semua','belum_bayar','dikemas','dikirim','selesai','dibatalkan'];
        return ['semua','belum_bayar','lunas','selesai','dibatalkan'];
    },
    filterLabel(f) {
        return { semua:'Semua', belum_bayar:'Belum Bayar', dikemas:'Dikemas', dikirim:'Dikirim', selesai:'Selesai', dibatalkan:'Dibatalkan', lunas:'Lunas' }[f] || f;
    },
    formatPaymentChannel(val) {
        if (!val) return '-';
        const labels = {
            'qris': 'QRIS', 'bca': 'Bank BCA (VA)', 'bni': 'Bank BNI (VA)', 'bri': 'Bank BRI (VA)',
            'mandiri': 'Mandiri Bill', 'mandiri_bill': 'Mandiri Bill', 'permata': 'Bank Permata (VA)',
            'cimb': 'Bank CIMB (VA)', 'gopay': 'GoPay', 'shopeepay': 'ShopeePay', 'dana': 'DANA',
            'alfamart': 'Alfamart', 'indomaret': 'Indomaret', 'bank_transfer': 'Transfer Bank',
            'credit_card': 'Kartu Kredit', 'akulaku': 'Akulaku', 'kredivo': 'Kredivo',
            'web_redirect': 'Midtrans', 'midtrans_snap': 'Midtrans'
        };
        return labels[val.toLowerCase()] || val.toUpperCase();
    }
}">
<div class="bg-white rounded-2xl border border-[#E8E0D8]/60 shadow-sm p-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
            <h2 class="text-xl font-extrabold text-[#1A1A1A]">Pesanan Saya</h2>
            <p class="text-sm text-slate-500 mt-1">Kelola dan cek status pesanan Anda dengan mudah di sini.</p>
        </div>
        <span class="text-sm text-slate-400 self-start sm:self-auto shrink-0"><span class="font-bold text-[#1A1A1A]" x-text="getFilteredOrders().length"></span> Transaksi</span>
    </div>

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

    {{-- Order Cards --}}
    <div class="space-y-4">
        <template x-if="getFilteredOrders().length === 0">
            <div class="bg-[#FAF8F5] rounded-2xl border border-dashed border-[#E8E0D8] p-12 text-center">
                <div class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center mb-4 mx-auto">
                    <svg class="w-8 h-8 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                </div>
                <h3 class="font-bold text-slate-400">Tidak ada pesanan</h3>
            </div>
        </template>

        <template x-for="order in getFilteredOrders()" :key="order.id">
            <div @click="selectedOrder = order; showInvoiceModal = true" 
                 x-init="$nextTick(() => { if ($root.highlightId == order.id) { $el.scrollIntoView({ behavior: 'smooth', block: 'center' }); } })" 
                 :class="$root.highlightId == order.id ? 'ring-2 ring-[#CFAF53] shadow-lg shadow-[#CFAF53]/20 bg-[#CFAF53]/5' : 'bg-slate-50'" 
                 class="rounded-2xl border border-slate-200 shadow-sm overflow-hidden cursor-pointer hover:border-[#CFAF53] transition-all duration-1000 hover:shadow-md">
                {{-- Header --}}
                <div class="flex items-center justify-between px-5 py-3 border-b border-[#E8E0D8]/40">
                    <div class="flex items-center gap-3">
                        <span class="text-xs font-mono text-slate-500" x-text="order.kode"></span>
                        <span class="text-xs text-slate-400" x-text="order.date"></span>
                    </div>
                    <span class="px-3 py-1 rounded-full text-[10px] font-bold border uppercase"
                        :class="{
                            'bg-amber-50 text-amber-700 border-amber-200': getComboStatus(order)==='belum_bayar',
                            'bg-blue-50 text-blue-700 border-blue-200': getComboStatus(order)==='dikemas',
                            'bg-indigo-50 text-indigo-700 border-indigo-200': getComboStatus(order)==='dikirim',
                            'bg-emerald-50 text-emerald-700 border-emerald-200': ['selesai','lunas'].includes(getComboStatus(order)),
                            'bg-rose-50 text-rose-700 border-rose-200': getComboStatus(order)==='dibatalkan'
                        }" x-text="filterLabel(getComboStatus(order))">
                    </span>
                </div>

                {{-- Items --}}
                <div class="px-5 py-4">
                    <template x-for="(item, idx) in order.items.slice(0, 3)" :key="idx">
                        <div class="flex items-center gap-4 py-2" :class="idx > 0 ? 'border-t border-[#E8E0D8]/30' : ''">
                            <div class="w-12 h-12 rounded-xl bg-slate-50 border border-[#E8E0D8]/40 flex items-center justify-center shrink-0 overflow-hidden">
                                <template x-if="item.foto">
                                    <img :src="'/storage/' + item.foto" class="w-full h-full object-cover">
                                </template>
                                <template x-if="!item.foto">
                                    <svg class="w-5 h-5 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                                </template>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-bold text-[#1A1A1A] truncate" x-text="item.name"></p>
                                <p class="text-xs text-slate-400">x<span x-text="item.qty"></span></p>
                            </div>
                            <p class="text-sm font-bold text-[#1A1A1A] shrink-0" x-text="'Rp ' + Number(item.price * item.qty).toLocaleString('id-ID')"></p>
                        </div>
                    </template>
                    <template x-if="order.items.length > 3">
                        <p class="text-xs text-slate-400 pt-2 border-t border-[#E8E0D8]/30">+ <span x-text="order.items.length - 3"></span> item lainnya</p>
                    </template>
                </div>

                {{-- Footer: Status Visual + Actions --}}
                <div class="px-5 py-4 border-t border-[#E8E0D8]/40 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    {{-- Status Icon --}}
                    <div class="flex items-center gap-2 text-sm shrink-0">
                        <template x-if="getComboStatus(order)==='belum_bayar'">
                            <span class="flex items-center gap-1.5 text-amber-600"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg> Menunggu Pembayaran</span>
                        </template>
                        <template x-if="getComboStatus(order)==='dikemas'">
                            <span class="flex items-center gap-1.5 text-blue-600"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg> Sedang Dikemas</span>
                        </template>
                        <template x-if="getComboStatus(order)==='dikirim'">
                            <span class="flex items-center gap-1.5 text-indigo-600"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10l2-1 2 1 2-1 2 1m0 0V6a1 1 0 011-1h2a1 1 0 011 1v4l4 2v6h-3m-7 0h7m-7 0a2 2 0 11-4 0m11 0a2 2 0 11-4 0"/></svg> Sedang Dikirim</span>
                        </template>
                        <template x-if="getComboStatus(order)==='selesai' || getComboStatus(order)==='lunas'">
                            <span class="flex items-center gap-1.5 text-emerald-600"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg> Selesai</span>
                        </template>
                        <template x-if="getComboStatus(order)==='dibatalkan'">
                            <div class="flex flex-col">
                                <span class="flex items-center gap-1.5 text-rose-600"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg> Dibatalkan</span>
                                <span class="text-[10px] text-slate-400 mt-0.5 ml-5" x-text="getCancelReason(order)"></span>
                            </div>
                        </template>
                    </div>

                    <div class="flex flex-col sm:flex-row sm:items-center gap-4 sm:gap-5 w-full sm:w-auto">
                        {{-- Link ke Jadwal (treatment/konsultasi) --}}
                        <template x-if="(order.type === 'treatment' || order.type === 'konsultasi') && order.items.some(i => i.apt_id)">
                            <button @click.stop="$dispatch('switch-tab', 'appointment')" class="text-xs font-bold text-[#CFAF53] hover:text-[#8C7A63] transition-colors flex items-center justify-center gap-1 w-full sm:w-auto border border-[#E8E0D8]/60 sm:border-none py-2 sm:py-0 rounded-lg sm:rounded-none bg-white sm:bg-transparent">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                Lihat Jadwal
                            </button>
                        </template>

                        <div class="flex items-center justify-between sm:justify-end gap-2 w-full sm:w-auto bg-white sm:bg-transparent p-3 sm:p-0 rounded-xl sm:rounded-none border border-[#E8E0D8]/60 sm:border-none shadow-sm sm:shadow-none">
                            <span class="text-xs text-slate-500 font-medium">Total Pesanan</span>
                            <span class="text-base sm:text-sm font-extrabold text-[#1A1A1A]" x-text="'Rp ' + Number(order.total).toLocaleString('id-ID')"></span>
                        </div>

                        {{-- Action Button: Bayar Sekarang --}}
                        <template x-if="getComboStatus(order)==='belum_bayar'">
                            <button @click.stop="processPayment(order.kode, $event)" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-2.5 bg-[#D4A017] hover:bg-[#b58812] text-white text-sm font-bold rounded-full transition-colors shadow-lg shadow-[#D4A017]/20 disabled:opacity-75 disabled:cursor-not-allowed">
                                Bayar Sekarang
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                            </button>
                        </template>
                    </div>
                </div>
            </div>
        </template>
    </div>
</div>

{{-- MODAL NOTA PESANAN --}}
<template x-teleport="body">
    <div x-show="showInvoiceModal" 
         class="fixed inset-0 z-[999] flex items-center justify-center p-4 sm:p-6 bg-[#1A1A1A]/60 backdrop-blur-sm"
         style="display: none;"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
         
        <div @click.away="showInvoiceModal = false"
             x-show="showInvoiceModal"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-8 scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0 scale-100"
             x-transition:leave-end="opacity-0 translate-y-8 scale-95"
             class="bg-white w-full max-w-3xl max-h-[90vh] rounded-3xl shadow-2xl flex flex-col overflow-hidden relative">

             {{-- Modal Header --}}
             <div class="px-6 py-5 border-b border-[#E8E0D8]/60 flex items-center justify-between shrink-0 bg-[#FAF8F5]">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-[#CFAF53] flex items-center justify-center shrink-0 shadow-inner">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-black text-[#1A1A1A] leading-tight">Nota Pesanan</h3>
                        <p class="text-xs text-[#8C7A63] font-bold tracking-widest uppercase">Aishi Beauty</p>
                    </div>
                </div>
                <div class="flex items-center gap-4">
                    <span x-text="selectedOrder ? (getComboStatus(selectedOrder) === 'belum_bayar' ? 'MENUNGGU PEMBAYARAN' : (getComboStatus(selectedOrder) === 'dibatalkan' ? 'DIBATALKAN' : 'LUNAS')) : ''"
                          class="px-3 py-1 text-[10px] font-black rounded uppercase tracking-wider"
                          :class="{
                              'bg-amber-100 text-amber-700': selectedOrder && getComboStatus(selectedOrder) === 'belum_bayar',
                              'bg-emerald-100 text-emerald-700': selectedOrder && !['belum_bayar','dibatalkan'].includes(getComboStatus(selectedOrder)),
                              'bg-rose-100 text-rose-700': selectedOrder && getComboStatus(selectedOrder) === 'dibatalkan'
                          }">
                    </span>
                    <button @click="showInvoiceModal = false" class="text-slate-400 hover:text-rose-500 bg-white hover:bg-rose-50 p-2 rounded-full transition-colors shadow-sm border border-[#E8E0D8]">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
             </div>

             {{-- Modal Body (Scrollable) --}}
             <div class="flex-1 overflow-y-auto p-6 sm:p-8 custom-scrollbar">
                 <div class="grid grid-cols-1 sm:grid-cols-2 gap-8 mb-8">
                     {{-- Info Klinik & Pelanggan --}}
                     <div>
                         <h4 class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-3">Detail Pelanggan</h4>
                         <div class="space-y-1">
                             <p class="text-sm font-extrabold text-[#1A1A1A]">{{ auth()->user()->name }}</p>
                             <p class="text-xs text-slate-500">{{ auth()->user()->email }}</p>
                             <p class="text-xs text-slate-500">{{ auth()->user()->pasienProfile?->telepon ?? '-' }}</p>
                         </div>
                         <div class="mt-5 border-l-2 border-[#CFAF53] pl-3">
                             <p class="text-xs font-bold text-[#1A1A1A]">Aishi Beauty Klinik</p>
                             <p class="text-[11px] text-slate-500 leading-relaxed mt-1 max-w-[200px]">
                                Jl. Nusantara Raya No.99b, Beji, Kec. Beji, Kota Depok, Jawa Barat 16421
                             </p>
                         </div>
                     </div>
                     {{-- Info Transaksi --}}
                     <div class="sm:text-right">
                         <h4 class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-3">Detail Transaksi</h4>
                         <div class="space-y-3">
                             <div>
                                 <p class="text-[10px] text-slate-400">Kode Pesanan</p>
                                 <p class="text-sm font-mono font-bold text-[#1A1A1A]" x-text="selectedOrder?.kode"></p>
                             </div>
                             <div>
                                 <p class="text-[10px] text-slate-400">Tanggal Transaksi</p>
                                 <p class="text-sm font-bold text-[#1A1A1A]" x-text="selectedOrder?.date"></p>
                             </div>
                             <div>
                                 <p class="text-[10px] text-slate-400">Metode Pembayaran</p>
                                 <p class="text-sm font-bold text-[#1A1A1A]" x-text="formatPaymentChannel(selectedOrder?.payment_method)"></p>
                             </div>
                             <template x-if="selectedOrder?.method">
                                 <div>
                                     <p class="text-[10px] text-slate-400">Kurir Pengiriman</p>
                                     <p class="text-sm font-bold text-[#1A1A1A]">
                                         <span x-text="selectedOrder.method.toUpperCase()"></span>
                                         <template x-if="selectedOrder.waybill">
                                             <span class="text-slate-500 font-medium ml-1" x-text="'- ' + selectedOrder.waybill"></span>
                                         </template>
                                     </p>
                                 </div>
                             </template>
                         </div>
                     </div>
                 </div>

                 {{-- Item Table --}}
                 <div class="rounded-2xl border border-[#E8E0D8]/60 overflow-hidden mb-6">
                     <table class="w-full text-left border-collapse">
                         <thead class="bg-[#FAF8F5] border-b border-[#E8E0D8]/60">
                             <tr>
                                 <th class="py-3 px-4 text-[10px] font-bold text-slate-500 uppercase tracking-widest">Produk / Treatment</th>
                                 <th class="py-3 px-4 text-[10px] font-bold text-slate-500 uppercase tracking-widest text-center">Qty</th>
                                 <th class="py-3 px-4 text-[10px] font-bold text-slate-500 uppercase tracking-widest text-right">Harga</th>
                                 <th class="py-3 px-4 text-[10px] font-bold text-slate-500 uppercase tracking-widest text-right">Subtotal</th>
                             </tr>
                         </thead>
                         <tbody class="divide-y divide-[#E8E0D8]/40">
                             <template x-if="selectedOrder">
                                 <template x-for="(item, index) in selectedOrder.items" :key="index">
                                     <tr class="hover:bg-slate-50/50 transition-colors">
                                         <td class="py-4 px-4">
                                             <p class="text-sm font-bold text-[#1A1A1A]" x-text="item.name"></p>
                                         </td>
                                         <td class="py-4 px-4 text-center">
                                             <span class="text-xs font-bold text-slate-600 bg-slate-100 px-2 py-1 rounded-md" x-text="item.qty"></span>
                                         </td>
                                         <td class="py-4 px-4 text-right">
                                             <p class="text-xs font-medium text-slate-500" x-text="'Rp ' + Number(item.price).toLocaleString('id-ID')"></p>
                                         </td>
                                         <td class="py-4 px-4 text-right">
                                             <p class="text-sm font-bold text-[#1A1A1A]" x-text="'Rp ' + Number(item.price * item.qty).toLocaleString('id-ID')"></p>
                                         </td>
                                     </tr>
                                 </template>
                             </template>
                         </tbody>
                     </table>
                 </div>

                 {{-- Payment Summary --}}
                 <div class="flex justify-end">
                     <div class="w-full sm:w-72 space-y-3">
                         <div class="flex justify-between items-center text-sm">
                             <span class="text-slate-500">Subtotal</span>
                             <span class="font-medium text-[#1A1A1A]" x-text="selectedOrder ? 'Rp ' + Number(selectedOrder.subtotal).toLocaleString('id-ID') : 'Rp 0'"></span>
                         </div>
                         <template x-if="selectedOrder && selectedOrder.discount > 0">
                             <div class="flex justify-between items-center text-sm">
                                 <span class="text-slate-500">Diskon</span>
                                 <span class="font-medium text-rose-500" x-text="'-Rp ' + Number(selectedOrder.discount).toLocaleString('id-ID')"></span>
                             </div>
                         </template>
                         <template x-if="selectedOrder && selectedOrder.shipping_cost > 0">
                             <div class="flex justify-between items-center text-sm">
                                 <span class="text-slate-500">Ongkos Kirim</span>
                                 <span class="font-medium text-[#1A1A1A]" x-text="'Rp ' + Number(selectedOrder.shipping_cost).toLocaleString('id-ID')"></span>
                             </div>
                         </template>
                         
                         <div class="pt-3 border-t border-dashed border-[#E8E0D8] flex justify-between items-center">
                             <span class="font-bold text-[#1A1A1A]">Total Pembayaran</span>
                             <span class="text-lg font-black text-[#CFAF53]" x-text="selectedOrder ? 'Rp ' + Number(selectedOrder.total).toLocaleString('id-ID') : 'Rp 0'"></span>
                         </div>
                     </div>
                 </div>
             </div>

             {{-- Modal Footer Actions --}}
             <div class="px-6 py-4 border-t border-[#E8E0D8]/60 bg-[#FAF8F5] flex items-center justify-end shrink-0">
                     <button @click="sendEmailInvoice(selectedOrder.kode, $event)" class="w-full sm:w-auto px-6 py-2.5 rounded-full bg-[#CFAF53] hover:bg-[#b89a3e] text-white text-sm font-bold shadow-lg shadow-[#CFAF53]/20 transition-colors flex items-center justify-center gap-2">
                         <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                         Kirim Nota ke Email
                     </button>
             </div>
        </div>
    </div>
</template>
</div>

<script>
    async function sendEmailInvoice(orderKode, btnEvent) {
        const btn = btnEvent.currentTarget;
        const originalText = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = `<svg class="animate-spin h-5 w-5 text-[#8B6F1B]" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Mengirim...`;
        
        try {
            const response = await fetch('/orders/' + orderKode + '/invoice/email', {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            
            let data;
            try {
                data = await response.json();
            } catch (jsonErr) {
                const text = await response.text();
                console.error('Non-JSON response:', text.substring(0, 500));
                throw new Error('Server mengembalikan response tidak valid (status: ' + response.status + ')');
            }
            
            if (response.ok && data.success) {
                window.showToast && window.showToast('success', data.message || 'Email berhasil dikirim.', 'Berhasil');
            } else {
                window.showToast && window.showToast('error', data.error || 'Gagal mengirim email. Silakan coba lagi.', 'Gagal');
            }
        } catch (err) {
            console.error('Email send error:', err);
            window.showToast && window.showToast('error', 'Gagal mengirim email: ' + err.message, 'Gagal');
        } finally {
            btn.disabled = false;
            btn.innerHTML = originalText;
        }
    }

    async function processPayment(orderKode, btnEvent) {
        const btn = btnEvent.currentTarget;
        const originalText = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = `<svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Memproses...`;
        
        try {
            const response = await fetch('/payment/' + orderKode + '/token');
            const data = await response.json();
            
            if (!response.ok) {
                window.showToast && window.showToast('error', data.error || 'Gagal memproses pembayaran. Silakan coba lagi.', 'Gagal');
                if (data.reload) {
                    setTimeout(() => window.location.reload(), 1200);
                } else {
                    btn.disabled = false;
                    btn.innerHTML = originalText;
                }
                return;
            }

            let paymentCompleted = false;
            window.snap.pay(data.token, {
                onSuccess: function(result) {
                    paymentCompleted = true;
                    const rawOrderId = result.order_id || orderKode;
                    const kodeOrder = rawOrderId.replace(/-[0-9]+$/, '');
                    window.location.href = "/payment-status/success?order_id=" + kodeOrder;
                },
                onPending: function(result) {
                    paymentCompleted = true;
                    window.showToast && window.showToast('warning', 'Pembayaran Anda masih tertunda.', 'Pembayaran Tertunda');
                    setTimeout(() => window.location.reload(), 1200);
                },
                onError: function(result) {
                    paymentCompleted = true;
                    window.showToast && window.showToast('error', 'Pembayaran gagal atau waktu pembayaran telah habis.', 'Pembayaran Gagal');
                    setTimeout(() => window.location.reload(), 1200);
                },
                onClose: function() {
                    if (!paymentCompleted) {
                        window.location.reload();
                    }
                }
            });
        } catch (err) {
            window.showToast && window.showToast('error', 'Terjadi kesalahan koneksi. Silakan periksa jaringan Anda.', 'Gagal Terhubung');
            btn.disabled = false;
            btn.innerHTML = originalText;
        }
    }
</script>
