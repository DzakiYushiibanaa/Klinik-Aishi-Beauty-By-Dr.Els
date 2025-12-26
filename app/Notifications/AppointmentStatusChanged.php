<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AppointmentStatusChanged extends Notification 
{
    use Queueable;

    public $appointment;
    public $statusBaru;

    // Terima data Appointment dan Status Baru
    public function __construct($appointment, $statusBaru)
    {
        $this->appointment = $appointment;
        $this->statusBaru = $statusBaru;
    }

    // Kirim ke Database (Lonceng) DAN Email
    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    // Format Email
    public function toMail(object $notifiable): MailMessage
    {
        $url = url('/dashboard'); // Arahkan ke dashboard user

        return (new MailMessage)
            ->subject('Update Status Booking - Aishi Beauty')
            ->greeting('Halo ' . $notifiable->name . ',')
            ->line('Status janji temu Anda pada tanggal ' . $this->appointment->tanggal_jam->format('d M Y H:i') . ' telah diperbarui.')
            ->line('Status Baru: ' . strtoupper($this->statusBaru))
            ->action('Cek Detail', $url)
            ->line('Terima kasih telah memilih Aishi Beauty Clinic.');
    }

    // Format Database (Lonceng)
    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Status Booking Berubah',
            'message' => 'Booking tgl ' . $this->appointment->tanggal_jam->format('d M') . ' kini statusnya: ' . $this->statusBaru,
            'link' => url('/dashboard'),
            'type' => 'info'
        ];
    }
}