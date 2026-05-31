<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderShippingUpdated extends Notification
{
    use Queueable;

    public Order $order;

    public function __construct(Order $order)
    {
        // Eager-load relasi yang dipakai email view supaya tidak N+1.
        $this->order = $order->loadMissing(['items.product', 'items.bundle', 'pasien.pasienProfile']);
    }

    /**
     * Kirim via Database (Lonceng Web) DAN Email
     */
    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    /**
     * Email pakai template HTML custom (bukan default Laravel MailMessage).
     */
    public function toMail(object $notifiable): MailMessage
    {
        $statusLabel = $this->statusLabel();

        // Subject lebih natural untuk inbox.
        $subject = match ($this->order->shipping_status) {
            'dikirim'    => "Pesanan Anda Sedang Dikirim - {$this->order->kode_order}",
            'selesai'    => "Pesanan Anda Selesai - {$this->order->kode_order}",
            'dikemas'    => "Pesanan Anda Sedang Dikemas - {$this->order->kode_order}",
            'diproses'   => "Pesanan Anda Sedang Diproses - {$this->order->kode_order}",
            'dibatalkan' => "Pengiriman Pesanan Dibatalkan - {$this->order->kode_order}",
            default      => "Update Pesanan {$this->order->kode_order}",
        };

        return (new MailMessage)
            ->subject($subject)
            ->view('emails.orders.shipping-updated', [
                'order'      => $this->order,
                'notifiable' => $notifiable,
            ]);
    }

    /**
     * Format Tampilan Notifikasi Web (Database)
     */
    public function toArray(object $notifiable): array
    {
        $statusLabel = $this->statusLabel();
        $pesan = "Status pesanan #{$this->order->kode_order} berubah menjadi {$statusLabel}.";

        if ($this->order->shipping_status === 'dikirim' && $this->order->shipping_waybill) {
            $pesan = "Paket #{$this->order->kode_order} sedang dikirim. Resi: {$this->order->shipping_waybill}";
        }

        return [
            'order_id' => $this->order->order_id,
            'title'    => $statusLabel,
            'message'  => $pesan,
            'type'     => 'shipping_update',
            'link'     => route('dashboard') . '?tab=orders&highlight=' . $this->order->order_id,
        ];
    }

    private function statusLabel(): string
    {
        return match ($this->order->shipping_status) {
            'dikirim'    => 'Sedang Dikirim',
            'selesai'    => 'Pesanan Selesai',
            'diproses'   => 'Sedang Diproses',
            'dikemas'    => 'Sedang Dikemas',
            'dibatalkan' => 'Pengiriman Dibatalkan',
            default      => 'Status Diupdate',
        };
    }
}
