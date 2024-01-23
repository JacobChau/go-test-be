<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class QuestionOption extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['question_id', 'answer', 'is_correct'];

    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }

    public function media(): MorphMany
    {
        return $this->morphMany(Media::class, 'mediable');
    }

    public function scopeQuestion($query, $question_id)
    {
        if (! $question_id) {
            return $query;
        }

        return $query->where('question_id', $question_id);
    }

    public function scopeQuestionIds($query, $question_ids)
    {
        if (! $question_ids) {
            return $query;
        }

        return $query->whereIn('question_id', $question_ids);
    }
}
