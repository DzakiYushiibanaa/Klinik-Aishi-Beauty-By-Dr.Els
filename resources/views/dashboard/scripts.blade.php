{{-- ====== SCRIPTS ====== --}}
<script>

function addressSelector() {
    return {
        // Lists (loaded lazily, only when user enters edit mode)
        provinces: [],
        cities: [],
        districts: [],

        // Loading flags
        loadingProvinces: false,
        loadingCities: false,
        loadingDistricts: false,

        // Mode flag — start in VIEW mode using saved DB data only
        editingAddress: false,

        // Phone number — shared antara input di tab "Profil Saya"
        // dan tab "Alamat Saya". Kedua input pakai x-model="phoneNumber"
        // supaya nilai yang disubmit selalu sinkron (form HTML
        // mengirim "last wins" untuk input dengan name yang sama,
        // jadi kalau tidak disinkron ada risiko nilai dari tab yang
        // tidak dipakai user menimpa input yang baru diisi).
        phoneNumber: '{{ old('phone', $user->phone ?? '') }}',

        // Selected IDs (synced to hidden form inputs)
        selectedProvinceId:   '{{ $user->pasienProfile->province_id ?? "" }}',
        selectedCityId:       '{{ $user->pasienProfile->city_id ?? "" }}',
        selectedDistrictId:   '{{ $user->pasienProfile->district_id ?? "" }}',
        selectedProvinceName: '{{ $user->pasienProfile->provinsi ?? "" }}',
        selectedCityName:     '{{ $user->pasienProfile->kota ?? "" }}',
        selectedDistrictName: '{{ $user->pasienProfile->district_name ?? "" }}',

        // Saved fallbacks (for pretty display in view mode)
        savedProvinceName:    '{{ $user->pasienProfile->provinsi ?? "" }}',
        savedCityName:        '{{ $user->pasienProfile->kota ?? "" }}',
        savedDistrictName:    '{{ $user->pasienProfile->district_name ?? "" }}',

        // hasSavedAddress = TRUE hanya kalau alamat di DB sudah lengkap
        // (no_telepon + province_id + provinsi + city_id + kota +
        // district_id + district_name + kode_pos + alamat). Server
        // menentukan ini lewat $hasCompleteShippingAddress untuk
        // menghindari false positive saat user baru memilih provinsi
        // di form edit (selectedProvinceId terisi tapi DB belum
        // tersimpan).
        hasSavedAddress: @json($hasCompleteShippingAddress ?? false),

        // True while we're auto-resolving cached IDs after enter edit mode
        resolvingEdit: false,

        init() {
            window.addEventListener('reset-alpine-profile', () => {
                this.editingAddress = false;
                this.selectedProvinceId   = '{{ $user->pasienProfile->province_id ?? "" }}';
                this.selectedCityId       = '{{ $user->pasienProfile->city_id ?? "" }}';
                this.selectedDistrictId   = '{{ $user->pasienProfile->district_id ?? "" }}';
                this.selectedProvinceName = '{{ $user->pasienProfile->provinsi ?? "" }}';
                this.selectedCityName     = '{{ $user->pasienProfile->kota ?? "" }}';
                this.selectedDistrictName = '{{ $user->pasienProfile->district_name ?? "" }}';
                this.provinces = [];
                this.cities = [];
                this.districts = [];
            });

            // Pasien yang belum punya alamat lengkap akan langsung melihat
            // form edit (lihat template `editingAddress || !hasSavedAddress`).
            // Untuk kasus ini, dropdown provinsi harus dimuat segera — kalau
            // tidak, list selalu kosong karena `enterEditMode()` baru
            // dijalankan saat tombol "Ubah Alamat" diklik (yang hanya tampil
            // ketika sudah ada alamat tersimpan).
            if (!this.hasSavedAddress) {
                this.fetchProvinces();
            }
        },

        /** Enter edit mode — only NOW load API data (provinces, then cascading cities/districts). */
        async enterEditMode() {
            this.editingAddress = true;
            this.resolvingEdit  = true;

            try {
                await this.fetchProvinces();

                // If saved name exists but no ID, try to resolve via list match.
                if (!this.selectedProvinceId && this.savedProvinceName) {
                    const m = this.provinces.find(p => p.name.toUpperCase() === this.savedProvinceName.toUpperCase());
                    if (m) {
                        this.selectedProvinceId = String(m.id);
                        this.selectedProvinceName = m.name;
                    }
                }

                if (this.selectedProvinceId) {
                    await this.fetchCities(true);

                    if (!this.selectedCityId && this.savedCityName) {
                        const m = this.cities.find(c => c.name.toUpperCase() === this.savedCityName.toUpperCase());
                        if (m) {
                            this.selectedCityId = String(m.id);
                            this.selectedCityName = m.name;
                        }
                    }

                    if (this.selectedCityId) {
                        await this.fetchDistricts(true);

                        if (!this.selectedDistrictId && this.savedDistrictName) {
                            const m = this.districts.find(d => d.name.toUpperCase() === this.savedDistrictName.toUpperCase());
                            if (m) {
                                this.selectedDistrictId = String(m.id);
                                this.selectedDistrictName = m.name;
                            }
                        }
                    }
                }
            } finally {
                this.resolvingEdit = false;
            }
        },

        /** Cancel edit — revert to saved values, keep view mode. */
        cancelEditMode() {
            this.editingAddress = false;
            this.selectedProvinceId   = '{{ $user->pasienProfile->province_id ?? "" }}';
            this.selectedCityId       = '{{ $user->pasienProfile->city_id ?? "" }}';
            this.selectedDistrictId   = '{{ $user->pasienProfile->district_id ?? "" }}';
            this.selectedProvinceName = '{{ $user->pasienProfile->provinsi ?? "" }}';
            this.selectedCityName     = '{{ $user->pasienProfile->kota ?? "" }}';
            this.selectedDistrictName = '{{ $user->pasienProfile->district_name ?? "" }}';
        },

        async fetchProvinces() {
            if (this.provinces.length > 0) return;
            this.loadingProvinces = true;
            try {
                const res  = await fetch("{{ route('api.provinces') }}");
                const data = await res.json();
                this.provinces = Array.isArray(data) ? data : (data.data || []);
            } catch (e) { console.error('Error fetching provinces:', e); }
            finally { this.loadingProvinces = false; }
        },

        async fetchCities(isInit = false) {
            if (!this.selectedProvinceId) return;
            this.loadingCities = true;
            if (!isInit) {
                // User changed province manually → reset downstream
                this.selectedCityId = '';
                this.selectedDistrictId = '';
                this.selectedCityName = '';
                this.selectedDistrictName = '';
                this.cities = [];
                this.districts = [];
            }
            const prov = this.provinces.find(p => p.id == this.selectedProvinceId);
            if (prov) this.selectedProvinceName = prov.name;
            try {
                const res  = await fetch(`/api/cities/${this.selectedProvinceId}`);
                const data = await res.json();
                this.cities = Array.isArray(data) ? data : (data.data || []);
            } catch (e) { console.error('Error fetching cities:', e); }
            finally { this.loadingCities = false; }
        },

        async fetchDistricts(isInit = false) {
            if (!this.selectedCityId) return;
            this.loadingDistricts = true;
            if (!isInit) {
                this.selectedDistrictId = '';
                this.selectedDistrictName = '';
                this.districts = [];
            }
            const city = this.cities.find(c => c.id == this.selectedCityId);
            if (city) this.selectedCityName = city.name;
            try {
                const res  = await fetch(`/api/districts/${this.selectedCityId}`);
                const data = await res.json();
                this.districts = Array.isArray(data) ? data : (data.data || []);
                this.updateLocationNames();
            } catch (e) { console.error('Error fetching districts:', e); }
            finally { this.loadingDistricts = false; }
        },

        updateLocationNames() {
            const dist = this.districts.find(d => d.id == this.selectedDistrictId);
            if (dist) this.selectedDistrictName = dist.name;
        }
    }
}

