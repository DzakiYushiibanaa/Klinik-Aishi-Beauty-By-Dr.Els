<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PatientForm extends Model
{
    protected $table = 'patient_forms';
    protected $primaryKey = 'patient_form_id';

    protected $fillable = [
        'pasien_id','dokter_id',
        'golongan_darah','riwayat_penyakit',
        'jenis_kulit','skincare_sebelumnya','periode_skincare_bulan',
        'pernah_facial','pernah_skin_booster','pernah_laser','pernah_inject_infus',
        'keluhan_utama',
        'foto_depan_path','foto_kiri_path','foto_kanan_path',
        'status','diagnosa',
        // Workflow review dokter
        'reviewed_by','review_started_at','reviewed_at',
        'catatan_tambahan',
    ];

    protected $casts = [
        'review_started_at' => 'datetime',
        'reviewed_at'       => 'datetime',
    ];

    public function pasien()
    {
        return $this->belongsTo(Pasien::class, 'pasien_id', 'pasien_id');
    }

    public function dokter()
    {
        return $this->belongsTo(User::class, 'dokter_id', 'id');
    }

    /**
     * Dokter yang melakukan review (boleh sama / berbeda dengan dokter
     * pemilik appointment). Diisi otomatis saat tombol "Mulai Review"
     * ditekan di Filament.
     */
    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by', 'id');
    }

    public function rekomendasiItems()
    {
        return $this->hasMany(PraKonsultasiRekomendasiItem::class, 'patient_form_id', 'patient_form_id');
    }
}
