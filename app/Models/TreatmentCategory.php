<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TreatmentCategory extends Model
{
    protected $primaryKey = 'treatment_category_id';
    protected $fillable = ['nama', 'deskripsi', 'is_active'];

    public function treatments()
    {
        return $this->hasMany(Treatment::class, 'treatment_category_id', 'treatment_category_id');
    }
}
