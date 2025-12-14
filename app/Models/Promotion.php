<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Promotion extends Model
{
    protected $primaryKey = 'promotion_id';
    protected $fillable = [
        'kode','nama','deskripsi','jenis','min_subtotal',
        'diskon_nominal','diskon_persen','start_date','end_date','is_active'
    ];
}
