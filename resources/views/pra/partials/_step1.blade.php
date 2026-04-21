<!-- STEP 1: Kondisi Kulit -->
<div x-show="step === 1" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-x-4" x-transition:enter-end="opacity-100 translate-x-0">
    <div class="space-y-6">
        <!-- Keluhan Utama -->
        <div class="space-y-2">
            <label class="block text-sm font-bold text-[#1A1A1A]">Keluhan Utama</label>
            <textarea name="keluhan_utama" rows="4" required x-model="keluhan_utama"
                class="w-full rounded-xl border bg-white px-4 py-3 text-sm text-[#1A1A1A] placeholder:text-gray-400 transition-all focus:outline-none focus:ring-2 resize-none"
                :class="keluhan_utama_error ? 'border-rose-400 focus:border-rose-400 focus:ring-rose-400/30' : 'border-gray-200 focus:border-[#8C7A63] focus:ring-[#8C7A63]/30'"
                placeholder="Ceritakan secara detail masalah atau keluhan pada kulit Anda saat ini...(cth: Jerawat, flek hitam, keriput,dll)"></textarea>
            <template x-if="keluhan_utama_error">
                <p class="text-xs text-rose-500 flex items-center gap-1"><svg class="w-3.5 h-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01"/></svg> <span x-text="keluhan_utama_error"></span></p>
            </template>
        </div>

        <!-- Jenis Kulit -->
        <div class="space-y-3">
            <label class="block text-sm font-bold text-[#1A1A1A]">Jenis Kulit</label>
            <div class="flex flex-wrap gap-2">
                @foreach(['Normal','Kering','Berminyak','Kombinasi','Sensitif'] as $jk)
                <label class="cursor-pointer">
                    <input type="radio" name="jenis_kulit" value="{{ $jk }}" class="peer sr-only" x-model="jenis_kulit">
                    <div class="rounded-full border bg-white px-5 py-2.5 text-sm font-medium text-gray-600 transition-all hover:border-[#8C7A63]/50 hover:bg-[#FAF8F5] peer-checked:border-[#8C7A63] peer-checked:bg-[#8C7A63]/10 peer-checked:text-[#6B5D4D] @error('jenis_kulit') border-rose-300 @else border-gray-200 @enderror">
                        {{ $jk }}
                    </div>
                </label>
                @endforeach
            </div>
            @error('jenis_kulit')
                <p class="text-xs text-rose-500 flex items-center gap-1"><svg class="w-3.5 h-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01"/></svg> {{ $message }}</p>
            @enderror
        </div>

        <!-- Golongan Darah -->
        <div class="space-y-2">
            <label class="block text-sm font-bold text-[#1A1A1A]">Golongan Darah</label>
            <select name="golongan_darah" x-model="golongan_darah"
                class="w-full rounded-xl border bg-white px-4 py-3 text-sm text-[#1A1A1A] transition-all focus:outline-none focus:ring-2 appearance-none bg-[url('data:image/svg+xml;charset=UTF-8,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%2212%22%20height%3D%2212%22%20viewBox%3D%220%200%2012%2012%22%3E%3Cpath%20fill%3D%22%239ca3af%22%20d%3D%22M6%208L1%203h10z%22%2F%3E%3C%2Fsvg%3E')] bg-no-repeat bg-[right_1rem_center] @error('golongan_darah') border-rose-400 focus:border-rose-400 focus:ring-rose-400/30 @else border-gray-200 focus:border-[#8C7A63] focus:ring-[#8C7A63]/30 @enderror">
                <option value="" disabled>Pilih golongan darah...</option>
                @foreach(['A','B','AB','O'] as $gd)
                <option value="{{ $gd }}" {{ old('golongan_darah') === $gd ? 'selected' : '' }}>{{ $gd }}</option>
                @endforeach
                <option value="-" {{ old('golongan_darah') === '-' ? 'selected' : '' }}>Tidak Tahu / Lewati</option>
            </select>
            @error('golongan_darah')
                <p class="text-xs text-rose-500 flex items-center gap-1"><svg class="w-3.5 h-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01"/></svg> {{ $message }}</p>
            @enderror
        </div>
    </div>
</div>
