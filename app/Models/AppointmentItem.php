<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AppointmentItem extends Model
{
    protected $table = 'appointment_items';
    protected $primaryKey = 'appointment_item_id';

    protected $fillable = [
        'appointment_id',
        'item_type',
        'treatment_id',
        'bundle_id',
        'consultation_id',
        'qty',
        // Handler & komisi (10% default).
        'handled_by_type',
        'handled_by_id',
        'commission_percent',
        'commission_amount',
    ];

    protected $casts = [
        'commission_percent' => 'decimal:2',
        'commission_amount'  => 'integer',
    ];

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class, 'appointment_id', 'appointment_id');
    }

    public function treatment(): BelongsTo
    {
        return $this->belongsTo(Treatment::class, 'treatment_id', 'treatment_id');
    }

    public function bundle(): BelongsTo
    {
        return $this->belongsTo(Bundle::class, 'bundle_id', 'bundle_id');
    }

    public function consultation(): BelongsTo
    {
        return $this->belongsTo(Consultation::class, 'consultation_id', 'consultation_id');
    }

    /**
     * Relasi ke user yang menangani layanan ini.
     * Hanya cocok dipanggil saat handled_by_type & handled_by_id sudah terisi.
     */
    public function handler(): BelongsTo
    {
        return $this->belongsTo(User::class, 'handled_by_id', 'id');
    }
}
