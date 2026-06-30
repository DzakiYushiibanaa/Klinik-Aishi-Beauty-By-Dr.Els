<?php

namespace App\Services\Orders;

use App\Models\Order;
use Carbon\Carbon;

/**
 * Builds receipt content for thermal-printer style invoice (58mm / 32-char width).
 *
 * Produces plain-text lines ready to send to ESC/POS printer OR to render
 * inside a browser <pre> tag for fallback printing.
 */
class InvoicePrintService
{
    /** Width in characters for a standard 58mm thermal receipt. */
    private const WIDTH = 32;

    /**
     * Render the invoice as a plain-text receipt (monospaced).
     */
    public function renderText(Order $order): string
    {
        $order->loadMissing(['items.product', 'items.treatment', 'items.bundle', 'pasien', 'dibuatOleh']);

        $lines = [];

        // Header
        $lines[] = $this->center('AISHI BEAUTY');
        $lines[] = $this->center('Jl. NUSANTARA BEJI, NO 99');
        $lines[] = $this->center('Telp: 085780050007');
        $lines[] = $this->divider();

        // Meta (invoice + date + kasir + time + customer)
        $createdAt = $order->created_at ? Carbon::parse($order->created_at) : now();
        $lines[] = $this->twoCols('No: ' . $order->kode_order, $createdAt->format('d/m/Y'));
        $lines[] = $this->twoCols('Kasir: Admin', $createdAt->format('H:i'));
        $lines[] = 'Pel.: ' . ($order->pasien?->name ?? '-');
        $lines[] = $this->divider();

        // Items
        foreach ($order->items as $item) {
            $name  = $this->itemName($item);
            $price = (int) ($item->subtotal ?? 0);
            $qty   = (int) ($item->qty ?? 1);

            if ($qty > 1) {
                $lines[] = $name;
                $lines[] = $this->twoCols("  {$qty} x " . $this->money((int) $item->harga_satuan), $this->money($price));
            } else {
                $lines[] = $this->twoCols($name, $this->money($price));
            }
        }

        $lines[] = $this->divider();

        // Totals
        $grand = (int) $order->grand_total;
        $lines[] = $this->twoCols('Total', '= ' . $this->money($grand));

        // Payment method (always shown)
        $methodLabel = $this->paymentMethodLabel($order->payment_method);
        if ($methodLabel) {
            $lines[] = $this->twoCols('Metode', '= ' . $methodLabel);
        }

        // Cash detail — Tunai + Kembali (only when payment_method=cash AND cash_received stored)
        if ($order->payment_method === 'cash' && $order->cash_received !== null) {
            $tunai = (int) $order->cash_received;
            $lines[] = $this->twoCols('Tunai', '= ' . $this->money($tunai));
            $lines[] = $this->divider();
            $lines[] = $this->twoCols('Kembali', '= ' . $this->money(max(0, $tunai - $grand)));
        }

        $lines[] = '';
        $lines[] = $this->center('Terima Kasih');
        $lines[] = $this->center('Aishi Beauty By DR.ELS');
        $lines[] = '';

        return implode(PHP_EOL, $lines);
    }

    /**
     * Send receipt to a network thermal printer (ESC/POS).
     *
     * @param  string  $host   e.g. "192.168.1.50"
     * @param  int     $port   typically 9100
     * @throws \Exception
     */
    public function printToNetworkPrinter(Order $order, string $host, int $port = 9100): void
    {
        $connector = new \Mike42\Escpos\PrintConnectors\NetworkPrintConnector($host, $port);
        $this->sendViaConnector($order, $connector);
    }

    /**
     * Send receipt to a local/USB printer (Windows share name or Linux device path).
     *
     * @param  string  $target e.g. "smb://localhost/POS58" or "/dev/usb/lp0"
     */
    public function printToLocalPrinter(Order $order, string $target): void
    {
        $connector = new \Mike42\Escpos\PrintConnectors\WindowsPrintConnector($target);
        $this->sendViaConnector($order, $connector);
    }

    // ═══════════════════════════════════════════════════════
    // INTERNAL
    // ═══════════════════════════════════════════════════════

    private function sendViaConnector(Order $order, $connector): void
    {
        $printer = new \Mike42\Escpos\Printer($connector);

        try {
            $printer->setJustification(\Mike42\Escpos\Printer::JUSTIFY_LEFT);
            $printer->text($this->renderText($order));
            $printer->feed(2);
            $printer->cut();
        } finally {
            $printer->close();
        }
    }

    private function itemName($item): string
    {
        if ($item->item_type === 'product' && $item->product) {
            return $item->product->nama;
        }
        if ($item->item_type === 'treatment' && $item->treatment) {
            return $item->treatment->nama;
        }
        if ($item->item_type === 'bundle' && $item->bundle) {
            return 'Bundle: ' . $item->bundle->nama;
        }
        return $item->description ?: ucfirst((string) $item->item_type);
    }

    /**
     * Human-readable payment method label for the receipt.
     */
    private function paymentMethodLabel(?string $method): ?string
    {
        return match ($method) {
            'cash'          => 'Cash',
            'bsi'           => 'BSI',
            'bca'           => 'BCA',
            'bri'           => 'BRI',
            'midtrans_qris' => 'QRIS',
            'qris'          => 'QRIS',
            'bank_transfer' => 'Bank Transfer',
            'transfer'      => 'Bank Transfer',
            'card'          => 'Kartu',
            'credit_card'   => 'Kartu Kredit',
            'debit_card'    => 'Kartu Debit',
            null, ''        => null,
            default         => strtoupper((string) $method),
        };
    }

    private function center(string $text): string
    {
        $len = mb_strlen($text);
        if ($len >= self::WIDTH) return mb_substr($text, 0, self::WIDTH);
        $pad = (int) floor((self::WIDTH - $len) / 2);
        return str_repeat(' ', $pad) . $text;
    }

    private function divider(): string
    {
        return str_repeat('-', self::WIDTH);
    }

    /**
     * Two columns: left-aligned label, right-aligned value, within WIDTH.
     * Truncates left side if needed.
     */
    private function twoCols(string $left, string $right): string
    {
        $maxLeft = self::WIDTH - mb_strlen($right) - 1;
        if (mb_strlen($left) > $maxLeft) {
            $left = mb_substr($left, 0, $maxLeft);
        }
        $spaces = self::WIDTH - mb_strlen($left) - mb_strlen($right);
        return $left . str_repeat(' ', max(1, $spaces)) . $right;
    }

    private function money(int $value): string
    {
        return number_format($value, 0, ',', '.');
    }
}
