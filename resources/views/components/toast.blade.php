{{--
  Toast Notification — premium modern design
  ---------------------------------------------
  Trigger via JS:
      window.showToast(type, message, title, duration)
      types: 'success' | 'error' | 'warning' | 'info'

  Trigger via Laravel session:
      return back()->with('success', 'Pesan berhasil disimpan');
--}}

<div x-data="toastComponent()"
     @notify.window="addToast($event.detail)"
     x-init="
        @if(session('success')) setTimeout(() => addToast({type: 'success', title: 'Berhasil', message: '{{ session('success') }}'}), 100); @endif
        @if(session('error')) setTimeout(() => addToast({type: 'error', title: 'Gagal', message: '{{ session('error') }}'}), 100); @endif
        @if(session('warning')) setTimeout(() => addToast({type: 'warning', title: 'Peringatan', message: '{{ session('warning') }}'}), 100); @endif
        @if(session('info')) setTimeout(() => addToast({type: 'info', title: 'Informasi', message: '{{ session('info') }}'}), 100); @endif
     "
     class="aishi-toast-stack"
     aria-live="polite"
     aria-atomic="true">

    <template x-for="toast in toasts" :key="toast.id">
        <div x-show="toast.visible"
             x-transition:enter="aishi-toast--enter"
             x-transition:enter-start="aishi-toast--enter-start"
             x-transition:enter-end="aishi-toast--enter-end"
             x-transition:leave="aishi-toast--leave"
             x-transition:leave-start="aishi-toast--leave-start"
             x-transition:leave-end="aishi-toast--leave-end"
             :class="`aishi-toast aishi-toast--${toast.type}`"
             role="status">

            {{-- Icon (small circle, soft tinted) --}}
            <span class="aishi-toast__icon" aria-hidden="true">
                {{-- Success --}}
                <svg x-show="toast.type === 'success'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.6" d="M5 13l4 4L19 7"/>
                </svg>
                {{-- Error --}}
                <svg x-show="toast.type === 'error'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.4" d="M6 18L18 6M6 6l12 12"/>
                </svg>
                {{-- Warning --}}
                <svg x-show="toast.type === 'warning'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.4" d="M12 9v3m0 3.5h.01"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M10.288 3.86l-7.83 13.566A2 2 0 004.18 20.5h15.64a2 2 0 001.722-3.074L13.713 3.86a2 2 0 00-3.425 0z"/>
                </svg>
                {{-- Info --}}
                <svg x-show="toast.type === 'info'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01"/>
                    <circle cx="12" cy="12" r="9" stroke-width="1.8" fill="none"/>
                </svg>
            </span>

            {{-- Body --}}
            <div class="aishi-toast__body">
                <p class="aishi-toast__title" x-show="toast.title" x-text="toast.title"></p>
                <p class="aishi-toast__message" x-text="toast.message"></p>
            </div>

            {{-- Close --}}
            <button type="button"
                    @click="removeToast(toast.id)"
                    class="aishi-toast__close"
                    aria-label="Tutup notifikasi">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>

            {{-- Progress bar (optional, subtle) --}}
            <span x-show="toast.duration > 0"
                  class="aishi-toast__progress"
                  :style="`animation-duration: ${toast.duration}ms`"
                  aria-hidden="true"></span>
        </div>
    </template>
</div>

