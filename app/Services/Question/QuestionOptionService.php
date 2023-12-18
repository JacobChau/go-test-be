<?php

declare(strict_types=1);

namespace App\Services\Question;

use App\Models\QuestionOption;
use App\Services\BaseService;
use App\Services\MediaService;

class QuestionOptionService extends BaseService
{
    protected MediaService $mediaService;
    public function __construct(QuestionOption $subject, MediaService $mediaService)
    {
        $this->model = $subject;
        $this->mediaService = $mediaService;
    }

    /**
     * Create question options.
     *
     * @param array $options
     * @param int $questionId
     */
    public function createOptions(array $options, int $questionId): void
    {
        foreach ($options as $option) {
            $this->mediaService->processImages($option['answer'], $questionId, QuestionOption::class);
            $this->create([
                'answer' => $option['answer'],
                'is_correct' => $option['isCorrect'],
                'question_id' => $questionId,
            ]);
        }
    }
}
