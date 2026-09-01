<div id="wa-widget" x-data="{ open: false }" class="fixed bottom-20 lg:bottom-6 right-6 z-[2147483647]">

        {{-- Chat Popup --}}
        <div x-show="open" x-cloak x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-4 scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 scale-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-y-0 scale-100"
            x-transition:leave-end="opacity-0 translate-y-4 scale-95" @click.away="open = false"
            style="position:absolute; bottom:72px; right:0; width:340px; border-radius:16px; overflow:hidden; background:#fff; box-shadow: 0 20px 60px rgba(0,0,0,0.3), 0 0 0 1px rgba(0,0,0,0.05); z-index:2147483647;">

            {{-- Header --}}
            <div
                style="background:#075E54; padding:16px 20px; display:flex; align-items:center; justify-content:space-between;">
                <div style="display:flex; align-items:center; gap:12px;">
                    <div
                        style="width:42px; height:42px; border-radius:50%; background:rgba(255,255,255,0.2); display:flex; align-items:center; justify-content:center;">
                        <svg style="width:22px; height:22px; color:#fff;" fill="currentColor" viewBox="0 0 24 24">
                            <path
                                d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z" />
                        </svg>
                    </div>
                    <div>
                        <p style="color:#fff; font-weight:700; font-size:14px; margin:0;">Aishi Beauty</p>
                        <p style="color:#a7f3d0; font-size:11px; margin:2px 0 0 0;">Biasanya membalas dalam 1 jam</p>
                    </div>
                </div>
                <button @click="open = false"
                    style="color:rgba(255,255,255,0.7); background:none; border:none; cursor:pointer; padding:4px;">
                    <svg style="width:20px; height:20px;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            {{-- Chat Body --}}
            <div style="background:#ECE5DD; padding:24px 20px; min-height:120px;">
                <div
                    style="background:#fff; border-radius:0 12px 12px 12px; padding:12px 16px; max-width:85%; box-shadow:0 1px 2px rgba(0,0,0,0.08);">
                    <p style="font-size:13px; color:#1f2937; font-weight:600; margin:0 0 4px 0;">Aishi Beauty</p>
                    <p style="font-size:13px; color:#4b5563; line-height:1.6; margin:0;">Halo! 👋<br>Ada yang bisa kami
                        bantu? Silakan chat untuk konsultasi atau informasi lebih lanjut.</p>
                    <p style="font-size:10px; color:#9ca3af; text-align:right; margin:6px 0 0 0;">
                        {{ now()->format('H:i') }}
                    </p>
                </div>
            </div>

            {{-- Action --}}
            <div style="background:#fff; padding:16px 20px; border-top:1px solid #f3f4f6;">
                <a href="https://wa.me/+6285780050007?text=Halo%20Aishi%20Beauty%2C%20saya%20ingin%20bertanya%20tentang%20layanan%20klinik."
                    target="_blank"
                    style="display:flex; align-items:center; justify-content:center; gap:8px; background:#25D366; color:#fff; font-weight:700; font-size:14px; padding:12px; border-radius:12px; text-decoration:none;"
                    onmouseover="this.style.background='#1EBE5B'" onmouseout="this.style.background='#25D366'">
                    <svg style="width:20px; height:20px;" fill="currentColor" viewBox="0 0 24 24">
                        <path
                            d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z" />
                    </svg>
                    Mulai Chat
                </a>
                <p style="font-size:10px; color:#9ca3af; text-align:center; margin:8px 0 0 0;">Powered by WhatsApp</p>
            </div>
        </div>

        {{-- Floating Green Button --}}
        <button @click="open = !open"
            style="width:56px; height:56px; border-radius:50%; background:#25D366; border:none; cursor:pointer; display:flex; align-items:center; justify-content:center; box-shadow:0 4px 15px rgba(37,211,102,0.4); transition:all 0.3s;"
            onmouseover="this.style.background='#1EBE5B'; this.style.transform='scale(1.1)'"
            onmouseout="this.style.background='#25D366'; this.style.transform='scale(1)'"
            aria-label="Chat via WhatsApp">
            <template x-if="!open">
                <svg style="width:28px; height:28px; color:#fff;" fill="currentColor" viewBox="0 0 24 24">
                    <path
                        d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z" />
                </svg>
            </template>
            <template x-if="open">
                <svg style="width:24px; height:24px; color:#fff;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </template>
        </button>
    </div>