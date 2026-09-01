{{-- ====== TAB: JANJI TEMU ====== --}}
<div class="bg-white rounded-2xl border border-[#E8E0D8]/60 shadow-sm p-6" x-data="{ 
    viewMode: 'list',
    filterStatus: 'aktif',
    filterWaktu: 'semua',
    filterStartDate: '',
    filterEndDate: '',
    filterJenis: 'semua',
    filterPayment: 'semua',
    searchQuery: '',
    sortOrder: 'terdekat',

    isDateInRange(dateStr) {
        if (this.filterWaktu === 'semua') return true;
        
        const itemDate = new Date(dateStr);
        itemDate.setHours(0, 0, 0, 0);
        
        const today = new Date();
        today.setHours(0, 0, 0, 0);

        if (this.filterWaktu === 'hari_ini') {
            return itemDate.getTime() === today.getTime();
        }
        if (this.filterWaktu === 'besok') {
            const tomorrow = new Date(today);
            tomorrow.setDate(tomorrow.getDate() + 1);
            return itemDate.getTime() === tomorrow.getTime();
        }
        if (this.filterWaktu === 'minggu_ini') {
            const dayOfWeek = today.getDay(); // 0 is Sunday
            const diffToMonday = today.getDate() - dayOfWeek + (dayOfWeek === 0 ? -6 : 1); 
            const startOfWeek = new Date(today);
            startOfWeek.setDate(diffToMonday);
            const endOfWeek = new Date(startOfWeek);
            endOfWeek.setDate(endOfWeek.getDate() + 6);
            return itemDate >= startOfWeek && itemDate <= endOfWeek;
        }
        if (this.filterWaktu === 'bulan_ini') {
            return itemDate.getMonth() === today.getMonth() && itemDate.getFullYear() === today.getFullYear();
        }
        if (this.filterWaktu === 'custom') {
            if (!this.filterStartDate || !this.filterEndDate) return true;
            const start = new Date(this.filterStartDate);
            start.setHours(0, 0, 0, 0);
            const end = new Date(this.filterEndDate);
            end.setHours(23, 59, 59, 999);
            return itemDate >= start && itemDate <= end;
        }
        return true;
    }
}">
    @include('dashboard._partials.appointments._filters')
    @include('dashboard._partials.appointments._list_view')
    @include('dashboard._partials.appointments._calendar_view')
    @include('dashboard._partials.appointments._scripts')
</div>
