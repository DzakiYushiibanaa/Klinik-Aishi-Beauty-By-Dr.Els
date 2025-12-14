<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $primaryKey = 'product_id';
    protected $fillable = [
        'product_category_id','nama','deskripsi','harga','berat_gram',
        'stok','tanggal_kadaluarsa','foto_path','is_active'
    ];

    public function category()
    {
        return $this->belongsTo(ProductCategory::class, 'product_category_id', 'product_category_id');
    }
}
