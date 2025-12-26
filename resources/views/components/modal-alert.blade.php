<div 
    x-data="{ open: false, message: '' }"
    x-show="open"
    x-transition
    class="fixed inset-0 z-50 flex items-center justify-center bg-black/40"
    style="display: none"
    @open-alert.window="
        message = $event.detail.message;
        open = true;
    "
>
    <div class="w-full max-w-sm rounded-2xl bg-white p-6 shadow-xl">
        <h3 class="text-lg font-semibold text-slate-900 mb-2">
            Perhatian
        </h3>

        <p class="text-sm text-slate-700" x-text="message"></p>

        <button
            @click="open = false"
            class="mt-4 w-full rounded-xl bg-[#CFAF53] px-4 py-2 text-sm font-semibold text-zinc-900 hover:bg-[#CFAF53]/90"
        >
            Mengerti
        </button>
    </div>
</div>
