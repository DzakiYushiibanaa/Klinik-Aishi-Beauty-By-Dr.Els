<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Dokter extends Model
{
    protected $table = 'dokter';
    protected $primaryKey = 'dokter_id';

    protected $fillable = [
        'user_id',
        'kode_dokter',
        'spesialis',
        'pengalaman_tahun',
        'status_praktik',
        'foto_profil_path',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
