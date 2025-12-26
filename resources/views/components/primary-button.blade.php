<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center px-4 py-2 bg-[#CFAF53] border border-transparent rounded-md font-semibold text-xs text-zinc-900 uppercase tracking-widest hover:bg-[#CFAF53]/90 focus:bg-[#CFAF53]/90 active:bg-[#CFAF53]/80 focus:outline-none focus:ring-2 focus:ring-[#CFAF53]/40 focus:ring-offset-2 transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>
