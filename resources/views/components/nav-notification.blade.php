@auth
    <div x-data="{ open: false }" class="relative z-50">
        
        {{-- TOMBOL LONCENG --}}
        <button @click="open = !open" @click.outside="open = false" class="relative text-slate-600 hover:text-[#CFAF53] transition mt-1 p-1">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-bell"><path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/><path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"/></svg>
            
            {{-- Badge Merah (Hanya muncul jika ada unread) --}}
            @if(auth()->user()->unreadNotifications->count() > 0)
                <span class="absolute top-0 right-0 flex h-4 w-4 items-center justify-center rounded-full bg-rose-500 text-[10px] font-bold text-white animate-pulse">
                    {{ auth()->user()->unreadNotifications->count() }}
                </span>
            @endif
        </button>

        {{-- DROPDOWN --}}
        <div x-show="open" 
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-75"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             class="absolute right-0 mt-2 w-80 origin-top-right rounded-xl border border-slate-100 bg-white shadow-xl ring-1 ring-black ring-opacity-5 focus:outline-none overflow-hidden"
             style="display: none;">
            
            {{-- Header Dropdown --}}
            <div class="px-4 py-3 border-b border-slate-100 bg-slate-50 flex justify-between items-center">
                <h3 class="text-sm font-bold text-slate-800">Notifikasi</h3>
                
                @if(auth()->user()->unreadNotifications->count() > 0)
                    {{-- Tombol "Tandai Semua Dibaca" --}}
                    <form action="{{ route('notification.readAll') }}" method="POST">
                        @csrf
                        <button type="submit" class="text-[10px] text-slate-500 hover:text-[#CFAF53] font-semibold underline">
                            Tandai semua dibaca
                        </button>
                    </form>
                @endif
            </div>

            {{-- List Notifikasi --}}
            <div class="max-h-64 overflow-y-auto">
                {{-- Kita loop SEMUA notifikasi (terbaru), tapi bedakan stylenya --}}
                @forelse(auth()->user()->notifications()->latest()->take(5)->get() as $notification)
                    
                    {{-- Logic Background: Kalau belum dibaca agak gelap (slate-50), kalau udah putih --}}
                    @php $isUnread = $notification->read_at === null; @endphp

                    <a href="{{ route('notification.read', $notification->id) }}" 
                       class="block px-4 py-3 border-b border-slate-50 transition cursor-pointer hover:bg-slate-100 {{ $isUnread ? 'bg-slate-50' : 'bg-white' }}">
                        
                        <div class="flex gap-3">
                            {{-- Icon --}}
                            <div class="mt-1 shrink-0">
                                <div class="h-8 w-8 rounded-full {{ $isUnread ? 'bg-[#CFAF53]/20 text-[#CFAF53]' : 'bg-slate-100 text-slate-400' }} flex items-center justify-center">
                                    {{-- Icon Lonceng Kecil --}}
                                    @if($isUnread)
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/><path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"/></svg>
                                    @else
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                                    @endif
                                </div>
                            </div>
                            
                            {{-- Konten --}}
                            <div>
                                <p class="text-sm font-semibold {{ $isUnread ? 'text-slate-900' : 'text-slate-500' }}">
                                    {{ $notification->data['title'] ?? 'Info Baru' }}
                                </p>
                                <p class="text-xs text-slate-500 mt-0.5 line-clamp-2">
                                    {{ $notification->data['message'] ?? '' }}
                                </p>
                                <p class="text-[10px] text-slate-400 mt-2">
                                    {{ $notification->created_at->diffForHumans() }}
                                </p>
                            </div>

                            {{-- Dot Merah Penanda Belum Dibaca --}}
                            @if($isUnread)
                                <div class="shrink-0">
                                    <span class="inline-block h-2 w-2 rounded-full bg-rose-500"></span>
                                </div>
                            @endif
                        </div>
                    </a>

                @empty
                    <div class="px-4 py-8 text-center text-slate-500">
                        <svg class="mx-auto h-8 w-8 text-slate-300 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" /></svg>
                        <p class="text-sm">Belum ada notifikasi.</p>
                    </div>
                @endforelse
            </div>
            
        </div>
    </div>
@endauth