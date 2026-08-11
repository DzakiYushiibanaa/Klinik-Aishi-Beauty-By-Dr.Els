{{-- Partial: RM Lightbox --}}
        {{-- LIGHTBOX FOTO (shared, single instance) --}}
        <div x-show="lightboxUrl"
             x-cloak
             @keydown.escape.window="lightboxUrl = ''"
             class="fixed inset-0 z-[10000] flex items-center justify-center bg-black/80 backdrop-blur-sm p-4"
             @click="lightboxUrl = ''"
             x-transition:enter="ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0">
            <div class="relative max-w-4xl w-full max-h-[90vh]" @click.stop>
                <button type="button"
                        @click="lightboxUrl = ''"
                        class="absolute -top-12 right-0 w-10 h-10 rounded-full bg-white/10 hover:bg-white/20 text-white flex items-center justify-center transition-colors cursor-pointer"
                        aria-label="Tutup foto">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
                <img :src="lightboxUrl" alt="" class="w-full h-full object-contain rounded-2xl shadow-2xl max-h-[90vh]">
            </div>
        </div>
