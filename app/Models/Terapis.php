<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Terapis extends Model
{
    protected $table = 'terapis';
    protected $primaryKey = 'terapis_id';

    protected $fillable = [
        'user_id',
        'kode_terapis',
        'status_kerja',
        'foto_profil_path',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
