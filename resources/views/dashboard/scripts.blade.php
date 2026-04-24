{{-- ====== SCRIPTS ====== --}}
<script>

function addressSelector() {
    return {
        provinces: [],
        cities: [],
        districts: [],
        loading: false,
        selectedProvinceId: '{{ $user->pasienProfile->province_id ?? "" }}',
        selectedCityId: '{{ $user->pasienProfile->city_id ?? "" }}',
        selectedDistrictId: '{{ $user->pasienProfile->district_id ?? "" }}',
        selectedProvinceName: '{{ $user->pasienProfile->provinsi ?? "" }}',
        selectedCityName: '{{ $user->pasienProfile->kota ?? "" }}',
        selectedDistrictName: '{{ $user->pasienProfile->district_name ?? "" }}',

        async init() {
            await this.fetchProvinces();
            if (this.selectedProvinceId) {
                await this.fetchCities(true);
                if (this.selectedCityId) {
                    await this.fetchDistricts(true);
                }
            }
        },

        async fetchProvinces() {
            if (this.loading) return;
            this.loading = true;
            try {
                const res = await fetch("{{ route('api.provinces') }}");
                const data = await res.json();
                this.provinces = Array.isArray(data) ? data : (data.data || []);
            } catch (e) { console.error('Error fetching provinces:', e); }
            finally { this.loading = false; }
        },

        async fetchCities(isInit = false) {
            if (!this.selectedProvinceId || this.loading) return;
            this.loading = true;
            if (!isInit) { this.selectedCityId = ''; this.selectedDistrictId = ''; this.cities = []; this.districts = []; }
            const prov = this.provinces.find(p => p.id == this.selectedProvinceId);
            if (prov) this.selectedProvinceName = prov.name;
            try {
                const res = await fetch(`/api/cities/${this.selectedProvinceId}`);
                const data = await res.json();
                this.cities = Array.isArray(data) ? data : (data.data || []);
            } catch (e) { console.error('Error fetching cities:', e); }
            finally { this.loading = false; }
        },

        async fetchDistricts(isInit = false) {
            if (!this.selectedCityId || this.loading) return;
            this.loading = true;
            if (!isInit) { this.selectedDistrictId = ''; this.districts = []; }
            const city = this.cities.find(c => c.id == this.selectedCityId);
            if (city) this.selectedCityName = city.name;
            try {
                const res = await fetch(`/api/districts/${this.selectedCityId}`);
                const data = await res.json();
                this.districts = Array.isArray(data) ? data : (data.data || []);
                this.updateLocationNames();
            } catch (e) { console.error('Error fetching districts:', e); }
            finally { this.loading = false; }
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
</script>
