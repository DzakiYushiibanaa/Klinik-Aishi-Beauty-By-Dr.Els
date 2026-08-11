                {{-- ALAMAT PENGIRIMAN (Hanya Tampil Jika BUKAN Membership) --}}
                @if($order->order_type !== 'membership' && $order->shipping_address)
                <div class="mb-8">
                    <h3 class="font-bold text-slate-800 mb-2 flex items-center gap-2">
                        <svg class="w-5 h-5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                        Dikirim ke:
                    </h3>
                    <div class="bg-blue-50 p-4 rounded-xl border border-blue-100 text-sm text-blue-900 leading-relaxed">
                        {{ $order->shipping_address }}
                        @if($order->shipping_method)
                        <div class="mt-2 text-xs font-semibold bg-blue-200 text-blue-800 inline-block px-2 py-1 rounded">
                            Kurir: {{ strtoupper($order->shipping_method) }}
                        </div>
                        @endif
                    </div>
                </div>
                @endif