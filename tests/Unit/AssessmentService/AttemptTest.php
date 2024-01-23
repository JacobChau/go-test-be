<?php

namespace Tests\Unit\AssessmentService;

use App\Models\Assessment;
use App\Models\AssessmentAttempt;
use App\Models\User;
use App\Services\AssessmentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class AttemptTest extends TestCase
{
    use RefreshDatabase;

    private AssessmentService $assessmentService;

    public function setUp(): void
    {
        parent::setUp();
        $this->assessmentService = $this->app->make(AssessmentService::class);
    }

    public function testAttemptWithNoMaxAttempts(): void
    {
        // Arrange
        $assessment = Assessment::factory()->create([
            'max_attempts' => null,
        ]);
        $user = User::factory()->create();
        Auth::shouldReceive('user')->andReturn($user);

        // Act
        $result = $this->assessmentService->attempt($assessment->id);

        // Assert
        $this->assertTrue($result['canStart']);
        $this->assertNotNull($result['attemptId']);
        $this->assertEquals('You can start this assessment', $result['message']);
    }

    public function testAttemptWithRemainingAttempts(): void
    {
        // Arrange
        $assessment = Assessment::factory()->create(['max_attempts' => 3]);
        $user = User::factory()->create();
        Auth::shouldReceive('user')->andReturn($user);
        AssessmentAttempt::factory()->count(1)->create([
            'user_id' => $user->id,
            'assessment_id' => $assessment->id,
        ]);

        // Act
        $result = $this->assessmentService->attempt($assessment->id);

        // Assert
        $this->assertTrue($result['canStart']);
        $this->assertNotNull($result['attemptId']);
        $this->assertEquals('You can start this assessment', $result['message']);
    }

    public function testAttemptWithMaxAttemptsReached(): void
    {
        // Arrange
        $assessment = Assessment::factory()->create(['max_attempts' => 3]);
        $user = User::factory()->create();
        Auth::shouldReceive('user')->andReturn($user);
        AssessmentAttempt::factory()->count(3)->create([
            'user_id' => $user->id,
            'assessment_id' => $assessment->id,
        ]);

        // Act
        $result = $this->assessmentService->attempt($assessment->id);

        // Assert
        $this->assertFalse($result['canStart']);
        $this->assertNull($result['attemptId']);
        $this->assertEquals('You have reached the maximum number of attempts for this assessment', $result['message']);
    }
}
