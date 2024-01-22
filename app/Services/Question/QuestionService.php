<?php

declare(strict_types=1);

namespace App\Services\Question;

use App\Enums\QuestionType;
use App\Enums\UserRole;
use App\Http\Resources\QuestionResource;
use App\Models\Question;
use App\Services\BaseService;
use App\Services\MediaService;
use App\Services\PassageService;
use App\Services\SubjectService;
use Exception;
use Illuminate\Database\Eloquent\Builder;
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
     * @param  array  $data Data for creating a question.
     *
     * @throws Exception
     */
    public function create(array $data): Question
    {
        try {
            DB::beginTransaction();
            $question = $this->model->create([
                'content' => $data['content'],
                'type' => $data['type'],
                'category_id' => $data['categoryId'],
                'passage_id' => $data['passageId'] ?? null,
            ]);

            $this->mediaService->processAndSaveImages($data['content'], $question->id, Question::class);

            if (isset($data['explanation'])) {
                $this->explanationService->createExplanation($data['explanation'], $question->id);
            }

            if (isset($data['options']) && count($data['options']) > 0) {
                $this->optionService->createOptions($data['options'], $question->id);
            }

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }

        return $question;
    }

    public function getList(?string $resourceClass = null, array $input = [], ?Builder $query = null, array $relations = []): array
    {
        $relations = ['category'];
        $query = $this->getModel()->query();

        if (auth()->user()->role !== UserRole::Admin) {
            $query->where('created_by', auth()->id());
        }

        if (isset($input['filters'])) {
            if (isset($input['filters']['category'])) {
                if (strtolower($input['filters']['category']) === 'all') {
                    unset($input['filters']['category']);
                } else {
                    $query->category($input['filters']['category']);
                }
            }

            if (isset($input['filters']['type'])) {
                // if question type is All, don't filter by type
                if (strtolower($input['filters']['type']) === 'all') {
                    unset($input['filters']['type']);
                } else {
                    $input['filters']['type'] = QuestionType::getValue($input['filters']['type']);
                }

                if (isset($input['filters']['type'])) {
                    $query->type($input['filters']['type']);
                }
            }
        }

        return parent::getList(QuestionResource::class, request()->all(), $query, $relations);
    }

    /**
     * @throws Exception
     */
    public function update(int $id, array $data): void
    {
        try {
            DB::beginTransaction();
            $question = $this->model->findOrFail($id);
            $question->update([
                'content' => $data['content'],
                'type' => $data['type'],
                'category_id' => $data['categoryId'],
                'passage_id' => $data['passageId'] ?? null,
            ]);

            // check if question has already images, if yes, delete them and save new ones
            $this->mediaService->syncContentImages($data['content'], $question->id, Question::class);

            if (isset($data['explanation'])) {
                $this->explanationService->updateOrCreateExplanation($question->id, $data['explanation']['id'], $data['explanation']['content']);
            }

            // Delete all options if question type is changed to text
            if ($question->type !== QuestionType::Text && $data['type'] === QuestionType::Text) {
                $this->optionService->deleteOptions($question->id);
            }

            if (isset($data['options']) && count($data['options']) > 0) {
                $this->optionService->updateOrCreateOptions($data['options'], $question->id);
            }

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
