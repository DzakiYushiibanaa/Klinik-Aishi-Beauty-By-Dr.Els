{{-- Partial: Sidebar Filter --}}
            <aside :class="mobileFilterOpen ? 'block' : 'hidden'"
                   class="w-full lg:block lg:w-[260px] shrink-0 mb-6 lg:mb-0">
                <div class="sticky top-24 bg-white rounded-2xl border border-[#E8E0D8]/60 shadow-sm p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-xs font-extrabold uppercase tracking-[0.2em] text-[#8C7A63]">Filter</h3>
                        @if(request('category'))
                            <a href="{{ route('treatment.index', ['search' => request('search')]) }}"
                               class="text-xs font-bold text-rose-500 hover:text-rose-600 transition-colors">
                                Reset
                            </a>
                        @endif
                    </div>

                    <form id="filter-form" action="{{ route('treatment.index') }}" method="GET" class="space-y-6">
                        @if(request('search'))
                            <input type="hidden" name="search" value="{{ request('search') }}">
                        @endif
                        @if(request('sort'))
                            <input type="hidden" name="sort" value="{{ request('sort') }}">
                        @endif

                        <div>
                            <h4 class="text-sm font-bold text-[#1A1A1A] mb-3">Kategori</h4>
                            <div class="space-y-2.5 max-h-72 overflow-y-auto custom-scrollbar pr-1">
                                @if(isset($categories))
                                    @foreach($categories as $cat)
                                        @php $checked = in_array($cat->treatment_category_id, (array)request('category')); @endphp
                                        <label class="flex items-center cursor-pointer group">
                                            <input type="checkbox" name="category[]" value="{{ $cat->treatment_category_id }}"
                                                   onchange="document.getElementById('filter-form').submit()"
                                                   {{ $checked ? 'checked' : '' }}
                                                   class="h-4 w-4 rounded border-slate-300 text-[#CFAF53] focus:ring-[#CFAF53]/40 cursor-pointer">
                                            <span class="ml-3 text-sm transition-colors {{ $checked ? 'font-bold text-[#1A1A1A]' : 'text-slate-600 group-hover:text-[#1A1A1A]' }}">
                                                {{ $cat->nama }}
                                            </span>
                                        </label>
                                    @endforeach
                                @endif
                            </div>
                        </div>
                    </form>
                </div>
            </aside>
