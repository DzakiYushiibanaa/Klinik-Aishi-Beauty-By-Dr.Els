<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RekamMedis extends Model
{
    protected $table = 'rekam_medis';
    protected $primaryKey = 'rekam_medis_id';

    protected $fillable = [
        'no_kunjungan',
        'appointment_id',
        'pasien_id',
        'dokter_id',
        'terapis_id',
        'keluhan',
        'diagnosa',
        'tindakan',
        'status_tindakan',
        'foto_sebelum_path',
        'foto_sesudah_path',
        'produk_rekomendasi',
        'catatan_lanjutan',
    ];

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class, 'appointment_id', 'appointment_id');
    }

    public function pasien(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pasien_id', 'id');
    }

    public function dokter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dokter_id');
    }

    public function terapis(): BelongsTo
    {
        return $this->belongsTo(User::class, 'terapis_id');
    }
}
