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
                    <div x-data="{
                        content: `{{ old('body') }}`,
                        
                        // Fungsi Insert Formatting (Bold/List)
                        insert(type) {
                            const textarea = this.$refs.textarea;
                            const start = textarea.selectionStart;
                            const end = textarea.selectionEnd;
                            const text = this.content;
                            let newText = '';
                            let cursorOffset = 0;

                            if (type === 'bold') {
                                newText = text.substring(0, start) + '**' + text.substring(start, end) + '**' + text.substring(end);
                                cursorOffset = 2; // Pindah kursor setelah **
                            } else if (type === 'list') {
                                // Cek apakah baris baru
                                const prefix = (start > 0 && text[start - 1] !== '\n') ? '\n' : '';
                                newText = text.substring(0, start) + prefix + '- ' + text.substring(end);
                                cursorOffset = prefix.length + 2;
                            } else if (type === 'number') {
                                const prefix = (start > 0 && text[start - 1] !== '\n') ? '\n' : '';
                                newText = text.substring(0, start) + prefix + '1. ' + text.substring(end);
                                cursorOffset = prefix.length + 3;
                            }

                            this.content = newText;
                            
                            // Balikin fokus ke textarea dan posisikan kursor
                            this.$nextTick(() => {
                                textarea.focus();
                                textarea.setSelectionRange(start + cursorOffset, end + cursorOffset);
                                this.resize();
                            });
                        },

                        // Fungsi Auto-Resize
                        resize() {
                            this.$refs.textarea.style.height = 'auto';
                            this.$refs.textarea.style.height = this.$refs.textarea.scrollHeight + 'px';
                        },

                        // Logic Pintar saat Enter ditekan
                        handleEnter(e) {
                            const textarea = this.$refs.textarea;
                            const cursorPosition = textarea.selectionStart;
                            const text = this.content;
                            
                            // Ambil baris saat ini (sebelum kursor)
                            const lastNewLine = text.lastIndexOf('\n', cursorPosition - 1);
                            const currentLine = text.substring(lastNewLine + 1, cursorPosition);

                            // Regex Cek List Angka (contoh: '1. ')
                            const numberMatch = currentLine.match(/^(\d+)\.\s/);
                            // Regex Cek List Bullet (contoh: '- ')
                            const bulletMatch = currentLine.match(/^[\-\*]\s/);

                            if (numberMatch) {
                                e.preventDefault();
                                const nextNumber = parseInt(numberMatch[1]) + 1;
                                // Masukkan angka berikutnya
                                const toInsert = '\n' + nextNumber + '. ';
                                this.insertAtCursor(toInsert);
                            } else if (bulletMatch) {
                                e.preventDefault();
                                // Ulangi bullet yang sama
                                const toInsert = '\n' + bulletMatch[0];
                                this.insertAtCursor(toInsert);
                            }
                        },

                        insertAtCursor(textToInsert) {
                            const textarea = this.$refs.textarea;
                            const start = textarea.selectionStart;
                            const end = textarea.selectionEnd;
                            const text = this.content;
                            
                            this.content = text.substring(0, start) + textToInsert + text.substring(end);
                            
                            this.$nextTick(() => {
                                textarea.selectionStart = textarea.selectionEnd = start + textToInsert.length;
                                this.resize();
                            });
                        }
                    }" x-init="resize()" class="relative group">
                        
                        <label for="body" class="block text-sm font-bold text-slate-900 mb-2">
                            Detail Pertanyaan <span class="text-red-500">*</span>
                        </label>

                        {{-- TOOLBAR --}}
                        <div class="flex items-center gap-1 mb-2 border border-slate-200 bg-slate-50 rounded-t-xl px-2 py-1.5 border-b-0">
                            <button type="button" @click="insert('bold')" class="p-1.5 text-slate-500 hover:text-slate-900 hover:bg-slate-200 rounded transition" title="Tebal">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 4h8a4 4 0 014 4 4 4 0 01-4 4H6V4zm0 8h9a4 4 0 014 4 4 4 0 01-4 4H6v-8z"></path></svg>
                            </button>
                            <div class="w-px h-4 bg-slate-300 mx-1"></div>
                            <button type="button" @click="insert('list')" class="p-1.5 text-slate-500 hover:text-slate-900 hover:bg-slate-200 rounded transition" title="List Simbol">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                            </button>
                            <button type="button" @click="insert('number')" class="p-1.5 text-slate-500 hover:text-slate-900 hover:bg-slate-200 rounded transition" title="List Angka">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h2.5M7 10h2.5M13 7h4M13 17h4M7 17h.01M7 14h.01M17 14h.01M13 10h4M13 14h4"></path></svg>
                            </button>
                        </div>

                        {{-- TEXTAREA --}}
                        <textarea id="body"
                                  name="body" 
                                  x-ref="textarea"
                                  x-model="content"
                                  @input="resize()"
                                  @keydown.enter="handleEnter($event)"
                                  rows="6"
                                  placeholder="Tuliskan keluhan Anda..."
                                  class="w-full rounded-b-xl rounded-t-none border border-slate-200 bg-white px-4 py-3 text-sm focus:border-[#CFAF53] focus:ring-2 focus:ring-[#CFAF53]/20 transition resize-none @error('body') border-red-300 @enderror"
                                  required></textarea>
                                  
                        @error('body')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                        
                        <p class="mt-2 text-xs text-slate-500">
                            💡 Tips: Gunakan tombol di atas untuk membuat poin-poin agar lebih mudah dibaca dokter.
                        </p>
                    </div>

                    {{-- Guidelines (SAMA SEPERTI SEBELUMNYA) --}}
                    <div class="rounded-xl bg-blue-50 border border-blue-100 p-4">
                        <p class="text-sm font-bold text-blue-900 mb-2">📋 Panduan Bertanya</p>
                        <ul class="space-y-1 text-xs text-blue-700">
                            <li class="flex items-start gap-2">
                                <svg class="w-4 h-4 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                <span>Gunakan bahasa yang sopan dan jelas</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <svg class="w-4 h-4 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                <span>Jelaskan gejala secara kronologis (urutan waktu)</span>
                            </li>
                        </ul>
                    </div>

                    {{-- Actions (SAMA SEPERTI SEBELUMNYA) --}}
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