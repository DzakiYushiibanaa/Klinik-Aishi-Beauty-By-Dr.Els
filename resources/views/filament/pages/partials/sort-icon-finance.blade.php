@php($field = $field ?? null)
@if($this->filter_sort_field === $field)
    @if($this->filter_sort_dir === 'asc')
        <svg style="width:0.75rem;height:0.75rem;color:#CFAF53;" fill="currentColor" viewBox="0 0 20 20">
            <path d="M5 12l5-5 5 5H5z"/>
        </svg>
    @else
        <svg style="width:0.75rem;height:0.75rem;color:#CFAF53;" fill="currentColor" viewBox="0 0 20 20">
            <path d="M5 8l5 5 5-5H5z"/>
        </svg>
    @endif
@else
    <svg style="width:0.75rem;height:0.75rem;color:rgba(255,255,255,0.4);" fill="currentColor" viewBox="0 0 20 20">
        <path d="M5 8l5-5 5 5H5zm0 4l5 5 5-5H5z"/>
    </svg>
@endif
