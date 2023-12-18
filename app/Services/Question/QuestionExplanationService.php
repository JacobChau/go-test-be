<?php

declare(strict_types=1);

namespace App\Services\Question;

use App\Models\QuestionExplanation;
use App\Services\BaseService;
use App\Services\MediaService;

class QuestionExplanationService extends BaseService
{
    protected MediaService $mediaService;
    public function __construct(QuestionExplanation $subject, MediaService $mediaService)
    {
        $this->model = $subject;
        $this->mediaService = $mediaService;
    }

    /**
     * Create question explanation.
     *
     * @param string $explanation
     * @param int $questionId
     */
    public function createExplanation(string $explanation, int $questionId): void
    {
        $this->mediaService->processImages($explanation, $questionId, QuestionExplanation::class);

        $this->create([
            'content' => $explanation,
            'question_id' => $questionId,
        ]);
    }
}
