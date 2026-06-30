<?php

namespace App\Services\Members;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Pasien;
use App\Models\User;
use App\Support\CodeGenerator;
use App\Support\EnsurePasienProfile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ConsultationMembershipService
{
    public function syncForUser(User $user): ?Pasien
    {
        $profile = EnsurePasienProfile::for($user);

        if ($profile->is_member) {
            return $profile;
        }

        $orders = Order::query()
            ->where('user_id', $user->id)
            ->where('status', 'sudah_dibayar')
            ->whereHas('items', fn ($query) => $query->whereNotNull('appointment_id'))
            ->with(['user', 'items.consultation', 'items.appointment.items.consultation'])
            ->latest('order_id')
            ->get();

        foreach ($orders as $order) {
            $profile = $this->activateIfEligibleForOrder($order);

            if ($profile?->is_member) {
                return $profile;
            }
        }

        return $profile;
    }

    public function activateIfEligibleForOrder(Order $order): ?Pasien
    {
        $order->loadMissing(['user', 'items.consultation', 'items.appointment.items.consultation']);

        if ($order->status !== 'sudah_dibayar' || ! $order->user) {
            return null;
        }

        $eligibleItem = $order->items->first(fn (OrderItem $item) => $this->isCompletedInitialConsultation($item));

        if (! $eligibleItem) {
            return null;
        }

        return DB::transaction(function () use ($order, $eligibleItem) {
            $profile = EnsurePasienProfile::for($order->user);

            /** @var Pasien $profile */
            $profile = Pasien::query()
                ->whereKey($profile->getKey())
                ->lockForUpdate()
                ->first();

            if (! $profile || $profile->is_member) {
                return $profile;
            }

            $rekamMedis = (string) ($profile->no_rekam_medis ?? '');
            $needsRmCode = $rekamMedis === '' || ! str_starts_with($rekamMedis, 'RM-');

            $profile->forceFill([
                'is_member' => true,
                'member_since' => $profile->member_since ?? now(),
                'no_rekam_medis' => $needsRmCode
                    ? CodeGenerator::prefixNumber(new Pasien(), 'no_rekam_medis', 'RM', 6, '-')
                    : $profile->no_rekam_medis,
            ])->save();

            Log::info('Membership activated from completed paid initial consultation', [
                'user_id' => $order->user_id,
                'pasien_id' => $profile->pasien_id,
                'order_id' => $order->order_id,
                'kode_order' => $order->kode_order,
                'appointment_id' => $eligibleItem->appointment_id,
                'no_rekam_medis' => $profile->no_rekam_medis,
            ]);

            return $profile;
        });
    }

    protected function isCompletedInitialConsultation(OrderItem $item): bool
    {
        $appointment = $item->appointment;

        if (! $appointment || $appointment->status !== 'selesai') {
            return false;
        }

        $type = strtolower((string) $item->item_type);

        if (! in_array($type, ['consultation', 'konsultasi'], true)) {
            return false;
        }

        $consultationName = strtolower((string) (
            $item->consultation?->nama
            ?? $appointment->items
                ?->first(fn ($appointmentItem) => in_array(strtolower((string) $appointmentItem->item_type), ['consultation', 'konsultasi'], true))
                ?->consultation
                ?->nama
            ?? $item->description
            ?? ''
        ));

        return str_contains($consultationName, 'awal');
    }
}
