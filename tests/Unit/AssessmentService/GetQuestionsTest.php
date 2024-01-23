<?php

namespace Tests\Unit\AssessmentService;

use App\Models\Assessment;
use App\Models\Question;
use App\Services\AssessmentService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Tests\TestCase;

class GetQuestionsTest extends TestCase
{
    use RefreshDatabase;

    private AssessmentService $assessmentService;

    public function setUp(): void
    {
        parent::setUp();
        $this->assessmentService = $this->app->make(AssessmentService::class);
    }

    public function testGetQuestionsSuccessfully(): void
    {
        $assessment = Assessment::factory()->create();
        $questions = Question::factory()->count(5)->create();

        foreach ($questions as $index => $question) {
            $assessment->questions()->attach($question->id, ['order' => $index]);
        }

        $result = $this->assessmentService->getQuestions($assessment->id);

        $this->assertInstanceOf(Collection::class, $result);
        $this->assertEquals($questions->pluck('id'), $result->pluck('id'));
    }

    public function testGetQuestionsWithInvalidAssessmentId(): void
    {
        $this->expectException(ModelNotFoundException::class);
        $this->expectExceptionMessage('Invalid ID provided');
        $this->assessmentService->getQuestions('invalid');
    }

    public function testGetQuestionsWithNonExistingAssessmentId(): void
    {
        $this->expectException(ModelNotFoundException::class);
        $this->expectExceptionMessage('Assessment not found');
        $this->assessmentService->getQuestions(-1);
    }
}
