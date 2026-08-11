{{-- Partial: Question Card --}}
                    @php
                        $statusClass = 'bg-slate-100 text-slate-600';
                        $statusText = 'Menunggu';
                        $statusIcon = '<svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>';

                        if ($question->status === 'ditutup') {
                            $statusClass = 'bg-slate-200 text-slate-500';
                            $statusText = 'Ditutup';
                            $statusIcon = '<svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>';
                        } elseif ($question->answers_count > 0) {
                            $statusClass = 'bg-emerald-50 text-emerald-700 border border-emerald-100';
                            $statusText = 'Dijawab';
                            $statusIcon = '<svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>';
                        }
                    @endphp

                    <a href="{{ route('forum.show', $question->forum_question_id) }}" 
                       class="group relative block rounded-2xl border border-slate-200 bg-white p-5 sm:p-6 transition-all hover:border-[#CFAF53]/40 hover:shadow-lg hover:shadow-[#CFAF53]/5 hover:-translate-y-0.5">
                        
                        <div class="flex gap-4 items-start">
                            {{-- Avatar (Inisial) --}}
                            <div class="hidden sm:flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-slate-50 text-slate-500 font-bold text-sm border border-slate-100 group-hover:bg-[#CFAF53] group-hover:text-white group-hover:border-[#CFAF53] transition-colors">
                                {{ $question->is_anonymous ? '?' : strtoupper(substr($question->user->name, 0, 1)) }}
                            </div>

                            <div class="flex-1 min-w-0">
                                {{-- Header Card --}}
                                <div class="flex flex-wrap items-center justify-between gap-y-2 gap-x-4 mb-2">
                                    <div class="flex items-center gap-2 text-xs text-slate-500">
                                        <span class="font-bold text-slate-800">
                                            {{ $question->is_anonymous ? 'Anonim' : $question->user->name }}
                                        </span>
                                        <span class="h-1 w-1 rounded-full bg-slate-300"></span>
                                        <span>{{ $question->created_at->diffForHumans() }}</span>
                                    </div>

                                    <span class="inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider {{ $statusClass }}">
                                        {!! $statusIcon !!}
                                        {{ $statusText }}
                                    </span>
                                </div>

                                {{-- Judul --}}
                                <h3 class="text-base font-bold text-slate-900 group-hover:text-[#CFAF53] transition-colors line-clamp-1 mb-1">
                                    {{ $question->title }}
                                </h3>

                                {{-- Preview Body --}}
                                <p class="text-sm text-slate-600 line-clamp-2 leading-relaxed mb-4">
                                    {{ Str::limit(strip_tags($question->body), 140) }}
                                </p>

                                {{-- Footer Card --}}
                                <div class="flex items-center justify-between border-t border-slate-50 pt-3 mt-auto">
                                    <div class="flex items-center gap-1.5 text-xs font-medium {{ $question->answers_count > 0 ? 'text-[#CFAF53]' : 'text-slate-400' }}">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" /></svg>
                                        {{ $question->answers_count }} Balasan Dokter
                                    </div>
                                    <div class="flex items-center gap-1 text-xs font-semibold text-slate-400 group-hover:text-[#CFAF53] transition-colors">
                                        Lihat Detail
                                        <svg class="w-3 h-3 transition-transform group-hover:translate-x-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </a>