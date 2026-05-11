<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes;

    protected $primaryKey = 'product_id';
    protected $fillable = [
        'product_category_id','nama','deskripsi','harga','berat_gram',
        'stok','tanggal_kadaluarsa','foto_path','is_active'
    ];

    protected $casts = [
        'deleted_at' => 'datetime',
    ];

    public function getRouteKeyName()
    {
        return 'product_id';
    }

    public function category()
    {
        return $this->belongsTo(ProductCategory::class, 'product_category_id', 'product_category_id');
    }
}