<style>
    /* ─────────  AISHI TOAST  ───────── */
    .aishi-toast-stack {
        position: fixed;
        top: 1rem;
        right: 1rem;
        left: 1rem;
        z-index: 9999;
        display: flex;
        flex-direction: column;
        gap: 0.625rem;
        pointer-events: none;
    }
    @media (min-width: 640px) {
        .aishi-toast-stack {
            top: 1.25rem;
            right: 1.25rem;
            left: auto;
            width: 360px;
        }
    }

    .aishi-toast {
        position: relative;
        pointer-events: auto;
        display: flex;
        align-items: flex-start;
        gap: 0.75rem;
        padding: 0.875rem 0.875rem 0.875rem 1rem;
        border-radius: 14px;
        background: rgba(255, 255, 255, 0.95);
        border: 1px solid rgba(232, 224, 216, 0.7);
        backdrop-filter: blur(10px) saturate(140%);
        -webkit-backdrop-filter: blur(10px) saturate(140%);
        box-shadow:
            0 1px 2px rgba(26, 26, 26, 0.04),
            0 12px 24px -8px rgba(26, 26, 26, 0.16),
            0 4px 8px -4px rgba(26, 26, 26, 0.08);
        font-family: 'Plus Jakarta Sans', sans-serif;
        overflow: hidden;
    }

    /* Icon container — small soft-tinted circle */
    .aishi-toast__icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 28px;
        height: 28px;
        border-radius: 9999px;
        flex-shrink: 0;
        margin-top: 1px;
        box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.35);
    }
    .aishi-toast__icon svg { width: 14px; height: 14px; }

    /* Body */
    .aishi-toast__body {
        flex: 1;
        min-width: 0;
        display: flex;
        flex-direction: column;
        gap: 2px;
    }
    .aishi-toast__title {
        margin: 0;
        font-size: 0.875rem;
        font-weight: 700;
        color: #1A1A1A;
        line-height: 1.3;
        letter-spacing: -0.005em;
    }
    .aishi-toast__message {
        margin: 0;
        font-size: 0.8125rem;
        line-height: 1.5;
        color: #64748B;
        word-break: break-word;
        overflow-wrap: anywhere;
    }

    /* Close */
    .aishi-toast__close {
        flex-shrink: 0;
        width: 24px;
        height: 24px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 9999px;
        background: transparent;
        color: #94A3B8;
        border: 0;
        cursor: pointer;
        opacity: 0.7;
        transition: background 160ms ease, color 160ms ease, opacity 160ms ease;
        margin-top: -2px;
        margin-right: -2px;
    }
    .aishi-toast__close svg { width: 14px; height: 14px; }
    .aishi-toast__close:hover {
        background: rgba(15, 23, 42, 0.06);
        color: #1A1A1A;
        opacity: 1;
    }

    /* Progress bar (subtle bottom strip indicating remaining time) */
    .aishi-toast__progress {
        position: absolute;
        left: 0; bottom: 0;
        height: 2px;
        width: 100%;
        background: currentColor;
        opacity: 0.18;
        transform-origin: left center;
        animation-name: aishiToastShrink;
        animation-timing-function: linear;
        animation-fill-mode: forwards;
    }

    /* ── Variant : SUCCESS ── */
    .aishi-toast--success {
        background: linear-gradient(180deg, rgba(236, 253, 245, 0.85) 0%, rgba(255, 255, 255, 0.95) 80%);
        border-color: rgba(16, 185, 129, 0.28);
    }
    .aishi-toast--success .aishi-toast__icon {
        background: linear-gradient(135deg, #10B981 0%, #047857 100%);
        color: #ffffff;
    }
    .aishi-toast--success .aishi-toast__progress { color: #10B981; }

    /* ── Variant : ERROR ── */
    .aishi-toast--error {
        background: linear-gradient(180deg, rgba(254, 242, 242, 0.85) 0%, rgba(255, 255, 255, 0.95) 80%);
        border-color: rgba(244, 63, 94, 0.26);
    }
    .aishi-toast--error .aishi-toast__icon {
        background: linear-gradient(135deg, #F43F5E 0%, #BE123C 100%);
        color: #ffffff;
    }
    .aishi-toast--error .aishi-toast__progress { color: #F43F5E; }

    /* ── Variant : WARNING ── */
    .aishi-toast--warning {
        background: linear-gradient(180deg, rgba(255, 248, 235, 0.9) 0%, rgba(255, 255, 255, 0.95) 80%);
        border-color: rgba(245, 158, 11, 0.28);
    }
    .aishi-toast--warning .aishi-toast__icon {
        background: linear-gradient(135deg, #F59E0B 0%, #B45309 100%);
        color: #ffffff;
    }
    .aishi-toast--warning .aishi-toast__progress { color: #F59E0B; }

    /* ── Variant : INFO ── */
    .aishi-toast--info {
        background: linear-gradient(180deg, rgba(239, 246, 255, 0.85) 0%, rgba(255, 255, 255, 0.95) 80%);
        border-color: rgba(59, 130, 246, 0.26);
    }
    .aishi-toast--info .aishi-toast__icon {
        background: linear-gradient(135deg, #3B82F6 0%, #1D4ED8 100%);
        color: #ffffff;
    }
    .aishi-toast--info .aishi-toast__progress { color: #3B82F6; }

    /* Transitions */
    .aishi-toast--enter { transition: opacity 220ms ease, transform 220ms cubic-bezier(0.34, 1.4, 0.64, 1); }
    .aishi-toast--enter-start { opacity: 0; transform: translateY(-6px) scale(0.97); }
    .aishi-toast--enter-end   { opacity: 1; transform: translateY(0) scale(1); }
    .aishi-toast--leave { transition: opacity 180ms ease, transform 180ms ease; }
    .aishi-toast--leave-start { opacity: 1; transform: translateY(0) scale(1); }
    .aishi-toast--leave-end   { opacity: 0; transform: translateY(-4px) scale(0.97); }

    @keyframes aishiToastShrink {
        from { transform: scaleX(1); }
        to   { transform: scaleX(0); }
    }

    @media (prefers-reduced-motion: reduce) {
        .aishi-toast--enter,
        .aishi-toast--leave,
        .aishi-toast__close { transition: none; }
        .aishi-toast--enter-start,
        .aishi-toast--enter-end,
        .aishi-toast--leave-start,
        .aishi-toast--leave-end { transform: none; }
        .aishi-toast__progress { animation: none; opacity: 0; }
    }
</style>

<script>
    function toastComponent() {
        return {
            toasts: [],
            maxVisible: 3,

            addToast(detail) {
                const id = Date.now() + Math.random().toString(36).substr(2, 9);
                const type = detail.type || 'info';
                const message = detail.message || '';
                const title = detail.title || '';
                const duration = typeof detail.duration === 'number' ? detail.duration : 4000;

                const toast = {
                    id,
                    type,
                    title,
                    message,
                    duration,
                    visible: false,
                };

                this.toasts.push(toast);

                // Cap stack at maxVisible (drop oldest)
                while (this.toasts.length > this.maxVisible) {
                    const removed = this.toasts.shift();
                    if (removed) removed.visible = false;
                }

                // Trigger entrance
                setTimeout(() => {
                    const idx = this.toasts.findIndex(t => t.id === id);
                    if (idx > -1) this.toasts[idx].visible = true;
                }, 30);

                if (duration > 0) {
                    setTimeout(() => this.removeToast(id), duration);
                }
            },

            removeToast(id) {
                const idx = this.toasts.findIndex(t => t.id === id);
                if (idx > -1) {
                    this.toasts[idx].visible = false;
                    // Allow leave transition before removal
                    setTimeout(() => {
                        this.toasts = this.toasts.filter(t => t.id !== id);
                    }, 220);
                }
            }
        }
    }

    // Global helper — same API
    window.showToast = function(type, message, title = null, duration = 4000) {
        window.dispatchEvent(new CustomEvent('notify', {
            detail: { type, message, title, duration }
        }));
    }
</script>
