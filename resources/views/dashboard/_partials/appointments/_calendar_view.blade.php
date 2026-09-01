{{-- Partial: Calendar View --}}
    {{-- CALENDAR VIEW --}}
    <div x-show="viewMode === 'calendar'" x-transition.opacity style="display:none">
        <div x-data="appCalendar()">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <span x-text="monthNames[month]" class="text-lg font-bold text-[#1A1A1A]"></span>
                    <span x-text="year" class="ml-1 text-lg text-slate-400"></span>
                </div>
                <div class="flex items-center rounded-lg border border-[#E8E0D8]/60 overflow-hidden">
                    <button @click="prevMonth()" class="px-3 py-1 hover:bg-[#FAF8F5]"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg></button>
                    <button @click="currentMonth()" class="px-3 py-1 text-xs font-bold text-[#CFAF53]">Hari Ini</button>
                    <button @click="nextMonth()" class="px-3 py-1 hover:bg-[#FAF8F5]"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg></button>
                </div>
            </div>
            <div class="grid grid-cols-7 mb-2">
                <template x-for="(day, i) in dayNames" :key="i"><div class="text-center text-[10px] font-bold text-slate-400 uppercase py-1" x-text="day"></div></template>
            </div>
            <div class="grid grid-cols-7 gap-1">
                <template x-for="b in blankdays" :key="b"><div class="h-14 md:h-16"></div></template>
                <template x-for="(date, i) in no_of_days" :key="i">
                    <div @click="selectDate(date)" class="h-14 md:h-16 border rounded-lg relative cursor-pointer flex flex-col items-center justify-start pt-2 transition"
                        :class="{ 'bg-[#CFAF53]/10 border-[#CFAF53] text-[#8C7A63]': isToday(date), 'border-transparent hover:border-[#E8E0D8]': !isToday(date) && selectedDate != date, 'ring-2 ring-[#CFAF53] border-[#CFAF53]': selectedDate == date }">
                        <span class="text-sm font-bold" x-text="date"></span>
                        <template x-if="hasEvent(date)"><div class="flex gap-0.5 mt-1"><template x-for="evt in getEvents(date)"><div class="w-1.5 h-1.5 rounded-full" :class="{ 'bg-blue-400': evt.status==='disetujui', 'bg-emerald-400': evt.status==='selesai', 'bg-rose-400': evt.status==='batal' }"></div></template></div></template>
                    </div>
                </template>
            </div>
            <div class="mt-6 pt-6 border-t border-[#E8E0D8]/40">
                <h4 class="text-xs font-bold text-slate-400 uppercase mb-3">Jadwal: <span x-text="selectedDate + ' ' + monthNames[month]" class="text-[#1A1A1A]"></span></h4>
                <div x-show="selectedEvents.length === 0" class="text-center py-4 bg-[#FAF8F5] rounded-xl border border-dashed border-[#E8E0D8]"><p class="text-sm text-slate-400">Tidak ada jadwal.</p></div>
                <div class="space-y-3">
                    <template x-for="event in selectedEvents">
                        <div class="p-4 bg-slate-50 border border-slate-200 shadow-sm rounded-xl">
                            <div class="flex items-start justify-between gap-3">
                                <div class="flex items-center gap-3">
                                    <div class="bg-[#CFAF53]/10 text-[#8C7A63] px-3 py-1.5 rounded-lg text-center">
                                        <span class="text-sm font-bold block" x-text="event.time"></span>
                                        <span class="text-[9px] font-bold uppercase tracking-wider opacity-70">WIB</span>
                                    </div>
                                    <div>
                                        <div class="font-bold text-[#1A1A1A]" x-text="event.title"></div>
                                        <div class="text-xs text-slate-400 mt-0.5">Dokter: <span x-text="event.doctor" class="text-slate-600"></span></div>
                                    </div>
                                </div>
                                <span class="text-[10px] font-bold px-2 py-1 rounded-md uppercase border" :class="{ 'bg-blue-50 text-blue-700 border-blue-200': event.status==='disetujui', 'bg-emerald-50 text-emerald-700 border-emerald-200': event.status==='selesai', 'bg-rose-50 text-rose-700 border-rose-200': event.status==='batal' }" x-text="event.status"></span>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>
