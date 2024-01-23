<?php

namespace Tests\Unit\QuestionExplanationService;

use App\Models\Question;
use App\Models\QuestionExplanation;
use App\Services\MediaService;
use App\Services\Question\QuestionExplanationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\Unit\BaseService\BaseServiceTest;

class QuestionExplanationTest extends BaseServiceTest
{
    use RefreshDatabase;

    private $mediaServiceMock;

    private QuestionExplanationService $questionExplanationService;

    public function setUp(): void
    {
        parent::setUp();
        $this->service = $this->app->make(QuestionExplanationService::class);
        $this->mediaServiceMock = Mockery::mock(MediaService::class);
        $this->app->instance(MediaService::class, $this->mediaServiceMock);
        $this->questionExplanationService = new QuestionExplanationService(new QuestionExplanation(), $this->mediaServiceMock);
    }

    public function testCreateExplanation(): void
    {
        // Arrange
        $explanationText = 'This is an explanation';
        $questionId = Question::factory()->create()->id;

        $this->mediaServiceMock->shouldReceive('processAndSaveImages')
            ->once()
            ->with($explanationText, Mockery::type('int'), QuestionExplanation::class)
            ->andReturnNull();

        // Act
        $this->questionExplanationService->createExplanation($explanationText, $questionId);

        // Assert
        $this->assertDatabaseHas('question_explanations', [
            'content' => $explanationText,
            'question_id' => $questionId,
        ]);
    }

    public function testUpdateExplanationIfIdIsNotNull(): void
    {
        // Arrange
        $question = Question::factory()->create();
        $explanationId = QuestionExplanation::factory()->create([
            'content' => 'This is an explanation',
            'question_id' => $question->id,
        ])->id;

        $explanation = 'This is an updated explanation';

        // Set expectations for media service
        $this->mediaServiceMock->shouldReceive('syncContentImages')
            ->once()
            ->with($explanation, Mockery::type('int'), QuestionExplanation::class)
            ->andReturnNull();

        // Act
        $this->questionExplanationService->updateOrCreateExplanation($question->id, $explanationId, $explanation);

        // Assert
        $this->assertDatabaseHas('question_explanations', [
            'content' => $explanation,
            'question_id' => $question->id,
        ]);
    }
}
