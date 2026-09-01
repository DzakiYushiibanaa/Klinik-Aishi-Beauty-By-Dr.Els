{{-- Partial: Dashboard Overview Styles --}}
    <style>
        /* ─────────  HERO HEADER (compact horizontal)  ───────── */
        .dash-hero {
            position: relative;
            overflow: hidden;
            border-radius: 24px;
            border: 1px solid rgba(232, 224, 216, 0.7);
            background:
                radial-gradient(110% 130% at 0% 0%, #FFFFFF 0%, transparent 55%),
                linear-gradient(110deg, #FAF8F5 0%, #F5F0EB 60%, #EFE7DD 100%);
            box-shadow:
                0 1px 2px rgba(26, 26, 26, 0.04),
                0 10px 28px -16px rgba(184, 149, 111, 0.22);
        }

        /* Decorative glows */
        .dash-hero__glow {
            position: absolute;
            border-radius: 9999px;
            filter: blur(60px);
            pointer-events: none;
        }
        .dash-hero__glow--gold {
            top: -90px; right: -70px;
            width: 240px; height: 240px;
            background: rgba(207, 175, 83, 0.2);
            animation: dashHeroPulse 7s ease-in-out infinite;
        }
        .dash-hero__glow--brown {
            bottom: -120px; left: -60px;
            width: 280px; height: 280px;
            background: rgba(184, 149, 111, 0.13);
            animation: dashHeroPulse 9s ease-in-out infinite reverse;
        }

        /* Subtle dot pattern overlay */
        .dash-hero__pattern {
            position: absolute; inset: 0;
            background-image: radial-gradient(rgba(184, 149, 111, 0.16) 1px, transparent 1px);
            background-size: 22px 22px;
            mask-image: linear-gradient(120deg, transparent 25%, black 50%, transparent 80%);
            -webkit-mask-image: linear-gradient(120deg, transparent 25%, black 50%, transparent 80%);
            opacity: 0.55;
            pointer-events: none;
        }

        /* Tiny sparkles */
        .dash-hero__sparkle {
            position: absolute;
            width: 3px; height: 3px;
            border-radius: 9999px;
            background: #CFAF53;
            box-shadow: 0 0 8px 2px rgba(207, 175, 83, 0.55);
            pointer-events: none;
        }
        .dash-hero__sparkle--1 { top: 22%; right: 38%; animation: dashHeroTwinkle 3.2s ease-in-out infinite; }
        .dash-hero__sparkle--2 { bottom: 28%; right: 18%; animation: dashHeroTwinkle 4.6s ease-in-out infinite 1.4s; }

        .dash-hero__inner {
            position: relative;
            z-index: 1;
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
            padding: 1.5rem 1.5rem;
        }
        @media (min-width: 768px) {
            .dash-hero__inner {
                padding: 1.625rem 2rem;
            }
        }
        @media (min-width: 1024px) {
            .dash-hero__inner {
                flex-direction: row;
                align-items: center;
                justify-content: space-between;
                gap: 2rem;
                padding: 1.75rem 2.25rem;
            }
        }

        /* LEFT — Greeting */
        .dash-hero__left { min-width: 0; flex: 1 1 auto; }

        .dash-hero__eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 0.625rem;
            font-weight: 800;
            letter-spacing: 0.28em;
            text-transform: uppercase;
            color: #B8956F;
        }
        .dash-hero__eyebrow-dot {
            width: 5px; height: 5px; border-radius: 9999px;
            background: #CFAF53;
            box-shadow: 0 0 0 3px rgba(207, 175, 83, 0.18);
        }

        .dash-hero__title {
            margin-top: 0.5rem;
            display: inline-flex;
            flex-wrap: wrap;
            align-items: baseline;
            column-gap: 0.5rem;
            row-gap: 0.25rem;
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 1.5rem;
            font-weight: 900;
            line-height: 1.15;
            letter-spacing: -0.015em;
            color: #1A1A1A;
        }
        @media (min-width: 768px) { .dash-hero__title { font-size: 1.75rem; } }
        @media (min-width: 1280px) { .dash-hero__title { font-size: 1.875rem; } }

        .dash-hero__title-text {
            background: linear-gradient(120deg, #1A1A1A 0%, #2C2622 55%, #B8956F 110%);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .dash-hero__wave {
            display: inline-block;
            transform-origin: 70% 70%;
            font-size: 0.85em;
            animation: dashHeroWave 2.6s ease-in-out infinite;
        }

        .dash-hero__subtitle {
            margin-top: 0.375rem;
            max-width: 32rem;
            font-size: 0.8125rem;
            line-height: 1.55;
            color: #64748B;
        }
        @media (min-width: 768px) { .dash-hero__subtitle { font-size: 0.875rem; } }

        /* Date pill — compact elegant */
        .dash-hero__meta {
            margin-top: 0.875rem;
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 0.5rem;
        }

        .dash-hero__date {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.3125rem 0.75rem 0.3125rem 0.3125rem;
            border-radius: 9999px;
            border: 1px solid rgba(232, 224, 216, 0.9);
            background: rgba(255, 255, 255, 0.78);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            font-size: 0.75rem;
            font-weight: 700;
            color: #1A1A1A;
            box-shadow: 0 4px 10px -6px rgba(184, 149, 111, 0.28);
            letter-spacing: 0.01em;
        }
        .dash-hero__date-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 22px; height: 22px;
            border-radius: 9999px;
            background: linear-gradient(135deg, #CFAF53 0%, #B8956F 100%);
            color: #fff;
            box-shadow: 0 2px 5px -2px rgba(184, 149, 111, 0.6);
        }

        /* RIGHT — Quick action pills (horizontal row) */
        .dash-member-chip {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            min-height: 32px;
            max-width: 100%;
            padding: 0.375rem 0.75rem;
            border-radius: 9999px;
            border: 1px solid rgba(232, 224, 216, 0.9);
            background: rgba(255, 255, 255, 0.72);
            color: #64748B;
            font-size: 0.72rem;
            font-weight: 700;
            line-height: 1.25;
        }
        .dash-member-chip--active {
            border-color: rgba(207, 175, 83, 0.45);
            background: #1A1A1A;
            color: #FFFFFF;
            box-shadow: 0 8px 18px -12px rgba(26, 26, 26, 0.45);
        }
        .dash-member-chip__icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 20px;
            height: 20px;
            border-radius: 9999px;
            background: #CFAF53;
            color: #1A1A1A;
            flex-shrink: 0;
        }
        .dash-member-chip__code {
            color: #EBD68E;
            font-size: 0.68rem;
            font-weight: 900;
            letter-spacing: 0.04em;
        }

        .dash-hero__actions {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 0.5rem;
            flex-shrink: 0;
        }
        @media (min-width: 1024px) { .dash-hero__actions { flex-wrap: nowrap; } }

        .dash-pill {
            position: relative;
            display: inline-flex;
            align-items: center;
            gap: 0.4375rem;
            padding: 0.5625rem 0.95rem;
            border-radius: 12px;
            border: 1px solid rgba(232, 224, 216, 0.85);
            background: rgba(255, 255, 255, 0.82);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            color: #1A1A1A;
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 0.8125rem;
            font-weight: 700;
            text-decoration: none;
            cursor: pointer;
            transition: transform 200ms cubic-bezier(0.34, 1.4, 0.64, 1),
                        box-shadow 200ms ease,
                        border-color 200ms ease,
                        background 200ms ease,
                        color 200ms ease;
            box-shadow: 0 1px 2px rgba(26, 26, 26, 0.04);
        }
        .dash-pill:hover {
            transform: translateY(-2px);
            border-color: rgba(184, 149, 111, 0.55);
            background: rgba(255, 255, 255, 0.96);
            color: #B8956F;
            box-shadow:
                0 1px 2px rgba(26, 26, 26, 0.05),
                0 10px 22px -12px rgba(184, 149, 111, 0.45);
        }
        .dash-pill:active { transform: translateY(0); }
        .dash-pill:focus-visible {
            outline: none;
            box-shadow:
                0 0 0 3px rgba(207, 175, 83, 0.35),
                0 10px 22px -12px rgba(184, 149, 111, 0.45);
        }

        .dash-pill__icon {
            width: 1rem;
            height: 1rem;
            color: #B8956F;
            flex-shrink: 0;
            transition: transform 200ms ease;
        }
        .dash-pill:hover .dash-pill__icon { transform: scale(1.1); }

        /* Primary variant */
        .dash-pill--primary {
            border-color: transparent;
            background: linear-gradient(135deg, #CFAF53 0%, #B8956F 100%);
            color: #fff;
            box-shadow:
                0 1px 2px rgba(184, 149, 111, 0.25),
                0 8px 18px -8px rgba(184, 149, 111, 0.55);
        }
        .dash-pill--primary:hover {
            background: linear-gradient(135deg, #d8b85b 0%, #c19f78 100%);
            border-color: transparent;
            color: #fff;
            box-shadow:
                0 1px 2px rgba(184, 149, 111, 0.3),
                0 14px 24px -10px rgba(184, 149, 111, 0.6);
        }
        .dash-pill--primary .dash-pill__icon { color: #fff; }

        /* Animations */
        @keyframes dashHeroWave {
            0%, 60%, 100% { transform: rotate(0deg); }
            10%, 30%      { transform: rotate(14deg); }
            20%           { transform: rotate(-8deg); }
            40%           { transform: rotate(14deg); }
            50%           { transform: rotate(-4deg); }
        }
        @keyframes dashHeroPulse {
            0%, 100% { opacity: 1; transform: scale(1); }
            50%      { opacity: 0.65; transform: scale(1.06); }
        }
        @keyframes dashHeroTwinkle {
            0%, 100% { opacity: 0.4; transform: scale(0.85); }
            50%      { opacity: 1;   transform: scale(1.2); }
        }
        @media (prefers-reduced-motion: reduce) {
            .dash-hero__wave,
            .dash-hero__glow--gold,
            .dash-hero__glow--brown,
            .dash-hero__sparkle { animation: none; }
            .dash-pill,
            .dash-pill__icon { transition: none; }
            .dash-pill:hover { transform: none; }
            .dash-pill:hover .dash-pill__icon { transform: none; }
        }

            /* ─────────  PROMO / NEXT ACTION BANNER  ───────── */
            .dash-promo {
                position: relative;
                overflow: hidden;
                border-radius: 22px;
                border: 1px solid var(--promo-border);
                background: var(--promo-grad);
                box-shadow:
                    0 1px 2px rgba(26, 26, 26, 0.04),
                    0 14px 32px -18px var(--promo-glow);
            }
            .dash-promo__orb {
                position: absolute;
                border-radius: 9999px;
                filter: blur(60px);
                pointer-events: none;
                background: var(--promo-glow);
            }
            .dash-promo__orb--a { top: -80px; right: -60px; width: 220px; height: 220px; opacity: 0.85; }
            .dash-promo__orb--b { bottom: -100px; left: -50px; width: 240px; height: 240px; opacity: 0.55; }
            .dash-promo__pattern {
                position: absolute; inset: 0;
                background-image: radial-gradient(rgba(184, 149, 111, 0.14) 1px, transparent 1px);
                background-size: 22px 22px;
                mask-image: linear-gradient(120deg, transparent 35%, black 65%, transparent 95%);
                -webkit-mask-image: linear-gradient(120deg, transparent 35%, black 65%, transparent 95%);
                opacity: 0.5;
                pointer-events: none;
            }

            .dash-promo__inner {
                position: relative;
                z-index: 1;
                display: flex;
                flex-direction: column;
                gap: 1.25rem;
                padding: 1.5rem 1.5rem;
            }
            @media (min-width: 768px) {
                .dash-promo__inner { padding: 1.5rem 2rem; flex-direction: row; align-items: center; justify-content: space-between; gap: 1.75rem; }
            }

            .dash-promo__left {
                display: flex;
                align-items: flex-start;
                gap: 1rem;
                min-width: 0;
            }
            .dash-promo__icon {
                width: 48px; height: 48px;
                border-radius: 14px;
                background: var(--promo-icon-bg);
                color: var(--promo-icon-color);
                display: inline-flex;
                align-items: center;
                justify-content: center;
                flex-shrink: 0;
                box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.6),
                            0 6px 16px -8px var(--promo-glow);
            }
            .dash-promo__icon svg { width: 22px; height: 22px; stroke-width: 2; }
            .dash-promo__text { min-width: 0; }
            .dash-promo__title {
                font-family: 'Plus Jakarta Sans', sans-serif;
                font-size: 1.0625rem;
                font-weight: 800;
                line-height: 1.3;
                letter-spacing: -0.01em;
                color: #1A1A1A;
            }
            .dash-promo__desc {
                margin-top: 0.25rem;
                font-size: 0.8125rem;
                line-height: 1.55;
                color: #64748B;
            }

            .dash-promo__actions {
                display: flex;
                flex-wrap: wrap;
                align-items: center;
                gap: 0.625rem;
                flex-shrink: 0;
            }
            .dash-promo__btn {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                gap: 0.4375rem;
                padding: 0.625rem 1.125rem;
                border-radius: 9999px;
                font-family: 'Plus Jakarta Sans', sans-serif;
                font-size: 0.8125rem;
                font-weight: 700;
                text-decoration: none;
                cursor: pointer;
                transition: transform 200ms ease, box-shadow 200ms ease, background 200ms ease, border-color 200ms ease;
                white-space: nowrap;
            }
            .dash-promo__btn--secondary {
                background: rgba(255, 255, 255, 0.85);
                backdrop-filter: blur(8px);
                -webkit-backdrop-filter: blur(8px);
                color: var(--promo-btn-sec);
                border: 1px solid rgba(0, 0, 0, 0.06);
                box-shadow: 0 1px 2px rgba(26, 26, 26, 0.04);
            }
            .dash-promo__btn--secondary:hover {
                background: #fff;
                transform: translateY(-1px);
                box-shadow: 0 8px 16px -8px rgba(0, 0, 0, 0.1);
            }
            .dash-promo__btn--primary {
                background: var(--promo-btn-pri-grad);
                color: #fff;
                border: 1px solid transparent;
                box-shadow:
                    0 1px 2px rgba(0, 0, 0, 0.08),
                    0 10px 22px -10px var(--promo-btn-pri-glow);
            }
            .dash-promo__btn--primary:hover {
                transform: translateY(-1px);
                box-shadow:
                    0 1px 2px rgba(0, 0, 0, 0.1),
                    0 14px 26px -10px var(--promo-btn-pri-glow);
                filter: brightness(1.05);
            }
            .dash-promo__btn--primary:active { transform: translateY(0); }

            @media (max-width: 767px) {
                .dash-promo__btn,
                .dash-promo__btn--secondary,
                .dash-promo__btn--primary { width: 100%; }
            }
            @media (prefers-reduced-motion: reduce) {
                .dash-promo__btn { transition: none; }
                .dash-promo__btn:hover { transform: none; filter: none; }
            }

        /* ─────────  STATS CARDS  ───────── */
        .dash-stats {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 0.875rem;
        }
        @media (min-width: 768px) {
            .dash-stats { grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 1rem; }
        }

        .dash-stat {
            position: relative;
            overflow: hidden;
            padding: 1.25rem;
            border-radius: 20px;
            background:
                linear-gradient(180deg, rgba(255, 255, 255, 0.85) 0%, rgba(255, 255, 255, 0.6) 100%),
                var(--stat-tint, #FAF8F5);
            border: 1px solid var(--stat-border, rgba(232, 224, 216, 0.7));
            box-shadow:
                0 1px 2px rgba(26, 26, 26, 0.04),
                0 10px 24px -16px var(--stat-glow, rgba(184, 149, 111, 0.2));
            transition: transform 250ms cubic-bezier(0.34, 1.4, 0.64, 1),
                        box-shadow 250ms ease,
                        border-color 250ms ease;
        }
        .dash-stat:hover {
            transform: translateY(-3px);
            border-color: var(--stat-border-hover, rgba(184, 149, 111, 0.5));
            box-shadow:
                0 1px 2px rgba(26, 26, 26, 0.05),
                0 18px 32px -14px var(--stat-glow, rgba(184, 149, 111, 0.32));
        }

        .dash-stat__glow {
            position: absolute;
            top: -50px; right: -50px;
            width: 160px; height: 160px;
            border-radius: 9999px;
            background: var(--stat-glow, rgba(184, 149, 111, 0.18));
            filter: blur(50px);
            opacity: 0.7;
            pointer-events: none;
        }

        .dash-stat__head {
            position: relative;
            z-index: 1;
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1rem;
        }
        .dash-stat__icon {
            width: 40px; height: 40px;
            border-radius: 12px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: var(--stat-icon-bg, rgba(184, 149, 111, 0.14));
            color: var(--stat-icon-color, #B8956F);
            box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.6);
        }
        .dash-stat__icon svg { width: 20px; height: 20px; stroke-width: 2; }
        .dash-stat__chip {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 0.625rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.16em;
            padding: 0.25rem 0.5rem;
            border-radius: 9999px;
            background: var(--stat-chip-bg, rgba(184, 149, 111, 0.12));
            color: var(--stat-chip-color, #B8956F);
        }

        .dash-stat__value {
            position: relative;
            z-index: 1;
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 2.75rem;
            line-height: 1;
            font-weight: 900;
            letter-spacing: -0.04em;
            color: #1A1A1A;
        }
        @media (min-width: 768px) { .dash-stat__value { font-size: 3.25rem; } }

        .dash-stat__label {
            position: relative;
            z-index: 1;
            margin-top: 0.625rem;
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 0.9375rem;
            font-weight: 800;
            letter-spacing: -0.005em;
            color: #1A1A1A;
        }
        .dash-stat__hint {
            position: relative;
            z-index: 1;
            margin-top: 0.25rem;
            font-size: 0.75rem;
            color: #94A3B8;
            font-weight: 500;
        }

        /* Variants — gold / amber / mint */
        .dash-stat--gold {
            --stat-tint: linear-gradient(180deg, #FFFCF3 0%, #FFFFFF 100%);
            --stat-border: rgba(207, 175, 83, 0.28);
            --stat-border-hover: rgba(207, 175, 83, 0.55);
            --stat-glow: rgba(207, 175, 83, 0.22);
            --stat-icon-bg: linear-gradient(135deg, rgba(207, 175, 83, 0.18) 0%, rgba(184, 149, 111, 0.18) 100%);
            --stat-icon-color: #B8956F;
            --stat-chip-bg: rgba(207, 175, 83, 0.14);
            --stat-chip-color: #8C7A63;
        }
        .dash-stat--amber {
            --stat-tint: linear-gradient(180deg, #FFF8EC 0%, #FFFFFF 100%);
            --stat-border: rgba(245, 158, 11, 0.22);
            --stat-border-hover: rgba(245, 158, 11, 0.5);
            --stat-glow: rgba(245, 158, 11, 0.22);
            --stat-icon-bg: linear-gradient(135deg, rgba(245, 158, 11, 0.16) 0%, rgba(217, 119, 6, 0.16) 100%);
            --stat-icon-color: #D97706;
            --stat-chip-bg: rgba(245, 158, 11, 0.14);
            --stat-chip-color: #B45309;
        }
        .dash-stat--mint {
            --stat-tint: linear-gradient(180deg, #F1FBF6 0%, #FFFFFF 100%);
            --stat-border: rgba(16, 185, 129, 0.22);
            --stat-border-hover: rgba(16, 185, 129, 0.5);
            --stat-glow: rgba(16, 185, 129, 0.22);
            --stat-icon-bg: linear-gradient(135deg, rgba(16, 185, 129, 0.16) 0%, rgba(5, 150, 105, 0.16) 100%);
            --stat-icon-color: #047857;
            --stat-chip-bg: rgba(16, 185, 129, 0.14);
            --stat-chip-color: #047857;
        }

        @media (max-width: 767px) {
            .dash-stat--mint { grid-column: span 2; }
        }

        @media (prefers-reduced-motion: reduce) {
            .dash-stat { transition: none; }
            .dash-stat:hover { transform: none; }
        }

    /* ─────────  SHARED SECTION CARD + EMPTY STATE  ───────── */
    .dash-section {
        position: relative;
        overflow: hidden;
        background:
            radial-gradient(140% 90% at 100% 0%, rgba(207, 175, 83, 0.05) 0%, transparent 55%),
            #ffffff;
        border: 1px solid rgba(232, 224, 216, 0.7);
        border-radius: 22px;
        padding: 1.5rem;
        box-shadow:
            0 1px 2px rgba(26, 26, 26, 0.03),
            0 8px 24px -18px rgba(184, 149, 111, 0.18);
    }
    @media (min-width: 768px) {
        .dash-section { padding: 1.75rem; }
    }

    .dash-section__head {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 1rem;
        margin-bottom: 1.25rem;
    }
    .dash-section__eyebrow {
        font-family: 'Plus Jakarta Sans', sans-serif;
        font-size: 0.6875rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.22em;
        color: #8C7A63;
    }
    .dash-section__sub {
        margin-top: 0.25rem;
        font-size: 0.75rem;
        color: #94A3B8;
        font-weight: 500;
    }
    .dash-section__link {
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
        padding: 0.4375rem 0.875rem;
        border-radius: 9999px;
        background: rgba(207, 175, 83, 0.1);
        color: #B8956F;
        font-family: 'Plus Jakarta Sans', sans-serif;
        font-size: 0.75rem;
        font-weight: 700;
        text-decoration: none;
        white-space: nowrap;
        transition: background 200ms ease, color 200ms ease, transform 200ms ease;
        cursor: pointer;
    }
    .dash-section__link:hover {
        background: linear-gradient(135deg, #CFAF53 0%, #B8956F 100%);
        color: #fff;
        transform: translateY(-1px);
    }

    /* Empty state — premium dashed card */
    .dash-empty {
        position: relative;
        overflow: hidden;
        padding: 2.5rem 1.5rem;
        text-align: center;
        background:
            radial-gradient(80% 100% at 50% 0%, rgba(207, 175, 83, 0.06) 0%, transparent 60%),
            linear-gradient(180deg, #FDFCFA 0%, #FAF8F5 100%);
        border: 1.5px dashed rgba(184, 149, 111, 0.3);
        border-radius: 20px;
    }
    .dash-empty__orb {
        position: absolute;
        top: -60px; right: -50px;
        width: 180px; height: 180px;
        border-radius: 9999px;
        background: rgba(207, 175, 83, 0.18);
        filter: blur(60px);
        pointer-events: none;
        opacity: 0.7;
    }
    .dash-empty__icon {
        position: relative;
        z-index: 1;
        margin: 0 auto 1rem;
        width: 60px; height: 60px;
        border-radius: 18px;
        background: linear-gradient(135deg, #FFFFFF 0%, #FAF8F5 100%);
        border: 1px solid rgba(232, 224, 216, 0.8);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: #B8956F;
        box-shadow:
            0 1px 2px rgba(26, 26, 26, 0.04),
            0 8px 18px -8px rgba(184, 149, 111, 0.3);
    }
    .dash-empty__icon svg { width: 28px; height: 28px; stroke-width: 1.6; }
    .dash-empty__title {
        position: relative;
        z-index: 1;
        font-family: 'Plus Jakarta Sans', sans-serif;
        font-size: 0.9375rem;
        font-weight: 800;
        color: #1A1A1A;
        letter-spacing: -0.01em;
    }
    .dash-empty__desc {
        position: relative;
        z-index: 1;
        margin-top: 0.375rem;
        max-width: 28rem;
        margin-left: auto;
        margin-right: auto;
        font-size: 0.8125rem;
        line-height: 1.55;
        color: #64748B;
    }
    .dash-empty__cta {
        position: relative;
        z-index: 1;
        margin-top: 1.25rem;
        display: inline-flex;
        align-items: center;
        gap: 0.4375rem;
        padding: 0.625rem 1.25rem;
        border-radius: 9999px;
        background: linear-gradient(135deg, #CFAF53 0%, #B8956F 100%);
        color: #fff;
        font-family: 'Plus Jakarta Sans', sans-serif;
        font-size: 0.8125rem;
        font-weight: 700;
        text-decoration: none;
        box-shadow:
            0 1px 2px rgba(184, 149, 111, 0.25),
            0 10px 22px -10px rgba(184, 149, 111, 0.55);
        transition: transform 200ms ease, box-shadow 200ms ease, filter 200ms ease;
        cursor: pointer;
    }
    .dash-empty__cta:hover {
        transform: translateY(-2px);
        filter: brightness(1.05);
        box-shadow:
            0 1px 2px rgba(184, 149, 111, 0.3),
            0 14px 26px -10px rgba(184, 149, 111, 0.6);
    }
    .dash-empty__cta:active { transform: translateY(0); }

    @media (prefers-reduced-motion: reduce) {
        .dash-section__link,
        .dash-empty__cta { transition: none; }
        .dash-section__link:hover,
        .dash-empty__cta:hover { transform: none; filter: none; }
    }
    </style>
