@props(['title' => 'Aishi Beauty By Dr.Els'])

<!doctype html>
<html lang="id" class="scroll-smooth">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>{{ $title }} - Aishi Beauty By Dr.Els</title>

    {{-- Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,600;0,700;1,400;1,600&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap"
        rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        .font-serif {
            font-family: 'Playfair Display', serif;
        }

        [x-cloak] {
            display: none !important;
        }

        .animate-fade-in-down {
            animation: fadeInDown 0.5s ease-out;
        }

        @keyframes fadeInDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>

<body class="min-h-screen bg-[#FAF8F5] text-slate-900 flex flex-col font-sans antialiased">

    {{-- 🔥 LOGIC HITUNG JUMLAH CART 🔥 --}}
    @php
        $cartSession = session('cart', []);
        $cartCount = collect($cartSession)->sum('qty'); 
    @endphp

    {{-- HEADER --}}
    @include('layouts._partials.guest._header')

    {{-- MAIN CONTENT --}}
    <main class="flex-1 w-full flex flex-col">
        <div class="w-full">
            {{-- Flash Messages --}}

            {{ $slot }}
        </div>
    </main>

    {{-- DARK FOOTER --}}
    @include('layouts._partials.guest._footer')

    {{-- Floating WhatsApp Chat Widget --}}
    @include('layouts._partials.guest._wa_widget')

    <x-toast />
    <x-confirm-dialog />
    <x-cart-success-modal />

</body>

</html>