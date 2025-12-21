<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    protected $table = 'order_items';
    protected $primaryKey = 'order_item_id';

    protected $fillable = [
        'order_id','appointment_id','product_id','treatment_id','bundle_id',
        'item_type','description','qty','harga_satuan','diskon','subtotal',
    ];

     protected $casts = [
        'qty' => 'integer',
        'harga_satuan' => 'integer',
        'diskon' => 'integer',
        'subtotal' => 'integer',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id', 'order_id');
    }

    public function appointment()
    {
        return $this->belongsTo(Appointment::class, 'appointment_id', 'appointment_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'product_id');
    }

    public function treatment()
    {
        return $this->belongsTo(Treatment::class, 'treatment_id', 'treatment_id');
    }

    public function bundle()
    {
        return $this->belongsTo(Bundle::class, 'bundle_id', 'bundle_id');
    }
}
