{{-- Partial: Hero Header --}}
    {{-- Hero Header — Dashboard Overview --}}
    @php
        $hour = (int) now()->format('H');
        $greeting = match (true) {
            $hour >= 4  && $hour < 11 => 'Selamat Pagi',
            $hour >= 11 && $hour < 15 => 'Selamat Siang',
            $hour >= 15 && $hour < 18 => 'Selamat Sore',
            default                   => 'Selamat Malam',
        };
        $firstName = trim(strtok($user->name, ' '));
    @endphp
    <section class="dash-hero">
        {{-- Decorative background layers --}}
        <div aria-hidden="true" class="dash-hero__glow dash-hero__glow--gold"></div>
        <div aria-hidden="true" class="dash-hero__glow dash-hero__glow--brown"></div>
        <div aria-hidden="true" class="dash-hero__pattern"></div>
        <div aria-hidden="true" class="dash-hero__sparkle dash-hero__sparkle--1"></div>
        <div aria-hidden="true" class="dash-hero__sparkle dash-hero__sparkle--2"></div>

        <div class="dash-hero__inner">
            {{-- LEFT: Greeting + date --}}
            <div class="dash-hero__left">
                <div class="dash-hero__eyebrow">
                    <span class="dash-hero__eyebrow-dot"></span>
                    Dashboard
                </div>

                <h1 class="dash-hero__title">
                    <span class="dash-hero__title-text">{{ $greeting }}, {{ $firstName }}</span>
                    <span class="dash-hero__wave" aria-hidden="true">👋</span>
                </h1>

                <p class="dash-hero__subtitle">
                    Ringkasan janji temu, pesanan, dan rekam perawatan Anda.
                </p>

                <div class="dash-hero__meta">
                    <div class="dash-hero__date">
                        <span class="dash-hero__date-icon">
                            <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                        </span>
                        <span class="dash-hero__date-text">{{ now()->translatedFormat('l, d F Y') }}</span>
                    </div>

                    @if($isMember)
                        <div class="dash-member-chip dash-member-chip--active">
                            <span class="dash-member-chip__icon">
                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.3" d="M5 13l4 4L19 7"/>
                                </svg>
                            </span>
                            <span>Member Aktif</span>
                            <span class="dash-member-chip__code">{{ $user->pasienProfile?->no_rekam_medis }}</span>
                        </div>
                    @endif
                </div>
            </div>

            {{-- RIGHT: Quick action pills --}}
            <div class="dash-hero__actions">
                <a href="{{ route('appointment.select') }}" class="dash-pill dash-pill--primary">
                    <svg class="dash-pill__icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                    </svg>
                    <span>Buat Janji</span>
                </a>

                <a href="{{ route('pra.create') }}" class="dash-pill">
                    <svg class="dash-pill__icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <span>Konsultasi</span>
                </a>

                @if (\Illuminate\Support\Facades\Route::has('treatment.index'))
                    <a href="{{ route('treatment.index') }}" class="dash-pill">
                        <svg class="dash-pill__icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                        </svg>
                        <span>Treatment</span>
                    </a>
                @endif

                @if (\Illuminate\Support\Facades\Route::has('shop.index'))
                    <a href="{{ route('shop.index') }}" class="dash-pill">
                        <svg class="dash-pill__icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                        </svg>
                        <span>Belanja</span>
                    </a>
                @endif
            </div>
        </div>
    </section>
