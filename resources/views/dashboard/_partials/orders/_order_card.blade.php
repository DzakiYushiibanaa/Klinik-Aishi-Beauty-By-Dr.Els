{{-- Partial: Order Card --}}
                {{-- Header --}}
                <div class="flex items-center justify-between px-5 py-3 border-b border-[#E8E0D8]/40">
                    <div class="flex items-center gap-3">
                        <span class="text-xs font-mono text-slate-500" x-text="order.kode"></span>
                        <span class="text-xs text-slate-400" x-text="order.date"></span>
                    </div>
                    <span class="px-3 py-1 rounded-full text-[10px] font-bold border uppercase"
                        :class="{
                            'bg-amber-50 text-amber-700 border-amber-200': getComboStatus(order)==='belum_bayar',
                            'bg-orange-50 text-orange-700 border-orange-200': getComboStatus(order)==='cancel_requested',
                            'bg-blue-50 text-blue-700 border-blue-200': getComboStatus(order)==='dikemas',
                            'bg-indigo-50 text-indigo-700 border-indigo-200': getComboStatus(order)==='dikirim',
                            'bg-emerald-50 text-emerald-700 border-emerald-200': ['selesai','sudah_dibayar'].includes(getComboStatus(order)),
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
                            <span class="flex items-center gap-1.5 text-amber-600">Menunggu Pembayaran</span>
                        </template>
                        <template x-if="getComboStatus(order)==='cancel_requested'">
                            <span class="flex items-center gap-1.5 text-orange-600">Menunggu Konfirmasi Pembatalan</span>
                        </template>
                        <template x-if="getComboStatus(order)==='dikemas'">
                            <span class="flex items-center gap-1.5 text-blue-600">Sedang Dikemas</span>
                        </template>
                        <template x-if="getComboStatus(order)==='dikirim'">
                            <span class="flex items-center gap-1.5 text-indigo-600">Sedang Dikirim</span>
                        </template>
                        <template x-if="getComboStatus(order)==='selesai' || getComboStatus(order)==='sudah_dibayar'">
                            <span class="flex items-center gap-1.5 text-emerald-600">Selesai</span>
                        </template>
                        <template x-if="getComboStatus(order)==='dibatalkan'">
                            <div class="flex flex-col">
                                <span class="flex items-center gap-1.5 text-rose-600">Dibatalkan</span>
                                <span class="text-[10px] text-slate-400 mt-0.5" x-text="getCancelReason(order)"></span>
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

                        {{-- Action Button: Bayar Sekarang + Batalkan Pesanan --}}
                        <template x-if="['belum_bayar', 'dikemas', 'sudah_dibayar'].includes(getComboStatus(order))">
                            <div class="flex flex-wrap items-center gap-2 w-full sm:w-auto">
                                <button @click.stop="window.confirmDialog({
                                            title: 'Batalkan Pesanan?',
                                            message: 'Apakah Anda yakin ingin membatalkan pesanan ini?',
                                            confirmText: 'Ya, Batalkan',
                                            cancelText: 'Kembali',
                                            variant: 'warning',
                                        }).then(ok => { if (ok) document.getElementById('cancel-order-form-' + order.id).submit() })"
                                        class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-rose-50 hover:bg-rose-100 text-rose-700 text-sm font-bold rounded-full border border-rose-200 transition-colors cursor-pointer">
                                    Batalkan Pesanan
                                </button>
                                <form :id="'cancel-order-form-' + order.id" :action="'/orders/' + order.id + '/cancel'" method="POST" class="hidden">
                                    @csrf
                                    @method('PATCH')
                                </form>
                                <template x-if="getComboStatus(order)==='belum_bayar'">
                                    <button @click.stop="processPayment(order.kode, $event)" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-2.5 bg-[#D4A017] hover:bg-[#b58812] text-white text-sm font-bold rounded-full transition-colors shadow-lg shadow-[#D4A017]/20 disabled:opacity-75 disabled:cursor-not-allowed">
                                        Bayar Sekarang
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                                    </button>
                                </template>
                            </div>
                        </template>
                    </div>
                </div>
