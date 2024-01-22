<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssessmentAttemptAnswer extends Model
{
    protected $fillable = ['assessment_attempt_id', 'assessment_question_id', 'answer_content', 'question_option_id', 'marks', 'answer_comment'];

    public function attempt(): BelongsTo
    {
        return $this->belongsTo(AssessmentAttempt::class);
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(AssessmentQuestion::class, 'assessment_question_id');
    }

    public function option(): BelongsTo
    {
        return $this->belongsTo(QuestionOption::class, 'question_option_id');
    }
}
