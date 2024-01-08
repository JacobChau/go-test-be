<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\QuestionType;
use App\Http\Resources\AssessmentResource;
use App\Models\Assessment;
use App\Models\AssessmentAttemptAnswer;
use DateTime;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

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
            // create assessment attempt
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

//{
//"attemptId": 16,
//"answers": [
//{
//"questionId": "20",
//"answer": [
//"42",
//"43",
//"44"
//]
//},
//{
//    "questionId": "31",
//      "answer": "85"
//    },
//{
//    "questionId": "6",
//      "answer": "123"
//    },
//{
//    "questionId": "9",
//      "answer": "23123"
//    },
//{
//    "questionId": "24",
//      "answer": "59"
//    },
//{
//    "questionId": "16",
//      "answer": [
//    "27",
//    "28"
//]
//    },
//{
//    "questionId": "28",
//      "answer": "75"
//    },
//{
//    "questionId": "17",
//      "answer": [
//    "31"
//]
//    }
//]
//}
    public function submit(array $data, string $id): array
    {
        DB::beginTransaction();
        try {
            $attempt = auth()->user()->assessmentAttempts()->find($data['attemptId']);

            if ($attempt === null) {
                return [
                    'message' => 'Assessment attempt not found',
                ];
            }

            $assessment = $this->getById((int)$id, ['questions.options']);

            $questions = $assessment->questions;

            $correctAnswers = 0;
            $totalMarks = 0;

            foreach ($data['answers'] as $answer) {
                $question = $questions->find($answer['questionId']);
                if ($question === null) {
                    continue;
                }

                // Store user answer
                $this->storeUserAnswer($attempt->id, $question->id, $answer['answer']);

                $correct = false;
                switch ($question->type) {
                    case QuestionType::TrueFalse:
                    case QuestionType::MultipleChoice:
                        $correct = $this->checkSingleAnswer($question, $answer['answer']);
                        break;
                    case QuestionType::MultipleAnswer:
                        $answerArray = is_array($answer['answer']) ? $answer['answer'] : [$answer['answer']];
                        $correct = $this->checkMultipleAnswers($question, $answerArray);
                        break;
                    case QuestionType::FillIn:
                        $correct = $this->checkFillInAnswer($question, $answer['answer']);
                        break;
                    case QuestionType::Text:
                        $correct = true; // Assuming all text answers are correct
                        break;
                }

                if ($correct) {
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
            'correctAnswers' => $correctAnswers,
            'totalMarks' => $totalMarks,
            'totalQuestions' => $questions->count(),
        ];
    }

    private function storeUserAnswer($attemptId, $questionId, $answer)
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
