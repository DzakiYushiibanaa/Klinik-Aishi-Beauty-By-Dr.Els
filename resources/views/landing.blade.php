<x-guest-layout title="Beranda">
    <div class="flex flex-col bg-[#FAF8F5]">
        @include('user._partials.landing._hero')
        @include('user._partials.landing._stats')
        @include('user._partials.landing._treatments')
        @include('user._partials.landing._products')
        @include('user._partials.landing._faq')
        @include('user._partials.landing._cta')
        @include('user._partials.landing._location')
    </div>
</x-guest-layout>