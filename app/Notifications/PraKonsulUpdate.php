<?php

namespace App\Notifications;

use App\Models\PatientForm;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PraKonsulUpdate extends Notification 
{
    use Queueable;

    public $form;

    public function __construct(PatientForm $form)
    {
        $this->form = $form;
    }

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        // Normalisasi status ke huruf kecil biar aman
        $status = strtolower($this->form->status);

        // Label Pesan Sesuai Screenshot
        $statusLabel = match($status) {
            'ditinjau' => 'Sedang Ditinjau Dokter 👨‍⚕️',
            'direkomendasikan' => 'Rekomendasi Dokter Telah Terbit 📄',
            'ditutup' => 'Sesi Konsultasi Selesai (Ditutup) ✅',
            default => 'Status Diupdate'
        };

        $mail = (new MailMessage)
            ->subject("Update Pra-Konsul #PK-{$this->form->patient_form_id}")
            ->greeting("Halo, {$notifiable->name}!")
            ->line("Status Pra-Konsultasi Anda (Keluhan: {$this->form->keluhan_utama}) telah diperbarui.")
            ->line("Status saat ini: **{$statusLabel}**");

        // Pesan khusus jika sudah direkomendasikan
        if ($status == 'direkomendasikan') {
            $mail->line("Dokter telah memberikan rekomendasi perawatan/produk untuk Anda. Silakan cek detailnya di aplikasi.");
        }

        return $mail->action('Lihat Detail Konsultasi', route('pra.show', $this->form->patient_form_id))
                    ->line('Semoga lekas sembuh dan glowing kembali! ✨');
    }

    public function toArray(object $notifiable): array
    {
        $status = strtolower($this->form->status);

        // Label Singkat untuk Lonceng Web
        $statusLabel = match($status) {
            'ditinjau' => 'Sedang Ditinjau',
            'direkomendasikan' => 'Rekomendasi Terbit',
            'ditutup' => 'Selesai',
            default => 'Status Diupdate'
        };

        $pesan = "Update status Pra-Konsul: {$statusLabel}.";

        if ($status == 'direkomendasikan') {
            $pesan = "Dokter telah membalas konsultasi Anda. Ketuk untuk melihat rekomendasi.";
        }

        return [
            'id' => $this->form->patient_form_id,
            'title' => 'Pra-Konsul Update 🩺',
            'message' => $pesan,
            'type' => 'prakonsul_update', // Trigger ikon stetoskop di header
            'link' => route('pra.show', $this->form->patient_form_id),
        ];
    }
}
