{{-- Partial: Empty State --}}
                    <div class="flex flex-col items-center justify-center rounded-2xl border-2 border-dashed border-slate-200 bg-slate-50/50 py-12 text-center">
                        <div class="mb-3 rounded-full bg-white p-3 shadow-sm ring-1 ring-slate-100">
                            <svg class="w-8 h-8 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" /></svg>
                        </div>
                        <h3 class="text-base font-bold text-slate-900">Belum ada pertanyaan</h3>
                        <p class="text-sm text-slate-500 max-w-xs mt-1 mb-4">Jadilah yang pertama berkonsultasi.</p>
                        @auth
                            <a href="{{ route('forum.create') }}" class="inline-flex items-center rounded-lg bg-slate-900 px-4 py-2 text-xs font-bold text-white hover:bg-[#CFAF53] transition">
                                Buat Pertanyaan
                            </a>
                        @endauth
                    </div>