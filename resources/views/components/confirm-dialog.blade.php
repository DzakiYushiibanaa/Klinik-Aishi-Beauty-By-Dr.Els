{{--
  Confirmation Dialog (global)
  ----------------------------------------------------------------------------
  Pasangkan satu kali di layout (sudah dipasang di layouts/app.blade.php).
  Pakai dari mana saja:

  // Sederhana (return Promise<boolean>)
  const ok = await window.confirmDialog({
      title: 'Hapus Data',
      message: 'Yakin ingin menghapus data ini? Tindakan tidak dapat dibatalkan.',
      confirmText: 'Hapus',
      cancelText: 'Batal',
      variant: 'danger', // 'danger' | 'primary' | 'warning'
  });
  if (ok) { ...lakukan aksi... }

  // Atau bind ke event:
  <button onclick="confirmThen(this, () => form.submit())">Hapus</button>
--}}

<div x-data="confirmDialogStore()"
     x-show="open"
     x-cloak
     @keydown.escape.window="cancel()"
     class="fixed inset-0 z-[10000] flex items-center justify-center bg-black/50 backdrop-blur-sm p-4"
     role="dialog"
     aria-modal="true"
     aria-labelledby="confirm-dialog-title"
     @click.stop
     x-transition:enter="ease-out duration-200"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="ease-in duration-150"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0">

    <div class="bg-white w-full max-w-sm rounded-2xl p-6 shadow-2xl"
         @click.away="cancel()"
         x-transition:enter="ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="ease-in duration-150"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95">

        {{-- Icon --}}
        <div class="flex items-center justify-center w-12 h-12 rounded-full mb-4"
             :class="{
                 'bg-rose-100 text-rose-600': variant === 'danger',
                 'bg-amber-100 text-amber-600': variant === 'warning',
                 'bg-[#CFAF53]/15 text-[#CFAF53]': variant === 'primary' || variant === 'info',
             }">
            {{-- Danger / warning icon --}}
            <svg x-show="variant === 'danger' || variant === 'warning'" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
            {{-- Info / question --}}
            <svg x-show="variant === 'primary' || variant === 'info'" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>

        <h3 id="confirm-dialog-title"
            class="text-lg font-bold text-[#1A1A1A]"
            x-text="title"></h3>
        <p class="text-sm text-slate-500 mt-2 leading-relaxed" x-text="message"></p>

        <div class="flex gap-3 mt-6">
            <button type="button"
                    @click="cancel()"
                    class="flex-1 py-2.5 rounded-xl border border-slate-200 text-sm font-bold text-slate-700 hover:bg-slate-50 cursor-pointer transition-colors"
                    x-text="cancelText"></button>
            <button type="button"
                    @click="confirm()"
                    :class="{
                        'bg-rose-600 hover:bg-rose-700 text-white': variant === 'danger',
                        'bg-amber-500 hover:bg-amber-600 text-white': variant === 'warning',
                        'bg-[#CFAF53] hover:bg-[#b09444] text-white': variant === 'primary' || variant === 'info',
                    }"
                    class="flex-1 py-2.5 rounded-xl text-sm font-bold transition-colors cursor-pointer"
                    x-text="confirmText"></button>
        </div>
    </div>
</div>

<script>
    function confirmDialogStore() {
        return {
            open: false,
            title: 'Konfirmasi',
            message: '',
            confirmText: 'Ya',
            cancelText: 'Batal',
            variant: 'primary',
            _resolver: null,

            init() {
                window.addEventListener('confirm-dialog:open', (e) => {
                    const opts = e.detail?.options || {};
                    this.title       = opts.title       ?? 'Konfirmasi';
                    this.message     = opts.message     ?? '';
                    this.confirmText = opts.confirmText ?? 'Ya';
                    this.cancelText  = opts.cancelText  ?? 'Batal';
                    this.variant     = opts.variant     ?? 'primary';
                    this._resolver   = e.detail?.resolver || null;
                    this.open = true;
                });
            },
            confirm() {
                this.open = false;
                if (this._resolver) this._resolver(true);
                this._resolver = null;
            },
            cancel() {
                this.open = false;
                if (this._resolver) this._resolver(false);
                this._resolver = null;
            },
        };
    }

    /**
     * Tampilkan confirmation dialog. Mengembalikan Promise<boolean>.
     */
    window.confirmDialog = function (options = {}) {
        return new Promise((resolve) => {
            window.dispatchEvent(new CustomEvent('confirm-dialog:open', {
                detail: { options, resolver: resolve },
            }));
        });
    };

    /**
     * Helper: konfirmasi lalu jalankan callback jika user setuju.
     * Cocok dipakai di onclick attribute.
     */
    window.confirmThen = async function (sender, callback, options = {}) {
        const ok = await window.confirmDialog(Object.assign({
            title: 'Konfirmasi',
            message: 'Apakah Anda yakin?',
            variant: 'primary',
        }, options));
        if (ok && typeof callback === 'function') callback(sender);
    };
</script>
