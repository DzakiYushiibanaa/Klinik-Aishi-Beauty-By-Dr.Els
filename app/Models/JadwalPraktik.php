<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JadwalPraktik extends Model
{
    protected $table = 'jadwal_praktiks';
    protected $primaryKey = 'jadwal_praktik_id';

    protected $fillable = [
        'user_id','jenis_layanan','hari','jam_mulai','jam_selesai',
        'durasi_slot_menit','is_active'
    ];

    public function tenagaMedis()
    {
        return $this->belongsTo(User::class, 'user_id', 'id'); // ubah kalau PK users = user_id
    }
}
