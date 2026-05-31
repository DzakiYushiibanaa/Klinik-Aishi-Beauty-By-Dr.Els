<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TreatmentCategory extends Model
{
    protected $primaryKey = 'treatment_category_id';
    protected $fillable = [
        'nama', 'deskripsi', 'is_active',
        'doctor_allowed', 'therapist_allowed',
    ];

    protected $casts = [
        'is_active'         => 'boolean',
        'doctor_allowed'    => 'boolean',
        'therapist_allowed' => 'boolean',
    ];

    public function treatments()
    {
        return $this->hasMany(Treatment::class, 'treatment_category_id', 'treatment_category_id');
    }

    /**
     * Daftar tipe staff yang boleh menangani kategori ini.
     * Output: array berisi 'dokter' dan/atau 'terapis'.
     * Dipakai oleh form admin untuk membatasi opsi dropdown handler.
     */
    public function allowedHandlerTypes(): array
    {
        $types = [];
        if ($this->doctor_allowed)    $types[] = 'dokter';
        if ($this->therapist_allowed) $types[] = 'terapis';

        // Safety net: kalau dua-duanya ter-set false (kemungkinan input
        // admin yang tidak konsisten), izinkan dokter sebagai default.
        return $types ?: ['dokter'];
    }
}
