                {{-- DETAIL ITEM --}}
                <h3 class="font-bold text-slate-800 mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                    Rincian Pesanan
                </h3>
                
                <div class="space-y-3 mb-6 bg-slate-50 p-4 rounded-xl border border-slate-100">
                    @foreach($order->items as $item)
                    <div class="flex justify-between text-sm group">
                        <div class="flex-1">
                            <span class="text-slate-700 font-medium">{{ $item->description }}</span>
                            <span class="text-slate-400 text-xs ml-1">x{{ $item->qty }}</span>
                        </div>
                        <span class="font-semibold text-slate-600">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</span>
                    </div>
                    @endforeach
                </div>

                <div class="space-y-2 text-sm border-t border-slate-100 pt-4 mb-6">
                    <div class="flex justify-between">
                        <span class="text-slate-500">Subtotal Produk</span>
                        <span class="font-medium text-slate-700">Rp {{ number_format($order->subtotal, 0, ',', '.') }}</span>
                    </div>

                    @if($order->diskon_total > 0)
                    <div class="flex justify-between text-green-600">
                        <span>Diskon</span>
                        <span>- Rp {{ number_format($order->diskon_total, 0, ',', '.') }}</span>
                    </div>
                    @endif

                    {{-- ONGKIR: Hanya tampil jika BUKAN membership --}}
                    @if($order->order_type !== 'membership')
                    <div class="flex justify-between">
                        <span class="text-slate-500">Biaya Pengiriman</span>
                        <span class="font-medium text-slate-700">
                            @if($order->ongkir > 0)
                                Rp {{ number_format($order->ongkir, 0, ',', '.') }}
                            @else
                                <span class="text-green-600">Gratis Ongkir</span>
                            @endif
                        </span>
                    </div>
                    @endif
                    
                    <div class="flex justify-between pt-2 border-t border-slate-100 mt-2 text-base">
                        <span class="font-bold text-slate-800">Grand Total</span>
                        <span class="font-bold text-[#CFAF53]">Rp {{ number_format($order->grand_total, 0, ',', '.') }}</span>
                    </div>
                </div>