function appCalendar() {
    return {
        month: '', year: '',
        no_of_days: [], blankdays: [],
        days: ['Min','Sen','Sel','Rab','Kam','Jum','Sab'],
        dayNames: ['Min','Sen','Sel','Rab','Kam','Jum','Sab'],
        monthNames: ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'],
        events: @json($eventsData),
        selectedDate: '', selectedEvents: [],

        init() {
            let today = new Date();
            this.month = today.getMonth();
            this.year = today.getFullYear();
            this.selectedDate = today.getDate();
            this.getNoOfDays();
            this.getEventsForSelectedDate();
        },

        isToday(date) {
            const today = new Date();
            const d = new Date(this.year, this.month, date);
            return today.toDateString() === d.toDateString();
        },

        hasEvent(date) {
            let ds = new Date(this.year, this.month, date).toLocaleDateString('en-CA');
            return this.events.filter(e => e.date === ds).length > 0;
        },

        getEvents(date) {
            let ds = new Date(this.year, this.month, date).toLocaleDateString('en-CA');
            return this.events.filter(e => e.date === ds);
        },

        getNoOfDays() {
            let dim = new Date(this.year, this.month + 1, 0).getDate();
            let dow = new Date(this.year, this.month).getDay();
            let bd = []; for (let i = 1; i <= dow; i++) bd.push(i);
            let dd = []; for (let i = 1; i <= dim; i++) dd.push(i);
            this.blankdays = bd; this.no_of_days = dd;
        },

        selectDate(date) { this.selectedDate = date; this.getEventsForSelectedDate(); },

        getEventsForSelectedDate() {
            if (!this.selectedDate) return;
            let ds = new Date(this.year, this.month, this.selectedDate).toLocaleDateString('en-CA');
            this.selectedEvents = this.events.filter(e => e.date === ds);
        },

        nextMonth() { this.month++; if (this.month > 11) { this.month = 0; this.year++; } this.getNoOfDays(); this.selectDate(null); },
        prevMonth() { this.month--; if (this.month < 0) { this.month = 11; this.year--; } this.getNoOfDays(); this.selectDate(null); },
        currentMonth() { let t = new Date(); this.month = t.getMonth(); this.year = t.getFullYear(); this.getNoOfDays(); this.selectDate(t.getDate()); }
    }
}

