{{-- Partial: Next Action Banner --}}
    {{-- Section A: Lanjutkan Aktivitas (Other logic) --}}
    @if(isset($nextAction))
        @php
            $naColors = match ($nextAction['color']) {
                'amber' => [
                    'bg_grad'      => 'linear-gradient(135deg, #FFF8EB 0%, #FFFDF7 50%, #FFFFFF 100%)',
                    'border'       => 'rgba(245, 158, 11, 0.18)',
                    'glow'         => 'rgba(245, 158, 11, 0.16)',
                    'icon_bg'      => 'rgba(245, 158, 11, 0.12)',
                    'icon_color'   => '#D97706',
                    'btn_sec_text' => '#92400E',
                    'btn_pri_grad' => 'linear-gradient(135deg, #F59E0B 0%, #D97706 100%)',
                    'btn_pri_glow' => 'rgba(217, 119, 6, 0.45)',
                ],
                'orange' => [
                    'bg_grad'      => 'linear-gradient(135deg, #FFF4EC 0%, #FFFCF8 50%, #FFFFFF 100%)',
                    'border'       => 'rgba(234, 88, 12, 0.18)',
                    'glow'         => 'rgba(234, 88, 12, 0.16)',
                    'icon_bg'      => 'rgba(234, 88, 12, 0.12)',
                    'icon_color'   => '#C2410C',
                    'btn_sec_text' => '#9A3412',
                    'btn_pri_grad' => 'linear-gradient(135deg, #F97316 0%, #C2410C 100%)',
                    'btn_pri_glow' => 'rgba(194, 65, 12, 0.45)',
                ],
                'blue' => [
                    'bg_grad'      => 'linear-gradient(135deg, #EFF6FF 0%, #F8FBFF 50%, #FFFFFF 100%)',
                    'border'       => 'rgba(37, 99, 235, 0.18)',
                    'glow'         => 'rgba(37, 99, 235, 0.16)',
                    'icon_bg'      => 'rgba(37, 99, 235, 0.12)',
                    'icon_color'   => '#1D4ED8',
                    'btn_sec_text' => '#1E40AF',
                    'btn_pri_grad' => 'linear-gradient(135deg, #3B82F6 0%, #1D4ED8 100%)',
                    'btn_pri_glow' => 'rgba(29, 78, 216, 0.45)',
                ],
                'emerald' => [
                    'bg_grad'      => 'linear-gradient(135deg, #ECFDF5 0%, #F4FBF7 50%, #FFFFFF 100%)',
                    'border'       => 'rgba(16, 185, 129, 0.2)',
                    'glow'         => 'rgba(16, 185, 129, 0.18)',
                    'icon_bg'      => 'rgba(16, 185, 129, 0.12)',
                    'icon_color'   => '#047857',
                    'btn_sec_text' => '#065F46',
                    'btn_pri_grad' => 'linear-gradient(135deg, #10B981 0%, #047857 100%)',
                    'btn_pri_glow' => 'rgba(4, 120, 87, 0.45)',
                ],
                default => [
                    'bg_grad'      => 'linear-gradient(135deg, #FAF8F5 0%, #FDFCFA 50%, #FFFFFF 100%)',
                    'border'       => 'rgba(184, 149, 111, 0.22)',
                    'glow'         => 'rgba(184, 149, 111, 0.18)',
                    'icon_bg'      => 'rgba(184, 149, 111, 0.12)',
                    'icon_color'   => '#B8956F',
                    'btn_sec_text' => '#1A1A1A',
                    'btn_pri_grad' => 'linear-gradient(135deg, #CFAF53 0%, #B8956F 100%)',
                    'btn_pri_glow' => 'rgba(184, 149, 111, 0.5)',
                ]
            };
        @endphp
        <section class="dash-promo"
                 style="--promo-grad: {{ $naColors['bg_grad'] }};
                        --promo-border: {{ $naColors['border'] }};
                        --promo-glow: {{ $naColors['glow'] }};
                        --promo-icon-bg: {{ $naColors['icon_bg'] }};
                        --promo-icon-color: {{ $naColors['icon_color'] }};
                        --promo-btn-sec: {{ $naColors['btn_sec_text'] }};
                        --promo-btn-pri-grad: {{ $naColors['btn_pri_grad'] }};
                        --promo-btn-pri-glow: {{ $naColors['btn_pri_glow'] }};">
            <div aria-hidden="true" class="dash-promo__orb dash-promo__orb--a"></div>
            <div aria-hidden="true" class="dash-promo__orb dash-promo__orb--b"></div>
            <div aria-hidden="true" class="dash-promo__pattern"></div>

            <div class="dash-promo__inner">
                <div class="dash-promo__left">
                    <div class="dash-promo__icon">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">{!! $nextAction['icon'] !!}</svg>
                    </div>
                    <div class="dash-promo__text">
                        <h2 class="dash-promo__title">{{ $nextAction['title'] }}</h2>
                        <p class="dash-promo__desc">{{ $nextAction['description'] }}</p>
                    </div>
                </div>
                <div class="dash-promo__actions">
                    @if(isset($nextAction['secondary_cta_text']))
                        <a href="{{ $nextAction['secondary_cta_link'] }}" class="dash-promo__btn dash-promo__btn--secondary">
                            {{ $nextAction['secondary_cta_text'] }}
                        </a>
                    @endif

                    @if(isset($nextAction['cta_tab']))
                        <button @click="activeTab = '{{ $nextAction['cta_tab'] }}'" class="dash-promo__btn dash-promo__btn--primary">
                            <span>{{ $nextAction['cta_text'] }}</span>
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.4" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                            </svg>
                        </button>
                    @elseif(isset($nextAction['cta_link']))
                        <a href="{{ $nextAction['cta_link'] }}" class="dash-promo__btn dash-promo__btn--primary">
                            <span>{{ $nextAction['cta_text'] }}</span>
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.4" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                            </svg>
                        </a>
                    @endif
                </div>
            </div>
        </section>
    @endif
