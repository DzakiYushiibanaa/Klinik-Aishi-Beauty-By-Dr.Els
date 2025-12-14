<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bundle extends Model
{
    protected $primaryKey = 'bundle_id';
    protected $fillable = [
        'kode','nama','jenis','deskripsi','foto_path',
        'harga_normal','harga_promo','is_member_only','is_active'
    ];

    public function productItems()
    {
        return $this->hasMany(BundleProductItem::class, 'bundle_id', 'bundle_id');
    }

    public function treatmentItems()
    {
        return $this->hasMany(BundleTreatmentItem::class, 'bundle_id', 'bundle_id');
    }
}
