<x-guest-layout :title="'Pilih Layanan'">
<div class="w-full min-h-screen bg-[#FAF8F5] flex flex-col items-center justify-center p-4">
    <!-- Back Navigation -->
    <div class="w-full max-w-2xl mb-6">
        <a href="{{ url('/') }}" class="inline-flex items-center gap-2 text-sm text-[#8C7A63] hover:text-[#6B5D4D] transition-colors font-medium">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            Kembali
        </a>
    </div>

    <!-- Main Card -->
    <div class="w-full max-w-2xl bg-white shadow-sm border border-[#E8E0D8]/60 overflow-hidden" style="border-radius: 32px;">
        <div class="px-6 py-12 sm:px-12 sm:py-14">
            <div class="text-center mb-10">
                <h1 class="font-serif text-3xl sm:text-4xl font-bold text-[#1A1A1A] mb-4">Pilih Layanan</h1>
                <p class="text-sm sm:text-base text-gray-500">Silakan pilih jenis layanan yang ingin Anda jadwalkan hari ini.</p>
            </div>

            <div class="space-y-4 max-w-xl mx-auto">
                <!-- Option 1: Konsultasi Dokter -->
                <a href="{{ route('appointment.konsultasi') }}" class="group flex items-center justify-between border border-gray-200 p-5 sm:p-6 transition-all duration-200 hover:border-[#8C7A63] hover:shadow-md hover:bg-[#FAF8F5]/50 focus:outline-none focus:ring-2 focus:ring-[#8C7A63] focus:ring-offset-2" style="border-radius: 16px;">
                    <div class="flex items-center gap-4 sm:gap-5">
                        <div class="flex h-5 w-5 flex-shrink-0 items-center justify-center rounded-full border border-gray-300 group-hover:border-[#8C7A63] transition-colors">
                            <!-- Radio indicator (empty) -->
                        </div>
                        <div>
                            <h3 class="text-sm sm:text-base font-bold text-[#1A1A1A]">Konsultasi Dokter</h3>
                            <p class="mt-1 text-xs sm:text-sm text-gray-500">Bertemu dan berdiskusi langsung dengan dokter spesialis kami.</p>
                        </div>
                    </div>
                    <svg class="h-5 w-5 flex-shrink-0 text-gray-400 group-hover:text-[#8C7A63] transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </a>

                <!-- Option 2: Booking Treatment -->
                <a href="{{ route('appointment.booking-treatment') }}" class="group flex items-center justify-between border border-gray-200 p-5 sm:p-6 transition-all duration-200 hover:border-[#8C7A63] hover:shadow-md hover:bg-[#FAF8F5]/50 focus:outline-none focus:ring-2 focus:ring-[#8C7A63] focus:ring-offset-2" style="border-radius: 16px;">
                    <div class="flex items-center gap-4 sm:gap-5">
                        <div class="flex h-5 w-5 flex-shrink-0 items-center justify-center rounded-full border border-gray-300 group-hover:border-[#8C7A63] transition-colors">
                            <!-- Radio indicator (empty) -->
                        </div>
                        <div>
                            <h3 class="text-sm sm:text-base font-bold text-[#1A1A1A]">Booking Treatment</h3>
                            <p class="mt-1 text-xs sm:text-sm text-gray-500">Pesan jadwal untuk treatment estetika rutin Anda.</p>
                        </div>
                    </div>
                    <svg class="h-5 w-5 flex-shrink-0 text-gray-400 group-hover:text-[#8C7A63] transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </a>
            </div>
        </div>
    </div>
</div>
</x-guest-layout>
