{{-- Partial: Search Bar --}}
        {{-- SECTION: STICKY SEARCH BAR (Mobile Optimized) --}}
        <div class="sticky top-0 z-30 w-full bg-white/80 backdrop-blur-md border-b border-slate-200 shadow-sm transition-all duration-300">
            <div class="mx-auto max-w-6xl px-4 py-3">
                <form method="GET" action="{{ route('forum.index') }}" class="flex gap-2 sm:gap-4">
                    
                    {{-- Input Search --}}
                    <div class="relative flex-1 group">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-slate-400 group-focus-within:text-[#CFAF53] transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                        </div>
                        <input type="text" 
                               name="search" 
                               value="{{ request('search') }}"
                               placeholder="Cari keluhan (cth: jerawat)..." 
                               class="w-full rounded-xl border-slate-200 bg-slate-50 pl-10 pr-4 py-2.5 text-sm focus:border-[#CFAF53] focus:ring-[#CFAF53] transition placeholder:text-slate-400 hover:bg-white">
                    </div>
                    
                    {{-- Dropdown Status (Hidden on very small screens, optional) --}}
                    <div class="relative hidden sm:block w-40">
                        <select name="status" 
                                class="w-full appearance-none rounded-xl border-slate-200 bg-slate-50 pl-4 pr-10 py-2.5 text-sm text-slate-600 focus:border-[#CFAF53] focus:ring-[#CFAF53] cursor-pointer hover:bg-white transition">
                            <option value="">Semua Status</option>
                            <option value="dibuka" {{ request('status') == 'dibuka' ? 'selected' : '' }}>Dibuka</option>
                            <option value="ditutup" {{ request('status') == 'ditutup' ? 'selected' : '' }}>Ditutup</option>
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-slate-500">
                             <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </div>
                    </div>
                    
                    {{-- Search Button --}}
                    <button type="submit" 
                            class="shrink-0 rounded-xl bg-slate-900 px-4 sm:px-6 py-2.5 text-sm font-bold text-white transition hover:bg-[#CFAF53] active:scale-95">
                        <span class="hidden sm:inline">Cari</span>
                        <span class="sm:hidden"><svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg></span>
                    </button>
                </form>
            </div>
        </div>