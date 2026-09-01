<x-guest-layout title="Ajukan Pertanyaan">
    <div class="flex flex-col gap-8 pb-12">

        {{-- Breadcrumb (SAMA SEPERTI SEBELUMNYA) --}}
        <section class="mx-auto w-full max-w-4xl px-4 pt-8">
            <nav class="flex items-center gap-2 text-sm text-slate-500">
                <a href="{{ route('forum.index') }}" class="hover:text-[#CFAF53] transition">Forum</a>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                <span class="text-slate-900 font-medium">Ajukan Pertanyaan</span>
            </nav>
        </section>

        {{-- SECTION: FORM --}}
        <section class="mx-auto w-full max-w-4xl px-4">
            <div class="rounded-3xl border border-slate-200 bg-white p-6 sm:p-10 shadow-sm">
                
                {{-- Header (SAMA SEPERTI SEBELUMNYA) --}}
                <div class="mb-8">
                    <div class="inline-flex items-center gap-2 rounded-full border border-[#CFAF53]/30 bg-[#CFAF53]/10 px-4 py-2 text-xs font-bold uppercase tracking-widest text-[#8B6F1B] mb-4">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                        Pertanyaan Baru
                    </div>
                    
                    <h1 class="text-2xl sm:text-3xl font-bold text-slate-900 mb-2">
                        Ajukan Pertanyaan Anda
                    </h1>
                    <p class="text-slate-600">
                        Jelaskan keluhan atau pertanyaan Anda dengan detail agar dokter kami dapat memberikan jawaban yang tepat.
                    </p>
                </div>

                {{-- Form --}}
                <form method="POST" action="{{ route('forum.store') }}" class="space-y-6">
                    @csrf

                    {{-- Anonymous Toggle (SAMA SEPERTI SEBELUMNYA) --}}
                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                        <label class="flex items-start gap-3 cursor-pointer">
                            <input type="checkbox" name="is_anonymous" value="1" class="mt-1 h-5 w-5 rounded border-slate-300 text-[#CFAF53] focus:ring-[#CFAF53]" {{ old('is_anonymous') ? 'checked' : '' }}>
                            <div class="flex-1">
                                <span class="font-bold text-slate-900 text-sm">Kirim sebagai Anonim</span>
                                <p class="text-xs text-slate-500 mt-1">
                                    Nama Anda tidak akan ditampilkan kepada pengguna lain. Namun dokter tetap dapat melihat identitas Anda.
                                </p>
                            </div>
                        </label>
                    </div>

                    {{-- Title (SAMA SEPERTI SEBELUMNYA) --}}
                    <div>
                        <label for="title" class="block text-sm font-bold text-slate-900 mb-2">
                            Judul Pertanyaan <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="title" name="title" value="{{ old('title') }}" placeholder="Contoh: Bagaimana cara mengatasi jerawat membandel?" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm focus:border-[#CFAF53] focus:ring-2 focus:ring-[#CFAF53]/20 transition @error('title') border-red-300 @enderror" required>
                        @error('title')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Body with SMART TEXTAREA --}}
                    @include('forum._partials._body_editor')

                    {{-- Guidelines --}}
                    @include('forum._partials._form_guidelines')

                    <div class="flex flex-col sm:flex-row gap-4 pt-4 border-t border-slate-200">
                        <button type="submit" class="flex-1 sm:flex-none inline-flex items-center justify-center gap-2 rounded-xl bg-[#CFAF53] px-8 py-3.5 text-sm font-bold text-slate-900 shadow-lg shadow-[#CFAF53]/20 transition-all hover:bg-[#CFAF53]/90 hover:-translate-y-0.5">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path></svg>
                            Kirim Pertanyaan
                        </button>
                        <a href="{{ route('forum.index') }}" class="inline-flex items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-6 py-3.5 text-sm font-bold text-slate-700 transition-all hover:border-[#CFAF53] hover:text-[#CFAF53]">
                            Batal
                        </a>
                    </div>
                </form>

            </div>
        </section>

    </div>
</x-guest-layout>
