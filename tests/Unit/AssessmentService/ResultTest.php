<?php

namespace Tests\Unit\AssessmentService;

use App\Models\Assessment;
use App\Models\AssessmentAttempt;
use App\Models\User;
use App\Services\AssessmentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class ResultTest extends TestCase
{
    use RefreshDatabase;

    private AssessmentService $assessmentService;

    public function setUp(): void
    {
        parent::setUp();
        $this->assessmentService = $this->app->make(AssessmentService::class);
    }

    public function testResultsWhenNoAttempts()
    {
        // Arrange
        $user = User::factory()->create();
        Auth::shouldReceive('user')->andReturn($user);

        // Act
        $result = $this->assessmentService->results();

        // Assert
        $this->assertTrue($result['data']->isEmpty());
        $this->assertEquals('Assessment results retrieved successfully.', $result['message']);
    }

    public function testResultsWithAttempts(): void
    {
        // Arrange
        $user = User::factory()->create();
        Auth::shouldReceive('user')->andReturn($user);

        $assessment = Assessment::factory()->create(['required_mark' => true]);
        $notRequiredAssessment = Assessment::factory()->create(['required_mark' => false]);
        $attempt = AssessmentAttempt::factory()->create(['user_id' => $user->id, 'assessment_id' => $assessment->id]);
        $notRequiredAttempt = AssessmentAttempt::factory()->create(['user_id' => $user->id, 'assessment_id' => $notRequiredAssessment->id]);

        // Act
        $result = $this->assessmentService->results();

        // Assert
        $this->assertEquals(1, $result['data']->count());
        $this->assertEquals($attempt->id, $result['data'][0]['id']);
        $this->assertNotContains($notRequiredAttempt->id, $result['data']->pluck('id'));
        $this->assertEquals('Assessment results retrieved successfully.', $result['message']);
    }
}
