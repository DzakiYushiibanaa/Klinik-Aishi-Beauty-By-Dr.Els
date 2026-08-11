@props([
    'titleLine1' => 'Mulai Perjalanan',
    'titleHighlight' => 'Kulit Sehatmu.',
    'description' => 'Buat akun untuk akses mudah ke jadwal booking, konsultasi dokter, dan riwayat perawatan Anda di klinik kami.',
])

<div class="relative hidden md:flex flex-col justify-between bg-slate-900 p-10 text-white overflow-hidden">
                
    {{-- 1. BACKGROUND BASE --}}
    <div class="absolute inset-0 z-0 bg-slate-900"></div>

    {{-- 2. GOLD GLOW EFFECT (Atas Kanan) --}}
    <div class="absolute -top-20 -right-20 h-96 w-96 rounded-full bg-[#CFAF53] opacity-20 blur-[80px]"></div>

    {{-- 3. GOLD GLOW EFFECT (Bawah Kiri) --}}
    <div class="absolute -bottom-20 -left-20 h-80 w-80 rounded-full bg-[#CFAF53] opacity-10 blur-[60px]"></div>

    {{-- 4. SUBTLE PATTERN (Garis Halus) --}}
    <div class="absolute inset-0 z-0 opacity-5" 
         style="background-image: radial-gradient(#ffffff 1px, transparent 1px); background-size: 30px 30px;">
    </div>

    {{-- Konten --}}
    <div class="relative z-10">
        <a href="{{ route('landing') }}" class="flex items-center gap-2 font-bold text-2xl tracking-tight text-white">
            <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-[#CFAF53] text-white">A</span>
            <span>Aishi<span class="text-[#CFAF53]">Beauty</span></span>
        </a>
    </div>

    <div class="relative z-10 mb-6">
        <h2 class="text-3xl font-bold leading-tight">
            {{ $titleLine1 }} <br>
            <span class="text-[#CFAF53]">{{ $titleHighlight }}</span>
        </h2>
        <p class="mt-4 text-slate-300 text-sm leading-relaxed max-w-sm border-l-2 border-[#CFAF53]/50 pl-4">
            {{ $description }}
        </p>
    </div>

    <div class="relative z-10 text-xs text-slate-500 flex justify-between items-end">
        <p>&copy; {{ date('Y') }} Aishi Beauty By Dr.Els.</p>
        <div class="flex gap-2">
            <div class="h-1.5 w-1.5 rounded-full bg-[#CFAF53]"></div>
            <div class="h-1.5 w-1.5 rounded-full bg-slate-700"></div>
            <div class="h-1.5 w-1.5 rounded-full bg-slate-700"></div>
        </div>
    </div>
</div>
