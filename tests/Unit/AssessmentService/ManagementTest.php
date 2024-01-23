<?php

use App\Enums\UserRole;
use App\Models\Assessment;
use App\Models\User;
use App\Services\AssessmentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class ManagementTest extends TestCase
{
    use RefreshDatabase;

    private AssessmentService $assessmentService;

    public function setUp(): void
    {
        parent::setUp();
        $this->assessmentService = $this->app->make(AssessmentService::class);
    }

    public function testManagementForAdmin(): void
    {
        // Arrange
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        Auth::shouldReceive('user')->andReturn($admin);
        Assessment::factory()->count(5)->create();

        // Act
        $result = $this->assessmentService->management();

        // Assert
        $this->assertEquals(5, $result['data']->count());
    }

    /**
     * @throws ReflectionException
     */
    public function testManagementForNonAdmin(): void
    {
        // Arrange
        $nonAdmin = User::factory()->create(['role' => UserRole::Student]);
        $otherUser = User::factory()->create(['role' => UserRole::Student]);

        Assessment::factory()->count(3)->create(['created_by' => $nonAdmin->id]); // 3 assessments created by the non-admin
        Assessment::factory()->count(2)->create(['created_by' => $otherUser->id]); // 2 assessments created by another user

        // Act
        $result = $this->actingAs($nonAdmin) // Authenticate as nonAdmin
            ->assessmentService->management();

        // Assert
        $this->assertEquals(3, $result['data']->count());
    }
}
