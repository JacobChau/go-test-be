<?php

use App\Enums\ResultDisplayMode;
use App\Mail\AssessmentPublished;
use App\Models\Assessment;
use App\Models\AssessmentAttempt;
use App\Models\User;
use App\Services\AssessmentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class PublishResultTest extends TestCase
{
    use RefreshDatabase;

    private AssessmentService $assessmentService;

    public function setUp(): void
    {
        parent::setUp();
        $this->assessmentService = $this->app->make(AssessmentService::class);
    }

    public function testPublishResultWhenAttemptNotFound(): void
    {
        // Arrange
        $nonExistingAttemptId = 9999;

        // Act
        $result = $this->assessmentService->publishResult('1', (string) $nonExistingAttemptId);
        // Assert
        $this->assertEquals(Response::HTTP_NOT_FOUND, $result['status']);
        $this->assertEquals('Assessment attempt not found', $result['message']);
    }

    public function testPublishResultSuccessfully(): void
    {
        // Arrange
        Mail::fake();
        $user = User::factory()->create();
        $assessment = Assessment::factory()->create(['result_display_mode' => ResultDisplayMode::DisplayMarkAndAnswers]);
        $attempt = AssessmentAttempt::factory()->create(['assessment_id' => $assessment->id, 'user_id' => $user->id, 'marked' => true]);

        // Act
        $result = $this->assessmentService->publishResult((string) $assessment->id, (string) $attempt->id);

        // Assert
        $this->assertNotNull($attempt->refresh(), 'Attempt should be found in database');
        $this->assertTrue((bool) $attempt->marked, 'Attempt should be marked');
        $this->assertEquals('Assessment result published successfully.', $result['message']);

        Mail::assertQueued(AssessmentPublished::class, function ($mail) use ($user, $assessment) {
            return $mail->hasTo($user->email) &&
                $mail->attempt->assessment_id === $assessment->id; // Access assessment_id through the attempt relationship
        });
    }

    public function testPublishResultWhenResultDisplayModeIsNotSet(): void
    {
        // Arrange
        Mail::fake();
        $assessment = Assessment::factory()->create(['result_display_mode' => null]);
        $attempt = AssessmentAttempt::factory()->create(['assessment_id' => $assessment->id]);

        // Act
        $result = $this->assessmentService->publishResult((string) $assessment->id, (string) $attempt->id);

        // Assert
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $result['status']);
        $this->assertEquals('Result display mode is not set', $result['message']);

        $attempt->refresh();
        $this->assertFalse($attempt->marked);

        Mail::assertNothingSent();
    }
}
