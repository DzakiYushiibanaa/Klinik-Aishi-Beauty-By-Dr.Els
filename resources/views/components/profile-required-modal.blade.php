{{--
  Profile Required Modal
  ----------------------------------------------------------------------------
  Tampil otomatis kalau session('show_profile_required_modal') true.
  Dipasang sekali di dashboard.blade.php. User dapat opsi: lengkapi profil
  sekarang (auto-scroll ke section profil + buka tab profile) atau Nanti Saja.
--}}

@php
    $shouldShow = session('show_profile_required_modal') ? true : false;
@endphp

<div x-data="{
        open: @js($shouldShow),
        scrollToProfile() {
            // Buka tab profile dulu
            this.$dispatch('switch-tab', 'profile_data');
            this.open = false;

            // Scroll ke heading profile setelah Alpine update DOM
            this.$nextTick(() => {
                const target = document.getElementById('sec-profil') || document.getElementById('sec-profile') || document.querySelector('[data-profile-section]');
                if (target) {
                    target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            });
        },
     }"
     x-show="open"
     x-cloak
     @keydown.escape.window="open = false"
     class="fixed inset-0 z-[10000] flex items-center justify-center bg-[#1A1A1A]/55 backdrop-blur-sm p-4 sm:p-6"
     role="alertdialog"
     aria-modal="true"
     aria-labelledby="profile-required-title"
     aria-describedby="profile-required-desc"
     x-transition:enter="ease-out duration-200"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="ease-in duration-150"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0">

    <div class="profile-required-modal__panel"
         @click.away="open = false"
         x-transition:enter="ease-out duration-300"
         x-transition:enter-start="opacity-0 scale-95 translate-y-3"
         x-transition:enter-end="opacity-100 scale-100 translate-y-0"
         x-transition:leave="ease-in duration-150"
         x-transition:leave-start="opacity-100 scale-100 translate-y-0"
         x-transition:leave-end="opacity-0 scale-95 translate-y-3">

        {{-- Decorative top glow --}}
        <div aria-hidden="true" class="profile-required-modal__glow"></div>

        {{-- Icon: warning amber --}}
        <div class="profile-required-modal__icon">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.4" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
        </div>

        <h3 id="profile-required-title" class="profile-required-modal__title">
            Lengkapi Profil Anda
        </h3>

        <p id="profile-required-desc" class="profile-required-modal__desc">
            Sebelum membuat janji temu, lengkapi
            <span class="profile-required-modal__strong">Nomor WhatsApp</span>
            dan
            <span class="profile-required-modal__strong">Tanggal Lahir</span>
            Anda terlebih dahulu.
        </p>

        <div class="profile-required-modal__badge">
            <svg class="profile-required-modal__badge-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span>Data ini diperlukan klinik untuk konfirmasi janji.</span>
        </div>

        <div class="profile-required-modal__actions">
            <button type="button"
                    @click="open = false"
                    class="profile-required-modal__btn profile-required-modal__btn--secondary">
                Nanti Saja
            </button>
            <button type="button"
                    @click="scrollToProfile()"
                    class="profile-required-modal__btn profile-required-modal__btn--primary">
                <span>Lengkapi Sekarang</span>
                <svg class="profile-required-modal__btn-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.4" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                </svg>
            </button>
        </div>
    </div>
</div>

