<?php

namespace App\Notifications;

use App\Models\Appointment;
use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Notifikasi untuk user ketika layanan appointment selesai.
 *
 * Dikirim via DB (bell) + Mail. Email versi detail mirip nota/invoice:
 * kode appointment, kode order, dokter/terapis, breakdown item (konsultasi +
 * treatment/bundle + produk tambahan), total pembayaran.
 */
class AppointmentCompleted extends Notification
{
    use Queueable;

    public Appointment $appointment;
    public ?Order $order;

    public function __construct(Appointment $appointment, ?Order $order = null)
    {
        $this->appointment = $appointment;
        $this->order = $order;
    }

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Terima kasih - Layanan Selesai | Aishi Beauty')
            ->view('emails.appointments.completed', [
                'appointment' => $this->appointment,
                'order'       => $this->order,
                'notifiable'  => $notifiable,
            ]);
    }

    public function toArray(object $notifiable): array
    {
        $kodeAppt = 'APT-' . str_pad((string) $this->appointment->appointment_id, 4, '0', STR_PAD_LEFT);

        $summary = $this->appointment->jenis === 'konsultasi'
            ? 'Konsultasi'
            : 'Treatment';

        $dokter = $this->appointment->dokter?->name
            ? ' dengan Dr. ' . $this->appointment->dokter->name
            : '';

        return [
            'title'   => 'Terima Kasih - Layanan Selesai',
            'message' => "Layanan {$summary}{$dokter} (#{$kodeAppt}) telah selesai. Detail invoice dikirim ke email Anda.",
            'link'    => url('/dashboard'),
            'type'    => 'success',
            'appointment_id' => $this->appointment->appointment_id,
            'order_id'       => $this->order?->order_id,
        ];
    }
}
