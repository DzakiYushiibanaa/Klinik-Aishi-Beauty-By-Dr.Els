{{-- Partial: Pra-Konsultasi Styles --}}
@once
    <style>
        /* ΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇ  SECTION CONTAINER  ΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇ */
        .prakonsul-section {
            position: relative;
            overflow: hidden;
            background:
                radial-gradient(140% 90% at 100% 0%, rgba(207, 175, 83, 0.08) 0%, transparent 60%),
                radial-gradient(120% 90% at 0% 100%, rgba(184, 149, 111, 0.06) 0%, transparent 55%),
                #ffffff;
            border: 1px solid rgba(232, 224, 216, 0.7);
            border-radius: 22px;
            box-shadow:
                0 1px 2px rgba(26, 26, 26, 0.03),
                0 12px 28px -18px rgba(184, 149, 111, 0.2);
        }
        .prakonsul-section__glow {
            position: absolute;
            top: -120px; right: -80px;
            width: 280px; height: 280px;
            border-radius: 9999px;
            background: rgba(207, 175, 83, 0.14);
            filter: blur(80px);
            pointer-events: none;
            opacity: 0.7;
        }
        .prakonsul-section__pattern {
            position: absolute; inset: 0;
            background-image: radial-gradient(rgba(184, 149, 111, 0.14) 1px, transparent 1px);
            background-size: 22px 22px;
            mask-image: linear-gradient(135deg, transparent 30%, black 60%, transparent 100%);
            -webkit-mask-image: linear-gradient(135deg, transparent 30%, black 60%, transparent 100%);
            opacity: 0.4;
            pointer-events: none;
        }
        .prakonsul-section__inner {
            position: relative;
            z-index: 1;
            padding: 1.5rem 1.25rem;
        }
        @media (min-width: 768px) {
            .prakonsul-section__inner { padding: 1.75rem 1.75rem; }
        }
        @media (min-width: 1024px) {
            .prakonsul-section__inner { padding: 2rem 2rem; }
        }

        /* Section header */
        .prakonsul-section__head {
            display: flex;
            flex-direction: column;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        @media (min-width: 640px) {
            .prakonsul-section__head {
                flex-direction: row;
                align-items: center;
                justify-content: space-between;
                gap: 1.5rem;
            }
        }
        .prakonsul-section__heading { min-width: 0; }
        .prakonsul-section__eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            margin: 0 0 0.5rem 0;
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 0.625rem;
            font-weight: 800;
            letter-spacing: 0.28em;
            text-transform: uppercase;
            color: #B8956F;
        }
        .prakonsul-section__dot {
            width: 5px; height: 5px;
            border-radius: 9999px;
            background: #CFAF53;
            box-shadow: 0 0 0 3px rgba(207, 175, 83, 0.18);
        }
        .prakonsul-section__title {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 1.25rem;
            font-weight: 900;
            letter-spacing: -0.015em;
            color: #1A1A1A;
            line-height: 1.2;
        }
        @media (min-width: 768px) {
            .prakonsul-section__title { font-size: 1.5rem; }
        }
        .prakonsul-section__sub {
            margin-top: 0.375rem;
            max-width: 36rem;
            font-size: 0.8125rem;
            line-height: 1.55;
            color: #64748B;
        }
        @media (min-width: 768px) {
            .prakonsul-section__sub { font-size: 0.875rem; }
        }
        .prakonsul-section__cta {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 0.625rem 1.125rem;
            border-radius: 9999px;
            background: linear-gradient(135deg, #CFAF53 0%, #B8956F 100%);
            color: #ffffff;
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 0.8125rem;
            font-weight: 700;
            text-decoration: none;
            cursor: pointer;
            white-space: nowrap;
            self-align: flex-start;
            box-shadow:
                0 1px 2px rgba(184, 149, 111, 0.25),
                0 8px 18px -8px rgba(184, 149, 111, 0.55);
            transition: transform 200ms ease, box-shadow 200ms ease, filter 200ms ease;
        }
        .prakonsul-section__cta svg { width: 14px; height: 14px; }
        .prakonsul-section__cta:hover {
            transform: translateY(-2px);
            filter: brightness(1.05);
            box-shadow:
                0 1px 2px rgba(184, 149, 111, 0.3),
                0 14px 24px -8px rgba(184, 149, 111, 0.6);
        }
        .prakonsul-section__cta:active { transform: translateY(0); }

        /* Grid (single centered card or auto-fill) */
        .prakonsul-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1rem;
            max-width: 820px;
            margin: 0 auto;
        }
        @media (min-width: 768px) {
            .prakonsul-grid { gap: 1.25rem; }
        }
        @media (min-width: 1024px) {
            .prakonsul-grid {
                grid-template-columns: repeat(auto-fill, minmax(360px, 1fr));
                max-width: 100%;
            }
        }
        /* When only 1 card, keep it centered max-width 820px on desktop */
        .prakonsul-grid:has(> .prakonsul-card:only-child) {
            grid-template-columns: 1fr;
            max-width: 820px;
        }

        /* ΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇ  PRA-KONSUL UNIFIED CARD  ΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇ */
        .prakonsul-card {
            position: relative;
            overflow: hidden;
            background:
                radial-gradient(120% 80% at 100% 0%, rgba(207, 175, 83, 0.06) 0%, transparent 60%),
                #FDFCFA;
            border: 1px solid rgba(232, 224, 216, 0.7);
            border-radius: 20px;
            box-shadow:
                0 1px 2px rgba(26, 26, 26, 0.03),
                0 8px 22px -16px rgba(184, 149, 111, 0.2);
        }
        .prakonsul-card__glow {
            position: absolute;
            top: -60px; right: -50px;
            width: 200px; height: 200px;
            border-radius: 9999px;
            background: rgba(207, 175, 83, 0.13);
            filter: blur(60px);
            pointer-events: none;
            opacity: 0.7;
        }
        .prakonsul-card__inner {
            position: relative;
            z-index: 1;
            padding: 1.5rem;
            display: flex;
            flex-direction: column;
            gap: 1.25rem;
        }
        @media (min-width: 768px) {
            .prakonsul-card__inner { padding: 1.75rem; gap: 1.5rem; }
        }

        /* Header ΓÇö date left, status badge right */
        .prakonsul-card__header {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 0.75rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid rgba(232, 224, 216, 0.65);
        }
        .prakonsul-card__date {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            min-width: 0;
        }
        .prakonsul-card__date-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 40px; height: 40px;
            border-radius: 12px;
            background: linear-gradient(135deg, rgba(207, 175, 83, 0.18) 0%, rgba(184, 149, 111, 0.22) 100%);
            color: #B8956F;
            border: 1px solid rgba(184, 149, 111, 0.22);
            flex-shrink: 0;
        }
        .prakonsul-card__date-icon svg { width: 18px; height: 18px; stroke-width: 1.8; }
        .prakonsul-card__date-text {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 1rem;
            font-weight: 800;
            color: #1A1A1A;
            line-height: 1.2;
            letter-spacing: -0.01em;
        }
        .prakonsul-card__date-time {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 0.8125rem;
            font-weight: 500;
            color: #94A3B8;
            line-height: 1.2;
            margin-top: 4px;
        }

        /* Section */
        .prakonsul-card__section {
            display: flex;
            flex-direction: column;
            gap: 0.625rem;
        }
        .prakonsul-card__label {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 0.6875rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.18em;
            color: #94A3B8;
            margin: 0;
        }

        /* Keluhan */
        .prakonsul-card__quote {
            padding: 1rem 1.125rem;
            background: #FAF8F5;
            border: 1px solid rgba(232, 224, 216, 0.7);
            border-left: 3px solid #CFAF53;
            border-radius: 14px;
        }
        .prakonsul-card__keluhan {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 0.9375rem;
            line-height: 1.7;
            color: #1A1A1A;
            font-weight: 500;
            margin: 0;
            text-align: left;
            white-space: pre-wrap;
            word-break: break-word;
            overflow-wrap: anywhere;
        }
        .prakonsul-card__keluhan--clamp {
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        .prakonsul-card__more {
            margin-top: 0.5rem;
            background: transparent;
            border: 0;
            padding: 0;
            color: #B8956F;
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 0.8125rem;
            font-weight: 700;
            cursor: pointer;
            transition: color 160ms ease;
        }
        .prakonsul-card__more:hover {
            color: #8C7A63;
            text-decoration: underline;
        }

        /* Status badge ΓÇö calmer, smaller */
        .prakonsul-card__status {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.4375rem 0.75rem;
            border-radius: 9999px;
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 0.75rem;
            font-weight: 700;
            line-height: 1;
            border: 1px solid transparent;
            white-space: nowrap;
        }
        .prakonsul-card__status-dot {
            width: 6px;
            height: 6px;
            border-radius: 9999px;
            display: inline-block;
            flex-shrink: 0;
        }
        .prakonsul-card__status--pending {
            background: #FFF8EB;
            color: #92400E;
            border-color: rgba(245, 158, 11, 0.25);
        }
        .prakonsul-card__status--pending .prakonsul-card__status-dot { background: #D97706; }
        .prakonsul-card__status--review {
            background: #EFF6FF;
            color: #1E40AF;
            border-color: rgba(37, 99, 235, 0.25);
        }
        .prakonsul-card__status--review .prakonsul-card__status-dot { background: #1D4ED8; }
        .prakonsul-card__status--done {
            background: #ECFDF5;
            color: #065F46;
            border-color: rgba(16, 185, 129, 0.25);
        }
        .prakonsul-card__status--done .prakonsul-card__status-dot { background: #047857; }
        .prakonsul-card__status--closed {
            background: #FEF2F2;
            color: #9F1239;
            border-color: rgba(244, 63, 94, 0.25);
        }
        .prakonsul-card__status--closed .prakonsul-card__status-dot { background: #E11D48; }

        /* Progress timeline */
        .prakonsul-timeline {
            list-style: none;
            margin: 0;
            padding: 1rem 1.125rem;
            background: #ffffff;
            border: 1px solid rgba(232, 224, 216, 0.7);
            border-radius: 14px;
            display: flex;
            flex-direction: column;
            gap: 1rem;
            position: relative;
        }
        .prakonsul-timeline__step {
            position: relative;
            display: flex;
            align-items: flex-start;
            gap: 0.875rem;
            padding-left: 0;
        }
        /* Connector line behind dots */
        .prakonsul-timeline__step:not(:last-child)::before {
            content: '';
            position: absolute;
            left: 11px;
            top: 26px;
            bottom: -1rem;
            width: 1.5px;
            background: #E2E8F0;
        }
        .prakonsul-timeline__step--done:not(:last-child)::before {
            background: linear-gradient(180deg, #B8956F 0%, #E2E8F0 100%);
        }
        .prakonsul-timeline__dot {
            position: relative;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 22px;
            height: 22px;
            border-radius: 9999px;
            background: #ffffff;
            border: 1.5px solid #E2E8F0;
            color: transparent;
            flex-shrink: 0;
            margin-top: 1px;
        }
        .prakonsul-timeline__dot svg { width: 12px; height: 12px; }
        .prakonsul-timeline__step--done .prakonsul-timeline__dot {
            background: linear-gradient(135deg, #CFAF53 0%, #B8956F 100%);
            border-color: transparent;
            color: #ffffff;
            box-shadow: 0 2px 6px -2px rgba(184, 149, 111, 0.5);
        }
        .prakonsul-timeline__step--active .prakonsul-timeline__dot {
            background: rgba(245, 158, 11, 0.12);
            border-color: rgba(245, 158, 11, 0.55);
        }
        .prakonsul-timeline__pulse {
            width: 8px;
            height: 8px;
            border-radius: 9999px;
            background: #D97706;
            box-shadow: 0 0 0 4px rgba(217, 119, 6, 0.18);
            animation: prakonsulPulse 2s ease-in-out infinite;
        }
        .prakonsul-timeline__body { flex: 1; min-width: 0; }
        .prakonsul-timeline__title {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 0.875rem;
            font-weight: 700;
            color: #1A1A1A;
            line-height: 1.3;
            margin: 0;
        }
        .prakonsul-timeline__step--done .prakonsul-timeline__title { color: #475569; }
        .prakonsul-timeline__step--idle .prakonsul-timeline__title { color: #94A3B8; }
        .prakonsul-timeline__sub {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 0.75rem;
            color: #94A3B8;
            line-height: 1.45;
            margin: 3px 0 0 0;
        }

        /* Info text */
        .prakonsul-card__info {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 0.8125rem;
            line-height: 1.6;
            color: #64748B;
            margin: 0;
            padding: 0.75rem 1rem;
            background: #FAF8F5;
            border-radius: 10px;
            border: 1px solid rgba(232, 224, 216, 0.55);
        }
        .prakonsul-card__info strong {
            color: #1A1A1A;
            font-weight: 700;
        }

        /* Footer */
        .prakonsul-card__footer {
            margin-top: 0.25rem;
            padding-top: 1.125rem;
            border-top: 1px solid rgba(232, 224, 216, 0.65);
        }
        .prakonsul-card__cta {
            width: 100%;
            min-height: 46px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 0.75rem 1.25rem;
            border-radius: 9999px;
            background: rgba(207, 175, 83, 0.08);
            border: 1px solid rgba(184, 149, 111, 0.4);
            color: #8C7A63;
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 0.875rem;
            font-weight: 700;
            cursor: pointer;
            transition: background 200ms ease, color 200ms ease, border-color 200ms ease, transform 200ms ease, box-shadow 200ms ease;
        }
        .prakonsul-card__cta svg {
            width: 14px; height: 14px;
            transition: transform 200ms ease;
        }
        .prakonsul-card__cta:hover {
            background: linear-gradient(135deg, #CFAF53 0%, #B8956F 100%);
            border-color: transparent;
            color: #ffffff;
            transform: translateY(-1px);
            box-shadow: 0 8px 18px -8px rgba(184, 149, 111, 0.55);
        }
        .prakonsul-card__cta:hover svg { transform: translateX(2px); }
        .prakonsul-card__cta:active { transform: translateY(0); }

        /* Primary CTA ΓÇö gold gradient solid */
        .prakonsul-card__cta--primary {
            background: linear-gradient(135deg, #CFAF53 0%, #B8956F 100%);
            border-color: transparent;
            color: #ffffff;
            box-shadow:
                0 1px 2px rgba(184, 149, 111, 0.25),
                0 8px 18px -8px rgba(184, 149, 111, 0.55);
        }
        .prakonsul-card__cta--primary:hover {
            filter: brightness(1.05);
            box-shadow:
                0 1px 2px rgba(184, 149, 111, 0.3),
                0 12px 22px -8px rgba(184, 149, 111, 0.6);
        }

        /* Waiting CTA ΓÇö disabled-soft state */
        .prakonsul-card__cta--waiting {
            background: #F8FAFC;
            border-color: rgba(226, 232, 240, 0.85);
            color: #94A3B8;
            cursor: pointer;
        }
        .prakonsul-card__cta--waiting:hover {
            background: #F8FAFC;
            border-color: rgba(184, 149, 111, 0.3);
            color: #8C7A63;
            transform: none;
            box-shadow: 0 4px 10px -6px rgba(0, 0, 0, 0.06);
        }
        .prakonsul-card__cta--waiting:hover svg { transform: none; }
        .prakonsul-card__cta--waiting svg { width: 14px; height: 14px; }

        @keyframes prakonsulPulse {
            0%, 100% { transform: scale(1);   opacity: 1;   box-shadow: 0 0 0 4px rgba(217, 119, 6, 0.18); }
            50%      { transform: scale(1.2); opacity: 0.85; box-shadow: 0 0 0 6px rgba(217, 119, 6, 0.05); }
        }

        /* ΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇ  COMPANION ASIDE PANEL  ΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇ */
        .prakonsul-aside {
            position: relative;
            overflow: hidden;
            background:
                radial-gradient(140% 100% at 100% 0%, rgba(207, 175, 83, 0.1) 0%, transparent 60%),
                linear-gradient(180deg, #FAF8F5 0%, #F5F0EB 100%);
            border: 1px solid rgba(232, 224, 216, 0.7);
            border-radius: 18px;
            box-shadow: 0 1px 2px rgba(26, 26, 26, 0.03);
        }
        .prakonsul-aside__glow {
            position: absolute;
            top: -60px; right: -40px;
            width: 200px; height: 200px;
            border-radius: 9999px;
            background: rgba(207, 175, 83, 0.18);
            filter: blur(60px);
            pointer-events: none;
        }
        .prakonsul-aside__inner {
            position: relative;
            z-index: 1;
            padding: 1.5rem;
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }
        @media (min-width: 1024px) {
            .prakonsul-aside__inner { padding: 1.75rem; }
        }
        .prakonsul-aside__icon {
            width: 44px; height: 44px;
            border-radius: 12px;
            background: linear-gradient(135deg, #CFAF53 0%, #B8956F 100%);
            color: #ffffff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            box-shadow:
                inset 0 0 0 1px rgba(255, 255, 255, 0.25),
                0 6px 14px -4px rgba(184, 149, 111, 0.5);
        }
        .prakonsul-aside__icon svg { width: 22px; height: 22px; }
        .prakonsul-aside__title {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 1rem;
            font-weight: 800;
            color: #1A1A1A;
            letter-spacing: -0.01em;
            line-height: 1.3;
        }
        .prakonsul-aside__desc {
            font-size: 0.8125rem;
            line-height: 1.6;
            color: #64748B;
            margin: 0;
        }
        .prakonsul-aside__strong {
            font-weight: 700;
            color: #1A1A1A;
        }

        /* Step list */
        .prakonsul-aside__steps {
            list-style: none;
            margin: 0.25rem 0 0 0;
            padding: 0.875rem;
            background: #ffffff;
            border: 1px solid rgba(232, 224, 216, 0.7);
            border-radius: 12px;
            display: flex;
            flex-direction: column;
            gap: 0.875rem;
        }
        .prakonsul-aside__step {
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
        }
        .prakonsul-aside__step-dot {
            position: relative;
            width: 22px; height: 22px;
            border-radius: 9999px;
            background: #ffffff;
            border: 1.5px solid #E2E8F0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: transparent;
            flex-shrink: 0;
            margin-top: 1px;
        }
        .prakonsul-aside__step-dot svg { width: 12px; height: 12px; }
        .prakonsul-aside__step--done .prakonsul-aside__step-dot {
            background: linear-gradient(135deg, #CFAF53 0%, #B8956F 100%);
            border-color: transparent;
            color: #ffffff;
            box-shadow: 0 2px 6px -2px rgba(184, 149, 111, 0.5);
        }
        .prakonsul-aside__step--active .prakonsul-aside__step-dot {
            background: rgba(245, 158, 11, 0.12);
            border-color: rgba(245, 158, 11, 0.5);
        }
        .prakonsul-aside__step-pulse {
            width: 8px; height: 8px;
            border-radius: 9999px;
            background: #D97706;
            box-shadow: 0 0 0 4px rgba(217, 119, 6, 0.2);
            animation: prakonsulPulse 2s ease-in-out infinite;
        }
        .prakonsul-aside__step-title {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 0.8125rem;
            font-weight: 700;
            color: #1A1A1A;
            line-height: 1.3;
            margin: 0;
        }
        .prakonsul-aside__step--done .prakonsul-aside__step-title { color: #475569; }
        .prakonsul-aside__step:not(.prakonsul-aside__step--done):not(.prakonsul-aside__step--active) .prakonsul-aside__step-title {
            color: #94A3B8;
        }
        .prakonsul-aside__step-sub {
            font-size: 0.6875rem;
            color: #94A3B8;
            line-height: 1.4;
            margin: 2px 0 0 0;
        }

        /* Aside link */
        .prakonsul-aside__link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.4375rem;
            margin-top: 0.25rem;
            padding: 0.625rem 1rem;
            border-radius: 9999px;
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            border: 1px solid rgba(184, 149, 111, 0.3);
            color: #8C7A63;
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 0.8125rem;
            font-weight: 700;
            text-decoration: none;
            transition: background 200ms ease, color 200ms ease, transform 200ms ease, box-shadow 200ms ease, border-color 200ms ease;
        }
        .prakonsul-aside__link svg { width: 14px; height: 14px; flex-shrink: 0; }
        .prakonsul-aside__link-arrow { transition: transform 200ms ease; }
        .prakonsul-aside__link:hover {
            background: linear-gradient(135deg, #CFAF53 0%, #B8956F 100%);
            color: #ffffff;
            border-color: transparent;
            transform: translateY(-1px);
            box-shadow: 0 8px 18px -8px rgba(184, 149, 111, 0.55);
        }
        .prakonsul-aside__link:hover .prakonsul-aside__link-arrow { transform: translateX(2px); }

        @keyframes prakonsulPulse {
            0%, 100% { transform: scale(1);   opacity: 1;   box-shadow: 0 0 0 4px rgba(217, 119, 6, 0.2); }
            50%      { transform: scale(1.2); opacity: 0.85; box-shadow: 0 0 0 6px rgba(217, 119, 6, 0.05); }
        }

        @media (prefers-reduced-motion: reduce) {
            .prakonsul-card,
            .prakonsul-card__cta,
            .prakonsul-card__cta svg,
            .prakonsul-card__more,
            .prakonsul-section__cta,
            .prakonsul-aside__link,
            .prakonsul-aside__link-arrow,
            .prakonsul-aside__step-pulse,
            .prakonsul-modal__close,
            .prakonsul-modal__close-btn,
            .prakonsul-modal__photo,
            .prakonsul-modal__photo img,
            .prakonsul-modal__photo-overlay { transition: none; animation: none; }
            .prakonsul-card:hover,
            .prakonsul-card__cta:hover,
            .prakonsul-section__cta:hover,
            .prakonsul-aside__link:hover,
            .prakonsul-modal__photo:hover,
            .prakonsul-modal__close-btn:hover { transform: none; filter: none; }
            .prakonsul-card__cta:hover svg,
            .prakonsul-aside__link:hover .prakonsul-aside__link-arrow,
            .prakonsul-modal__photo:hover img { transform: none; }
        }

        /* ΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇ  MODAL DETAIL PRA-KONSULTASI  ΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇ */
        .prakonsul-modal {
            position: fixed;
            inset: 0;
            z-index: 9999;
            display: flex;
            align-items: flex-end;
            justify-content: center;
            background: rgba(26, 26, 26, 0.55);
            backdrop-filter: blur(4px);
            -webkit-backdrop-filter: blur(4px);
            padding: 0;
        }
        @media (min-width: 640px) {
            .prakonsul-modal {
                align-items: center;
                padding: 1.5rem;
            }
        }

        .prakonsul-modal__panel {
            position: relative;
            display: flex;
            flex-direction: column;
            width: 100%;
            max-height: 92vh;
            background:
                radial-gradient(120% 60% at 100% 0%, rgba(207, 175, 83, 0.08) 0%, transparent 60%),
                #FFFFFF;
            border: 1px solid rgba(232, 224, 216, 0.7);
            border-radius: 24px 24px 0 0;
            box-shadow:
                0 -2px 4px rgba(0, 0, 0, 0.04),
                0 -32px 60px -12px rgba(26, 26, 26, 0.32);
            overflow: hidden;
        }
        @media (min-width: 640px) {
            .prakonsul-modal__panel {
                max-width: 760px;
                max-height: 88vh;
                border-radius: 24px;
                box-shadow:
                    0 1px 4px rgba(0, 0, 0, 0.04),
                    0 32px 60px -12px rgba(26, 26, 26, 0.32),
                    0 18px 32px -12px rgba(184, 149, 111, 0.18);
            }
        }
        .prakonsul-modal__glow {
            position: absolute;
            top: -100px; right: -80px;
            width: 280px; height: 280px;
            border-radius: 9999px;
            background: rgba(207, 175, 83, 0.16);
            filter: blur(80px);
            pointer-events: none;
            opacity: 0.65;
            z-index: 0;
        }

        /* Drag handle (mobile only) */
        .prakonsul-modal__handle {
            display: flex;
            justify-content: center;
            padding: 0.5rem 0 0.25rem;
            flex-shrink: 0;
            position: relative;
            z-index: 1;
        }
        .prakonsul-modal__handle span {
            width: 42px;
            height: 4px;
            border-radius: 9999px;
            background: rgba(184, 149, 111, 0.35);
        }
        @media (min-width: 640px) {
            .prakonsul-modal__handle { display: none; }
        }

        /* Header */
        .prakonsul-modal__header {
            position: relative;
            z-index: 1;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.875rem;
            padding: 1rem 1.25rem 1rem;
            border-bottom: 1px solid rgba(232, 224, 216, 0.6);
            flex-shrink: 0;
        }
        @media (min-width: 640px) {
            .prakonsul-modal__header { padding: 1.25rem 1.5rem; }
        }
        .prakonsul-modal__header-left {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            min-width: 0;
            flex: 1;
        }
        .prakonsul-modal__header-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 40px; height: 40px;
            border-radius: 12px;
            background: linear-gradient(135deg, #CFAF53 0%, #B8956F 100%);
            color: #fff;
            box-shadow:
                inset 0 0 0 1px rgba(255, 255, 255, 0.25),
                0 4px 10px -4px rgba(184, 149, 111, 0.5);
            flex-shrink: 0;
        }
        .prakonsul-modal__header-icon svg { width: 20px; height: 20px; }
        .prakonsul-modal__title {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 1rem;
            font-weight: 800;
            color: #1A1A1A;
            letter-spacing: -0.01em;
            line-height: 1.25;
        }
        @media (min-width: 640px) {
            .prakonsul-modal__title { font-size: 1.125rem; }
        }
        .prakonsul-modal__subtitle {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 0.75rem;
            color: #94A3B8;
            margin-top: 2px;
            font-weight: 500;
        }
        .prakonsul-modal__close {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 36px; height: 36px;
            border-radius: 9999px;
            background: #FAF8F5;
            color: #64748B;
            border: 1px solid rgba(232, 224, 216, 0.7);
            cursor: pointer;
            transition: background 200ms ease, color 200ms ease, transform 200ms ease, border-color 200ms ease;
            flex-shrink: 0;
        }
        .prakonsul-modal__close svg { width: 16px; height: 16px; }
        .prakonsul-modal__close:hover {
            background: #ffffff;
            border-color: rgba(184, 149, 111, 0.4);
            color: #1A1A1A;
            transform: rotate(90deg);
        }

        /* Body */
        .prakonsul-modal__body {
            position: relative;
            z-index: 1;
            flex: 1;
            overflow-y: auto;
            padding: 1.25rem 1.25rem 1.5rem;
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }
        @media (min-width: 640px) {
            .prakonsul-modal__body { padding: 1.5rem 1.75rem 1.75rem; gap: 1.75rem; }
        }

        /* Meta badges */
        .prakonsul-modal__meta {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 0.5rem;
        }
        .prakonsul-modal__badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.4375rem 0.75rem;
            border-radius: 9999px;
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 0.75rem;
            font-weight: 700;
            line-height: 1;
            border: 1px solid transparent;
        }
        .prakonsul-modal__badge-dot {
            width: 6px; height: 6px;
            border-radius: 9999px;
            display: inline-block;
            flex-shrink: 0;
        }
        .prakonsul-modal__badge--pending {
            background: #FFF8EB;
            color: #92400E;
            border-color: rgba(245, 158, 11, 0.28);
        }
        .prakonsul-modal__badge--pending .prakonsul-modal__badge-dot { background: #D97706; }
        .prakonsul-modal__badge--review {
            background: #EFF6FF;
            color: #1E40AF;
            border-color: rgba(37, 99, 235, 0.28);
        }
        .prakonsul-modal__badge--review .prakonsul-modal__badge-dot { background: #1D4ED8; }
        .prakonsul-modal__badge--done {
            background: #ECFDF5;
            color: #065F46;
            border-color: rgba(16, 185, 129, 0.28);
        }
        .prakonsul-modal__badge--done .prakonsul-modal__badge-dot { background: #047857; }
        .prakonsul-modal__badge--closed {
            background: #FEF2F2;
            color: #9F1239;
            border-color: rgba(244, 63, 94, 0.28);
        }
        .prakonsul-modal__badge--closed .prakonsul-modal__badge-dot { background: #E11D48; }

        .prakonsul-modal__chip {
            display: inline-flex;
            align-items: center;
            gap: 0.375rem;
            padding: 0.375rem 0.75rem;
            border-radius: 9999px;
            background: rgba(184, 149, 111, 0.08);
            color: #8C7A63;
            border: 1px solid rgba(184, 149, 111, 0.2);
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 0.6875rem;
            font-weight: 700;
            line-height: 1;
        }
        .prakonsul-modal__chip svg { width: 12px; height: 12px; }

        /* Section */
        .prakonsul-modal__section {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }
        .prakonsul-modal__section-head {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .prakonsul-modal__section-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 28px; height: 28px;
            border-radius: 9px;
            background: rgba(207, 175, 83, 0.12);
            color: #B8956F;
            flex-shrink: 0;
        }
        .prakonsul-modal__section-icon svg { width: 15px; height: 15px; }
        .prakonsul-modal__section-title {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 0.75rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.16em;
            color: #1A1A1A;
            margin: 0;
        }

        /* Quote (Keluhan) */
        .prakonsul-modal__quote {
            position: relative;
            padding: 1rem 1.125rem;
            background: linear-gradient(180deg, #FAF8F5 0%, #F5F0EB 100%);
            border: 1px solid rgba(232, 224, 216, 0.7);
            border-left: 3px solid #CFAF53;
            border-radius: 14px;
        }
        .prakonsul-modal__quote-text {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 0.9375rem;
            line-height: 1.7;
            color: #1A1A1A;
            font-weight: 500;
            margin: 0;
            white-space: pre-wrap;
            word-break: break-word;
            overflow-wrap: anywhere;
        }

        /* Note (Riwayat Penyakit) */
        .prakonsul-modal__note {
            padding: 0.875rem 1rem;
            background: #FAF8F5;
            border: 1px solid rgba(232, 224, 216, 0.7);
            border-radius: 12px;
        }
        .prakonsul-modal__note p {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 0.875rem;
            line-height: 1.6;
            color: #475569;
            margin: 0;
            white-space: pre-wrap;
            word-break: break-word;
        }

        /* Info Grid */
        .prakonsul-modal__info-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 0.75rem;
        }
        .prakonsul-modal__info {
            background: #FDFCFA;
            border: 1px solid rgba(232, 224, 216, 0.6);
            border-radius: 12px;
            padding: 0.875rem 1rem;
        }
        .prakonsul-modal__info--wide { grid-column: 1 / -1; }
        .prakonsul-modal__info-label {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 0.625rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.16em;
            color: #94A3B8;
            margin: 0 0 0.375rem 0;
        }
        .prakonsul-modal__info-value {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 0.9375rem;
            font-weight: 700;
            color: #1A1A1A;
            line-height: 1.45;
            margin: 0;
            word-break: break-word;
        }
        .prakonsul-modal__info-value--empty {
            color: #94A3B8;
            font-weight: 500;
            font-style: italic;
        }
        .prakonsul-modal__info-hint {
            margin-top: 0.5rem;
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 0.75rem;
            color: #64748B;
        }
        .prakonsul-modal__info-hint strong { color: #1A1A1A; font-weight: 700; }

        /* Treatment chips */
        .prakonsul-modal__chips-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 0.5rem;
        }
        @media (min-width: 640px) {
            .prakonsul-modal__chips-grid { grid-template-columns: repeat(4, minmax(0, 1fr)); }
        }
        .prakonsul-modal__pill {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.4375rem;
            padding: 0.5625rem 0.875rem;
            border-radius: 12px;
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 0.8125rem;
            font-weight: 700;
            border: 1px solid transparent;
            line-height: 1;
        }
        .prakonsul-modal__pill svg { width: 14px; height: 14px; flex-shrink: 0; }
        .prakonsul-modal__pill--active {
            background: linear-gradient(135deg, #ECFDF5 0%, #D1FAE5 100%);
            color: #047857;
            border-color: rgba(16, 185, 129, 0.32);
        }
        .prakonsul-modal__pill--inactive {
            background: #F8FAFC;
            color: #94A3B8;
            border-color: rgba(226, 232, 240, 0.8);
        }

        /* Photos */
        .prakonsul-modal__photos {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 0.75rem;
        }
        .prakonsul-modal__photo {
            position: relative;
            display: block;
            aspect-ratio: 3 / 4;
            border-radius: 14px;
            overflow: hidden;
            background: #FAF8F5;
            border: 1px solid rgba(232, 224, 216, 0.7);
            cursor: pointer;
            transition: transform 220ms ease, box-shadow 220ms ease, border-color 220ms ease;
        }
        .prakonsul-modal__photo img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
            transition: transform 320ms ease;
        }
        .prakonsul-modal__photo:hover {
            transform: translateY(-2px);
            border-color: rgba(184, 149, 111, 0.5);
            box-shadow: 0 12px 24px -10px rgba(184, 149, 111, 0.4);
        }
        .prakonsul-modal__photo:hover img { transform: scale(1.05); }
        .prakonsul-modal__photo-overlay {
            position: absolute;
            inset: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(180deg, rgba(26, 26, 26, 0) 50%, rgba(26, 26, 26, 0.55) 100%);
            opacity: 0;
            transition: opacity 220ms ease;
        }
        .prakonsul-modal__photo:hover .prakonsul-modal__photo-overlay { opacity: 1; }
        .prakonsul-modal__photo-overlay svg {
            width: 24px; height: 24px;
            color: #fff;
            margin-bottom: 30%;
            filter: drop-shadow(0 2px 4px rgba(0,0,0,0.3));
        }
        .prakonsul-modal__photo-label {
            position: absolute;
            left: 0; right: 0; bottom: 0;
            text-align: center;
            padding: 0.4375rem 0.5rem;
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 0.6875rem;
            font-weight: 700;
            color: #ffffff;
            background: linear-gradient(180deg, rgba(26, 26, 26, 0) 0%, rgba(26, 26, 26, 0.7) 100%);
            letter-spacing: 0.04em;
        }
        .prakonsul-modal__photo--empty {
            cursor: default;
            border-style: dashed;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            color: #CBD5E1;
        }
        .prakonsul-modal__photo--empty:hover {
            transform: none;
            border-color: rgba(232, 224, 216, 0.7);
            box-shadow: none;
        }
        .prakonsul-modal__photo--empty svg { width: 24px; height: 24px; }
        .prakonsul-modal__photo--empty .prakonsul-modal__photo-label {
            position: static;
            background: transparent;
            color: #94A3B8;
            padding: 0;
        }

        /* Footer */
        .prakonsul-modal__footer {
            position: relative;
            z-index: 1;
            padding: 1rem 1.25rem;
            border-top: 1px solid rgba(232, 224, 216, 0.6);
            flex-shrink: 0;
            background: #FFFFFF;
            display: flex;
            justify-content: flex-end;
        }
        @media (min-width: 640px) {
            .prakonsul-modal__footer { padding: 1.125rem 1.75rem; }
        }
        .prakonsul-modal__close-btn {
            min-height: 44px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.625rem 1.5rem;
            border-radius: 9999px;
            background: #FAF8F5;
            color: #475569;
            border: 1px solid rgba(232, 224, 216, 0.85);
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 0.875rem;
            font-weight: 700;
            cursor: pointer;
            transition: background 200ms ease, color 200ms ease, border-color 200ms ease, transform 200ms ease, box-shadow 200ms ease;
        }
        .prakonsul-modal__close-btn:hover {
            background: #ffffff;
            color: #1A1A1A;
            border-color: rgba(184, 149, 111, 0.4);
            transform: translateY(-1px);
            box-shadow: 0 8px 18px -10px rgba(0, 0, 0, 0.15);
        }
        @media (max-width: 639px) {
            .prakonsul-modal__footer { justify-content: stretch; }
            .prakonsul-modal__close-btn { width: 100%; }
        }
    </style>
@endonce