<style>
    /* ─────────  PROFILE REQUIRED MODAL  ───────── */
    .profile-required-modal__panel {
        position: relative;
        width: 100%;
        max-width: 420px;
        background:
            radial-gradient(120% 80% at 50% 0%, rgba(245, 158, 11, 0.08) 0%, transparent 60%),
            #ffffff;
        border: 1px solid rgba(232, 224, 216, 0.7);
        border-radius: 24px;
        padding: 1.75rem 1.5rem 1.5rem;
        text-align: center;
        box-shadow:
            0 1px 3px rgba(0, 0, 0, 0.05),
            0 24px 48px -12px rgba(26, 26, 26, 0.25),
            0 18px 32px -16px rgba(245, 158, 11, 0.18);
        overflow: hidden;
        max-height: calc(100vh - 2rem);
        overflow-y: auto;
    }
    @media (min-width: 480px) {
        .profile-required-modal__panel {
            max-width: 440px;
            padding: 2rem 1.75rem 1.75rem;
            border-radius: 28px;
        }
    }

    .profile-required-modal__glow {
        position: absolute;
        top: -80px; left: 50%;
        transform: translateX(-50%);
        width: 240px; height: 240px;
        border-radius: 9999px;
        background: rgba(245, 158, 11, 0.18);
        filter: blur(70px);
        pointer-events: none;
        opacity: 0.85;
    }

    /* Icon */
    .profile-required-modal__icon {
        position: relative;
        z-index: 1;
        margin: 0 auto 1.125rem;
        width: 64px;
        height: 64px;
        border-radius: 9999px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        background: linear-gradient(135deg, #FBB040 0%, #F59E0B 60%, #D97706 100%);
        box-shadow:
            inset 0 0 0 1px rgba(255, 255, 255, 0.25),
            0 6px 14px -4px rgba(245, 158, 11, 0.45),
            0 0 0 8px rgba(245, 158, 11, 0.08);
    }
    .profile-required-modal__icon svg { width: 30px; height: 30px; }
    @media (min-width: 480px) {
        .profile-required-modal__icon { width: 68px; height: 68px; }
        .profile-required-modal__icon svg { width: 32px; height: 32px; }
    }

    /* Typography */
    .profile-required-modal__title {
        position: relative;
        z-index: 1;
        font-family: 'Plus Jakarta Sans', sans-serif;
        font-size: 1.125rem;
        font-weight: 800;
        letter-spacing: -0.01em;
        line-height: 1.3;
        color: #1A1A1A;
    }
    @media (min-width: 480px) {
        .profile-required-modal__title { font-size: 1.25rem; }
    }

    .profile-required-modal__desc {
        position: relative;
        z-index: 1;
        margin-top: 0.625rem;
        font-size: 0.875rem;
        line-height: 1.6;
        color: #64748B;
    }
    .profile-required-modal__strong {
        font-weight: 700;
        color: #1A1A1A;
        white-space: nowrap;
    }

    /* Info badge */
    .profile-required-modal__badge {
        position: relative;
        z-index: 1;
        margin-top: 1rem;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        padding: 0.625rem 0.875rem;
        background: linear-gradient(135deg, #FFF8EB 0%, #FFFCF5 100%);
        border: 1px solid rgba(245, 158, 11, 0.22);
        border-radius: 12px;
        font-family: 'Plus Jakarta Sans', sans-serif;
        font-size: 0.75rem;
        font-weight: 600;
        color: #92400E;
        line-height: 1.4;
        text-align: left;
    }
    .profile-required-modal__badge-icon {
        width: 16px;
        height: 16px;
        flex-shrink: 0;
        color: #D97706;
    }

    /* Actions */
    .profile-required-modal__actions {
        position: relative;
        z-index: 1;
        margin-top: 1.5rem;
        display: flex;
        flex-direction: column;
        gap: 0.625rem;
    }
    @media (min-width: 480px) {
        .profile-required-modal__actions {
            flex-direction: row;
            gap: 0.75rem;
        }
    }

    .profile-required-modal__btn {
        flex: 1 1 0;
        min-height: 46px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.4375rem;
        padding: 0.75rem 1rem;
        border-radius: 9999px;
        font-family: 'Plus Jakarta Sans', sans-serif;
        font-size: 0.875rem;
        font-weight: 700;
        line-height: 1;
        cursor: pointer;
        border: 1px solid transparent;
        transition: transform 200ms ease, box-shadow 200ms ease, background 200ms ease, border-color 200ms ease, color 200ms ease;
    }

    .profile-required-modal__btn:focus-visible {
        outline: none;
        box-shadow: 0 0 0 3px rgba(207, 175, 83, 0.35);
    }

    .profile-required-modal__btn--secondary {
        background: #ffffff;
        color: #475569;
        border-color: #E2E8F0;
    }
    .profile-required-modal__btn--secondary:hover {
        background: #F8FAFC;
        border-color: #CBD5E1;
        color: #1A1A1A;
        transform: translateY(-1px);
    }

    .profile-required-modal__btn--primary {
        background: linear-gradient(135deg, #1A1A1A 0%, #2C2622 100%);
        color: #ffffff;
        box-shadow:
            0 1px 2px rgba(0, 0, 0, 0.1),
            0 8px 18px -8px rgba(26, 26, 26, 0.45);
    }
    .profile-required-modal__btn--primary:hover {
        background: linear-gradient(135deg, #B8956F 0%, #CFAF53 100%);
        transform: translateY(-1px);
        box-shadow:
            0 1px 2px rgba(184, 149, 111, 0.25),
            0 12px 22px -8px rgba(184, 149, 111, 0.55);
    }
    .profile-required-modal__btn--primary:active { transform: translateY(0); }

    .profile-required-modal__btn-icon {
        width: 14px;
        height: 14px;
        transition: transform 200ms ease;
    }
    .profile-required-modal__btn--primary:hover .profile-required-modal__btn-icon {
        transform: translateX(2px);
    }

    @media (prefers-reduced-motion: reduce) {
        .profile-required-modal__btn,
        .profile-required-modal__btn-icon { transition: none; }
        .profile-required-modal__btn:hover { transform: none; }
        .profile-required-modal__btn--primary:hover .profile-required-modal__btn-icon { transform: none; }
    }
</style>
