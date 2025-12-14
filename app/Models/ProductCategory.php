<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductCategory extends Model
{
    protected $primaryKey = 'product_category_id';
    protected $fillable = ['nama', 'deskripsi', 'is_active'];

    public function products()
    {
        return $this->hasMany(Product::class, 'product_category_id', 'product_category_id');
    }
}
