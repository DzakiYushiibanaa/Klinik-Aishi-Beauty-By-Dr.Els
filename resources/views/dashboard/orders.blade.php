{{-- ====== TAB: PESANAN SAYA ====== --}}
<div x-data="ordersPage(@js($orders->map(fn($o) => [
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
        'date' => $o->created_at->translatedFormat('d M Y') . ' • ' . $o->created_at->translatedFormat('H:i'),
        'method' => $o->shipping_method,
        'waybill' => $o->shipping_waybill,
        'shipping_address' => $o->shipping_address ?? null,
        'items' => $o->items->map(fn($i) => [
            'name' => $i->product?->nama ?? $i->treatment?->nama ?? $i->description ?? 'Item',
            'qty' => $i->qty,
            'price' => $i->harga_satuan ?? 0,
            'foto' => $i->product?->foto_path ?? $i->treatment?->foto_path ?? null,
            'apt_id' => $i->appointment_id,
        ]),
    ])))">
<div class="bg-white rounded-2xl border border-[#E8E0D8]/60 shadow-sm p-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
            <h2 class="text-xl font-extrabold text-[#1A1A1A]">Pesanan Saya</h2>
            <p class="text-sm text-slate-500 mt-1">Kelola dan cek status pesanan Anda dengan mudah di sini.</p>
        </div>
        <span class="text-sm text-slate-400 self-start sm:self-auto shrink-0"><span class="font-bold text-[#1A1A1A]" x-text="getFilteredOrders().length"></span> Transaksi</span>
    </div>

    @include('dashboard._partials.orders._order_filters')
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
                @include('dashboard._partials.orders._order_card')
            </div>
        </template>
    </div>
</div>

    @include('dashboard._partials.orders._modal_invoice')
</div>

@include('dashboard._partials.orders._order_scripts')