document.addEventListener('alpine:init', () => {
    Alpine.data('countdownTimer', (expiredAt) => ({
        h1: 0, h2: 0, m1: 0, m2: 0, s1: 0, s2: 0,
        showHours: false,
        expired: false,
        init() {
            if (!expiredAt) return;
            const end = new Date(expiredAt.replace(/-/g, '/')).getTime();
            const initialDiff = Math.floor((end - new Date().getTime()) / 1000);
            
            // Jika sudah expired dari awal, hanya set expired flag, jangan reload
            if (initialDiff <= 0) {
                this.expired = true;
                return;
            }

            const update = () => {
                const now = new Date().getTime();
                let diff = Math.floor((end - now) / 1000);
                if (diff < 0) diff = 0;
                
                const hours = Math.floor(diff / 3600);
                const minutes = Math.floor((diff % 3600) / 60);
                const seconds = diff % 60;
                
                this.showHours = hours > 0;

                const hStr = String(hours).padStart(2, '0');
                const mStr = String(minutes).padStart(2, '0');
                const sStr = String(seconds).padStart(2, '0');

                this.h1 = parseInt(hStr[0]) || 0;
                this.h2 = parseInt(hStr[1]) || 0;
                this.m1 = parseInt(mStr[0]) || 0;
                this.m2 = parseInt(mStr[1]) || 0;
                this.s1 = parseInt(sStr[0]) || 0;
                this.s2 = parseInt(sStr[1]) || 0;
                
                if (diff > 0) {
                    setTimeout(update, 1000);
                } else {
                    this.expired = true;
                }
            };
            update();
        }
    }));
});
</script>
