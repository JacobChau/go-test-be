<?php

namespace Tests\Unit\AssessmentService;

use App\Enums\ResultDisplayMode;
use App\Enums\UserRole;
use App\Http\Resources\UserResource;
use App\Models\Assessment;
use App\Models\AssessmentAttempt;
use App\Models\User;
use App\Services\AssessmentService;
use BenSampo\Enum\Exceptions\InvalidEnumMemberException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;
use TiMacDonald\JsonApi\JsonApiResource;

class GetResultsByAssessmentIdTest extends TestCase
{
    use RefreshDatabase;

    private AssessmentService $assessmentService;

    public function setUp(): void
    {
        parent::setUp();
        $this->assessmentService = $this->app->make(AssessmentService::class);
    }

    public function testGetResultsByAssessmentIdWhenNoAttempts()
    {
        // Arrange
        $assessment = Assessment::factory()->create();

        // Act
        $result = $this->assessmentService->getResultsByAssessmentId($assessment->id);

        // Assert
        $this->assertTrue($result['data']->isEmpty());
        $this->assertEquals('Assessment results retrieved successfully.', $result['message']);
    }

    public function testGetResultsByAssessmentIdWithAttempts(): void
    {
        // Arrange
        $assessment = Assessment::factory()->create();
        AssessmentAttempt::factory()->count(3)->create(['assessment_id' => $assessment->id]);
        User::factory()->count(3)->create(); // Assuming each attempt is by a different user

        // Act
        $result = $this->assessmentService->getResultsByAssessmentId($assessment->id);

        // Assert
        $this->assertEquals(3, $result['data']->count());
        $this->assertEquals('Assessment results retrieved successfully.', $result['message']);
    }
}
