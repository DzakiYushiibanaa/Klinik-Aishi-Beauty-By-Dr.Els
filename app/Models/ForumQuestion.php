<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ForumQuestion extends Model
{
    protected $table = 'forum_questions';
    protected $primaryKey = 'forum_question_id';

    protected $fillable = [
        'user_id',
        'title',
        'body',
        'is_anonymous',
        'status',
    ];

    protected $casts = [
        'is_anonymous' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function answers(): HasMany
    {
        return $this->hasMany(ForumAnswer::class, 'question_id', 'forum_question_id');
    }

    // Helper method untuk cek status
    public function isClosed(): bool
    {
        return $this->status === 'ditutup';
    }

    public function isOpen(): bool
    {
        return $this->status === 'dibuka';
    }
}
