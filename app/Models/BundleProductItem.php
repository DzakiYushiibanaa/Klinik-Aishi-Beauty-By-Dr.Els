<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BundleProductItem extends Model
{
    protected $primaryKey = 'bundle_product_item_id';
    protected $fillable = ['bundle_id','product_id','qty'];

    public function bundle()
    {
        return $this->belongsTo(Bundle::class, 'bundle_id', 'bundle_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'product_id');
    }
}
