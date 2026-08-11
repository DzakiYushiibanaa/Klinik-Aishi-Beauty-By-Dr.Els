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