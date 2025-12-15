<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PatientForm extends Model
{
    protected $table = 'patient_forms';
    protected $primaryKey = 'patient_form_id';

    protected $fillable = [
        'user_id','dokter_id','tipe_form',
        'golongan_darah','riwayat_penyakit',
        'jenis_kulit','skincare_sebelumnya','periode_skincare_bulan',
        'pernah_facial','pernah_skin_booster','pernah_laser','pernah_inject_infus',
        'keluhan_utama',
        'foto_depan_path','foto_kiri_path','foto_kanan_path',
        'status','rekomendasi_dokter',
    ];

    public function pasien()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function dokter()
    {
        return $this->belongsTo(User::class, 'dokter_id', 'id');
    }

    public function rekomendasiItems()
    {
        return $this->hasMany(PraKonsultasiRekomendasiItem::class, 'patient_form_id', 'patient_form_id');
    }
}
