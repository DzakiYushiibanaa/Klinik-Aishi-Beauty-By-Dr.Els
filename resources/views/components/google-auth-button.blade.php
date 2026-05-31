@props([
    'label' => 'Lanjutkan dengan Google',
])

<a href="{{ route('auth.google.redirect') }}"
   class="google-auth-btn"
   {{ $attributes }}>
    <svg class="google-auth-btn__logo" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
        <path fill="#EA4335" d="M12 10.2v3.9h5.5c-.24 1.4-1.7 4.1-5.5 4.1-3.3 0-6-2.7-6-6.1s2.7-6.1 6-6.1c1.9 0 3.2.8 3.9 1.5l2.7-2.6C16.7 3.3 14.6 2.4 12 2.4 6.7 2.4 2.4 6.7 2.4 12s4.3 9.6 9.6 9.6c5.5 0 9.2-3.9 9.2-9.4 0-.6 0-1.1-.2-1.6L12 10.2z" opacity="0"/>
        <path fill="#4285F4" d="M21.6 12.2c0-.7-.1-1.4-.2-2.1H12v3.9h5.4c-.2 1.2-.9 2.3-2 3v2.5h3.2c1.9-1.7 3-4.3 3-7.3z"/>
        <path fill="#34A853" d="M12 21.6c2.7 0 5-.9 6.6-2.4l-3.2-2.5c-.9.6-2 1-3.4 1-2.6 0-4.8-1.8-5.6-4.1H3.1v2.6C4.7 19.4 8.1 21.6 12 21.6z"/>
        <path fill="#FBBC05" d="M6.4 13.6c-.2-.6-.3-1.2-.3-1.6s.1-1 .3-1.6V7.8H3.1c-.7 1.3-1 2.7-1 4.2s.4 2.9 1 4.2l3.3-2.6z"/>
        <path fill="#EA4335" d="M12 6.4c1.5 0 2.5.6 3.1 1.2l2.3-2.3C16 4 14.2 3 12 3 8.1 3 4.7 5.2 3.1 8.6L6.4 11.2c.8-2.3 3-4.8 5.6-4.8z"/>
    </svg>
    <span class="google-auth-btn__label">{{ $label }}</span>
</a>

@once
    <style>
        .google-auth-btn {
            width: 100%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.625rem;
            padding: 0.75rem 1rem;
            min-height: 46px;
            border-radius: 12px;
            border: 1px solid #E2E8F0;
            background: #ffffff;
            color: #1A1A1A;
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 0.875rem;
            font-weight: 700;
            letter-spacing: -0.005em;
            text-decoration: none;
            cursor: pointer;
            transition: border-color 180ms ease, background 180ms ease, box-shadow 180ms ease, transform 180ms ease;
            box-shadow: 0 1px 2px rgba(26, 26, 26, 0.04);
        }
        .google-auth-btn:hover {
            border-color: #CBD5E1;
            background: #F8FAFC;
            transform: translateY(-1px);
            box-shadow:
                0 1px 2px rgba(26, 26, 26, 0.05),
                0 8px 18px -10px rgba(26, 26, 26, 0.18);
        }
        .google-auth-btn:active { transform: translateY(0); }
        .google-auth-btn:focus-visible {
            outline: none;
            border-color: #CFAF53;
            box-shadow: 0 0 0 3px rgba(207, 175, 83, 0.25);
        }

        .google-auth-btn__logo {
            width: 20px;
            height: 20px;
            flex-shrink: 0;
        }
        .google-auth-btn__label { line-height: 1; }

        @media (prefers-reduced-motion: reduce) {
            .google-auth-btn { transition: none; }
            .google-auth-btn:hover { transform: none; }
        }

        /* Auth divider used above the Google button */
        .auth-divider {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        .auth-divider__line {
            flex: 1;
            height: 1px;
            background: #E2E8F0;
        }
        .auth-divider__text {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 0.75rem;
            font-weight: 600;
            color: #94A3B8;
            text-transform: lowercase;
            letter-spacing: 0.06em;
        }
    </style>
@endonce
