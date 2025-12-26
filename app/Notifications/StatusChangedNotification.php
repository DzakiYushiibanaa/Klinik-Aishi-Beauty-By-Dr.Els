<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue; // Aktifkan ini biar email dikirim di background (biar ga loading lama)
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class StatusChangedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $status;
    public $message;
    public $url;

    /**
     * Terima data dari Controller (misal: status baru, pesan, link)
     */
    public function __construct($status, $url = null)
    {
        $this->status = $status;
        $this->message = "Status pra-konsultasi Anda telah diperbarui menjadi: " . $status;
        $this->url = $url ?? url('/dashboard');
    }

    /**
     * Tentukan jalur pengiriman: Database (Lonceng) DAN Mail (Email)
     */
    public function via(object $notifiable): array
    {
        // KUNCI UTAMANYA DISINI:
        return ['database', 'mail']; 
    }

    /**
     * 1. Pengaturan Tampilan EMAIL
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
                    ->subject('Update Status: ' . $this->status . ' - Aishi Beauty') // Judul Email
                    ->greeting('Halo, ' . $notifiable->name . '!') // Sapaan (Halo Budi!)
                    ->line($this->message) // Isi pesan
                    ->action('Lihat Detail', $this->url) // Tombol Klik
                    ->line('Terima kasih telah mempercayakan perawatan Anda pada Aishi Beauty.'); // Penutup
    }

    /**
     * 2. Pengaturan Data DATABASE (Untuk Ikon Lonceng)
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title'   => 'Status Berubah',
            'message' => $this->message,
            'link'    => $this->url,
            // Kamu bisa tambah data lain sesuka hati
        ];
    }
}