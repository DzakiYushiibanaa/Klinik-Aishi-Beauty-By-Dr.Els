<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Consultation extends Model
{
    protected $table = 'consultations';
    protected $primaryKey = 'consultation_id';
    protected $guarded = [];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function appointmentItems()
    {
        return $this->hasMany(AppointmentItem::class, 'consultation_id', 'consultation_id');
    }
}
