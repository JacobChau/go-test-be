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
     * @return void $questionId
     */
    public function createOptions(array $options, int $questionId): void
    {
        foreach ($options as $option) {
            $option = $this->create([
                'answer' => $option['answer'],
                'is_correct' => $option['isCorrect'],
                'question_id' => $questionId,
            ]);

            $this->mediaService->processAndSaveImages($option->answer, $option->id, QuestionOption::class);
        }
    }

    public function updateOrCreateOptions(array $options, int $questionId): void
    {
        foreach ($options as $option) {
            $option = $this->model->updateOrCreate([
                'id' => $option['id'],
                'question_id' => $questionId,
            ], [
                'answer' => $option['answer'],
                'is_correct' => $option['isCorrect'],
            ]);

            $this->mediaService->syncContentImages($option->answer, $option->id, QuestionOption::class);
        }
    }

    public function deleteOptions(array $options): void
    {
        foreach ($options as $option) {
            $this->delete($option['id']);
        }
    }

    //        $questionOptions = QuestionOption::with('question')->whereIn('question_id', $questions->pluck('id'))->get();

    public function getOptionsByQuestionIds(array $questionIds, array $with = []): object
    {
        return $this->getModel()->with($with)->questionIds($questionIds)->get();
    }
}
