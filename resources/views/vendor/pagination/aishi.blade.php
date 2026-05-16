@if ($paginator->hasPages())
    <nav class="flex items-center justify-center gap-2" aria-label="{{ __('Pagination Navigation') }}">

        {{-- Previous Page --}}
        @if ($paginator->onFirstPage())
            <span class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-[#E8E0D8] bg-white text-slate-300 cursor-not-allowed" aria-disabled="true" aria-label="@lang('pagination.previous')">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}"
               rel="prev"
               aria-label="@lang('pagination.previous')"
               class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-[#E8E0D8] bg-white text-[#1A1A1A] hover:border-[#CFAF53] hover:bg-[#CFAF53]/5 transition-colors cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
        @endif

        {{-- Page Number Pills --}}
        <div class="flex items-center gap-1.5">
            @foreach ($elements as $element)
                {{-- "Three Dots" Separator --}}
                @if (is_string($element))
                    <span class="inline-flex h-10 min-w-[40px] px-2 items-center justify-center text-sm font-bold text-slate-400" aria-hidden="true">
                        {{ $element }}
                    </span>
                @endif

                {{-- Array Of Links --}}
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span class="inline-flex h-10 min-w-[40px] px-3 items-center justify-center rounded-full bg-[#CFAF53] text-white text-sm font-bold shadow-sm shadow-[#CFAF53]/30" aria-current="page">
                                {{ $page }}
                            </span>
                        @else
                            <a href="{{ $url }}"
                               aria-label="{{ __('Go to page :page', ['page' => $page]) }}"
                               class="inline-flex h-10 min-w-[40px] px-3 items-center justify-center rounded-full border border-transparent bg-white text-[#1A1A1A] text-sm font-bold hover:border-[#CFAF53] hover:bg-[#CFAF53]/5 transition-colors cursor-pointer">
                                {{ $page }}
                            </a>
                        @endif
                    @endforeach
                @endif
            @endforeach
        </div>

        {{-- Next Page --}}
        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}"
               rel="next"
               aria-label="@lang('pagination.next')"
               class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-[#E8E0D8] bg-white text-[#1A1A1A] hover:border-[#CFAF53] hover:bg-[#CFAF53]/5 transition-colors cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </a>
        @else
            <span class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-[#E8E0D8] bg-white text-slate-300 cursor-not-allowed" aria-disabled="true" aria-label="@lang('pagination.next')">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </span>
        @endif
    </nav>
@endif
