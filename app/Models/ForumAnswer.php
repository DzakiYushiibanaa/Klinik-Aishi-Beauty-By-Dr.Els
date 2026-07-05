<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ForumAnswer extends Model
{
    protected $table = 'forum_answers';
    protected $primaryKey = 'forum_answer_id';

    protected $fillable = [
        'question_id',
        'dokter_id',
        'body',
    ];

    public function question(): BelongsTo
    {
        return $this->belongsTo(ForumQuestion::class, 'question_id', 'forum_question_id');
    }

    public function dokter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dokter_id', 'id');
    }
}
