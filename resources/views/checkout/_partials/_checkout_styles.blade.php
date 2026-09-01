@once
    <style>
        /* ════════ PAGE CONTAINER & HEADER ════════ */
        .checkout-page {
            background: #FAF8F5;
            min-height: 100vh;
        }
        .checkout-page__container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 1.5rem 1rem 4rem;
        }
        @media (min-width: 768px) {
            .checkout-page__container { padding: 2rem 1.5rem 5rem; }
        }
        @media (min-width: 1024px) {
            .checkout-page__container { padding: 2.5rem 2rem 5rem; }
        }

        .checkout-header {
            margin-bottom: 1.75rem;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            gap: 1.25rem;
        }
        @media (min-width: 768px) {
            .checkout-header { margin-bottom: 2.25rem; gap: 1.5rem; }
        }
        .checkout-header__heading { min-width: 0; }
        .checkout-header__eyebrow {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            margin: 0 0 0.5rem 0;
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 0.6875rem;
            font-weight: 800;
            letter-spacing: 0.28em;
            text-transform: uppercase;
            color: #B8956F;
        }
        .checkout-header__dot {
            width: 5px; height: 5px;
            border-radius: 9999px;
            background: #CFAF53;
            box-shadow: 0 0 0 3px rgba(207, 175, 83, 0.18);
        }
        .checkout-header__title {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 1.75rem;
            font-weight: 900;
            letter-spacing: -0.02em;
            color: #1A1A1A;
            line-height: 1.15;
            margin: 0;
        }
        @media (min-width: 768px) {
            .checkout-header__title { font-size: 2rem; }
        }
        .checkout-header__subtitle {
            margin-top: 0.5rem;
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 0.9375rem;
            line-height: 1.55;
            color: #64748B;
            max-width: 32rem;
        }

        /* ════════ STEPPER ════════ */
        .checkout-stepper {
            list-style: none;
            margin: 0;
            padding: 0.625rem 0.875rem;
            display: flex;
            align-items: center;
            gap: 0.625rem;
            background: #ffffff;
            border: 1px solid rgba(232, 224, 216, 0.7);
            border-radius: 9999px;
            box-shadow: 0 1px 2px rgba(26, 26, 26, 0.03);
            overflow-x: auto;
            scrollbar-width: none;
        }
        .checkout-stepper::-webkit-scrollbar { display: none; }
        .checkout-stepper__step {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            white-space: nowrap;
        }
        .checkout-stepper__dot {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 22px;
            height: 22px;
            border-radius: 9999px;
            background: #ffffff;
            color: #94A3B8;
            border: 1.5px solid #E2E8F0;
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 0.6875rem;
            font-weight: 800;
            flex-shrink: 0;
        }
        .checkout-stepper__dot svg { width: 12px; height: 12px; }
        .checkout-stepper__step--done .checkout-stepper__dot {
            background: linear-gradient(135deg, #CFAF53 0%, #B8956F 100%);
            border-color: transparent;
            color: #ffffff;
            box-shadow: 0 2px 6px -2px rgba(184, 149, 111, 0.5);
        }
        .checkout-stepper__step--active .checkout-stepper__dot {
            background: rgba(207, 175, 83, 0.14);
            border-color: rgba(184, 149, 111, 0.55);
            color: #B8956F;
        }
        .checkout-stepper__label {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 0.75rem;
            font-weight: 700;
            color: #94A3B8;
        }
        .checkout-stepper__step--done .checkout-stepper__label,
        .checkout-stepper__step--active .checkout-stepper__label { color: #1A1A1A; }
        .checkout-stepper__connector {
            flex: 0 0 24px;
            height: 1.5px;
            background: #E2E8F0;
            border-radius: 9999px;
        }
        @media (min-width: 768px) {
            .checkout-stepper__connector { flex-basis: 32px; }
        }

        /* ════════ GRID ════════ */
        .checkout-grid {
            display: grid;
            grid-template-columns: minmax(0, 1fr);
            gap: 1.25rem;
        }
        @media (min-width: 1024px) {
            .checkout-grid {
                grid-template-columns: minmax(0, 1fr) minmax(0, 380px);
                gap: 1.75rem;
                align-items: start;
            }
        }
        .checkout-main {
            display: flex;
            flex-direction: column;
            gap: 1.25rem;
        }
        @media (min-width: 768px) {
            .checkout-main { gap: 1.5rem; }
        }

        /* ════════ INFO CHIPS ════════ */
        .checkout-chips {
            display: grid;
            grid-template-columns: 1fr;
            gap: 0.75rem;
        }
        @media (min-width: 640px) {
            .checkout-chips { grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 1rem; }
        }
        .checkout-chip {
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
            padding: 0.875rem 1rem;
            background: #ffffff;
            border: 1px solid rgba(232, 224, 216, 0.65);
            border-radius: 14px;
            box-shadow: 0 1px 2px rgba(26, 26, 26, 0.02);
        }
        .checkout-chip__icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 36px; height: 36px;
            border-radius: 11px;
            flex-shrink: 0;
        }
        .checkout-chip__icon svg { width: 18px; height: 18px; }
        .checkout-chip__icon--gold {
            background: linear-gradient(135deg, rgba(207, 175, 83, 0.15) 0%, rgba(184, 149, 111, 0.18) 100%);
            color: #B8956F;
            border: 1px solid rgba(184, 149, 111, 0.22);
        }
        .checkout-chip__icon--success {
            background: linear-gradient(135deg, #ECFDF5 0%, #D1FAE5 100%);
            color: #047857;
            border: 1px solid rgba(16, 185, 129, 0.28);
        }
        .checkout-chip__body { min-width: 0; flex: 1; }
        .checkout-chip__label {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 0.625rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.18em;
            color: #94A3B8;
            margin: 0 0 2px 0;
        }
        .checkout-chip__value {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 0.875rem;
            font-weight: 800;
            color: #1A1A1A;
            line-height: 1.3;
            margin: 0;
        }
        .checkout-chip__hint {
            font-size: 0.75rem;
            color: #94A3B8;
            margin: 2px 0 0 0;
        }
        .checkout-chip__link {
            display: inline-block;
            margin-top: 4px;
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 0.75rem;
            font-weight: 700;
            color: #B8956F;
            text-decoration: none;
        }
        .checkout-chip__link:hover { color: #8C7A63; text-decoration: underline; }

        /* ════════ CARD ════════ */
        .checkout-card {
            background: #ffffff;
            border: 1px solid rgba(232, 224, 216, 0.65);
            border-radius: 18px;
            padding: 1.25rem;
            box-shadow: 0 1px 2px rgba(26, 26, 26, 0.03);
        }
        @media (min-width: 768px) {
            .checkout-card { padding: 1.5rem; }
        }
        .checkout-card__head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.75rem;
            margin-bottom: 1rem;
            flex-wrap: wrap;
        }
        .checkout-card__eyebrow {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 0.6875rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.18em;
            color: #94A3B8;
            margin: 0 0 4px 0;
        }
        .checkout-card__title {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 1.0625rem;
            font-weight: 800;
            letter-spacing: -0.01em;
            color: #1A1A1A;
            margin: 0;
        }
        .checkout-card__optional {
            color: #94A3B8;
            font-weight: 500;
            font-size: 0.875rem;
        }
        .checkout-card__chip {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 0.75rem;
            font-weight: 700;
            color: #B8956F;
            background: rgba(207, 175, 83, 0.1);
            border: 1px solid rgba(184, 149, 111, 0.22);
            padding: 0.3125rem 0.75rem;
            border-radius: 9999px;
        }
        .checkout-card__edit {
            display: inline-flex;
            align-items: center;
            gap: 0.375rem;
            padding: 0.4375rem 0.875rem;
            border-radius: 9999px;
            background: rgba(207, 175, 83, 0.08);
            color: #B8956F;
            border: 1px solid rgba(184, 149, 111, 0.25);
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 0.75rem;
            font-weight: 700;
            text-decoration: none;
            transition: background 200ms ease, color 200ms ease, transform 200ms ease;
        }
        .checkout-card__edit svg { width: 12px; height: 12px; }
        .checkout-card__edit:hover {
            background: linear-gradient(135deg, #CFAF53 0%, #B8956F 100%);
            color: #fff;
            border-color: transparent;
            transform: translateY(-1px);
        }
        .checkout-card__helper {
            margin: 0.625rem 0 0 0;
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 0.75rem;
            color: #94A3B8;
            line-height: 1.5;
        }

        /* ════════ ADDRESS ════════ */
        .checkout-address {
            padding: 1rem 1.125rem;
            background: linear-gradient(180deg, #FAF8F5 0%, #FFFFFF 100%);
            border: 1px solid rgba(232, 224, 216, 0.6);
            border-radius: 14px;
        }
        .checkout-address__head {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 0.5rem;
            margin-bottom: 4px;
        }
        .checkout-address__name {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 1rem;
            font-weight: 800;
            color: #1A1A1A;
            letter-spacing: -0.005em;
        }
        .checkout-address__badge {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 0.625rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.12em;
            padding: 0.25rem 0.5rem;
            border-radius: 9999px;
            background: rgba(207, 175, 83, 0.14);
            color: #8C7A63;
            border: 1px solid rgba(184, 149, 111, 0.22);
        }
        .checkout-address__phone {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 0.8125rem;
            color: #94A3B8;
            margin: 0 0 0.625rem 0;
        }
        .checkout-address__line {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 0.875rem;
            line-height: 1.6;
            color: #1A1A1A;
            margin: 0;
        }
        .checkout-address__line--muted { color: #64748B; margin-top: 2px; }

        /* ════════ ROUTE INFO ════════ */
        .checkout-route {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.75rem;
            margin-bottom: 0.875rem;
            padding: 0.625rem 0.875rem;
            background: #FAF8F5;
            border: 1px solid rgba(232, 224, 216, 0.6);
            border-radius: 12px;
            flex-wrap: wrap;
        }
        .checkout-route__path {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 0.8125rem;
            font-weight: 700;
            color: #1A1A1A;
        }
        .checkout-route__node { white-space: nowrap; }
        .checkout-route__arrow { width: 14px; height: 14px; color: #B8956F; flex-shrink: 0; }
        .checkout-route__weight {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 0.75rem;
            color: #94A3B8;
        }
        .checkout-route__weight strong { color: #1A1A1A; font-weight: 700; }

        /* ════════ COURIER GROUP ════════ */
        .checkout-couriers {
            display: flex;
            flex-direction: column;
            gap: 0.625rem;
        }
        .checkout-courier-group {
            background: #ffffff;
            border: 1px solid rgba(232, 224, 216, 0.7);
            border-radius: 14px;
            overflow: hidden;
            transition: border-color 200ms ease, box-shadow 200ms ease;
        }
        .checkout-courier-group--open {
            border-color: rgba(184, 149, 111, 0.4);
            box-shadow: 0 4px 14px -8px rgba(184, 149, 111, 0.25);
        }
        .checkout-courier-group__head {
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.875rem;
            padding: 0.875rem 1rem;
            background: transparent;
            border: 0;
            cursor: pointer;
            text-align: left;
            transition: background 180ms ease;
        }
        .checkout-courier-group__head:hover { background: #FAF8F5; }
        .checkout-courier-group__brand {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            min-width: 0;
        }
        .checkout-courier-group__logo {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 48px;
            height: 36px;
            padding: 0 0.625rem;
            border-radius: 8px;
            background: linear-gradient(135deg, #FAF8F5 0%, #F5F0EB 100%);
            border: 1px solid rgba(184, 149, 111, 0.18);
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 0.6875rem;
            font-weight: 900;
            letter-spacing: 0.05em;
            color: #8C7A63;
            flex-shrink: 0;
        }
        .checkout-courier-group__name {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 0.9375rem;
            font-weight: 800;
            color: #1A1A1A;
            line-height: 1.2;
            margin: 0;
            letter-spacing: -0.005em;
        }
        .checkout-courier-group__meta {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 0.75rem;
            color: #94A3B8;
            margin: 2px 0 0 0;
        }
        .checkout-courier-group__caret {
            width: 16px; height: 16px;
            color: #94A3B8;
            transition: transform 220ms ease;
            flex-shrink: 0;
        }
        .checkout-courier-group--open .checkout-courier-group__caret {
            transform: rotate(180deg);
            color: #B8956F;
        }
        .checkout-courier-group__body {
            padding: 0 0.625rem 0.625rem;
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        /* ════════ COURIER ITEM ════════ */
        .checkout-courier {
            display: flex;
            align-items: center;
            gap: 0.875rem;
            padding: 0.75rem 0.875rem;
            background: #FDFCFA;
            border: 1.5px solid rgba(232, 224, 216, 0.7);
            border-radius: 12px;
            cursor: pointer;
            transition: border-color 200ms ease, background 200ms ease, box-shadow 200ms ease;
        }
        .checkout-courier:hover {
            border-color: rgba(184, 149, 111, 0.5);
            background: #ffffff;
        }
        .checkout-courier--selected {
            border-color: #CFAF53;
            background: linear-gradient(135deg, rgba(207, 175, 83, 0.08) 0%, rgba(184, 149, 111, 0.05) 100%);
            box-shadow: 0 4px 14px -6px rgba(184, 149, 111, 0.32);
        }
        .checkout-courier__radio {
            width: 18px;
            height: 18px;
            border-radius: 9999px;
            border: 1.5px solid #CBD5E1;
            background: #ffffff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            transition: border-color 200ms ease, background 200ms ease;
        }
        .checkout-courier__radio-inner {
            width: 8px;
            height: 8px;
            border-radius: 9999px;
            background: transparent;
            transform: scale(0);
            transition: transform 200ms ease;
        }
        .checkout-courier--selected .checkout-courier__radio {
            border-color: #B8956F;
            background: linear-gradient(135deg, #CFAF53 0%, #B8956F 100%);
        }
        .checkout-courier--selected .checkout-courier__radio-inner {
            background: #ffffff;
            transform: scale(1);
        }
        .checkout-courier__body { flex: 1; min-width: 0; }
        .checkout-courier__service {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 0.875rem;
            font-weight: 700;
            color: #1A1A1A;
            margin: 0;
        }
        .checkout-courier__etd {
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 0.75rem;
            color: #94A3B8;
            margin: 2px 0 0 0;
        }
        .checkout-courier__etd svg { width: 12px; height: 12px; }
        .checkout-courier__cost {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 0.9375rem;
            font-weight: 800;
            color: #1A1A1A;
            margin: 0;
            white-space: nowrap;
        }
        .checkout-courier--selected .checkout-courier__cost { color: #B8956F; }

        /* ════════ LOADING / ERROR / EMPTY ════════ */
        .checkout-loading {
            padding: 0.625rem 0;
            display: flex;
            flex-direction: column;
            gap: 0.625rem;
            position: relative;
        }
        .checkout-loading__skeleton {
            height: 60px;
            border-radius: 12px;
            background: linear-gradient(90deg, #F5F0EB 0%, #FAF8F5 50%, #F5F0EB 100%);
            background-size: 200% 100%;
            animation: checkoutSkeleton 1.4s ease-in-out infinite;
        }
        .checkout-loading__text {
            position: absolute;
            inset: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 0.8125rem;
            font-weight: 700;
            color: #B8956F;
        }
        @keyframes checkoutSkeleton {
            0%   { background-position: -200% 0; }
            100% { background-position: 200% 0; }
        }

        .checkout-error {
            padding: 1.5rem 1rem;
            text-align: center;
            background: #FEF2F2;
            border: 1px solid rgba(244, 63, 94, 0.22);
            border-radius: 14px;
        }
        .checkout-error__icon {
            display: inline-flex;
            width: 36px; height: 36px;
            margin: 0 auto 0.625rem;
            align-items: center;
            justify-content: center;
            border-radius: 9999px;
            background: rgba(244, 63, 94, 0.12);
            color: #E11D48;
        }
        .checkout-error__icon svg { width: 18px; height: 18px; }
        .checkout-error__title {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 0.875rem;
            font-weight: 700;
            color: #9F1239;
            margin: 0 0 4px 0;
        }
        .checkout-error__msg {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 0.75rem;
            color: #BE123C;
            margin: 0 0 0.625rem 0;
        }
        .checkout-error__btn {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 0.75rem;
            font-weight: 700;
            color: #BE123C;
            background: rgba(244, 63, 94, 0.1);
            border: 1px solid rgba(244, 63, 94, 0.22);
            padding: 0.4375rem 0.875rem;
            border-radius: 9999px;
            cursor: pointer;
            transition: background 180ms ease;
        }
        .checkout-error__btn:hover { background: rgba(244, 63, 94, 0.18); }

        .checkout-empty {
            padding: 2rem 1rem;
            text-align: center;
            background: #FAF8F5;
            border: 1.5px dashed rgba(232, 224, 216, 0.85);
            border-radius: 14px;
        }
        .checkout-empty svg { width: 38px; height: 38px; color: #CBD5E1; margin: 0 auto 0.625rem; }
        .checkout-empty p {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 0.8125rem;
            color: #94A3B8;
            margin: 0;
        }

        /* ════════ TEXTAREA ════════ */
        .checkout-textarea {
            width: 100%;
            min-height: 100px;
            padding: 0.75rem 0.875rem;
            border-radius: 12px;
            border: 1px solid rgba(232, 224, 216, 0.85);
            background: #ffffff;
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 0.9375rem;
            line-height: 1.6;
            color: #1A1A1A;
            resize: vertical;
            transition: border-color 180ms ease, box-shadow 180ms ease;
        }
        .checkout-textarea::placeholder { color: #94A3B8; }
        .checkout-textarea:focus {
            outline: none;
            border-color: #CFAF53;
            box-shadow: 0 0 0 3px rgba(207, 175, 83, 0.18);
        }

        /* ════════ SUMMARY (RIGHT COLUMN) ════════ */
        .checkout-summary {
            position: relative;
        }
        @media (min-width: 1024px) {
            .checkout-summary {
                position: sticky;
                top: 6.5rem;
            }
        }
        .checkout-summary__inner {
            background:
                radial-gradient(120% 60% at 100% 0%, rgba(207, 175, 83, 0.06) 0%, transparent 60%),
                #ffffff;
            border: 1px solid rgba(232, 224, 216, 0.7);
            border-radius: 20px;
            padding: 1.25rem;
            box-shadow:
                0 1px 2px rgba(26, 26, 26, 0.03),
                0 12px 28px -16px rgba(184, 149, 111, 0.18);
        }
        @media (min-width: 768px) {
            .checkout-summary__inner { padding: 1.5rem; }
        }
        .checkout-summary__head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1rem;
            padding-bottom: 0.875rem;
            border-bottom: 1px solid rgba(232, 224, 216, 0.6);
        }
        .checkout-summary__title {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 1rem;
            font-weight: 800;
            color: #1A1A1A;
            margin: 0;
            letter-spacing: -0.005em;
        }
        .checkout-summary__count {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 0.75rem;
            font-weight: 700;
            color: #B8956F;
            background: rgba(207, 175, 83, 0.1);
            border: 1px solid rgba(184, 149, 111, 0.22);
            padding: 0.25rem 0.625rem;
            border-radius: 9999px;
        }

        .checkout-summary__items {
            list-style: none;
            margin: 0 0 0.5rem 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            gap: 0.625rem;
            max-height: 280px;
            overflow-y: auto;
            padding-right: 0.25rem;
        }
        .checkout-summary__items::-webkit-scrollbar { width: 4px; }
        .checkout-summary__items::-webkit-scrollbar-thumb { background: rgba(184, 149, 111, 0.28); border-radius: 4px; }
        .checkout-summary__item {
            display: flex;
            align-items: flex-start;
            gap: 0.625rem;
            padding: 0.625rem 0.75rem;
            background: #FAF8F5;
            border-radius: 10px;
            border: 1px solid rgba(232, 224, 216, 0.5);
        }
        .checkout-summary__item-info { flex: 1; min-width: 0; }
        .checkout-summary__item-name {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 0.8125rem;
            font-weight: 700;
            color: #1A1A1A;
            margin: 0;
            line-height: 1.35;
            word-break: break-word;
        }
        .checkout-summary__item-qty {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 0.6875rem;
            color: #94A3B8;
            margin: 2px 0 0 0;
        }
        .checkout-summary__item-price {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 0.8125rem;
            font-weight: 800;
            color: #1A1A1A;
            margin: 0;
            white-space: nowrap;
            text-align: right;
        }

        .checkout-summary__divider {
            height: 1px;
            background: rgba(232, 224, 216, 0.6);
            margin: 1rem 0;
        }

        /* ════════ PROMO ════════ */
        .checkout-promo__label {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 0.6875rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.16em;
            color: #94A3B8;
            margin: 0 0 0.5rem 0;
        }
        .checkout-promo__row {
            display: flex;
            gap: 0.5rem;
        }
        .checkout-promo__input {
            flex: 1;
            min-height: 40px;
            padding: 0.5rem 0.75rem;
            border-radius: 10px;
            border: 1px dashed rgba(184, 149, 111, 0.35);
            background: #FAF8F5;
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 0.8125rem;
            font-weight: 700;
            color: #1A1A1A;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            transition: border-color 180ms ease, background 180ms ease;
        }
        .checkout-promo__input::placeholder {
            color: #94A3B8;
            font-weight: 600;
            letter-spacing: 0.04em;
        }
        .checkout-promo__input:focus {
            outline: none;
            border-color: #CFAF53;
            border-style: solid;
            background: #ffffff;
        }
        .checkout-promo__input:disabled {
            background: #ECFDF5;
            border-color: rgba(16, 185, 129, 0.32);
            color: #047857;
        }
        .checkout-promo__btn {
            min-height: 40px;
            padding: 0.5rem 0.875rem;
            border-radius: 10px;
            background: linear-gradient(135deg, #1A1A1A 0%, #2C2622 100%);
            color: #ffffff;
            border: 0;
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 0.8125rem;
            font-weight: 700;
            cursor: pointer;
            transition: filter 180ms ease, transform 180ms ease;
        }
        .checkout-promo__btn:hover:not(:disabled) {
            filter: brightness(1.15);
            transform: translateY(-1px);
        }
        .checkout-promo__btn:disabled {
            opacity: 0.45;
            cursor: not-allowed;
        }
        .checkout-promo__cancel {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            border-radius: 10px;
            background: #FEF2F2;
            color: #BE123C;
            border: 1px solid rgba(244, 63, 94, 0.22);
            cursor: pointer;
            transition: background 180ms ease;
        }
        .checkout-promo__cancel svg { width: 14px; height: 14px; }
        .checkout-promo__cancel:hover { background: #FEE2E2; }

        .checkout-promo__msg {
            margin: 0.5rem 0 0 0;
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 0.75rem;
            font-weight: 600;
            line-height: 1.4;
        }
        .checkout-promo__msg--ok  { color: #047857; }
        .checkout-promo__msg--err { color: #BE123C; }

        /* ════════ TOTALS ════════ */
        .checkout-summary__totals {
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }
        .checkout-summary__totals > div {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .checkout-summary__totals dt {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 0.8125rem;
            color: #64748B;
        }
        .checkout-summary__totals dd {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 0.875rem;
            font-weight: 700;
            color: #1A1A1A;
            margin: 0;
        }
        .checkout-summary__totals-discount dt { color: #047857; font-weight: 600; }
        .checkout-summary__totals-discount dd { color: #047857; }

        .checkout-summary__grand {
            display: flex;
            align-items: baseline;
            justify-content: space-between;
            margin-bottom: 1rem;
        }
        .checkout-summary__grand-label {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 0.875rem;
            font-weight: 700;
            color: #1A1A1A;
        }
        .checkout-summary__grand-value {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 1.375rem;
            font-weight: 900;
            letter-spacing: -0.01em;
            background: linear-gradient(135deg, #CFAF53 0%, #B8956F 100%);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        @media (min-width: 768px) {
            .checkout-summary__grand-value { font-size: 1.5rem; }
        }

        /* ════════ CTA ════════ */
        .checkout-cta {
            width: 100%;
            min-height: 50px;
            padding: 0.875rem 1.25rem;
            border-radius: 9999px;
            background: linear-gradient(135deg, #CFAF53 0%, #B8956F 100%);
            color: #ffffff;
            border: 0;
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 0.9375rem;
            font-weight: 800;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            box-shadow:
                0 1px 2px rgba(184, 149, 111, 0.25),
                0 12px 24px -8px rgba(184, 149, 111, 0.55);
            transition: transform 200ms ease, box-shadow 200ms ease, filter 200ms ease;
        }
        .checkout-cta > span {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }
        .checkout-cta svg { width: 16px; height: 16px; }
        .checkout-cta:hover:not(:disabled) {
            transform: translateY(-1px);
            filter: brightness(1.05);
            box-shadow:
                0 1px 2px rgba(184, 149, 111, 0.3),
                0 16px 30px -8px rgba(184, 149, 111, 0.6);
        }
        .checkout-cta--disabled,
        .checkout-cta:disabled {
            background: #E2E8F0;
            color: #94A3B8;
            box-shadow: none;
            cursor: not-allowed;
            transform: none;
        }

        .checkout-summary__notice {
            margin: 0.625rem 0 0 0;
            text-align: center;
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 0.75rem;
            font-weight: 600;
            color: #B45309;
        }
        .checkout-summary__terms {
            margin: 0.875rem 0 0 0;
            text-align: center;
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 0.6875rem;
            color: #94A3B8;
            line-height: 1.5;
        }

        @media (prefers-reduced-motion: reduce) {
            .checkout-loading__skeleton { animation: none; }
            .checkout-courier-group__caret,
            .checkout-courier__radio,
            .checkout-courier__radio-inner,
            .checkout-cta,
            .checkout-card__edit,
            .checkout-promo__btn { transition: none; }
            .checkout-cta:hover:not(:disabled),
            .checkout-card__edit:hover,
            .checkout-promo__btn:hover { transform: none; filter: none; }
        }

        [x-cloak] { display: none !important; }
    </style>
@endonce