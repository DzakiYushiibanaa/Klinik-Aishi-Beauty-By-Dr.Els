<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Appointment extends Model
{
    protected $table = 'appointments';
    protected $primaryKey = 'appointment_id';

    protected $fillable = [
        'user_id','dokter_id','terapis_id','patient_form_id',
        'sumber','dibuat_oleh_id',
        'jenis',
        'tanggal_jam','payment_expired_at','status',
        'catatan_pasien','catatan_klinik',
        'canceled_by','cancel_reason',
    ];

    protected $casts = [
        'tanggal_jam'         => 'datetime',
        'payment_expired_at'  => 'datetime',
    ];

    public function pasien()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function dokter()
    {
        return $this->belongsTo(User::class, 'dokter_id', 'id');
    }

    public function terapis()
    {
        return $this->belongsTo(User::class, 'terapis_id', 'id');
    }

    public function dibuatOleh()
    {
        return $this->belongsTo(User::class, 'dibuat_oleh_id', 'id');
    }

    public function patientForm()
    {
        return $this->belongsTo(PatientForm::class, 'patient_form_id', 'patient_form_id');
    }

    public function treatment()
    {
        return $this->belongsTo(Treatment::class, 'treatment_id', 'treatment_id');
    }

    public function rekamMedis()
    {
        return $this->hasOne(RekamMedis::class, 'appointment_id', 'appointment_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(AppointmentItem::class, 'appointment_id', 'appointment_id');
    }
}
