<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class AssessmentQuestion extends Pivot
{
    protected $table = 'assessment_questions';

    public $incrementing = true;

    protected $primaryKey = 'id';

    public $timestamps = true;
}
