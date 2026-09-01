<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Apoteker extends Model
{
    protected $table = 'apotekers';
    protected $primaryKey = 'apoteker_id';

    protected $fillable = [
        'user_id',
        'kode_apoteker',
        'status_kerja',
        'foto_profil_path',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
