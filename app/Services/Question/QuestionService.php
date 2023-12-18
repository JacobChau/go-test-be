<?php

declare(strict_types=1);

namespace App\Services\Question;

use App\Models\Question;
use App\Services\BaseService;
use App\Services\PassageService;
use App\Services\SubjectService;
use Illuminate\Support\Facades\DB;

class QuestionService extends BaseService
{
    protected QuestionExplanationService $explanationService;
    protected QuestionOptionService $questionOptionService;
    protected PassageService $passageService;
    protected SubjectService $subjectService;
    public function __construct(Question $subject, QuestionExplanationService $explanationService, QuestionOptionService $questionOptionService, PassageService $passageService, SubjectService $subjectService)
    {
        $this->model = $subject;
        $this->explanationService = $explanationService;
        $this->questionOptionService = $questionOptionService;
        $this->passageService = $passageService;
        $this->subjectService = $subjectService;
    }

    public function create(array $data): Question
    {
        DB::beginTransaction();
        $question = $this->model->create([
            'content' => $data['content'],
            'type' => $data['type'],
            'category_id' => $data['categoryId'],
        ]);

        if (isset($data['explanation'])) {
            $this->explanationService->create([
                'content' => $data['explanation'],
                'question_id' => $question->id,
            ]);
        }

        if (isset($data['passageId'])) {
            $this->passageService->create([
                'passage_id' => $data['passageId'],
                'question_id' => $question->id,
            ]);
        }

        if (isset($data['options']) && count($data['options']) > 0) {
            foreach ($data['options'] as $option) {
                $this->questionOptionService->create([
                    'answer' => $option['answer'],
                    'is_correct' => $option['isCorrect'],
                    'question_id' => $question->id,
                ]);
            }
        }

        DB::commit();

        return $question;
    }
}
