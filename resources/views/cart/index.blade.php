<x-guest-layout :title="'Keranjang Belanja'">


    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

        {{-- HEADER --}}
        <div class="mb-8">
            <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-[#1A1A1A]">Keranjang</h1>
            <p class="text-sm text-slate-500 mt-1">Tinjau pesanan Anda sebelum lanjut ke pembayaran.</p>
        </div>

        @if (empty($cart))
            @include('cart._partials._empty_state')
        @else
            <form method="POST" action="{{ route('cart.update') }}" class="grid gap-8 lg:grid-cols-12 items-start">
                @csrf

                {{-- LIST ITEM --}}
                <div class="lg:col-span-8 space-y-4">
                    @foreach ($cart as $key => $item)
                        @include('cart._partials._cart_item')
                    @endforeach
                </div>

                @include('cart._partials._order_summary')
            </form>
        @endif
    </div>
</x-guest-layout>
