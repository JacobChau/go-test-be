<?php

use App\Models\Assessment;
use App\Models\AssessmentAttempt;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\User;
use App\Services\AssessmentService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SubmitTest extends TestCase
{
    use RefreshDatabase;

    private AssessmentService $assessmentService;

    public function setUp(): void
    {
        parent::setUp();
        $this->assessmentService = $this->app->make(AssessmentService::class);
    }

    public function testSubmitWithAttemptNotFound(): void
    {
        // Arrange
        $user = User::factory()->create();
        Auth::shouldReceive('user')->andReturn($user);
        $data = [
            'attemptId' => 999, // Assuming this attempt does not exist
            'answers' => [],
        ];

        $assessmentId = '1'; // Dummy assessment ID

        // Act

        $this->expectException(ModelNotFoundException::class);
        $this->expectExceptionMessage('Assessment attempt not found');
        $this->assessmentService->submit($data, $assessmentId);
    }

    public function testSubmitSuccessWithCorrectAnswer(): void
    {
        // Arrange
        $assessment = Assessment::factory()->create();
        $user = User::factory()->create();
        Auth::shouldReceive('user')->andReturn($user);
        Db::shouldReceive('beginTransaction')->once();
        DB::shouldReceive('commit')->once();
        DB::shouldReceive('rollBack')->never();
        $attempt = AssessmentAttempt::factory()->create([
            'user_id' => $user->id,
            'assessment_id' => $assessment->id,
        ]);
        $question = Question::factory()->create();
        $questionOptions = QuestionOption::factory()->count(4)->create([
            'question_id' => $question->id,
            'is_correct' => false,
        ]);
        $correctOption = $questionOptions->first();
        $correctOption->update([
            'is_correct' => true,
        ]);

        $assessment->questions()->attach($question->id, ['order' => 0, 'marks' => 1]);

        $data = [
            'attemptId' => $attempt->id,
            'answers' => [
                [
                    'questionId' => $question->id,
                    'answer' => $correctOption->id,
                ],
            ],
        ];

        $assessmentId = (string) $assessment->id;

        // Act
        $result = $this->assessmentService->submit($data, $assessmentId);

        // Assert
        $this->assertEquals('Assessment submitted successfully.', $result['message']);
        $this->assertEquals([
            'totalMarks' => 1,
            'correctAnswers' => 1,
            'totalQuestions' => 1,
        ], $result['data']);
    }

    public function testSubmitSuccessWithArrayAnswers(): void
    {
        // Arrange
        $assessment = Assessment::factory()->create();
        $user = User::factory()->create();
        Auth::shouldReceive('user')->andReturn($user);
        Db::shouldReceive('beginTransaction')->once();
        DB::shouldReceive('commit')->once();
        DB::shouldReceive('rollBack')->never();
        $attempt = AssessmentAttempt::factory()->create([
            'user_id' => $user->id,
            'assessment_id' => $assessment->id,
        ]);
        $questions = Question::factory()->count(3)->create();

        // get the index
        $data = ['attemptId' => $attempt->id, 'answers' => []];
        foreach ($questions as $index => $question) {
            $questionOptions = QuestionOption::factory()->count(4)->create([
                'question_id' => $question->id,
                'is_correct' => false,
            ]);
            $correctOption = $questionOptions->first();
            $correctOption->update([
                'is_correct' => true,
            ]);
            $assessment->questions()->attach($question->id, ['order' => $index, 'marks' => 1]);

            $data['answers'][] = [
                'questionId' => $question->id,
                'answer' => $correctOption->id,
            ];
        }

        $assessmentId = (string) $assessment->id;

        // Act
        $result = $this->assessmentService->submit($data, $assessmentId);

        // Assert
        $this->assertEquals('Assessment submitted successfully.', $result['message']);
        $this->assertEquals([
            'totalMarks' => 3,
            'correctAnswers' => 3,
            'totalQuestions' => 3,
        ], $result['data']);
    }
}
