<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pasien extends Model
{
    protected $table = 'pasien';
    protected $primaryKey = 'pasien_id';

    protected $fillable = [
        'user_id',
        'no_rekam_medis',
        'is_member',
        'member_since',
        'foto_profil_path',
        'alamat',
        'kota',
        'provinsi',
        'kode_pos',
    ];

    protected $casts = [
        'is_member' => 'boolean',
        'member_since' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
