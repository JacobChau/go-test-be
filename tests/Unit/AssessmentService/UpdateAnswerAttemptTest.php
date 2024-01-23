<?php

namespace Tests\Unit\AssessmentService;

use App\Enums\QuestionType;
use App\Enums\ResultDisplayMode;
use App\Models\Assessment;
use App\Models\AssessmentAttempt;
use App\Models\Question;
use App\Services\AssessmentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UpdateAnswerAttemptTest extends TestCase
{
    use RefreshDatabase;

    private AssessmentService $assessmentService;

    public function setUp(): void
    {
        parent::setUp();
        $this->assessmentService = $this->app->make(AssessmentService::class);
    }

    public function testUpdateAnswerAttemptCreatesNewAnswerIfNotExist(): void
    {
        // Arrange
        $assessment = Assessment::factory()->create();
        $attempt = AssessmentAttempt::factory()->create([
            'assessment_id' => $assessment->id,
        ]);
        $question = Question::factory()->create();
        $assessment->questions()->attach($question->id, [
            'marks' => 1,
            'order' => 0,
        ]);

        $assessmentQuestionId = $assessment->questions->first()->pivot->id;

        // Act
        $result = $this->assessmentService->updateAnswerAttempt((string) $assessment->id, (string) $attempt->id, (string) $assessmentQuestionId, ['marks' => 1, 'comment' => 'Good answer']);

        // Assert
        $this->assertEquals(1, $result['data']['marks']);
        $this->assertEquals('Good answer', $result['data']['comment']);
        $this->assertEquals('Assessment answer updated successfully.', $result['message']);
    }

    public function testUpdateAnswerAttemptUpdatesExistingAnswer(): void
    {
        // Arrange
        $assessment = Assessment::factory()->create();
        $attempt = AssessmentAttempt::factory()->create([
            'assessment_id' => $assessment->id,
        ]);
        $question = Question::factory()->create(['type' => QuestionType::Text]);
        $assessment->questions()->attach($question->id, [
            'marks' => 1,
            'order' => 0,
        ]);

        $assessmentQuestionId = $assessment->questions->first()->pivot->id;

        $correctAnswer = $question->options()->createMany([
            ['answer' => 'Option 1', 'is_correct' => true],
            ['answer' => 'Option 2', 'is_correct' => false],
            ['answer' => 'Option 3', 'is_correct' => false],
            ['answer' => 'Option 4', 'is_correct' => false],
        ])->where('is_correct', true)->first()->id;

        $attempt->answers()->create([
            'assessment_question_id' => $assessmentQuestionId,
            'marks' => 1,
            'answer_comment' => 'Good answer',
            'answer_content' => $correctAnswer,
        ]);

        // Act
        $result = $this->assessmentService->updateAnswerAttempt((string) $assessment->id, (string) $attempt->id, (string) $assessmentQuestionId, ['marks' => 2, 'comment' => 'Great answer']);

        // Assert
        $this->assertEquals(2, $result['data']['marks']);
        $this->assertEquals('Great answer', $result['data']['comment']);
        $this->assertEquals('Assessment answer updated successfully.', $result['message']);
    }

    public function testUpdateAnswerAttemptUpdatesTotalMarks(): void
    {
        // Arrange
        $assessment = Assessment::factory()->create(['result_display_mode' => ResultDisplayMode::DisplayMarkAndAnswers, 'total_marks' => 1, 'required_mark' => 1]);
        $attempt = AssessmentAttempt::factory()->create([
            'assessment_id' => $assessment->id,
        ]);
        $question = Question::factory()->create(['type' => QuestionType::Text]);
        $assessment->questions()->attach($question->id, ['order' => 0, 'marks' => 1]);

        $assessmentQuestionId = $assessment->questions->first()->pivot->id; // Correct way to get pivot id

        $correctAnswer = $question->options()->createMany([
            ['answer' => 'Option 1', 'is_correct' => true],
            ['answer' => 'Option 2', 'is_correct' => false],
            ['answer' => 'Option 3', 'is_correct' => false],
            ['answer' => 'Option 4', 'is_correct' => false],
        ])->where('is_correct', true)->first()->id;

        $attempt->answers()->create([
            'assessment_question_id' => $assessmentQuestionId,
            'marks' => 1,
            'answer_content' => $correctAnswer,
            'answer_comment' => 'Good answer',
        ]);

        // Act
        $result = $this->assessmentService->updateAnswerAttempt((string) $assessment->id, (string) $attempt->id, (string) $assessmentQuestionId, ['marks' => 2, 'comment' => 'Great answer']);

        // Assert
        $this->assertEquals(2, $result['data']['marks']);
        $this->assertEquals('Great answer', $result['data']['comment']);
        $this->assertEquals('Assessment answer updated successfully.', $result['message']);
    }
}
