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
        'qty',
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
}
