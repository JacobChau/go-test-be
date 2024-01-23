<?php

namespace App\Models;

use App\Traits\HasCreatedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Assessment extends Model
{
    use HasCreatedBy, HasFactory;

    protected $fillable = ['name', 'description', 'total_marks', 'pass_marks', 'max_attempts', 'is_published', 'thumbnail', 'duration', 'valid_from', 'valid_to', 'subject_id', 'result_display_mode', 'required_mark'];

    protected $casts = [
        'is_published' => 'boolean',
        'valid_from' => 'datetime',
        'valid_to' => 'datetime',
    ];

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function questions(): BelongsToMany
    {
        return $this->belongsToMany(Question::class, 'assessment_questions')
            ->using(AssessmentQuestion::class)->withTimestamps()->withPivot('id', 'marks', 'order');
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(AssessmentAttempt::class);
    }

    public function groups(): BelongsToMany
    {
        return $this->belongsToMany(Group::class, 'assessment_groups')->withTimestamps();
    }

    // SCOPES
    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    public function scopeNotExpired($query)
    {
        return $query->where('valid_to', '>=', now());
    }

    public function scopeSubject($query, $subject_id)
    {
        if (! $subject_id) {
            return $query;
        }

        return $query->where('subject_id', $subject_id);
    }

    public function scopeIsTaken($query, $user_id)
    {
        return $query->whereHas('attempts', function ($query) use ($user_id) {
            $query->where('user_id', $user_id);
        });
    }

    public function scopeIsNotTaken($query, $user_id)
    {
        return $query->whereDoesntHave('attempts', function ($query) use ($user_id) {
            $query->where('user_id', $user_id);
        });
    }

    public function scopeHasDuration($query)
    {
        return $query->whereNotNull('duration');
    }

    public function scopeHasNoDuration($query)
    {
        return $query->whereNull('duration');
    }
}
