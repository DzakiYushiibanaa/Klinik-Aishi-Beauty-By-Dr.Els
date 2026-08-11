<section class="pay-section">
    <h2 id="pay-section-title" class="pay-section__title">Detail Belanja</h2>
    <ul class="pay-items" role="list">
        @foreach($order->items as $item)
            @php
                $itemImage = null;
                if ($item->item_type === 'product' && $item->product?->foto_path) {
                    $itemImage = asset('storage/' . $item->product->foto_path);
                } elseif ($item->item_type === 'treatment' && $item->treatment?->foto_path) {
                    $itemImage = asset('storage/' . $item->treatment->foto_path);
                }
            @endphp
            <li class="pay-item">
                <div class="pay-item__thumb">
                    @if($itemImage)
                        <img src="{{ $itemImage }}" alt="{{ $item->description }}">
                    @else
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    @endif
                </div>
                <div class="pay-item__body">
                    <p class="pay-item__name">{{ $item->description }}</p>
                    <p class="pay-item__qty">{{ $item->qty }} × Rp {{ number_format($item->harga_satuan, 0, ',', '.') }}</p>
                </div>
                <p class="pay-item__price">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</p>
            </li>
        @endforeach
    </ul>
</section>
