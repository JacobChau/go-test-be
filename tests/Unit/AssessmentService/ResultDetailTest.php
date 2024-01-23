<?php

namespace Tests\Unit\AssessmentService;

use App\Enums\QuestionType;
use App\Enums\ResultDisplayMode;
use App\Models\Assessment;
use App\Models\AssessmentAttempt;
use App\Models\Question;
use App\Models\QuestionCategory;
use App\Models\User;
use App\Services\AssessmentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class ResultDetailTest extends TestCase
{
    use RefreshDatabase;

    private AssessmentService $assessmentService;

    public function setUp(): void
    {
        parent::setUp();
        $this->assessmentService = $this->app->make(AssessmentService::class);
    }

    public function testResultDetailWithForbiddenAccess(): void
    {
        // Arrange
        $assessment = Assessment::factory()->create(['result_display_mode' => ResultDisplayMode::HideResults]);
        $user = User::factory()->create();
        $anotherUser = User::factory()->create(); // User who is not the owner of the assessment
        $attempt = AssessmentAttempt::factory()->create(['assessment_id' => $assessment->id, 'user_id' => $anotherUser->id]);
        Auth::shouldReceive('user')->andReturn($user);
        Auth::shouldReceive('id')->andReturn($user->id);

        // Act
        $result = $this->assessmentService->resultDetail((string) $assessment->id, (string) $attempt->id);

        // Assert
        $this->assertEquals(Response::HTTP_FORBIDDEN, $result['status']);
        $this->assertEquals('You do not have permission to view this assessment result.', $result['message']);
    }

    public function testResultDetailWithNotFoundAttempt(): void
    {
        // Arrange
        $assessment = Assessment::factory()->create(['result_display_mode' => ResultDisplayMode::DisplayMarkAndAnswers]);
        $user = User::factory()->create();
        Auth::shouldReceive('user')->andReturn($user);

        // Act
        $result = $this->assessmentService->resultDetail((string) $assessment->id, '1');

        // Assert
        $this->assertEquals(Response::HTTP_NOT_FOUND, $result['status']);
        $this->assertEquals('Assessment attempt not found', $result['message']);
    }

    public function testResultDetailSuccessWithMultipleChoice(): void
    {
        // Arrange
        $assessment = Assessment::factory()->create(['result_display_mode' => ResultDisplayMode::DisplayMarkAndAnswers, 'total_marks' => 1, 'required_mark' => 1]);
        $user = User::factory()->create();
        Auth::shouldReceive('user')->andReturn($user);

        $category = QuestionCategory::factory()->create();
        $attempt = AssessmentAttempt::factory()->create(['assessment_id' => $assessment->id, 'user_id' => $user->id]);
        $question = Question::factory()->create(['category_id' => $category->id, 'type' => QuestionType::MultipleChoice]);

        $question->options()->createMany([
            ['answer' => 'Option 1', 'is_correct' => true],
            ['answer' => 'Option 2', 'is_correct' => false],
            ['answer' => 'Option 3', 'is_correct' => false],
            ['answer' => 'Option 4', 'is_correct' => false],
        ]);

        $assessment->questions()->attach($question->id, ['order' => 0, 'marks' => 1]);

        $assessmentQuestion = $assessment->questions->first()->pivot;

        $correctAnswer = $question->options()->where('is_correct', true)->first()->id;

        $attempt->answers()->create([
            'assessment_question_id' => $assessmentQuestion->id,
            'answer_content' => $correctAnswer,
            'marks' => 1,
        ]);

        // Assert
        $result = $this->assessmentService->resultDetail((string) $assessment->id, (string) $attempt->id);

        $this->assertEquals('Assessment result retrieved successfully.', $result['message']);
        $this->assertEquals($attempt->id, $result['data']['id']);
        $this->assertEquals($assessment->name, $result['data']['name']);
        $this->assertEquals(1, $result['data']['score']);
        $this->assertEquals(1, $result['data']['totalCorrect']);
        $this->assertEquals($assessment->total_marks, $result['data']['totalMarks']);
        $this->assertEquals($assessment->questions->count(), $result['data']['totalQuestions']);
        $this->assertEquals($assessment->created_by, $result['data']['ownerId']);
        $this->assertEquals($assessment->required_mark, $result['data']['requiredMark']);
        $this->assertEquals($user->id, $result['data']['user']['id']);
        $this->assertEquals($user->name, $result['data']['user']['name']);
        $this->assertEquals($user->email, $result['data']['user']['email']);
        $this->assertEquals($user->avatar, $result['data']['user']['avatar']);
        $this->assertEquals($attempt->marked, $result['data']['marked']);
        $this->assertCount(1, $result['data']['questions']);
        $this->assertEquals($question->id, $result['data']['questions'][0]['id']);
        $this->assertEquals($question->content, $result['data']['questions'][0]['content']);
        $this->assertEquals(QuestionType::getKey($question->type), $result['data']['questions'][0]['type']);
        $this->assertCount(4, $result['data']['questions'][0]['options']);
        $this->assertEquals($question->options()->where('is_correct', true)->first()->id, $result['data']['questions'][0]['correctAnswer']);
        $this->assertEquals($question->options()->where('is_correct', true)->first()->id, $result['data']['questions'][0]['userAnswer']);
        $this->assertTrue($result['data']['questions'][0]['isCorrect']);
        $this->assertEquals(null, $result['data']['questions'][0]['explanation']);
        $this->assertEquals(null, $result['data']['questions'][0]['comment']);
    }

    public function testResultDetailSuccessWithMultipleAnswer(): void
    {
        // Arrange
        $assessment = Assessment::factory()->create(['result_display_mode' => ResultDisplayMode::DisplayMarkAndAnswers, 'total_marks' => 2, 'required_mark' => true]);
        $user = User::factory()->create();
        Auth::shouldReceive('user')->andReturn($user);

        $category = QuestionCategory::factory()->create();
        $attempt = AssessmentAttempt::factory()->create(['assessment_id' => $assessment->id, 'user_id' => $user->id]);
        $question = Question::factory()->create(['category_id' => $category->id, 'type' => QuestionType::MultipleAnswer]);

        $question->options()->createMany([
            ['answer' => 'Option 1', 'is_correct' => true],
            ['answer' => 'Option 2', 'is_correct' => true],
            ['answer' => 'Option 3', 'is_correct' => false],
            ['answer' => 'Option 4', 'is_correct' => false],
        ]);

        $assessment->questions()->attach($question->id, ['order' => 0, 'marks' => 2]);

        $assessmentQuestion = $assessment->questions->first()->pivot;

        $correctAnswer = $question->options()->where('is_correct', true)->pluck('id')->sort();

        $attempt->answers()->create([
            'assessment_question_id' => $assessmentQuestion->id,
            'answer_content' => json_encode($correctAnswer),
            'marks' => 2,
        ]);

        // Assert
        $result = $this->assessmentService->resultDetail((string) $assessment->id, (string) $attempt->id);

        $this->assertEquals('Assessment result retrieved successfully.', $result['message']);
        $this->assertEquals($attempt->id, $result['data']['id']);
        $this->assertEquals($assessment->name, $result['data']['name']);
        $this->assertEquals(2, $result['data']['score']);
        $this->assertEquals(1, $result['data']['totalCorrect']);
        $this->assertEquals($assessment->total_marks, $result['data']['totalMarks']);
        $this->assertEquals($assessment->questions->count(), $result['data']['totalQuestions']);
        $this->assertEquals($assessment->created_by, $result['data']['ownerId']);
        $this->assertEquals($assessment->required_mark, $result['data']['requiredMark']);
        $this->assertEquals($user->id, $result['data']['user']['id']);
        $this->assertEquals($user->name, $result['data']['user']['name']);
        $this->assertEquals($user->email, $result['data']['user']['email']);
        $this->assertEquals($user->avatar, $result['data']['user']['avatar']);
        $this->assertEquals($attempt->marked, $result['data']['marked']);
        $this->assertCount(1, $result['data']['questions']);
        $this->assertEquals($question->id, $result['data']['questions'][0]['id']);
        $this->assertEquals($question->content, $result['data']['questions'][0]['content']);
    }

    //    private function checkMultipleAnswers($question, $answers): bool
    //    {
    //        $correctOptions = $question->options->where('is_correct', true)->pluck('id')->sort();
    //        $selectedOptions = collect($answers)->sort();
    //        return $selectedOptions->count() === $correctOptions->count() && $selectedOptions->diff($correctOptions)->isEmpty();
    //    }

    public function testResultDetailSuccessWithText(): void
    {
        // Arrange
        $assessment = Assessment::factory()->create(['result_display_mode' => ResultDisplayMode::DisplayMarkAndAnswers, 'total_marks' => 2, 'required_mark' => true]);
        $user = User::factory()->create();
        Auth::shouldReceive('user')->andReturn($user);

        $category = QuestionCategory::factory()->create();
        $attempt = AssessmentAttempt::factory()->create(['assessment_id' => $assessment->id, 'user_id' => $user->id]);
        $question = Question::factory()->create(['category_id' => $category->id, 'type' => QuestionType::Text]);

        $assessment->questions()->attach($question->id, ['order' => 0, 'marks' => 2]);

        $assessmentQuestion = $assessment->questions->first()->pivot;

        $attempt->answers()->create([
            'assessment_question_id' => $assessmentQuestion->id,
            'answer_content' => 'Answer',
            'marks' => 2,
        ]);

        // Act
        $result = $this->assessmentService->resultDetail((string) $assessment->id, (string) $attempt->id);

        // Assert
        $this->assertEquals('Assessment result retrieved successfully.', $result['message']);
        $this->assertEquals($attempt->id, $result['data']['id']);
        $this->assertEquals($assessment->name, $result['data']['name']);
        $this->assertEquals(2, $result['data']['score']);
        $this->assertEquals(0, $result['data']['totalCorrect']);
        $this->assertEquals($assessment->total_marks, $result['data']['totalMarks']);
        $this->assertEquals($assessment->questions->count(), $result['data']['totalQuestions']);
        $this->assertEquals($assessment->created_by, $result['data']['ownerId']);
    }

    public function testResultDetailSuccessWithFillIn(): void
    {
        // Arrange
        $assessment = Assessment::factory()->create(['result_display_mode' => ResultDisplayMode::DisplayMarkAndAnswers, 'total_marks' => 2, 'required_mark' => true]);
        $user = User::factory()->create();
        Auth::shouldReceive('user')->andReturn($user);

        $category = QuestionCategory::factory()->create();
        $attempt = AssessmentAttempt::factory()->create(['assessment_id' => $assessment->id, 'user_id' => $user->id]);
        $question = Question::factory()->create(['category_id' => $category->id, 'type' => QuestionType::FillIn]);

        $question->options()->create([
            'answer' => 'Answer',
            'is_correct' => true,
        ]);

        $assessment->questions()->attach($question->id, ['order' => 0, 'marks' => 2]);

        $assessmentQuestion = $assessment->questions->first()->pivot;

        $attempt->answers()->create([
            'assessment_question_id' => $assessmentQuestion->id,
            'answer_content' => 'Answer',
            'marks' => 2,
        ]);

        // Assert
        $result = $this->assessmentService->resultDetail((string) $assessment->id, (string) $attempt->id);

        $this->assertEquals('Assessment result retrieved successfully.', $result['message']);
        $this->assertEquals($attempt->id, $result['data']['id']);
        $this->assertEquals($assessment->name, $result['data']['name']);
        $this->assertEquals(2, $result['data']['score']);
        $this->assertEquals(1, $result['data']['totalCorrect']);
        $this->assertEquals($assessment->total_marks, $result['data']['totalMarks']);
        $this->assertEquals($assessment->questions->count(), $result['data']['totalQuestions']);
        $this->assertEquals($assessment->created_by, $result['data']['ownerId']);
    }
}
