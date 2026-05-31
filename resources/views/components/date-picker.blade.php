@props([
    'name'        => 'date',
    'id'          => null,
    'value'       => null,           // YYYY-MM-DD string (or null/empty)
    'placeholder' => 'dd/mm/yyyy',
    'minYear'     => null,           // default: 100 years ago
    'maxYear'     => null,           // default: current year
    'required'    => false,
])

@php
    $id ??= $name;
    $minYear ??= now()->year - 100;
    $maxYear ??= now()->year;

    // Initial value (accept ISO YYYY-MM-DD or full datetime)
    $iso = '';
    if ($value) {
        try {
            $iso = \Illuminate\Support\Carbon::parse($value)->format('Y-m-d');
        } catch (\Throwable $e) {
            $iso = '';
        }
    }
@endphp

<div class="aishi-dp"
     x-data="aishiDatePicker({
         iso: @js($iso),
         minYear: {{ (int) $minYear }},
         maxYear: {{ (int) $maxYear }},
     })"
     x-init="init()"
     @click.outside="close()"
     @keydown.escape.window="close()">

    {{-- Hidden input that gets posted to backend (ISO format) --}}
    <input type="hidden"
           id="{{ $id }}"
           name="{{ $name }}"
           x-model="iso"
           @if($required) required @endif>

    {{-- Visible "input" trigger --}}
    <button type="button"
            class="aishi-dp__trigger"
            :class="{ 'aishi-dp__trigger--open': open, 'aishi-dp__trigger--filled': iso }"
            @click="toggle()"
            :aria-expanded="open"
            aria-haspopup="dialog">
        <span class="aishi-dp__display"
              :class="{ 'aishi-dp__display--placeholder': !iso }"
              x-text="iso ? formatDisplay(iso) : '{{ $placeholder }}'"></span>
        <svg class="aishi-dp__cal-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
        </svg>
    </button>

    {{-- Backdrop (mobile only — fades in behind the centered modal) --}}
    <div class="aishi-dp__backdrop"
         x-show="open"
         x-cloak
         @click="close()"
         x-transition:enter="aishi-dp__backdrop-enter"
         x-transition:enter-start="aishi-dp__backdrop-enter-start"
         x-transition:enter-end="aishi-dp__backdrop-enter-end"
         x-transition:leave="aishi-dp__backdrop-leave"
         x-transition:leave-start="aishi-dp__backdrop-leave-start"
         x-transition:leave-end="aishi-dp__backdrop-leave-end"
         aria-hidden="true"></div>

    {{-- Popup --}}
    <div class="aishi-dp__popup"
         x-show="open"
         x-cloak
         x-transition:enter="aishi-dp__pop-enter"
         x-transition:enter-start="aishi-dp__pop-enter-start"
         x-transition:enter-end="aishi-dp__pop-enter-end"
         x-transition:leave="aishi-dp__pop-leave"
         x-transition:leave-start="aishi-dp__pop-leave-start"
         x-transition:leave-end="aishi-dp__pop-leave-end"
         role="dialog"
         aria-label="Pilih tanggal">

        {{-- Header: month/year nav + view switch --}}
        <div class="aishi-dp__header">
            <button type="button"
                    class="aishi-dp__nav"
                    @click="prevMonth()"
                    :aria-label="view === 'days' ? 'Bulan sebelumnya' : (view === 'months' ? 'Tahun sebelumnya' : 'Dekade sebelumnya')">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M15 19l-7-7 7-7"/>
                </svg>
            </button>

            <button type="button" class="aishi-dp__title" @click="cycleView()">
                <span x-show="view === 'days'" x-text="monthLabel(viewMonth) + ' ' + viewYear"></span>
                <span x-show="view === 'months'" x-text="viewYear"></span>
                <span x-show="view === 'years'" x-text="yearRangeLabel()"></span>
                <svg class="aishi-dp__title-caret" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>

            <button type="button"
                    class="aishi-dp__nav"
                    @click="nextMonth()"
                    :aria-label="view === 'days' ? 'Bulan berikutnya' : (view === 'months' ? 'Tahun berikutnya' : 'Dekade berikutnya')">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M9 5l7 7-7 7"/>
                </svg>
            </button>
        </div>

        {{-- Day view --}}
        <div x-show="view === 'days'" class="aishi-dp__view">
            <div class="aishi-dp__weekdays">
                <template x-for="w in weekdayLabels" :key="w">
                    <span x-text="w"></span>
                </template>
            </div>
            <div class="aishi-dp__days">
                <template x-for="(d, i) in daysGrid" :key="i">
                    <button type="button"
                            class="aishi-dp__day"
                            :class="{
                                'aishi-dp__day--muted': !d.inMonth,
                                'aishi-dp__day--today': d.isToday && !d.isSelected,
                                'aishi-dp__day--selected': d.isSelected,
                                'aishi-dp__day--disabled': d.disabled,
                            }"
                            :disabled="d.disabled"
                            @click="selectDay(d)">
                        <span x-text="d.day"></span>
                    </button>
                </template>
            </div>
        </div>

        {{-- Month view --}}
        <div x-show="view === 'months'" class="aishi-dp__view">
            <div class="aishi-dp__months">
                <template x-for="(m, i) in monthShortLabels" :key="i">
                    <button type="button"
                            class="aishi-dp__month"
                            :class="{ 'aishi-dp__month--selected': i === viewMonth }"
                            @click="selectMonth(i)"
                            x-text="m"></button>
                </template>
            </div>
        </div>

        {{-- Year view --}}
        <div x-show="view === 'years'" class="aishi-dp__view">
            <div class="aishi-dp__years">
                <template x-for="y in yearGrid" :key="y">
                    <button type="button"
                            class="aishi-dp__year"
                            :class="{
                                'aishi-dp__year--selected': y === viewYear,
                                'aishi-dp__year--out-of-range': y < minYear || y > maxYear,
                            }"
                            :disabled="y < minYear || y > maxYear"
                            @click="selectYear(y)"
                            x-text="y"></button>
                </template>
            </div>
        </div>

        {{-- Footer --}}
        <div class="aishi-dp__footer">
            <button type="button" class="aishi-dp__footer-btn aishi-dp__footer-btn--clear" @click="clear()">Hapus</button>
            <button type="button" class="aishi-dp__footer-btn aishi-dp__footer-btn--today" @click="goToday()">Hari Ini</button>
        </div>
    </div>
