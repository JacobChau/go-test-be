<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuestionOption extends Model
{
    protected $fillable = ['question_id', 'answer', 'media_url', 'media_type', 'is_correct'];

    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }
}