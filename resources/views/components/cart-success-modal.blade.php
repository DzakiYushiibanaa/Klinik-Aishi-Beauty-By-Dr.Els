{{--
  Cart Success Modal — global, auto-mounted di guest layout.
  Trigger lewat helper window.cartSuccess({ message, name }) atau
  event 'cart-success' dengan detail.
--}}

<div x-data="cartSuccessModal()"
     x-show="open"
     x-cloak
     @click.stop
     @keydown.escape.window="close()"
     class="fixed inset-0 z-[10000] flex items-center justify-center bg-black/50 backdrop-blur-sm p-4"
     role="alertdialog"
     aria-modal="true"
     aria-labelledby="cart-success-title"
     x-transition:enter="ease-out duration-200"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="ease-in duration-150"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0">

    <div class="bg-white w-full max-w-sm rounded-3xl p-7 shadow-2xl text-center"
         @click.away="close()"
         x-transition:enter="ease-out duration-300"
         x-transition:enter-start="opacity-0 scale-90"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="ease-in duration-150"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-90">

        {{-- Icon centang gold --}}
        <div class="mx-auto mb-5 flex h-20 w-20 items-center justify-center rounded-full bg-emerald-500 shadow-lg shadow-emerald-500/30">
            <svg class="h-10 w-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
            </svg>
        </div>

        <h3 id="cart-success-title" class="text-lg font-extrabold text-[#1A1A1A]">Berhasil Ditambahkan</h3>
        <p class="text-sm text-slate-500 mt-2 leading-relaxed" x-text="message"></p>

        <p x-show="name" class="mt-3 inline-block max-w-full rounded-full bg-[#FAF8F5] border border-[#E8E0D8] px-4 py-2 text-xs font-bold text-[#1A1A1A] truncate" x-text="name"></p>

        <div class="flex flex-col sm:flex-row gap-2 mt-6">
            <button type="button"
                    @click="close()"
                    class="flex-1 py-2.5 rounded-full border border-slate-200 text-sm font-bold text-slate-700 hover:bg-slate-50 cursor-pointer transition-colors">
                Lanjut Belanja
            </button>
            <a :href="cartUrl"
               class="flex-1 inline-flex items-center justify-center gap-1.5 rounded-full bg-[#1A1A1A] hover:bg-[#CFAF53] text-white text-sm font-bold py-2.5 transition-colors cursor-pointer">
                Lihat Keranjang
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                </svg>
            </a>
        </div>
    </div>
</div>

<script>
    function cartSuccessModal() {
        return {
            open: false,
            message: 'Produk telah ditambahkan ke keranjang belanja.',
            name: '',
            cartUrl: @json(route('cart.index')),
            _autoCloseTimer: null,

            init() {
                window.addEventListener('cart-success', (e) => {
                    this.show(e.detail || {});
                });
            },
            show(detail) {
                this.message = detail.message || 'Produk telah ditambahkan ke keranjang belanja.';
                this.name = detail.name || '';
                this.open = true;

                // Auto-close 4 detik
                if (this._autoCloseTimer) clearTimeout(this._autoCloseTimer);
                this._autoCloseTimer = setTimeout(() => this.close(), 4000);
            },
            close() {
                this.open = false;
                if (this._autoCloseTimer) clearTimeout(this._autoCloseTimer);
            },
        };
    }

    /**
     * Helper API: window.cartSuccess({ message, name })
     */
    window.cartSuccess = function (detail = {}) {
        window.dispatchEvent(new CustomEvent('cart-success', { detail }));
    };

    /**
     * Submit cart form via AJAX. Pakai pada form yang ingin trigger modal
     * tanpa redirect: tambahkan attribute data-cart-form ke <form>.
     */
    document.addEventListener('submit', async function (e) {
        const form = e.target;
        if (!form.matches('form[data-cart-form]')) return;

        e.preventDefault();

        const submitBtn = form.querySelector('[type="submit"]');
        const originalLabel = submitBtn ? submitBtn.innerHTML : null;
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.style.opacity = '0.6';
        }

        try {
            const response = await fetch(form.action, {
                method: form.method || 'POST',
                body: new FormData(form),
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                credentials: 'same-origin',
            });

            let data = null;
            try { data = await response.json(); } catch (_) {}

            if (response.ok && data && data.success) {
                // Update cart count badge kalau ada
                if (typeof data.cart_count === 'number') {
                    document.querySelectorAll('[data-cart-count]').forEach(el => {
                        el.textContent = data.cart_count > 99 ? '99+' : data.cart_count;
                        el.style.display = data.cart_count > 0 ? '' : 'none';
                    });
                }

                window.cartSuccess({
                    message: data.message || 'Produk telah ditambahkan ke keranjang belanja.',
                    name: form.dataset.cartName || data.name || '',
                });
            } else if (data && data.message) {
                if (window.showToast) window.showToast('warning', data.message, 'Info');
                else alert(data.message);
            } else {
                if (window.showToast) window.showToast('error', 'Gagal menambahkan ke keranjang. Silakan coba lagi.', 'Gagal');
            }
        } catch (err) {
            if (window.showToast) window.showToast('error', 'Tidak dapat terhubung ke server.', 'Gagal');
        } finally {
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.style.opacity = '';
                if (originalLabel) submitBtn.innerHTML = originalLabel;
            }
        }
    });
</script>
