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
     */
    public function createExplanation(string $explanation, int $questionId): void
    {
        $explanation = $this->create([
            'content' => $explanation,
            'question_id' => $questionId,
        ]);

        $this->mediaService->processAndSaveImages($explanation->content, $explanation->id, QuestionExplanation::class);

    }

    public function updateOrCreateExplanation(int $questionId, ?int $explanationId, ?string $content): void
    {
        // if id is null, create, if id is not null, update, if id is not null but content is empty, delete
        if ($explanationId !== null && $content === null) {
            $this->delete($explanationId);
            $this->mediaService->deleteMedia(QuestionExplanation::class, $explanationId);

            return;
        }

        $explanation = $this->model->updateOrCreate([
            'id' => $explanationId,
            'question_id' => $questionId,
        ], [
            'content' => $content,
        ]);

        $this->mediaService->syncContentImages($explanation->content, $explanation->id, QuestionExplanation::class);

    }
}
