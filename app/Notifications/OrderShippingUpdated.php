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

    public $order;

    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    /**
     * Kirim via Database (Lonceng Web) DAN Email
     */
    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    /**
     * Format Tampilan Email
     */
    public function toMail(object $notifiable): MailMessage
    {
        // Tentukan Label Status
        $statusLabel = match($this->order->shipping_status) {
            'dikirim' => 'Sedang Dikirim 🚚',
            'selesai' => 'Pesanan Selesai ✅',
            'diproses' => 'Sedang Diproses 📦',
            'dibatalkan' => 'Pengiriman Dibatalkan ❌',
            default => 'Status Diupdate'
        };

        // Buat Pesan Email
        $mail = (new MailMessage)
            ->subject("Update Pesanan #{$this->order->kode_order}: {$statusLabel}")
            ->greeting("Halo, {$notifiable->name}!")
            ->line("Ada update terbaru untuk pesanan Anda **#{$this->order->kode_order}**.")
            ->line("Status saat ini: **{$statusLabel}**");

        // Jika status DIKIRIM, tambahkan info Resi
        if ($this->order->shipping_status == 'dikirim' && $this->order->shipping_waybill) {
            $kurir = strtoupper($this->order->shipping_method ?? '-');
            $mail->line("Paket dikirim menggunakan kurir **{$kurir}**.")
                 ->line("Nomor Resi: **{$this->order->shipping_waybill}**")
                 ->line('(Simpan nomor resi ini untuk melacak paket Anda).');
        }

        // Tombol Aksi & Penutup
        return $mail->action('Lihat Detail Pesanan', route('dashboard'))
                    ->line('Terima kasih telah berbelanja di Aishi Beauty!');
    }

    /**
     * Format Tampilan Notifikasi Web (Database)
     */
    public function toArray(object $notifiable): array
    {
        $statusLabel = match($this->order->shipping_status) {
            'dikirim' => 'Sedang Dikirim 🚚',
            'selesai' => 'Pesanan Selesai ✅',
            'diproses' => 'Sedang Diproses 📦',
            'dibatalkan' => 'Pengiriman Dibatalkan ❌',
            default => 'Status Diupdate'
        };

        $pesan = "Status pesanan #{$this->order->kode_order} berubah menjadi {$statusLabel}.";
        
        if ($this->order->shipping_status == 'dikirim' && $this->order->shipping_waybill) {
            $pesan = "Paket #{$this->order->kode_order} sedang dikirim. Resi: {$this->order->shipping_waybill}";
        }

        return [
            'order_id' => $this->order->order_id,
            'title' => $statusLabel,
            'message' => $pesan,
            'type' => 'shipping_update',
            'link' => route('dashboard'), // Arahkan ke dashboard user
        ];
    }
}