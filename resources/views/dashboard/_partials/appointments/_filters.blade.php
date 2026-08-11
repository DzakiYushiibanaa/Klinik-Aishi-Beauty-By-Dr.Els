{{-- Partial: Appointment Filters --}}
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
            <h2 class="text-xl font-extrabold text-[#1A1A1A]">Janji Temu Saya</h2>
            <p class="text-sm text-slate-500 mt-1">Lihat dan kelola semua jadwal konsultasi dan treatment Anda di sini.</p>
        </div>
        <div class="bg-white border border-[#E8E0D8]/60 p-1 rounded-xl flex items-center self-start sm:self-auto shrink-0">
            <button @click="viewMode = 'list'" :class="viewMode === 'list' ? 'bg-[#FAF8F5] text-[#1A1A1A] shadow-sm' : 'text-slate-400'" class="px-3 py-1.5 rounded-lg text-xs font-bold transition">List</button>
            <button @click="viewMode = 'calendar'" :class="viewMode === 'calendar' ? 'bg-[#FAF8F5] text-[#CFAF53] shadow-sm' : 'text-slate-400'" class="px-3 py-1.5 rounded-lg text-xs font-bold transition">Kalender</button>
        </div>
    </div>

    <div class="mb-6 space-y-4" x-show="viewMode === 'list'">
        {{-- Status Filter Chips --}}
        <div class="flex items-center gap-2 overflow-x-auto pb-2 scrollbar-hide">
            <template x-for="status in [
                { id: 'semua', label: 'Semua' },
                { id: 'aktif', label: 'Aktif' },
                { id: 'disetujui', label: 'Disetujui' },
                { id: 'selesai', label: 'Selesai' },
                { id: 'batal', label: 'Dibatalkan' },
                { id: 'tidak_hadir', label: 'Tidak Hadir' }
            ]" :key="status.id">
                <button @click="filterStatus = status.id"
                        class="shrink-0 px-4 py-1.5 rounded-full text-sm font-bold transition border"
                        :class="filterStatus === status.id ? 'bg-[#3B3529] text-white border-[#3B3529]' : 'bg-white text-slate-500 border-slate-200 hover:border-[#8C7A63] hover:text-[#8C7A63]'">
                    <span x-text="status.label"></span>
                </button>
            </template>
        </div>

        {{-- Search Bar --}}
        <div class="relative mb-4">
            <svg class="w-4 h-4 text-slate-400 absolute left-4 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            <input x-model="searchQuery" type="text" placeholder="Cari nama dokter atau layanan..." class="w-full pl-11 pr-4 py-3 rounded-xl bg-white border border-[#E8E0D8]/60 text-sm focus:border-[#CFAF53] focus:ring-[#CFAF53]/20 focus:ring-2 outline-none transition">
        </div>

        {{-- Payment Filter Chips --}}
        <div class="flex items-center gap-2 overflow-x-auto pb-2 mb-2 scrollbar-hide">
            <template x-for="pay in [
                { id: 'semua', label: 'Semua Pembayaran' },
                { id: 'belum_bayar', label: 'Belum Bayar' },
                { id: 'sudah_dibayar', label: 'Sudah Bayar' }
            ]" :key="pay.id">
                <button @click="filterPayment = pay.id"
                        class="shrink-0 px-4 py-1.5 rounded-full text-sm font-bold transition border"
                        :class="filterPayment === pay.id ? 'bg-[#CFAF53] text-white border-[#CFAF53]' : 'bg-white text-slate-500 border-slate-200 hover:border-[#CFAF53] hover:text-[#CFAF53]'">
                    <span x-text="pay.label"></span>
                </button>
            </template>
        </div>

        {{-- Dropdowns for Waktu, Layanan, Sorting --}}
        <div class="flex flex-col sm:flex-row gap-3 p-4 bg-[#FAF8F5] rounded-xl border border-[#E8E0D8]/60">
            <div class="flex-1">
                <label class="block text-xs font-bold text-slate-400 mb-1">Tanggal</label>
                <select x-model="filterWaktu" class="w-full text-sm border-slate-200 rounded-lg focus:ring-[#CFAF53] focus:border-[#CFAF53]">
                    <option value="semua">Semua Waktu</option>
                    <option value="hari_ini">Hari Ini</option>
                    <option value="besok">Besok</option>
                    <option value="minggu_ini">Minggu Ini</option>
                    <option value="bulan_ini">Bulan Ini</option>
                    <option value="custom">Pilih Tanggal</option>
                </select>
                <div x-show="filterWaktu === 'custom'" class="flex gap-2 mt-2" x-transition>
                    <input type="date" x-model="filterStartDate" class="w-1/2 text-sm border-slate-200 rounded-lg focus:ring-[#CFAF53] focus:border-[#CFAF53]">
                    <input type="date" x-model="filterEndDate" class="w-1/2 text-sm border-slate-200 rounded-lg focus:ring-[#CFAF53] focus:border-[#CFAF53]">
                </div>
            </div>
            
            <div class="flex-1">
                <label class="block text-xs font-bold text-slate-400 mb-1">Jenis Layanan</label>
                <select x-model="filterJenis" class="w-full text-sm border-slate-200 rounded-lg focus:ring-[#CFAF53] focus:border-[#CFAF53]">
                    <option value="semua">Semua Layanan</option>
                    <option value="konsultasi">Konsultasi</option>
                    <option value="treatment">Treatment</option>
                </select>
            </div>

            <div class="flex-1">
                <label class="block text-xs font-bold text-slate-400 mb-1">Urutkan</label>
                <select x-model="sortOrder" class="w-full text-sm border-slate-200 rounded-lg focus:ring-[#CFAF53] focus:border-[#CFAF53]">
                    <option value="terdekat">Terdekat</option>
                    <option value="terlama">Terlama</option>
                </select>
            </div>
        </div>
    </div>
