<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $table = 'orders';
    protected $primaryKey = 'order_id';

    protected $fillable = [
        'user_id','kode_order','promotion_id','order_type','sumber','dibuat_oleh_id',
        'status','payment_method','payment_channel','payment_ref','midtrans_order_id','snap_token',
        'subtotal','diskon_total','ongkir','grand_total','total_weight_gram',
        'shipping_method','shipping_address','shipping_waybill','shipping_status','paid_at','expired_at',
    ];

    protected $casts = [
        'subtotal'     => 'integer',
        'diskon_total' => 'integer',
        'ongkir'       => 'integer',
        'grand_total'  => 'integer',
        'paid_at'      => 'datetime',
        'expired_at'   => 'datetime',
    ];

    public function items()
    {
        return $this->hasMany(OrderItem::class, 'order_id', 'order_id');
    }

    public function pasien()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function dibuatOleh()
    {
        return $this->belongsTo(User::class, 'dibuat_oleh_id', 'id');
    }

    public function promotion()
    {
        return $this->belongsTo(Promotion::class, 'promotion_id', 'promotion_id');
    }
}
