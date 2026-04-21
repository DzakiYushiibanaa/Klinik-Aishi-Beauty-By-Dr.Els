<div x-data="toastComponent()"
     @notify.window="addToast($event.detail)"
     x-init="
        @if(session('success')) setTimeout(() => addToast({type: 'success', title: 'Berhasil', message: '{{ session('success') }}'}), 100); @endif
        @if(session('error')) setTimeout(() => addToast({type: 'error', title: 'Gagal', message: '{{ session('error') }}'}), 100); @endif
        @if(session('warning')) setTimeout(() => addToast({type: 'warning', title: 'Peringatan', message: '{{ session('warning') }}'}), 100); @endif
        @if(session('info')) setTimeout(() => addToast({type: 'info', title: 'Informasi', message: '{{ session('info') }}'}), 100); @endif
     "
     class="fixed top-4 right-4 z-[9999] flex flex-col gap-3 w-full max-w-sm pointer-events-none">
    
    <template x-for="toast in toasts" :key="toast.id">
        <div x-show="toast.visible"
             x-transition:enter="transform ease-out duration-300 transition"
             x-transition:enter-start="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
             x-transition:enter-end="translate-y-0 opacity-100 sm:translate-x-0"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="pointer-events-auto flex w-full max-w-md bg-white shadow-lg rounded-xl overflow-hidden border border-gray-100"
             role="alert">
            
            <div class="flex items-center justify-center w-12"
                 :class="{
                     'bg-green-500': toast.type === 'success',
                     'bg-rose-500': toast.type === 'error',
                     'bg-amber-500': toast.type === 'warning',
                     'bg-blue-500': toast.type === 'info'
                 }">
                <!-- Success Icon -->
                <svg x-show="toast.type === 'success'" class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                <!-- Error Icon -->
                <svg x-show="toast.type === 'error'" class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
                <!-- Warning Icon -->
                <svg x-show="toast.type === 'warning'" class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                <!-- Info Icon -->
                <svg x-show="toast.type === 'info'" class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>

            <div class="px-4 py-3 bg-white w-full flex justify-between items-center">
                <div class="flex flex-col">
                    <p class="text-sm font-semibold text-gray-800 capitalize" x-text="toast.title || toast.type"></p>
                    <p class="text-sm text-gray-500 mt-0.5" x-text="toast.message"></p>
                </div>
                <button @click="removeToast(toast.id)" class="text-gray-400 hover:text-gray-600 transition-colors focus:outline-none shrink-0 ml-4">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </template>
</div>

<script>
    function toastComponent() {
        return {
            toasts: [],
            addToast(detail) {
                const id = Date.now() + Math.random().toString(36).substr(2, 9);
                const type = detail.type || 'info'; // success, error, warning, info
                const message = detail.message || '';
                const title = detail.title || '';
                const duration = detail.duration || 4000;

                const toast = {
                    id,
                    type,
                    title,
                    message,
                    visible: false,
                };

                this.toasts.push(toast);
                
                // Trigger animation
                setTimeout(() => {
                    const idx = this.toasts.findIndex(t => t.id === id);
                    if (idx > -1) this.toasts[idx].visible = true;
                }, 50);

                if (duration > 0) {
                    setTimeout(() => {
                        this.removeToast(id);
                    }, duration);
                }
            },
            removeToast(id) {
                const idx = this.toasts.findIndex(t => t.id === id);
                if (idx > -1) {
                    this.toasts[idx].visible = false;
                    // Wait for leave animation before completely removing
                    setTimeout(() => {
                        this.toasts = this.toasts.filter(t => t.id !== id);
                    }, 300);
                }
            }
        }
    }

    // Global helper function to trigger toast from any JS code
    window.showToast = function(type, message, title = null, duration = 4000) {
        window.dispatchEvent(new CustomEvent('notify', {
            detail: { type, message, title, duration }
        }));
    }
</script>
