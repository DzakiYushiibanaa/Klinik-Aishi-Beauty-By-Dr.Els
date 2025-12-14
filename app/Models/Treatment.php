<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Treatment extends Model
{
    protected $primaryKey = 'treatment_id';
    protected $fillable = [
        'treatment_category_id','nama','deskripsi','harga',
        'durasi_menit','foto_path','is_active'
    ];

    public function category()
    {
        return $this->belongsTo(TreatmentCategory::class, 'treatment_category_id', 'treatment_category_id');
    }
}