</div>

@once
    <script>
        window.aishiDatePicker = function (opts) {
            const today = new Date();
            today.setHours(0, 0, 0, 0);

            const parseIso = (s) => {
                if (!s || typeof s !== 'string') return null;
                const m = s.match(/^(\d{4})-(\d{2})-(\d{2})/);
                if (!m) return null;
                const d = new Date(parseInt(m[1], 10), parseInt(m[2], 10) - 1, parseInt(m[3], 10));
                return isNaN(d.getTime()) ? null : d;
            };

            const initial = parseIso(opts.iso);
            const initialMonth = initial ? initial.getMonth() : today.getMonth();
            const initialYear = initial ? initial.getFullYear() : Math.min(today.getFullYear(), opts.maxYear);

            return {
                open: false,
                view: 'days', // 'days' | 'months' | 'years'
                iso: opts.iso || '',
                minYear: opts.minYear,
                maxYear: opts.maxYear,
                viewMonth: initialMonth,
                viewYear: initialYear,
                monthLongLabels: ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'],
                monthShortLabels: ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'],
                weekdayLabels: ['Sen','Sel','Rab','Kam','Jum','Sab','Min'],

                init() {
                    this.$watch('open', (v) => {
                        if (v) {
                            this.view = 'days';
                            const sel = parseIso(this.iso);
                            if (sel) {
                                this.viewMonth = sel.getMonth();
                                this.viewYear = sel.getFullYear();
                            }
                        }
                    });
                },

                toggle() { this.open = !this.open; },
                close()  { this.open = false; },

                monthLabel(idx) { return this.monthLongLabels[idx] || ''; },

                yearRangeLabel() {
                    const start = Math.floor(this.viewYear / 12) * 12;
                    return start + ' \u2013 ' + (start + 11);
                },

                cycleView() {
                    if (this.view === 'days') this.view = 'months';
                    else if (this.view === 'months') this.view = 'years';
                    else this.view = 'days';
                },

                prevMonth() {
                    if (this.view === 'days') {
                        if (this.viewMonth === 0) { this.viewMonth = 11; this.viewYear--; }
                        else this.viewMonth--;
                    } else if (this.view === 'months') {
                        this.viewYear--;
                    } else if (this.view === 'years') {
                        this.viewYear -= 12;
                    }
                },

                nextMonth() {
                    if (this.view === 'days') {
                        if (this.viewMonth === 11) { this.viewMonth = 0; this.viewYear++; }
                        else this.viewMonth++;
                    } else if (this.view === 'months') {
                        this.viewYear++;
                    } else if (this.view === 'years') {
                        this.viewYear += 12;
                    }
                },

                get daysGrid() {
                    const year = this.viewYear;
                    const month = this.viewMonth;
                    const firstOfMonth = new Date(year, month, 1);
                    const startDow = (firstOfMonth.getDay() + 6) % 7; // monday = 0
                    const daysInMonth = new Date(year, month + 1, 0).getDate();
                    const daysInPrevMonth = new Date(year, month, 0).getDate();
                    const sel = parseIso(this.iso);
                    const cells = [];

                    for (let i = startDow - 1; i >= 0; i--) {
                        const d = daysInPrevMonth - i;
                        cells.push(this.buildCell(year, month - 1, d, false, sel));
                    }
                    for (let d = 1; d <= daysInMonth; d++) {
                        cells.push(this.buildCell(year, month, d, true, sel));
                    }
                    while (cells.length < 42) {
                        const next = cells.length - (startDow + daysInMonth) + 1;
                        cells.push(this.buildCell(year, month + 1, next, false, sel));
                    }
                    return cells;
                },

                buildCell(y, m, d, inMonth, sel) {
                    const date = new Date(y, m, d);
                    const isToday = date.getTime() === today.getTime();
                    const isSelected = sel && date.getTime() === sel.getTime();
                    const yyyy = date.getFullYear();
                    const disabled = yyyy < this.minYear || yyyy > this.maxYear;
                    return {
                        day: date.getDate(),
                        year: yyyy,
                        month: date.getMonth(),
                        inMonth, isToday, isSelected, disabled,
                    };
                },

                selectDay(cell) {
                    if (cell.disabled) return;
                    const yyyy = String(cell.year);
                    const mm = String(cell.month + 1).padStart(2, '0');
                    const dd = String(cell.day).padStart(2, '0');
                    this.iso = yyyy + '-' + mm + '-' + dd;
                    this.viewMonth = cell.month;
                    this.viewYear = cell.year;
                    this.close();
                },

                selectMonth(idx) {
                    this.viewMonth = idx;
                    this.view = 'days';
                },

                selectYear(y) {
                    if (y < this.minYear || y > this.maxYear) return;
                    this.viewYear = y;
                    this.view = 'months';
                },

                get yearGrid() {
                    const start = Math.floor(this.viewYear / 12) * 12;
                    return Array.from({ length: 12 }, (_, i) => start + i);
                },

                formatDisplay(iso) {
                    const d = parseIso(iso);
                    if (!d) return '';
                    const dd = String(d.getDate()).padStart(2, '0');
                    const mm = String(d.getMonth() + 1).padStart(2, '0');
                    return dd + '/' + mm + '/' + d.getFullYear();
                },

                clear() {
                    this.iso = '';
                    this.close();
                },

                goToday() {
                    const t = new Date();
                    if (t.getFullYear() < this.minYear || t.getFullYear() > this.maxYear) return;
                    const yyyy = String(t.getFullYear());
                    const mm = String(t.getMonth() + 1).padStart(2, '0');
                    const dd = String(t.getDate()).padStart(2, '0');
                    this.iso = yyyy + '-' + mm + '-' + dd;
                    this.viewMonth = t.getMonth();
                    this.viewYear = t.getFullYear();
                    this.close();
                },
            };
        };
    </script>

    <style>
        /* ─────────  AISHI DATE PICKER  ───────── */
        .aishi-dp { position: relative; width: 100%; }

        .aishi-dp__trigger {
            width: 100%;
            display: inline-flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.5rem;
            padding: 0.625rem 0.875rem;
            border-radius: 12px;
            border: 1px solid #E2E8F0;
            background: #ffffff;
            color: #1A1A1A;
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 0.875rem;
            cursor: pointer;
            transition: border-color 180ms ease, box-shadow 180ms ease, background 180ms ease;
        }
        .aishi-dp__trigger:hover { border-color: rgba(184, 149, 111, 0.5); }
        .aishi-dp__trigger:focus-visible,
        .aishi-dp__trigger--open {
            outline: none;
            border-color: #CFAF53;
            box-shadow: 0 0 0 3px rgba(207, 175, 83, 0.18);
            background: #ffffff;
        }
        .aishi-dp__display {
            font-weight: 600;
            color: #1A1A1A;
            letter-spacing: 0.005em;
        }
        .aishi-dp__display--placeholder {
            color: #94A3B8;
            font-weight: 500;
        }
        .aishi-dp__cal-icon {
            width: 18px;
            height: 18px;
            color: #B8956F;
            flex-shrink: 0;
        }

        /* Backdrop (mobile only) */
        .aishi-dp__backdrop {
            display: none;
        }

        /* Popup */
        .aishi-dp__popup {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 10001;
            width: min(340px, 92vw);
            max-height: calc(100vh - 2rem);
            overflow-y: auto;
            background: #ffffff;
            border: 1px solid rgba(232, 224, 216, 0.85);
            border-radius: 20px;
            padding: 0.875rem;
            box-shadow:
                0 1px 2px rgba(0, 0, 0, 0.04),
                0 32px 60px -12px rgba(26, 26, 26, 0.32),
                0 18px 32px -12px rgba(184, 149, 111, 0.22);
            font-family: 'Plus Jakarta Sans', sans-serif;
            color: #1A1A1A;
        }

        /* Mobile (center modal) → show backdrop */
        @media (max-width: 639px) {
            .aishi-dp__backdrop {
                display: block;
                position: fixed;
                inset: 0;
                z-index: 10000;
                background: rgba(26, 26, 26, 0.5);
                backdrop-filter: blur(2px);
                -webkit-backdrop-filter: blur(2px);
            }
        }

        /* Desktop / tablet (≥640px): inline dropdown anchored to trigger */
        @media (min-width: 640px) {
            .aishi-dp__popup {
                position: absolute;
                top: calc(100% + 8px);
                left: 0;
                transform: none;
                z-index: 50;
                width: 320px;
                max-height: none;
                overflow-y: visible;
                border-radius: 18px;
                box-shadow:
                    0 1px 2px rgba(0, 0, 0, 0.04),
                    0 18px 40px -12px rgba(26, 26, 26, 0.18),
                    0 12px 24px -10px rgba(184, 149, 111, 0.18);
            }
        }

        /* Header */
        .aishi-dp__header {
            display: flex;
            align-items: center;
            gap: 0.375rem;
            margin-bottom: 0.625rem;
        }
        .aishi-dp__nav {
            width: 32px;
            height: 32px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 9px;
            background: transparent;
            color: #475569;
            border: 1px solid transparent;
            cursor: pointer;
            transition: background 160ms ease, color 160ms ease, border-color 160ms ease;
        }
        .aishi-dp__nav svg { width: 16px; height: 16px; }
        .aishi-dp__nav:hover {
            background: #FAF8F5;
            border-color: rgba(184, 149, 111, 0.25);
            color: #1A1A1A;
        }
        .aishi-dp__title {
            flex: 1;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.375rem;
            height: 32px;
            padding: 0 0.625rem;
            background: transparent;
            border: 1px solid transparent;
            border-radius: 9px;
            color: #1A1A1A;
            font-size: 0.8125rem;
            font-weight: 700;
            letter-spacing: -0.005em;
            cursor: pointer;
            transition: background 160ms ease, border-color 160ms ease;
        }
        .aishi-dp__title:hover {
            background: #FAF8F5;
            border-color: rgba(184, 149, 111, 0.25);
        }
        .aishi-dp__title-caret {
            width: 12px;
            height: 12px;
            color: #94A3B8;
        }

        .aishi-dp__view { padding-top: 0.125rem; }

        /* Weekday header */
        .aishi-dp__weekdays {
            display: grid;
            grid-template-columns: repeat(7, minmax(0, 1fr));
            margin-bottom: 0.25rem;
        }
        .aishi-dp__weekdays span {
            text-align: center;
            font-size: 0.6875rem;
            font-weight: 700;
            letter-spacing: 0.06em;
            color: #94A3B8;
            text-transform: uppercase;
            padding: 0.375rem 0;
        }

        /* Day grid */
        .aishi-dp__days {
            display: grid;
            grid-template-columns: repeat(7, minmax(0, 1fr));
            gap: 2px;
        }
        .aishi-dp__day {
            aspect-ratio: 1 / 1;
            width: 100%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: transparent;
            border: 1px solid transparent;
            border-radius: 10px;
            font-size: 0.8125rem;
            font-weight: 600;
            color: #1A1A1A;
            cursor: pointer;
            transition: background 140ms ease, color 140ms ease, border-color 140ms ease;
        }
        .aishi-dp__day:hover {
            background: #FAF8F5;
            border-color: rgba(184, 149, 111, 0.3);
        }
        .aishi-dp__day--muted { color: #CBD5E1; font-weight: 500; }
        .aishi-dp__day--today {
            color: #B8956F;
            border-color: rgba(184, 149, 111, 0.45);
        }
        .aishi-dp__day--selected,
        .aishi-dp__day--selected:hover {
            background: linear-gradient(135deg, #CFAF53 0%, #B8956F 100%);
            color: #ffffff;
            border-color: transparent;
            box-shadow:
                0 1px 2px rgba(184, 149, 111, 0.25),
                0 6px 14px -6px rgba(184, 149, 111, 0.55);
            font-weight: 700;
        }
        .aishi-dp__day--disabled,
        .aishi-dp__day--disabled:hover {
            color: #E2E8F0 !important;
            background: transparent !important;
            border-color: transparent !important;
            cursor: not-allowed;
        }

        /* Month grid */
        .aishi-dp__months {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 0.375rem;
            padding: 0.25rem 0;
        }
        .aishi-dp__month {
            padding: 0.625rem 0.5rem;
            background: transparent;
            border: 1px solid transparent;
            border-radius: 10px;
            font-size: 0.8125rem;
            font-weight: 600;
            color: #1A1A1A;
            cursor: pointer;
            transition: background 160ms ease, color 160ms ease, border-color 160ms ease;
        }
        .aishi-dp__month:hover {
            background: #FAF8F5;
            border-color: rgba(184, 149, 111, 0.3);
        }
        .aishi-dp__month--selected,
        .aishi-dp__month--selected:hover {
            background: linear-gradient(135deg, #CFAF53 0%, #B8956F 100%);
            color: #ffffff;
            border-color: transparent;
            box-shadow:
                0 1px 2px rgba(184, 149, 111, 0.25),
                0 6px 12px -6px rgba(184, 149, 111, 0.5);
            font-weight: 700;
        }

        /* Year grid */
        .aishi-dp__years {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 0.375rem;
            padding: 0.25rem 0;
        }
        .aishi-dp__year {
            padding: 0.625rem 0.5rem;
            background: transparent;
            border: 1px solid transparent;
            border-radius: 10px;
            font-size: 0.8125rem;
            font-weight: 600;
            color: #1A1A1A;
            cursor: pointer;
            transition: background 160ms ease, color 160ms ease, border-color 160ms ease;
        }
        .aishi-dp__year:hover {
            background: #FAF8F5;
            border-color: rgba(184, 149, 111, 0.3);
        }
        .aishi-dp__year--selected,
        .aishi-dp__year--selected:hover {
            background: linear-gradient(135deg, #CFAF53 0%, #B8956F 100%);
            color: #ffffff;
            border-color: transparent;
            box-shadow:
                0 1px 2px rgba(184, 149, 111, 0.25),
                0 6px 12px -6px rgba(184, 149, 111, 0.5);
            font-weight: 700;
        }
        .aishi-dp__year--out-of-range,
        .aishi-dp__year--out-of-range:hover {
            color: #E2E8F0;
            background: transparent;
            border-color: transparent;
            cursor: not-allowed;
        }

        /* Footer */
        .aishi-dp__footer {
            margin-top: 0.625rem;
            padding-top: 0.625rem;
            border-top: 1px solid rgba(232, 224, 216, 0.7);
            display: flex;
            gap: 0.375rem;
            justify-content: flex-end;
        }
        .aishi-dp__footer-btn {
            padding: 0.4375rem 0.875rem;
            background: transparent;
            border: 1px solid transparent;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 700;
            cursor: pointer;
            transition: background 160ms ease, color 160ms ease, border-color 160ms ease;
        }
        .aishi-dp__footer-btn--clear { color: #64748B; }
        .aishi-dp__footer-btn--clear:hover {
            background: #F8FAFC;
            color: #1A1A1A;
            border-color: #E2E8F0;
        }
        .aishi-dp__footer-btn--today { color: #B8956F; }
        .aishi-dp__footer-btn--today:hover {
            background: rgba(207, 175, 83, 0.1);
            color: #8C7A63;
            border-color: rgba(184, 149, 111, 0.3);
        }

        /* Transitions */
        /* Backdrop fade */
        .aishi-dp__backdrop-enter { transition: opacity 200ms ease; }
        .aishi-dp__backdrop-enter-start { opacity: 0; }
        .aishi-dp__backdrop-enter-end   { opacity: 1; }
        .aishi-dp__backdrop-leave { transition: opacity 150ms ease; }
        .aishi-dp__backdrop-leave-start { opacity: 1; }
        .aishi-dp__backdrop-leave-end   { opacity: 0; }

        /* Mobile: scale modal entrance / exit (centered) */
        .aishi-dp__pop-enter { transition: transform 200ms ease, opacity 200ms ease; }
        .aishi-dp__pop-enter-start { opacity: 0; transform: translate(-50%, -50%) scale(0.94); }
        .aishi-dp__pop-enter-end   { opacity: 1; transform: translate(-50%, -50%) scale(1); }
        .aishi-dp__pop-leave { transition: transform 150ms ease, opacity 150ms ease; }
        .aishi-dp__pop-leave-start { opacity: 1; transform: translate(-50%, -50%) scale(1); }
        .aishi-dp__pop-leave-end   { opacity: 0; transform: translate(-50%, -50%) scale(0.94); }

        /* Desktop / tablet: dropdown slide */
        @media (min-width: 640px) {
            .aishi-dp__pop-enter-start { opacity: 0; transform: translateY(-4px); }
            .aishi-dp__pop-enter-end   { opacity: 1; transform: translateY(0); }
            .aishi-dp__pop-leave-start { opacity: 1; transform: translateY(0); }
            .aishi-dp__pop-leave-end   { opacity: 0; transform: translateY(-4px); }
        }

        @media (prefers-reduced-motion: reduce) {
            .aishi-dp__day,
            .aishi-dp__month,
            .aishi-dp__year,
            .aishi-dp__nav,
            .aishi-dp__title,
            .aishi-dp__footer-btn,
            .aishi-dp__trigger { transition: none; }
        }
    </style>
@endonce
