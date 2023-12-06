<?php

namespace App\Models;

use App\Traits\HasCreatedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Question extends Model
{
    use HasCreatedBy;

    protected $fillable = ['content', 'type', 'media_url', 'media_type', 'is_published', 'passage_id'];

    public function passage(): BelongsTo
    {
        return $this->belongsTo(Passage::class);
    }

    public function explanation(): HasOne
    {
        return $this->hasOne(QuestionExplanation::class);
    }

    public function options(): HasMany
    {
        return $this->hasMany(QuestionOption::class);
    }

    public function assessments(): BelongsToMany
    {
        return $this->belongsToMany(Assessment::class, 'assessment_questions')->withTimestamps()->withPivot('marks');
    }
}
