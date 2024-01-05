<?php

namespace App\Models;

use App\Traits\HasCreatedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Assessment extends Model
{
    use HasCreatedBy;

    protected $fillable = ['name', 'description', 'total_marks', 'pass_marks', 'max_attempts', 'is_published', 'thumbnail', 'duration', 'valid_from', 'valid_to', 'subject_id'];

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function questions(): BelongsToMany
    {
        return $this->belongsToMany(Question::class, 'assessment_questions')->withTimestamps()->withPivot('marks');
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(AssessmentAttempt::class);
    }
}
