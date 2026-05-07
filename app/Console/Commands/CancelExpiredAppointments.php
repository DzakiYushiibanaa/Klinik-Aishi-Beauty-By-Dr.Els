<?php

namespace App\Console\Commands;

use App\Models\Appointment;
use App\Models\Order;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CancelExpiredAppointments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'appointments:cancel-expired';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Batalkan appointment treatment yang payment_expired_at sudah lewat';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $expired = Appointment::where('status', 'menunggu_pembayaran')
            ->where('payment_expired_at', '<', now())
            ->get();

        $count = 0;

        foreach ($expired as $appointment) {
            DB::transaction(function () use ($appointment, &$count) {
                
                $appointment->update([
                    'status'        => 'batal',
                    'canceled_by'   => 'sistem',
                    'cancel_reason' => 'Batas waktu pembayaran 15 menit telah habis.',
                ]);

                // Batalkan juga order yang terkait jika masih menunggu pembayaran
                Order::whereHas('items', function($q) use ($appointment) {
                        $q->where('appointment_id', $appointment->appointment_id);
                    })
                    ->where('status', 'menunggu_pembayaran')
                    ->update(['status' => 'batal']);

                $count++;
            });
        }

        if ($count > 0) {
            $this->info("Berhasil membatalkan {$count} appointment yang expired.");
            Log::info("Scheduler: cancelled {$count} expired appointments");
        } else {
            $this->info("Tidak ada appointment yang expired.");
        }
    }
}
