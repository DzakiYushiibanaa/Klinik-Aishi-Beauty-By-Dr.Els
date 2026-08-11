{{-- Partial: Order Invoice Modal --}}
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
                    <span x-text="selectedOrder ? (getComboStatus(selectedOrder) === 'belum_bayar' ? 'MENUNGGU PEMBAYARAN' : (getComboStatus(selectedOrder) === 'dibatalkan' ? 'DIBATALKAN' : 'SUDAH DIBAYAR')) : ''"
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
                 @php
                     /* â”€â”€ Resolve nomor telepon (filter blank / "-") â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */
                     $authUser = auth()->user();
                     $authProfile = $authUser?->pasienProfile;
                     $authPhoneCandidates = [
                         $authUser?->phone,
                         $authProfile?->no_telepon,
                     ];
                     $authPhone = collect($authPhoneCandidates)
                         ->map(fn ($v) => trim((string) $v))
                         ->first(fn ($v) => $v !== '' && $v !== '-');

                     /* â”€â”€ Resolve alamat lengkap dari profil pasien â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */
                     $authAddressParts = $authProfile ? array_filter([
                         $authProfile->alamat ?? null,
                         $authProfile->district_name ?? null,
                         $authProfile->kota ?? null,
                         $authProfile->provinsi ?? null,
                         $authProfile->kode_pos ?? null,
                     ], fn ($p) => trim((string) $p) !== '') : [];
                     $authProfileAddress = trim(implode(', ', $authAddressParts));
                 @endphp

                 <div class="grid grid-cols-1 sm:grid-cols-2 gap-8 mb-8">
                     {{-- Detail Pelanggan --}}
                     <div>
                         <h4 class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-3">Detail Pelanggan</h4>
                         <div class="space-y-1">
                             <p class="text-sm font-extrabold text-[#1A1A1A]">{{ $authUser->name }}</p>
                             @if($authUser->email)
                                 <p class="text-xs text-slate-500">{{ $authUser->email }}</p>
                             @endif
                             @if($authPhone)
                                 <p class="text-xs text-slate-500">{{ $authPhone }}</p>
                             @else
                                 <p class="text-xs text-slate-400 italic">Nomor HP belum diisi</p>
                             @endif
                         </div>

                         {{-- Alamat Pengiriman: prefer order.shipping_address, fallback profil --}}
                         <template x-if="selectedOrder && (selectedOrder.shipping_address || @js($authProfileAddress))">
                             <div class="mt-5 border-l-2 border-[#CFAF53] pl-3">
                                 <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">Alamat Pengiriman</p>
                                 <p class="text-xs text-slate-600 leading-relaxed max-w-[260px]"
                                    x-text="selectedOrder.shipping_address || @js($authProfileAddress)"></p>
                             </div>
                         </template>
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
