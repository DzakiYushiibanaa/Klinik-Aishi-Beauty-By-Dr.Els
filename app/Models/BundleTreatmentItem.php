<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BundleTreatmentItem extends Model
{
    protected $primaryKey = 'bundle_treatment_item_id';
    protected $fillable = ['bundle_id','treatment_id','qty'];

    public function bundle()
    {
        return $this->belongsTo(Bundle::class, 'bundle_id', 'bundle_id');
    }

    public function treatment()
    {
        return $this->belongsTo(Treatment::class, 'treatment_id', 'treatment_id');
    }
}
