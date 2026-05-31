@php($field = $field ?? null)
@if($this->filter_sort_field === $field)
    @if($this->filter_sort_dir === 'asc')
        <svg class="w-3 h-3 text-[#CFAF53]" fill="currentColor" viewBox="0 0 20 20">
            <path d="M5 12l5-5 5 5H5z"/>
        </svg>
    @else
        <svg class="w-3 h-3 text-[#CFAF53]" fill="currentColor" viewBox="0 0 20 20">
            <path d="M5 8l5 5 5-5H5z"/>
        </svg>
    @endif
@else
    <svg class="w-3 h-3 text-white/40" fill="currentColor" viewBox="0 0 20 20">
        <path d="M5 8l5-5 5 5H5zm0 4l5 5 5-5H5z"/>
    </svg>
@endif
