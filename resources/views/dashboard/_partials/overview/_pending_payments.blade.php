{{-- Partial: Pending Payments --}}
    {{-- Section A: Pending Payments (Priority Action) --}}
    @if(isset($pendingPayments) && count($pendingPayments) > 0)
        @foreach($pendingPayments as $payment)
            @php
                $order = $payment['order'];
                $appointment = $payment['appointment'] ?? null;

                // Eager-load relasi item supaya nama tampil dengan benar.
                $order->loadMissing(['items.product', 'items.treatment', 'items.consultation']);

                // Group item per kategori untuk rendering yang clean.
                $groupedItems = [
                    'Konsultasi' => collect(),
                    'Treatment'  => collect(),
                    'Produk'     => collect(),
                ];
                foreach ($order->items as $item) {
                    $type = strtolower((string) $item->item_type);
                    $bucket = match ($type) {
                        'consultation' => 'Konsultasi',
                        'treatment'    => 'Treatment',

                        'product'      => 'Produk',
                        default        => 'Produk',
                    };
                    $groupedItems[$bucket]->push($item);
                }
                // Buang grup yang kosong.
                $groupedItems = collect($groupedItems)->filter(fn ($c) => $c->isNotEmpty());

                // Resolver nama item dengan fallback aman.
                $resolveItemName = function ($item) {
                    return $item->product?->nama
                        ?? $item->treatment?->nama
                        ?? $item->consultation?->nama
                        ?? $item->description
                        ?? 'Item';
                };

                // Cek apakah order punya pengiriman (untuk show row Ongkir).
                $hasShipping = !empty($order->shipping_method)
                    || !empty($order->shipping_address)
                    || (int) ($order->ongkir ?? 0) > 0;

                $subtotal     = (int) ($order->subtotal ?? 0);
                $diskonTotal  = (int) ($order->diskon_total ?? 0);
                $ongkir       = $hasShipping ? (int) ($order->ongkir ?? 0) : 0;
                $grandTotal   = (int) ($order->grand_total ?? 0);
            @endphp

            <div class="bg-white rounded-2xl border border-[#E8E0D8]/70 shadow-sm mb-6 overflow-hidden">
                {{-- HEADER --}}
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center p-4 sm:p-5 border-b border-[#E8E0D8]/60 bg-[#FAF8F5]/70 gap-3">
                    <div class="flex items-center gap-3 flex-wrap">
                        <h2 class="text-[16px] font-bold text-[#1A1A1A]">Invoice</h2>
                        <span class="text-[11px] font-mono font-medium text-slate-500 bg-white px-2 py-1 rounded border border-slate-200 tracking-wide">{{ $order->kode_order }}</span>
                        <span class="text-[11px] text-slate-500">·</span>
                        <span class="text-[11px] text-slate-500">{{ $order->created_at->translatedFormat('d M Y') }}</span>
                    </div>

                    <div class="flex items-center gap-2.5 w-full sm:w-auto justify-between sm:justify-end">
                        @if($order->status === 'cancel_requested')
                            <span class="bg-orange-100 text-orange-800 text-[10px] font-bold px-2.5 py-1 rounded-md uppercase tracking-wide">Menunggu Konfirmasi Pembatalan</span>
                        @else
                            <span class="bg-amber-100 text-amber-800 text-[10px] font-bold px-2.5 py-1 rounded-md uppercase tracking-wide">Menunggu Pembayaran</span>
                        @endif
                        @if($order->status !== 'cancel_requested' && $order->expired_at)
                        <div x-data="countdownTimer('{{ $order->expired_at }}')" class="flex items-center gap-1.5 bg-rose-50 border border-rose-200 px-2.5 py-1 rounded-md">
                            <svg class="w-3.5 h-3.5 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <div class="flex items-center text-rose-600 font-bold text-[13px] tabular-nums tracking-tighter">
                                <template x-if="showHours">
                                    <div class="flex items-center">
                                        <div class="h-[1em] overflow-hidden leading-none relative">
                                            <div class="transition-transform duration-300 flex flex-col" :style="`transform: translateY(-${h1 * 10}%)`"><span>0</span><span>1</span><span>2</span><span>3</span><span>4</span><span>5</span><span>6</span><span>7</span><span>8</span><span>9</span></div>
                                        </div>
                                        <div class="h-[1em] overflow-hidden leading-none relative">
                                            <div class="transition-transform duration-300 flex flex-col" :style="`transform: translateY(-${h2 * 10}%)`"><span>0</span><span>1</span><span>2</span><span>3</span><span>4</span><span>5</span><span>6</span><span>7</span><span>8</span><span>9</span></div>
                                        </div>
                                        <span class="mx-0.5 text-rose-400 font-black animate-pulse">:</span>
                                    </div>
                                </template>
                                <div class="h-[1em] overflow-hidden leading-none relative">
                                    <div class="transition-transform duration-300 flex flex-col" :style="`transform: translateY(-${m1 * 10}%)`"><span>0</span><span>1</span><span>2</span><span>3</span><span>4</span><span>5</span><span>6</span><span>7</span><span>8</span><span>9</span></div>
                                </div>
                                <div class="h-[1em] overflow-hidden leading-none relative">
                                    <div class="transition-transform duration-300 flex flex-col" :style="`transform: translateY(-${m2 * 10}%)`"><span>0</span><span>1</span><span>2</span><span>3</span><span>4</span><span>5</span><span>6</span><span>7</span><span>8</span><span>9</span></div>
                                </div>
                                <span class="mx-0.5 text-rose-400 font-black animate-pulse">:</span>
                                <div class="h-[1em] overflow-hidden leading-none relative">
                                    <div class="transition-transform duration-300 flex flex-col" :style="`transform: translateY(-${s1 * 10}%)`"><span>0</span><span>1</span><span>2</span><span>3</span><span>4</span><span>5</span><span>6</span><span>7</span><span>8</span><span>9</span></div>
                                </div>
                                <div class="h-[1em] overflow-hidden leading-none relative">
                                    <div class="transition-transform duration-300 flex flex-col" :style="`transform: translateY(-${s2 * 10}%)`"><span>0</span><span>1</span><span>2</span><span>3</span><span>4</span><span>5</span><span>6</span><span>7</span><span>8</span><span>9</span></div>
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>

                {{-- ITEMS BY GROUP --}}
                <div class="px-4 sm:px-5 py-2 divide-y divide-[#E8E0D8]/50">
                    @if($appointment)
                        <div class="py-3 flex items-center gap-2 text-xs">
                            <svg class="w-4 h-4 text-[#CFAF53]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            <span class="text-slate-500">Jadwal:</span>
                            <span class="font-semibold text-[#1A1A1A]">{{ $appointment->tanggal_jam->translatedFormat('d F Y • H:i') }} WIB</span>
                        </div>
                    @endif

                    @foreach($groupedItems as $groupName => $items)
                        <div class="py-3">
                            <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-2.5">{{ $groupName }}</p>
                            <ul class="space-y-2">
                                @foreach($items as $item)
                                    @php
                                        $name     = $resolveItemName($item);
                                        $qty      = max(1, (int) ($item->qty ?? 1));
                                        $unitPrice= (int) ($item->harga_satuan ?? 0);
                                        $diskonItem = (int) ($item->diskon ?? 0);
                                        $lineSubtotal = (int) ($item->subtotal ?? ($unitPrice * $qty - $diskonItem));
                                    @endphp
                                    <li class="flex items-start justify-between gap-3">
                                        <div class="min-w-0 flex-1">
                                            <p class="text-sm font-semibold text-[#1A1A1A] line-clamp-2">{{ $name }}</p>
                                            <p class="text-xs text-slate-500 mt-0.5">{{ $qty }} × Rp {{ number_format($unitPrice, 0, ',', '.') }}</p>
                                            @if($diskonItem > 0)
                                                <p class="text-[11px] text-rose-600 mt-0.5">Diskon Rp {{ number_format($diskonItem, 0, ',', '.') }}</p>
                                            @endif
                                        </div>
                                        <p class="text-sm font-bold text-[#1A1A1A] tabular-nums whitespace-nowrap">Rp {{ number_format($lineSubtotal, 0, ',', '.') }}</p>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endforeach
                </div>

                {{-- RINGKASAN PEMBAYARAN --}}
                <div class="px-4 sm:px-5 py-4 bg-[#FAF8F5]/60 border-t border-[#E8E0D8]/60">
                    <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-2.5">Ringkasan Pembayaran</p>
                    <div class="space-y-1.5 text-sm">
                        <div class="flex justify-between text-slate-600">
                            <span>Subtotal</span>
                            <span class="tabular-nums">Rp {{ number_format($subtotal, 0, ',', '.') }}</span>
                        </div>
                        @if($diskonTotal > 0)
                            <div class="flex justify-between text-rose-600">
                                <span>Diskon</span>
                                <span class="tabular-nums">-Rp {{ number_format($diskonTotal, 0, ',', '.') }}</span>
                            </div>
                        @endif
                        @if($ongkir > 0)
                            <div class="flex justify-between text-slate-600">
                                <span>Ongkos Kirim</span>
                                <span class="tabular-nums">Rp {{ number_format($ongkir, 0, ',', '.') }}</span>
                            </div>
                        @endif
                        <div class="flex justify-between items-center pt-2.5 mt-2 border-t border-dashed border-[#E8E0D8]">
                            <span class="text-[11px] font-bold text-[#8C7A63] uppercase tracking-widest">Total Pembayaran</span>
                            <span class="text-lg font-extrabold text-[#CFAF53] tabular-nums">Rp {{ number_format($grandTotal, 0, ',', '.') }}</span>
                        </div>
                    </div>
                </div>

                {{-- FOOTER --}}
                <div class="px-4 sm:px-5 py-4 bg-white border-t border-[#E8E0D8]/60 flex flex-col sm:flex-row justify-between items-stretch sm:items-center gap-3">
                    @if($order->status === 'cancel_requested')
                        <p class="text-xs text-slate-500 leading-relaxed flex-1">
                            <svg class="inline-block w-4 h-4 text-amber-500 mr-1 -mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                            Permintaan pembatalan sedang diproses.
                        </p>
                    @else
                        <p class="text-xs text-slate-500 leading-relaxed flex-1">
                            Invoice ini dibuat berdasarkan layanan dan produk yang tercatat pada transaksi Anda. Silakan lakukan pembayaran untuk menyelesaikan transaksi ini.
                        </p>
                    @endif
                    <div class="flex items-center gap-3 sm:flex-shrink-0">
                        <div class="text-right hidden sm:block">
                            <p class="text-[10px] text-slate-500 uppercase tracking-widest font-semibold">Total</p>
                            <p class="text-base font-extrabold text-[#1A1A1A] tabular-nums">Rp {{ number_format($grandTotal, 0, ',', '.') }}</p>
                        </div>
                        @if($order->status === 'menunggu_pembayaran')
                            <button type="button"
                                    @click="window.confirmDialog({
                                        title: 'Batalkan Pesanan?',
                                        message: 'Apakah Anda yakin ingin membatalkan pesanan ini?',
                                        confirmText: 'Ya, Batalkan',
                                        cancelText: 'Kembali',
                                        variant: 'warning',
                                    }).then(ok => { if (ok) document.getElementById('cancel-order-form-{{ $order->order_id }}').submit() })"
                                    class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-rose-50 hover:bg-rose-100 text-rose-700 text-sm font-bold rounded-full border border-rose-200 transition-colors cursor-pointer">
                                Batalkan Pesanan
                            </button>
                            <form id="cancel-order-form-{{ $order->order_id }}" action="{{ route('orders.cancel', $order->order_id) }}" method="POST" class="hidden">
                                @csrf
                                @method('PATCH')
                            </form>
                            <button @click="processPayment('{{ $order->kode_order }}', $event)" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-2.5 bg-[#CFAF53] hover:bg-[#b09444] text-white text-sm font-bold rounded-full transition-colors shadow-lg shadow-[#CFAF53]/20">
                                Bayar Sekarang
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    @endif
