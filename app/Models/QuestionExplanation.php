<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuestionExplanation extends Model
{
    protected $fillable = ['content', 'media_url', 'media_type', 'question_id'];

    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }
}
