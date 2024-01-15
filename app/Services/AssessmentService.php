<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\PaginationSetting;
use App\Enums\QuestionType;
use App\Enums\UserRole;
use App\Http\Resources\AssessmentDetailResource;
use App\Http\Resources\AssessmentResource;
use App\Http\Resources\AssessmentResultResource;
use App\Http\Resources\QuestionOptionResource;
use App\Models\Assessment;
use App\Models\AssessmentAttemptAnswer;
use App\Models\QuestionOption;
use App\Services\Question\QuestionOptionService;
use BenSampo\Enum\Exceptions\InvalidEnumMemberException;
use DateTime;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class AssessmentService extends BaseService
{
    protected QuestionOptionService $questionOptionService;
    public function __construct(
        Assessment $assessment,
        QuestionOptionService $questionOptionService
    ) {
        $this->model = $assessment;
        $this->questionOptionService = $questionOptionService;
    }

    /**
     * @throws Exception
     */
    public function create(array $data): Assessment
    {
        try {
            DB::beginTransaction();

            $assessment = $this->model->create([
                'name' => $data['name'],
                'subject_id' => $data['subjectId'],
                'description' => $data['description'],
                'duration' => $data['duration'],
                'pass_marks' => $data['passMarks'],
                'total_marks' => $data['totalMarks'],
                'max_attempts' => $data['maxAttempts'],
                'valid_from' => $data['validFrom'] ? (new DateTime($data['validFrom']))->format('Y-m-d H:i:s') : null,
                'valid_to' => $data['validTo'] ? (new DateTime($data['validTo']))->format('Y-m-d H:i:s') : null,
                'is_published' => $data['isPublished'],
            ]);

            foreach ($data['questions'] as $question) {
                $assessment->questions()->attach($question['id'], ['marks' => $question['marks'], 'order' => $question['order']]);
            }

            foreach ($data['groupIds'] as $groupId) {
                $assessment->groups()->attach($groupId);
            }

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }

        return $assessment;
    }

    public function getList(?string $resourceClass = null, array $input = [], ?Builder $query = null, array $relations = []): array
    {
        $query = $query ?? $this->model->query();
        if (auth()->user()->role === UserRole::Admin) {
            return parent::getList($resourceClass, $input, $query, $relations);
        }

        $query->published()->notExpired();

        $userGroupIds = auth()->user()->groups->pluck('id')->toArray();

        $query->whereHas('groups', function ($query) use ($userGroupIds) {
            $query->whereIn('groups.id', $userGroupIds);
        });

        return parent::getList($resourceClass, $input, $query, $relations);
    }

    public function update(int $id, array $data): void
    {
        try {
            DB::beginTransaction();

            $assessment = $this->model->find($id);
            $assessment->update([
                'name' => $data['name'],
                'subject_id' => $data['subjectId'],
                'description' => $data['description'],
                'duration' => $data['duration'],
                'pass_marks' => $data['passMarks'],
                'total_marks' => $data['totalMarks'],
                'max_attempts' => $data['maxAttempts'],
                'valid_from' => $data['validFrom'] ? (new DateTime($data['validFrom']))->format('Y-m-d H:i:s') : null,
                'valid_to' => $data['validTo'] ? (new DateTime($data['validTo']))->format('Y-m-d H:i:s') : null,
                'is_published' => $data['isPublished'],
            ]);

            $assessment->questions()->detach();
            foreach ($data['questions'] as $question) {
                $assessment->questions()->attach($question['id'], ['marks' => $question['marks'], 'order' => $question['order']]);
            }

            $assessment->groups()->detach();
            foreach ($data['groupIds'] as $groupId) {
                $assessment->groups()->attach($groupId);
            }

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }


    public function getQuestions(string $id): Collection
    {
        $assessment = $this->model->with('questions.options')->find($id);

        return $assessment->questions;
    }

    public function attempt(string $id): array
    {
        // check assessment attempt of user with max attempt of assessment
        // if max attempt of assessment is null, then user can start assessment
        $assessment = $this->getById((int) $id);
        $user = auth()->user();

        if ($assessment->max_attempts === null) {
            $attempt = $user->assessmentAttempts()->create([
                'assessment_id' => $id,
            ]);

            return [
                'canStart' => true,
                'attemptId' => $attempt->id,
                'message' => 'You can start this assessment',
            ];
        }

        $count = $user->assessmentAttempts()
            ->where('assessment_id', $id)
            ->count();

        if ($count < $assessment->max_attempts) {
            $attempt = $user->assessmentAttempts()->create([
                'assessment_id' => $id,
            ]);

            return [
                'canStart' => true,
                'attemptId' => $attempt->id,
                'message' => 'You can start this assessment',
            ];
        }

        return [
            'canStart' => false,
            'attemptId' => null,
            'message' => 'You have reached the maximum number of attempts for this assessment',
        ];
    }

    public function submit(array $data, string $id): array
    {
        DB::beginTransaction();
        try {
            $attempt = auth()->user()->assessmentAttempts()->find($data['attemptId']);

            if ($attempt === null) {
                return [
                    'message' => 'Assessment attempt not found',
                    'status' => Response::HTTP_NOT_FOUND,
                ];
            }

            $assessment = $this->getById((int)$id, ['questions.options']);
            $questions = $assessment->questions;

            $totalMarks = 0;
            $correctAnswers = 0;

            foreach ($data['answers'] as $answer) {
                $question = $questions->find($answer['questionId']);
                if ($question === null) {
                    continue;
                }

                $this->storeUserAnswer($attempt->id, $question->id, $answer['answer']);

                if ($this->checkAnswer($question, $answer['answer'])) {
                    $totalMarks += $question->pivot->marks;
                    $correctAnswers++;
                }
            }

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }

        return [
            'data' => [
                'totalMarks' => $totalMarks,
                'correctAnswers' => $correctAnswers,
                'totalQuestions' => $questions->count(),
            ],
            'message' => 'Assessment submitted successfully.',
        ];
    }

    public function resultDetail(string $id, string $attemptId): array
    {
        $attempt = auth()->user()->assessmentAttempts()->find($attemptId);

        if ($attempt === null) {
            return [
                'status' => Response::HTTP_NOT_FOUND,
                'message' => 'Assessment attempt not found',
            ];
        }

        $assessment = $this->getById((int)$id, ['questions.options', 'questions.explanation']);
        $questions = $assessment->questions;
        $questionOptions = $this->questionOptionService->getOptionsByQuestionIds($questions->pluck('id')->toArray(), ['question']);

        $score = 0;
        $totalCorrect = 0;
        $answers = [];

        foreach ($questions as $question) {
            $userAnswer = $attempt->answers->where('assessment_question_id', $question->id)->first();
            $correct = false;
            $answer = null;
            $mark = $question->pivot->marks;

            if ($userAnswer !== null) {
                $answer = $this->formatAnswerBasedOnQuestionType($question, $userAnswer->answer_content);
                $correct = $this->checkAnswer($question, $answer);
            }

            if ($correct) {
                $score += $mark;
                $totalCorrect++;
            }

            $options = $questionOptions->where('question_id', $question->id);
            $answers[] = $this->formatQuestionResult($question, $options, $answer, $correct, $mark);
        }

        return [
            'data' => [
                'id' => $attempt->id,
                'name' => $assessment->name,
                'score' => $score,
                'totalCorrect' => $totalCorrect,
                'totalMarks' => $assessment->total_marks,
                'totalQuestions' => $questions->count(),
                'questions' => $answers,
            ],
            'message' => 'Assessment result retrieved successfully.',
        ];
    }

    public function results(): array
    {
        $result = auth()->user()->assessmentAttempts()
            ->with('assessment')
            ->orderBy(request()->get('orderBy', PaginationSetting::ORDER_BY), request()->get('orderDir', PaginationSetting::ORDER_DIRECTION))
            ->paginate(request()->get('perPage', PaginationSetting::PER_PAGE));

        $items = AssessmentResultResource::collection($result->getCollection());

        return [
            'data' => $items,
            'meta' => [
                'total' => $result->total(),
                'perPage' => $result->perPage(),
                'currentPage' => $result->currentPage(),
                'lastPage' => $result->lastPage(),
            ],
            'message' => 'Assessment results retrieved successfully.',
        ];
    }

    public function management(): array
    {
        $query = $query ?? $this->model->query();

        if (auth()->user()->role !== UserRole::Admin) {
            $query->where('created_by', auth()->id());
        }

        return parent::getList(AssessmentDetailResource::class, request()->all(), $query);
    }

    private function checkAnswer($question, $answer): bool
    {
        return match ($question->type) {
            QuestionType::TrueFalse, QuestionType::MultipleChoice => $this->checkSingleAnswer($question, $answer),
            QuestionType::MultipleAnswer => $this->checkMultipleAnswers($question, is_array($answer) ? $answer : [$answer]),
            QuestionType::FillIn => $this->checkFillInAnswer($question, $answer),
            QuestionType::Text => true,
            default => false,
        };
    }

    private function formatAnswerBasedOnQuestionType($question, $answer): int|array|null|string
    {
        switch ($question->type) {
            case QuestionType::TrueFalse:
            case QuestionType::MultipleChoice:
                return (int)$answer;
            case QuestionType::MultipleAnswer:
                $answer = json_decode($answer, true);
                return array_map('intval', $answer);
            case QuestionType::Text:
            case QuestionType::FillIn:
                return $answer;
            default:
                return null;
        }
    }

    private function formatCorrectAnswerBasedOnQuestionType($question, $correctAnswers): int|array|null|string
    {
        return match ($question->type) {
            QuestionType::TrueFalse, QuestionType::MultipleChoice => $correctAnswers->pluck('id')->first(),
            QuestionType::MultipleAnswer => $correctAnswers->pluck('id')->toArray(),
            QuestionType::Text, QuestionType::FillIn => $correctAnswers->pluck('answer')->first(),
            default => null,
        };
    }

    /**
     * @throws InvalidEnumMemberException
     */
    private function formatQuestionResult($question, $options, $answer, $correct, $mark): array
    {
        return [
            'id' => $question->id,
            'content' => $question->content,
            'type' => QuestionType::getKey($question->type),
            'options' => QuestionOptionResource::collection($options),
            'userAnswer' => $answer,
            'correctAnswer' => $this->formatCorrectAnswerBasedOnQuestionType($question, $options->where('is_correct', true)),
            'isCorrect' => $correct,
            'marks' => $mark,
            'explanation' => $question->explanation ? $question->explanation->content : null,
        ];
    }


    private function storeUserAnswer($attemptId, $questionId, $answer): void
    {
        $userAnswer = new AssessmentAttemptAnswer;
        $userAnswer->assessment_attempt_id = $attemptId;
        $userAnswer->assessment_question_id = $questionId;

        if (is_array($answer)) {
            $userAnswer->answer_content = json_encode($answer);
        } else {
            $userAnswer->answer_content = $answer;
        }

        $userAnswer->save();
    }

    private function checkSingleAnswer($question, $answer): bool
    {
        return $question->options->contains('id', $answer) && $question->options->find($answer)->is_correct;
    }

    private function checkMultipleAnswers($question, $answers): bool
    {
        $correctOptions = $question->options->where('is_correct', true)->pluck('id')->sort();
        $selectedOptions = collect($answers)->sort();
        return $selectedOptions->count() === $correctOptions->count() && $selectedOptions->diff($correctOptions)->isEmpty();
    }

    private function checkFillInAnswer($question, $answer): bool
    {
        return $question->options->contains('answer', $answer) && $question->options->where('answer', $answer)->first()->is_correct;
    }
}
