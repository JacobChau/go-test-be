<?php

declare(strict_types=1);

namespace App\Services\Question;

use App\Enums\MediaType;
use App\Models\Question;
use App\Models\QuestionExplanation;
use App\Models\QuestionOption;
use App\Services\BaseService;
use App\Services\MediaService;
use App\Services\PassageService;
use App\Services\SubjectService;
use Exception;
use Illuminate\Support\Facades\DB;

class QuestionService extends BaseService
{
    protected QuestionExplanationService $explanationService;
    protected QuestionOptionService $optionService;
    protected PassageService $passageService;
    protected SubjectService $subjectService;
    protected MediaService $mediaService;

    public function __construct(
        Question $question,
        QuestionExplanationService $explanationService,
        QuestionOptionService $optionService,
        PassageService $passageService,
        SubjectService $subjectService,
        MediaService $mediaService
    ) {
        $this->model = $question;
        $this->explanationService = $explanationService;
        $this->optionService = $optionService;
        $this->passageService = $passageService;
        $this->subjectService = $subjectService;
        $this->mediaService = $mediaService;
    }

    /**
     * Create a new question with associated data.
     *
     * @param array $data Data for creating a question.
     * @return Question
     */
    public function create(array $data): Question
    {
        $question = $this->model->create([
            'content' => $data['content'],
            'type' => $data['type'],
            'category_id' => $data['categoryId'],
        ]);

        $this->mediaService->processImages($data['content'], $question->id, Question::class);

        if (isset($data['explanation'])) {
            $this->explanationService->createExplanation($data['explanation'], $question->id);
        }

        if (isset($data['passageId'])) {
            $this->passageService->create([
                'passage_id' => $data['passageId'],
                'question_id' => $question->id,
            ]);
        }

        if (isset($data['options']) && count($data['options']) > 0) {
            $this->optionService->createOptions($data['options']);
        }

        return $question;
    }
}
