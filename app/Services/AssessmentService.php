<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\PaginationSetting;
use App\Enums\QuestionType;
use App\Enums\ResultDisplayMode;
use App\Enums\UserRole;
use App\Http\Resources\AssessmentDetailResource;
use App\Http\Resources\AssessmentResultResource;
use App\Http\Resources\QuestionOptionResource;
use App\Mail\AssessmentPublished;
use App\Models\Assessment;
use App\Models\AssessmentAttempt;
use App\Models\AssessmentAttemptAnswer;
use App\Services\Question\QuestionOptionService;
use BenSampo\Enum\Exceptions\InvalidEnumMemberException;
use DateTime;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
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
                'duration' => $data['duration'] ?? null,
                'pass_marks' => $data['passMarks'] ?? null,
                'total_marks' => $data['totalMarks'] ?? null,
                'max_attempts' => $data['maxAttempts'] ?? null,
                'valid_from' => $data['validFrom'] ? (new DateTime($data['validFrom']))->format('Y-m-d H:i:s') : null,
                'valid_to' => $data['validTo'] ? (new DateTime($data['validTo']))->format('Y-m-d H:i:s') : null,
                'is_published' => $data['isPublished'],
                'required_mark' => $data['requiredMark'] ?? false,
                'result_display_mode' => $data['resultDisplayMode'] ?? null,
            ]);

            foreach ($data['questions'] as $question) {
                $marks = $assessment->required_mark ? $question['marks'] : null;
                $assessment->questions()->attach($question['id'], ['marks' => $marks, 'order' => $question['order']]);
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

        // Apply common filters for all roles
        if (isset($input['filters'])) {
            if (isset($input['filters']['isTaken']) && $input['filters']['isTaken'] === 'true') {
                $query->isTaken(auth()->id());
            }

            if (isset($input['filters']['hasDuration']) && $input['filters']['hasDuration'] === 'true') {
                $query->hasDuration();
            }
        }

        if (auth()->user()->role !== UserRole::Admin) {
            $query->published()->notExpired();

            if (! isset($input['filters']['isTaken'])) {
                $query->isNotTaken(auth()->id());
            }

            $userGroupIds = auth()->user()->groups->pluck('id')->toArray();
            $query->whereHas('groups', function ($query) use ($userGroupIds) {
                $query->whereIn('groups.id', $userGroupIds);
            });
        }

        $query->has('questions');

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
                'duration' => $data['duration'] ?? $assessment->duration,
                'pass_marks' => $data['passMarks'] ?? $assessment->pass_marks,
                'total_marks' => $data['totalMarks'] ?? $assessment->total_marks,
                'max_attempts' => $data['maxAttempts'] ?? $assessment->max_attempts,
                'valid_from' => $data['validFrom'] ? (new DateTime($data['validFrom']))->format('Y-m-d H:i:s') : null,
                'valid_to' => $data['validTo'] ? (new DateTime($data['validTo']))->format('Y-m-d H:i:s') : null,
                'is_published' => $data['isPublished'],
                'required_mark' => $data['requiredMark'] ?? $assessment->required_mark,
                'result_display_mode' => $data['resultDisplayMode'] ?? $assessment->result_display_mode,
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

    /**
     * @throws Exception
     */
    public function getQuestions(string $id): Collection
    {
        if (! is_numeric($id)) {
            throw new ModelNotFoundException('Invalid ID provided');
        }

        $assessment = $this->model->with('questions.options', 'questions.explanation', 'questions.category', 'questions.passage')->find($id);

        if ($assessment === null) {
            throw new ModelNotFoundException('Assessment not found');
        }

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
                throw new ModelNotFoundException('Assessment attempt not found');
            }

            $assessment = $this->getById((int) $id, ['questions.options']);
            $questions = $assessment->questions;

            $totalMarks = 0;
            $correctAnswers = 0;

            foreach ($data['answers'] as $answer) {
                $question = $questions->find($answer['questionId']);
                if ($question === null) {
                    continue;
                }

                $assessmentQuestionId = $question->pivot->id;
                $this->storeUserAnswer($attempt->id, $assessmentQuestionId, $answer['answer']);

                if ($this->checkAnswer($question, $answer['answer'])) {
                    $totalMarks += $question->pivot->marks;
                    $correctAnswers++;
                }
            }

            $attempt->update([
                'total_marks' => $totalMarks,
            ]);

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

    /**
     * @throws InvalidEnumMemberException
     */
    public function resultDetail(string $assessmentId, string $attemptId): array
    {
        $assessment = $this->getById((int) $assessmentId, ['questions.options', 'questions.explanation']);

        if ($assessment->result_display_mode !== ResultDisplayMode::DisplayMarkAndAnswers && $assessment->created_by !== auth()->id()) {
            return [
                'status' => Response::HTTP_FORBIDDEN,
                'message' => 'You do not have permission to view this assessment result.',
            ];
        }

        $attempt = AssessmentAttempt::with('answers')->find($attemptId);

        if ($attempt === null) {
            return [
                'status' => Response::HTTP_NOT_FOUND,
                'message' => 'Assessment attempt not found',
            ];
        }

        $questions = $assessment->questions;
        $questionOptions = $this->questionOptionService->getOptionsByQuestionIds($questions->pluck('id')->toArray(), ['question']);

        $score = 0;
        $totalCorrect = 0;
        $answers = [];

        foreach ($questions as $question) {
            $userAnswer = $attempt->answers->where('assessment_question_id', $question->pivot->id)->first();
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

            $userMarks = null;
            if ($question->type === QuestionType::Text) {
                $userMarks = $userAnswer ? $userAnswer->marks : null;
                $score += $userMarks ?? 0;
            }

            $options = $questionOptions->where('question_id', $question->id);
            $answers[] = $this->formatQuestionResult($question, $options, $answer, $correct, $userMarks, $userAnswer ? $userAnswer->answer_comment : null);
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
                'ownerId' => $assessment->created_by,
                'requiredMark' => $assessment->required_mark,
                'user' => [
                    'id' => $attempt->user->id,
                    'name' => $attempt->user->name,
                    'email' => $attempt->user->email,
                    'avatar' => $attempt->user->avatar,
                ],
                'marked' => $attempt->marked,
                'resultDisplayMode' => $assessment->result_display_mode ? ResultDisplayMode::getKey($assessment->result_display_mode) : null,
            ],
            'message' => 'Assessment result retrieved successfully.',
        ];
    }

    public function results(): array
    {
        $result = auth()->user()->assessmentAttempts()
            ->with('assessment')
            ->whereHas('assessment', function ($query) {
                $query->where('required_mark', true);
            })
            ->orderBy(request()->get('orderBy', 'created_at'), request()->get('orderDir', 'desc'))
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

    /**
     * @throws \ReflectionException
     */
    public function management(): array
    {
        $query = $query ?? $this->model->query();

        if (auth()->user()->role !== UserRole::Admin) {
            $query->where('created_by', auth()->id());
        }

        return parent::getList(AssessmentDetailResource::class, request()->all(), $query);
    }

    public function getResultsByAssessmentId(string $id): array
    {
        $result = $this->model->find($id)->attempts()
            ->with('user')
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

    public function updateAnswerAttempt(string $assessmentId, string $attemptId, string $assessmentQuestionId, array $data): array
    {
        $attemptAnswer = AssessmentAttemptAnswer::where('assessment_attempt_id', $attemptId)
            ->where('assessment_question_id', $assessmentQuestionId)
            ->first();

        if ($attemptAnswer === null) {
            $attemptAnswer = $this->storeUserAnswer($attemptId, $assessmentQuestionId, null);
        }

        $updateData = [];

        if (array_key_exists('marks', $data)) {
            $updateData['marks'] = $data['marks'];
        }

        if (array_key_exists('comment', $data)) {
            $updateData['answer_comment'] = $data['comment'];
        }

        if (! empty($updateData)) {
            $attemptAnswer->update($updateData);

            $attempt = AssessmentAttempt::find($attemptId);
            $questions = $attempt->assessment->questions;
            $attempt->update([
                'total_marks' => $questions->sum('marks') + $attempt->answers->sum('marks'),
            ]);
        }

        return [
            'data' => [
                'marks' => $attemptAnswer->marks,
                'comment' => $attemptAnswer->answer_comment ?? null,
            ],
            'message' => 'Assessment answer updated successfully.',
        ];

    }

    public function publishResult(string $assessmentId, string $attemptId): array
    {
        $attempt = AssessmentAttempt::find($attemptId);

        if ($attempt === null) {
            return [
                'status' => Response::HTTP_NOT_FOUND,
                'message' => 'Assessment attempt not found',
            ];
        }

        $assessment = $this->getById((int) $assessmentId);
        if ($assessment->result_display_mode === null) {
            return [
                'status' => Response::HTTP_BAD_REQUEST,
                'message' => 'Result display mode is not set',
            ];
        }

        $attempt->update([
            'marked' => true,
        ]);

        Mail::to($attempt->user->email)->send(new AssessmentPublished($attempt));

        return [
            'message' => 'Assessment result published successfully.',
        ];
    }

    private function checkAnswer($question, $answer): ?bool
    {
        return match ($question->type) {
            QuestionType::TrueFalse, QuestionType::MultipleChoice => $this->checkSingleAnswer($question, $answer),
            QuestionType::MultipleAnswer => $this->checkMultipleAnswers($question, is_array($answer) ? $answer : [$answer]),
            QuestionType::FillIn => $this->checkFillInAnswer($question, $answer),
            QuestionType::Text => null,
            default => false,
        };
    }

    private function formatAnswerBasedOnQuestionType($question, $answer): int|array|null|string
    {
        switch ($question->type) {
            case QuestionType::TrueFalse:
            case QuestionType::MultipleChoice:
                return (int) $answer;
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
    private function formatQuestionResult($question, $options, $answer, $correct, $userMarks, $comment): array
    {
        return [
            'id' => $question->id,
            'assessmentQuestionId' => $question->pivot->id,
            'content' => $question->content,
            'type' => QuestionType::getKey($question->type),
            'options' => QuestionOptionResource::collection($options),
            'userAnswer' => $answer,
            'correctAnswer' => $this->formatCorrectAnswerBasedOnQuestionType($question, $options->where('is_correct', true)),
            'isCorrect' => $correct,
            'marks' => $question->pivot->marks,
            'userMarks' => $userMarks,
            'explanation' => $question->explanation ? $question->explanation->content : null,
            'comment' => $comment,
        ];
    }

    private function storeUserAnswer($attemptId, $assessmentQuestionId, $answer): AssessmentAttemptAnswer
    {
        $userAnswer = new AssessmentAttemptAnswer;
        $userAnswer->assessment_attempt_id = $attemptId;
        $userAnswer->assessment_question_id = $assessmentQuestionId;

        if (is_array($answer)) {
            $userAnswer->answer_content = json_encode($answer);
        } else {
            $userAnswer->answer_content = $answer;
        }

        $userAnswer->save();

        return $userAnswer;
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
