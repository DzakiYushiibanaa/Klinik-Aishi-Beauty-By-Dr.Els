<?php

namespace App\Notifications;

use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue; // Kalau mau antrian, uncomment ini
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Filament\Notifications\Notification as FilamentNotification; // Import Filament Notif
use Filament\Notifications\Actions\Action; // Import Action Button

class AppointmentCanceledNotification extends Notification // implements ShouldQueue
{
    use Queueable;

    public $appointment;
    public $canceledBy;
    public $cancelReason;

    public function __construct(Appointment $appointment)
    {
        $this->appointment = $appointment;
        $this->canceledBy = $appointment->canceled_by;
        $this->cancelReason = $appointment->cancel_reason;
    }

    public function via(object $notifiable): array
    {
        // Kirim ke database (panel filament)
        return ['database']; 
    }

    /**
     * 🔥 KUNCI UTAMA: Format Notifikasi Filament (v3) 🔥
     * Method ini mengubah notifikasi menjadi format yang dikenali oleh Filament Admin Panel
     */
    public function toDatabase(object $notifiable): array
    {
        $jenis = ucfirst($this->appointment->jenis);
        $pasien = $this->appointment->pasien->name ?? 'Pasien';
        $tanggal = $this->appointment->tanggal_jam->format('d M, H:i');

        // Gunakan Helper Filament untuk membuat payload notifikasi
        return FilamentNotification::make()
            ->title('Janji Temu Dibatalkan 📅')
            ->body("**{$pasien}** membatalkan {$jenis} pada tanggal **{$tanggal}**.\nAlasan: \"{$this->cancelReason}\"")
            ->warning() // Warna Kuning/Oranye
            ->icon('heroicon-o-x-circle') // Icon Silang
            ->actions([
                Noti::make('Lihat')
                    ->url("/admin/appointments/{$this->appointment->appointment_id}/edit") 
                    ->button()
                    ->markAsRead(), // Biar pas diklik, notifnya ilang
            ])
            ->getDatabaseMessage(); // Mengubah object Filament jadi Array Database
    }

    // (Opsional) Format Email jika suatu saat butuh
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Janji Temu Dibatalkan')
            ->line('Janji temu telah dibatalkan.')
            ->action('Cek Panel', url('/admin'));
    }
}
