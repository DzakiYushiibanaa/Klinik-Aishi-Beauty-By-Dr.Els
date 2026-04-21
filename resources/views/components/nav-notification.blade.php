@auth
    <div x-data="{ open: false }" class="relative z-50">
        
        {{-- TOMBOL LONCENG --}}
        <button @click="open = !open" @click.outside="open = false" class="relative group p-2 rounded-full hover:bg-slate-100 transition-colors duration-200">
            {{-- Icon Lonceng --}}
            <svg class="w-6 h-6 text-slate-600 group-hover:text-[#CFAF53] transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
            
            {{-- Badge Merah (Hanya muncul jika ada unread) --}}
            @if(auth()->user()->unreadNotifications->count() > 0)
                <span class="absolute top-1.5 right-1.5 flex h-2.5 w-2.5">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-rose-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-rose-500 border-2 border-white"></span>
                </span>
            @endif
        </button>

        {{-- DROPDOWN --}}
        <div x-show="open" 
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95 translate-y-2"
             x-transition:enter-end="opacity-100 scale-100 translate-y-0"
             x-transition:leave="transition ease-in duration-75"
             x-transition:leave-start="opacity-100 scale-100 translate-y-0"
             x-transition:leave-end="opacity-0 scale-95 translate-y-2"
             class="absolute right-0 mt-3 w-80 sm:w-96 origin-top-right rounded-2xl bg-white shadow-2xl ring-1 ring-black/5 focus:outline-none overflow-hidden z-[60]"
             style="display: none;">
            
            {{-- 1. Header Dropdown --}}
            <div class="px-5 py-4 border-b border-slate-100 bg-white flex justify-between items-center sticky top-0 z-10">
                <div>
                    <h3 class="text-sm font-bold text-slate-800">Notifikasi</h3>
                    @if(auth()->user()->unreadNotifications->count() > 0)
                        <p class="text-[10px] text-slate-400 mt-0.5">{{ auth()->user()->unreadNotifications->count() }} pesan belum dibaca</p>
                    @endif
                </div>
                
                @if(auth()->user()->unreadNotifications->count() > 0)
                    <a href="{{ route('notification.readAll') }}" class="text-[11px] font-bold text-[#CFAF53] hover:text-[#b59b48] hover:underline transition">
                        Tandai semua dibaca
                    </a>
                @endif
            </div>

            {{-- 2. List Notifikasi --}}
            <div class="max-h-[20rem] overflow-y-auto custom-scrollbar">
                @forelse(auth()->user()->notifications()->latest()->take(10)->get() as $notification)
                    
                    @php 
                        $isUnread = $notification->read_at === null; 
                        $type = $notification->data['type'] ?? 'info';
                    @endphp

                    <a href="{{ route('notification.read', $notification->id) }}" 
                       class="group block px-5 py-4 border-b border-slate-50 transition-all duration-200 hover:bg-slate-50 {{ $isUnread ? 'bg-blue-50/30' : 'bg-white' }}">
                        
                        <div class="flex gap-4">
                            {{-- Icon Berdasarkan Tipe --}}
                            <div class="shrink-0 mt-1">
                                @if($type == 'shipping_update')
                                    <div class="h-10 w-10 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center shadow-sm group-hover:scale-110 transition-transform">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg> {{-- Atau icon truck --}}
                                        <svg class="w-5 h-5 absolute opacity-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"/></svg>
                                        {{-- Hack visual: Icon Truck --}}
                                        <span class="absolute text-lg">🚚</span>
                                    </div>

                                {{-- 2. PRA KONSUL (BARU) --}}
                                @elseif($type == 'prakonsul_update')
                                    <div class="h-10 w-10 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center shadow-sm group-hover:scale-110 transition-transform">
                                        {{-- Ikon Stetoskop --}}
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                                    </div>
                                    
                                @else
                                    <div class="h-10 w-10 rounded-full bg-slate-100 text-slate-500 flex items-center justify-center shadow-sm group-hover:scale-110 transition-transform">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                                    </div>
                                @endif
                            </div>
                            
                            {{-- Content --}}
                            <div class="flex-1">
                                <div class="flex justify-between items-start mb-1">
                                    <p class="text-sm font-bold {{ $isUnread ? 'text-slate-900' : 'text-slate-600' }}">
                                        {{ $notification->data['title'] ?? 'Info Baru' }}
                                    </p>
                                    @if($isUnread)
                                        <span class="h-2 w-2 rounded-full bg-[#CFAF53]"></span>
                                    @endif
                                </div>
                                <p class="text-xs {{ $isUnread ? 'text-slate-600 font-medium' : 'text-slate-400' }} line-clamp-2 leading-relaxed">
                                    {{ $notification->data['message'] ?? '' }}
                                </p>
                                <p class="text-[10px] text-slate-400 mt-2 font-medium">
                                    {{ $notification->created_at->diffForHumans() }}
                                </p>
                            </div>
                        </div>
                    </a>

                @empty
                    {{-- Empty State --}}
                    <div class="flex flex-col items-center justify-center py-10 px-4 text-center">
                        <div class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center mb-3">
                            <svg class="w-8 h-8 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" /></svg>
                        </div>
                        <p class="text-sm font-bold text-slate-600">Belum ada notifikasi</p>
                        <p class="text-xs text-slate-400 mt-1 max-w-[200px]">Update pesanan dan info terbaru akan muncul di sini.</p>
                    </div>
                @endforelse
            </div>
            
            {{-- Footer (Opsional: Lihat Semua) --}}
            {{-- 
            <div class="bg-slate-50 p-2 text-center border-t border-slate-100">
                <a href="#" class="text-xs font-bold text-slate-600 hover:text-[#CFAF53]">Lihat Semua</a>
            </div> 
            --}}
            
        </div>
    </div>
@endauth