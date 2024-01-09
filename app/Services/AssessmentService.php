<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\QuestionType;
use App\Http\Resources\AssessmentResource;
use App\Http\Resources\QuestionOptionResource;
use App\Models\Assessment;
use App\Models\AssessmentAttemptAnswer;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class AssessmentService extends BaseService
{
    public function __construct(
        Assessment $assessment,
    ) {
        $this->model = $assessment;
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
        $relations = [];
        $query = $this->getModel()->query();

        return parent::getList(AssessmentResource::class, request()->all(), $query, $relations);
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

    /**
     * Create a new assessment with associated data.
     *
     * @param  array  $data Data for creating an assessment.
     *
     * @throws Exception
     */
    public function create(array $data): Assessment
    {
        try {
            DB::beginTransaction();
            $assessment = $this->model->create([
                'content' => $data['content'],
                'type' => $data['type'],
                'category_id' => $data['categoryId'],
                'passage_id' => $data['passageId'] ?? null,
            ]);

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }

        return $assessment;
    }

//    public function submit(array $data, string $id): array
//    {
//        DB::beginTransaction();
//        try {
//            $attempt = auth()->user()->assessmentAttempts()->find($data['attemptId']);
//
//            if ($attempt === null) {
//                return [
//                    'message' => 'Assessment attempt not found',
//                ];
//            }
//
//            $assessment = $this->getById((int)$id, ['questions.options']);
//
//            $questions = $assessment->questions;
//
//            $correctAnswers = 0;
//            $totalMarks = 0;
//
//            foreach ($data['answers'] as $answer) {
//                $question = $questions->find($answer['questionId']);
//                if ($question === null) {
//                    continue;
//                }
//
//                $this->storeUserAnswer($attempt->id, $question->id, $answer['answer']);
//
//                $correct = false;
//                switch ($question->type) {
//                    case QuestionType::TrueFalse:
//                    case QuestionType::MultipleChoice:
//                        $correct = $this->checkSingleAnswer($question, $answer['answer']);
//                        break;
//                    case QuestionType::MultipleAnswer:
//                        $answerArray = is_array($answer['answer']) ? $answer['answer'] : [$answer['answer']];
//                        $correct = $this->checkMultipleAnswers($question, $answerArray);
//                        break;
//                    case QuestionType::FillIn:
//                        $correct = $this->checkFillInAnswer($question, $answer['answer']);
//                        break;
//                    case QuestionType::Text:
//                        $correct = true; // Assuming all text answers are correct
//                        break;
//                }
//
//                if ($correct) {
//                    $totalMarks += $question->pivot->marks;
//                    $correctAnswers++;
//                }
//            }
//
//            $attempt->update([
//                'total_marks' => $totalMarks,
//            ]);
//
//            DB::commit();
//        } catch (Exception $e) {
//            DB::rollBack();
//            throw $e;
//        }
//
//        return [
//            'correctAnswers' => $correctAnswers,
//            'totalMarks' => $totalMarks,
//            'totalQuestions' => $questions->count(),
//        ];
//    }

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

            $correctAnswers = 0;

            foreach ($data['answers'] as $answer) {
                $question = $questions->find($answer['questionId']);
                if ($question === null) {
                    continue;
                }

                $this->storeUserAnswer($attempt->id, $question->id, $answer['answer']);

                if ($this->checkAnswer($question, $answer['answer'])) {
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
                'correctAnswers' => $correctAnswers,
                'totalQuestions' => $questions->count(),
            ],
            'message' => 'Assessment submitted successfully.',
        ];
    }


//    public function resultDetail(string $id, string $attemptId): array
//    {
//        $attempt = auth()->user()->assessmentAttempts()->find($attemptId);
//
//        if ($attempt === null) {
//            return [
//                'status' => Response::HTTP_NOT_FOUND,
//                'message' => 'Assessment attempt not found',
//            ];
//        }
//
//        $assessment = $this->getById((int)$id, ['questions.options']);
//
//        $questions = $assessment->questions;
//
//        $score = 0;
//        $totalCorrect = 0;
//        $totalMarks = 0;
//        $answers = [];
//
//        foreach ($questions as $question) {
//            $userAnswer = $attempt->answers->where('assessment_question_id', $question->id)->first();
//            $correct = false;
//            $answer = null;
//            $mark = $question->pivot->marks;
//            if ($userAnswer !== null) {
//                $answer = $userAnswer->answer_content;
//                // convert answer to int if question type is the option type
//                switch ($question->type) {
//                    case QuestionType::TrueFalse:
//                    case QuestionType::MultipleChoice:
//                        $correct = $this->checkSingleAnswer($question, $answer);
//                        $answer = (int) $answer;
//                        break;
//                    case QuestionType::MultipleAnswer:
//                        $answer = json_decode($answer, true);
//                        $answer = array_map(function ($item) {
//                            return (int) $item;
//                        }, $answer);
//
//                        $correct = $this->checkMultipleAnswers($question, $answer);
//                        break;
//                    case QuestionType::FillIn:
//                        $correct = $this->checkFillInAnswer($question, $answer);
//                        break;
//                    case QuestionType::Text:
//                        $correct = true;
//                        break;
//                }
//            }
//
//            if ($correct) {
//                $score += $mark;
//                $totalCorrect++;
//            }
//
//            $totalMarks += $mark;
//
//            $answers[] = [
//                'id' => $question->id,
//                'content' => $question->content,
//                'type' => QuestionType::getKey($question->type),
//                'options' => QuestionOptionResource::collection($question->options),
//                'userAnswer' => $answer,
//                'correctAnswer' => $question->options->where('is_correct', true)->pluck('id')->toArray(),
//                'isCorrect' => $correct,
//                'marks' => $mark,
//                'explanation' => $question->explanation->content,
//            ];
//        }
//
//        return [
//            'id' => $attempt->id,
//            'name' => $assessment->name,
//            'score' => $score,
//            'totalCorrect' => $totalCorrect,
//            'totalMarks' => $totalMarks,
//            'totalQuestions' => $questions->count(),
//            'questions' => $answers,
//        ];
//    }

    public function resultDetail(string $id, string $attemptId): array
    {
        $attempt = auth()->user()->assessmentAttempts()->find($attemptId);

        if ($attempt === null) {
            return [
                'status' => Response::HTTP_NOT_FOUND,
                'message' => 'Assessment attempt not found',
            ];
        }

        $assessment = $this->getById((int)$id, ['questions.options']);
        $questions = $assessment->questions;

        $score = 0;
        $totalCorrect = 0;
        $totalMarks = 0;
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

            $totalMarks += $mark;

            $answers[] = $this->formatQuestionResult($question, $answer, $correct, $mark);
        }

        return [
            'data' => [
                'id' => $attempt->id,
                'name' => $assessment->name,
                'score' => $score,
                'totalCorrect' => $totalCorrect,
                'totalMarks' => $totalMarks,
                'totalQuestions' => $questions->count(),
                'questions' => $answers,
            ],
            'message' => 'Assessment result retrieved successfully.',
        ];
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

    private function formatAnswerBasedOnQuestionType($question, $answer)
    {
        return match ($question->type) {
            QuestionType::TrueFalse, QuestionType::MultipleChoice => (int)$answer,
            QuestionType::MultipleAnswer => json_decode($answer, true),
            QuestionType::Text, QuestionType::FillIn => $answer,
            default => null,
        };
    }

    private function formatQuestionResult($question, $answer, $correct, $mark): array
    {
        return [
            'id' => $question->id,
            'content' => $question->content,
            'type' => QuestionType::getKey($question->type),
            'options' => QuestionOptionResource::collection($question->options),
            'userAnswer' => $answer,
            'correctAnswer' => $question->options->where('is_correct', true)->pluck('id')->toArray(),
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
