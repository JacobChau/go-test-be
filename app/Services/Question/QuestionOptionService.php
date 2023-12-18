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
     */
    public function createOptions(array $options): void
    {
        foreach ($options as $option) {
            $option = $this->create([
                'answer' => $option['answer'],
                'is_correct' => $option['isCorrect'],
            ]);

            $this->mediaService->processImages($option->answer, $option->id, QuestionOption::class);
        }
    }
}
