<?php

declare(strict_types=1);

namespace App\Services\Question;

use App\Enums\MediaType;
use App\Models\Question;
use App\Services\BaseService;
use App\Services\MediaService;
use App\Services\PassageService;
use App\Services\SubjectService;
use Exception;
use Illuminate\Support\Facades\DB;

class QuestionService extends BaseService
{
    protected QuestionExplanationService $explanationService;
    protected QuestionOptionService $questionOptionService;
    protected PassageService $passageService;
    protected SubjectService $subjectService;
    protected MediaService $mediaService;

    public function __construct(
        Question $question,
        QuestionExplanationService $explanationService,
        QuestionOptionService $questionOptionService,
        PassageService $passageService,
        SubjectService $subjectService,
        MediaService $mediaService
    ) {
        $this->model = $question;
        $this->explanationService = $explanationService;
        $this->questionOptionService = $questionOptionService;
        $this->passageService = $passageService;
        $this->subjectService = $subjectService;
        $this->mediaService = $mediaService;
    }

    /**
     * @throws Exception
     */
    public function create(array $data): Question
    {
        DB::beginTransaction();
        try {
            $question = $this->model->create([
                'content' => $data['content'],
                'type' => $data['type'],
                'category_id' => $data['categoryId'],
            ]);

            if (preg_match_all('/<img.*?src="([^"]+)"/', $data['content'], $matches)) {
                // $matches[1] is an array of all src attributes from img tags
                foreach ($matches[1] as $imageUrl) {
                    $this->mediaService->create([
                        'url' => $imageUrl,
                        'type' => MediaType::Image,
                        'mediable_id' => $question->id,
                        'mediable_type' => Question::class,
                    ]);
                }
            }
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

        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
        return $question;
    }
}
