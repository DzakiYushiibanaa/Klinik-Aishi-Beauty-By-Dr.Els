@once
    <style>
        /* â”€â”€â”€â”€â”€â”€â”€â”€â”€  ALAMAT PENGIRIMAN  â”€â”€â”€â”€â”€â”€â”€â”€â”€ */
        .alamat-notice {
            display: flex;
            align-items: flex-start;
            gap: 0.625rem;
            padding: 0.75rem 0.875rem;
            background: linear-gradient(135deg, #FFF8EB 0%, #FFFCF5 100%);
            border: 1px solid rgba(245, 158, 11, 0.22);
            border-radius: 12px;
        }
        .alamat-notice__icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 28px; height: 28px;
            border-radius: 9px;
            background: rgba(207, 175, 83, 0.18);
            color: #B8956F;
            flex-shrink: 0;
            margin-top: 1px;
        }
        .alamat-notice__icon svg { width: 16px; height: 16px; }
        .alamat-notice__text {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 0.8125rem;
            line-height: 1.55;
            color: #6F5A3F;
            margin: 0;
        }

        /* Inputs & Textareas (premium minimal) */
        .alamat-input {
            width: 100%;
            min-height: 44px;
            padding: 0.625rem 0.875rem;
            border: 1px solid rgba(232, 224, 216, 0.85);
            border-radius: 12px;
            background: #ffffff;
            color: #1A1A1A;
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 0.9375rem;
            line-height: 1.5;
            box-shadow: 0 1px 2px rgba(26, 26, 26, 0.02);
            transition: border-color 180ms ease, box-shadow 180ms ease, background 180ms ease;
        }
        .alamat-input::placeholder { color: #94A3B8; }
        .alamat-input:hover { border-color: rgba(184, 149, 111, 0.5); }
        .alamat-input:focus {
            outline: none;
            border-color: #CFAF53;
            box-shadow: 0 0 0 3px rgba(207, 175, 83, 0.18);
        }
        .alamat-input--textarea {
            min-height: 110px;
            resize: vertical;
            line-height: 1.6;
        }

        /* Select wrapper */
        .alamat-select-wrap {
            position: relative;
        }
        .alamat-select {
            width: 100%;
            min-height: 44px;
            padding: 0.625rem 2.5rem 0.625rem 0.875rem;
            border: 1px solid rgba(232, 224, 216, 0.85);
            border-radius: 12px;
            background: #ffffff;
            color: #1A1A1A;
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 0.9375rem;
            line-height: 1.5;
            cursor: pointer;
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
            box-shadow: 0 1px 2px rgba(26, 26, 26, 0.02);
            transition: border-color 180ms ease, box-shadow 180ms ease, background 180ms ease;
        }
        .alamat-select:hover { border-color: rgba(184, 149, 111, 0.5); }
        .alamat-select:focus {
            outline: none;
            border-color: #CFAF53;
            box-shadow: 0 0 0 3px rgba(207, 175, 83, 0.18);
        }
        .alamat-select:disabled {
            background: #F8FAFC;
            border-color: rgba(232, 224, 216, 0.7);
            color: #94A3B8;
            cursor: not-allowed;
            opacity: 0.85;
        }
        .alamat-select--loading { opacity: 0.6; }
        .alamat-select__caret {
            position: absolute;
            top: 50%;
            right: 0.875rem;
            transform: translateY(-50%);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 16px; height: 16px;
            color: #94A3B8;
            pointer-events: none;
        }
        .alamat-select__caret svg { width: 14px; height: 14px; }
        .alamat-select:focus + .alamat-select__caret,
        .alamat-select-wrap:hover .alamat-select__caret { color: #B8956F; }

        /* Submit button */
        .alamat-submit {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.4375rem;
            padding: 0.625rem 1.5rem;
            min-height: 44px;
            border-radius: 9999px;
            background: linear-gradient(135deg, #CFAF53 0%, #B8956F 100%);
            color: #ffffff;
            border: 1px solid transparent;
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 0.875rem;
            font-weight: 700;
            cursor: pointer;
            box-shadow:
                0 1px 2px rgba(184, 149, 111, 0.25),
                0 8px 18px -8px rgba(184, 149, 111, 0.55);
            transition: transform 200ms ease, box-shadow 200ms ease, filter 200ms ease;
        }
        .alamat-submit svg { width: 14px; height: 14px; }
        .alamat-submit:hover {
            transform: translateY(-1px);
            filter: brightness(1.05);
            box-shadow:
                0 1px 2px rgba(184, 149, 111, 0.3),
                0 14px 24px -8px rgba(184, 149, 111, 0.6);
        }
        .alamat-submit:active { transform: translateY(0); }

        @media (max-width: 639px) {
            .alamat-submit { width: 100%; }
        }
        @media (prefers-reduced-motion: reduce) {
            .alamat-input,
            .alamat-select,
            .alamat-submit { transition: none; }
            .alamat-submit:hover { transform: none; filter: none; }
        }

        /* â”€â”€ View Mode Display (when address is already saved) â”€â”€ */
        .alamat-view {
            padding: 1rem 1.125rem;
            background: linear-gradient(180deg, #FAF8F5 0%, #FFFFFF 100%);
            border: 1px solid rgba(232, 224, 216, 0.7);
            border-radius: 14px;
        }
        .alamat-view__grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 0.875rem 1.25rem;
        }
        @media (min-width: 640px) {
            .alamat-view__grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        }
        .alamat-view__cell { min-width: 0; }
        .alamat-view__label {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 0.6875rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.16em;
            color: #94A3B8;
            margin: 0 0 0.25rem 0;
        }
        .alamat-view__value {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 0.9375rem;
            font-weight: 700;
            color: #1A1A1A;
            line-height: 1.4;
            margin: 0;
            word-break: break-word;
        }

        /* â”€â”€ Edit toggle button (header) â”€â”€ */
        .alamat-edit-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.375rem;
            padding: 0.4375rem 0.875rem;
            border-radius: 9999px;
            background: rgba(207, 175, 83, 0.08);
            color: #B8956F;
            border: 1px solid rgba(184, 149, 111, 0.3);
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 0.75rem;
            font-weight: 700;
            cursor: pointer;
            transition: background 200ms ease, color 200ms ease, border-color 200ms ease, transform 200ms ease;
            white-space: nowrap;
        }
        .alamat-edit-btn svg { width: 12px; height: 12px; }
        .alamat-edit-btn:hover {
            background: linear-gradient(135deg, #CFAF53 0%, #B8956F 100%);
            border-color: transparent;
            color: #ffffff;
            transform: translateY(-1px);
        }

        /* â”€â”€ Cancel edit (when in edit mode with saved data) â”€â”€ */
        .alamat-cancel-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.375rem;
            padding: 0.4375rem 0.875rem;
            border-radius: 9999px;
            background: transparent;
            color: #64748B;
            border: 1px solid #E2E8F0;
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 0.75rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 180ms ease, color 180ms ease, border-color 180ms ease;
        }
        .alamat-cancel-btn:hover {
            background: #F8FAFC;
            color: #1A1A1A;
            border-color: #CBD5E1;
        }

        @media (prefers-reduced-motion: reduce) {
            .alamat-edit-btn,
            .alamat-cancel-btn { transition: none; }
            .alamat-edit-btn:hover { transform: none; }
        }
    </style>
@endonce
