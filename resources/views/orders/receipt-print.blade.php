<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Struk {{ $order->kode_order }}</title>
    <style>
        @page { size: 58mm auto; margin: 0; }

        html, body {
            margin: 0;
            padding: 0;
            background: #f0f0f0;
            font-family: "Courier New", Consolas, monospace;
        }

        .receipt {
            width: 58mm;
            max-width: 58mm;
            padding: 8px 6px;
            margin: 24px auto;
            background: #ffffff;
            color: #000000;
        }

        pre {
            margin: 0;
            padding: 0;
            font-family: "Courier New", Consolas, monospace;
            font-size: 11px;
            line-height: 1.35;
            white-space: pre;
            word-wrap: break-word;
        }

        @media print {
            html, body { background: #ffffff; }
            .receipt {
                margin: 0;
                padding: 4px;
                box-shadow: none;
            }
            .no-print { display: none; }
        }

        .no-print {
            text-align: center;
            margin: 20px auto;
            max-width: 240px;
        }

        .no-print button {
            padding: 10px 18px;
            background: #B8956F;
            color: #fff;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            font-family: system-ui, sans-serif;
        }

        .no-print button:hover { background: #A07E5A; }
    </style>
</head>
<body>
    <div class="receipt">
        <pre>{{ $content }}</pre>
    </div>

    <div class="no-print">
        <button onclick="window.print()">Cetak Ulang</button>
        <p style="font-size: 12px; color: #666; font-family: system-ui, sans-serif; margin-top: 12px;">
            Struk akan otomatis dicetak. Pastikan printer aktif.
        </p>
    </div>

    <script>
        window.addEventListener('load', function () {
            setTimeout(function () { window.print(); }, 300);
        });
    </script>
</body>
</html>
