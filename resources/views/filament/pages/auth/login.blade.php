<x-filament-panels::page.simple>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400..900;1,400..900&family=Plus+Jakarta+Sans:ital,wght@0,200..800;1,200..800&display=swap');

        /*
         * Custom Admin Login Page Styles
         * Design System: Medical-Luxury (Charcoal Brown + Elegant Gold)
         * Container: Warm card (#FFFFFF) on cream background (#EDE8E3)
         */

        /* ========== GLOBAL BACKGROUND & FONT ========== */
        html, body, button, input, textarea, select, .fi-simple-layout, main, .fi-simple-main, .fi-simple-main-ctn, .fi-simple-page, * {
            font-family: 'Plus Jakarta Sans', sans-serif !important;
        }
        
        html, body {
            background-color: #EDE8E3 !important;
        }
        .fi-simple-layout,
        .fi-simple-main-ctn {
            background-color: #EDE8E3 !important;
        }
        .dark .fi-simple-layout,
        .dark .fi-simple-main-ctn,
        .dark body {
            background-color: #EDE8E3 !important;
            color-scheme: light !important;
        }

        /* ========== THE CARD CONTAINER ========== */
        .fi-simple-main {
            background-color: #FFFFFF !important;
            border-radius: 1.25rem !important;
            box-shadow: 0 4px 30px rgba(0, 0, 0, 0.06) !important;
            padding: 3rem 2.5rem !important;
            max-width: 28rem !important;
            width: 100% !important;
            margin: 2rem auto !important;
        }
        @media (max-width: 480px) {
            .fi-simple-main {
                padding: 2rem 1.5rem !important;
                margin: 1rem !important;
                border-radius: 1rem !important;
            }
        }

        /* Remove any inner section/ring styling that Filament adds */
        .fi-simple-page,
        .fi-simple-page > *,
        .fi-simple-page-content {
            background-color: transparent !important;
            box-shadow: none !important;
            border: none !important;
            --tw-ring-shadow: none !important;
            --tw-ring-color: transparent !important;
        }

        /* ========== HIDE DEFAULT HEADER (we render custom one) ========== */
        .fi-simple-header {
            display: none !important;
        }

        /* ========== LABELS ========== */
        label,
        label span,
        .fi-fo-field-wrp-label,
        .fi-fo-field-wrp-label span {
            color: #1F2937 !important;
            font-weight: 600 !important;
            font-size: 0.875rem !important;
        }
        /* Hide red asterisks for required fields */
        sup,
        .text-danger-600 {
            display: none !important;
        }

        /* ========== INPUT FIELDS ========== */
        .fi-input-wrp {
            background-color: #FFFFFF !important;
            border: 1.5px solid #D1D5DB !important;
            border-radius: 0.625rem !important;
            box-shadow: none !important;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
            overflow: hidden;
        }
        .fi-input-wrp:focus-within {
            border-color: #B58E6B !important;
            box-shadow: 0 0 0 2px rgba(181, 142, 107, 0.15) !important;
        }
        .fi-input-wrp input,
        .fi-input-wrp input[type="email"],
        .fi-input-wrp input[type="password"],
        .fi-input-wrp input[type="text"] {
            color: #1F2937 !important;
            background-color: #FFFFFF !important;
            padding: 0.75rem 1rem !important;
            font-size: 0.9375rem !important;
            -webkit-appearance: none !important;
        }
        .fi-input-wrp input::placeholder {
            color: #9CA3AF !important;
        }

        /* ========== CHECKBOX ========== */
        .fi-checkbox-input {
            border-color: #D1D5DB !important;
            border-radius: 0.25rem !important;
            background-color: #FFFFFF !important;
        }
        .fi-checkbox-input:checked {
            background-color: #B58E6B !important;
            border-color: #B58E6B !important;
        }
        /* Checkbox label text */
        .fi-checkbox-input ~ *,
        .fi-checkbox-input + span {
            color: #374151 !important;
        }

        /* ========== SIGN IN BUTTON ========== */
        .fi-btn[type="submit"],
        button.fi-btn {
            background-color: #B58E6B !important;
            background-image: linear-gradient(135deg, #C4A07D, #B58E6B) !important;
            color: #FFFFFF !important;
            border: none !important;
            border-radius: 0.625rem !important;
            padding: 0.875rem 1.5rem !important;
            font-weight: 600 !important;
            font-size: 1rem !important;
            box-shadow: 0 2px 8px rgba(181, 142, 107, 0.3) !important;
            transition: all 0.25s ease;
            width: 100% !important;
            height: 3.25rem !important;
            cursor: pointer;
        }
        .fi-btn[type="submit"]:hover,
        button.fi-btn:hover {
            background-image: linear-gradient(135deg, #B58E6B, #A37A58) !important;
            box-shadow: 0 4px 12px rgba(181, 142, 107, 0.4) !important;
            transform: translateY(-1px);
        }

        /* ========== LINKS ========== */
        a {
            color: #B58E6B !important;
            text-decoration: none !important;
        }
        a:hover {
            color: #A37A58 !important;
        }

        /* ========== CUSTOM AUTH HEADER ========== */
        .custom-auth-header {
            text-align: center;
            margin-bottom: 2.5rem;
        }
        .custom-auth-logo {
            margin-bottom: 1.5rem;
        }
        .custom-auth-logo h2 {
            font-family: 'Playfair Display', serif !important;
            font-size: 1.125rem;
            letter-spacing: 0.25em;
            color: #3E362E;
            text-transform: uppercase;
            font-weight: 600;
            margin: 0;
        }
        .custom-auth-logo p {
            font-family: 'Plus Jakarta Sans', sans-serif !important;
            font-size: 0.625rem;
            letter-spacing: 0.35em;
            color: #6B6259;
            text-transform: uppercase;
            margin-top: 0.25rem;
            font-weight: 700;
        }
        .custom-auth-header h1 {
            font-family: 'Plus Jakarta Sans', sans-serif !important;
            font-size: 3rem;
            font-weight: 600;
            color: #3E362E;
            letter-spacing: -0.02em;
            margin: 0 0 0.5rem 0;
            line-height: 1.15;
        }
        .custom-auth-header .custom-subheading {
            font-size: 0.9375rem;
            color: #6B7280;
            margin: 0;
            line-height: 1.5;
        }
        @media (max-width: 480px) {
            .custom-auth-header h1 {
                font-size: 2.25rem;
            }
        }
    </style>

    {{-- Custom Header: Logo + Heading + Subheading --}}
    <div class="custom-auth-header">
        <div class="custom-auth-logo">
            <h2>Aishi Beauty</h2>
            <p>By Dr. ELS</p>
        </div>
        <h1>Welcome Back</h1>
        <p class="custom-subheading">Please enter your details to access the<br>clinic dashboard.</p>
    </div>

    {{ $this->content }}
</x-filament-panels::page.simple>
