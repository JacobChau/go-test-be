<?php

namespace Tests\Unit\AssessmentService;

use App\Enums\QuestionType;
use App\Enums\ResultDisplayMode;
use App\Models\Assessment;
use App\Models\Group;
use App\Models\Question;
use App\Models\QuestionCategory;
use App\Models\Subject;
use App\Services\AssessmentService;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class CreateTest extends TestCase
{
    use RefreshDatabase;

    private AssessmentService $assessmentService;

    public function setUp(): void
    {
        parent::setUp();
        $this->assessmentService = $this->app->make(AssessmentService::class);
    }

    #[DataProvider('testCreateAssessmentSuccessProvider')]
    public function testCreateAssessmentSuccessfully(array &$data): void
    {
        $subject = Subject::factory()->create();
        $questionCategory = QuestionCategory::factory()->create();

        $question1 = Question::factory()->create([
            'category_id' => $questionCategory->id,
            'type' => QuestionType::MultipleChoice,
        ]);

        $question2 = Question::factory()->create([
            'category_id' => $questionCategory->id,
            'type' => QuestionType::MultipleChoice,
        ]);

        $group = Group::factory()->create();

        $data['questions'] = [
            [
                'id' => $question1->id,
                'marks' => 10,
                'order' => 1,
            ],
            [
                'id' => $question2->id,
                'marks' => 10,
                'order' => 2,
            ],
        ];

        $data['subjectId'] = $subject->id;
        $data['groupIds'] = [$group->id];

        DB::shouldReceive('beginTransaction')->once();
        DB::shouldReceive('commit')->once();

        DB::shouldReceive('rollback')->never();

        $result = $this->assessmentService->create($data);

        $this->assertInstanceOf(Assessment::class, $result);

        $this->assertEquals($data['name'], $result->name);
        $this->assertEquals($data['subjectId'], $result->subject_id);
        $this->assertEquals($data['description'], $result->description);
        $this->assertEquals($data['duration'], $result->duration);
        $this->assertEquals($data['totalMarks'], $result->total_marks);
        $this->assertEquals($data['passMarks'], $result->pass_marks);
        $this->assertEquals($data['maxAttempts'], $result->max_attempts);
        $this->assertEquals($data['validFrom'], $result->valid_from->format('Y-m-d'));
        $this->assertEquals($data['validTo'], $result->valid_to->format('Y-m-d'));
        $this->assertEquals($data['isPublished'], $result->is_published);
        $this->assertEquals($data['requiredMark'], $result->required_mark);
        $this->assertEquals($data['resultDisplayMode'], $result->result_display_mode);

        foreach ($data['questions'] as $question) {
            $this->assertTrue($result->questions->contains($question['id']));
            if ($data['requiredMark']) {
                $this->assertEquals($question['marks'], $result->questions->find($question['id'])->pivot->marks);
            }
            $this->assertEquals($question['order'], $result->questions->find($question['id'])->pivot->order);
        }

        $this->assertTrue($result->groups->contains($group->id), 'Group is not attached to assessment');

        $this->assertCount(count($data['questions']), $result->questions, 'Number of questions in assessment does not match');
        $this->assertCount(count($data['groupIds']), $result->groups, 'Number of groups in assessment does not match');

    }

    /**
     * @throws \Exception
     */
    #[DataProvider('testCreateAssessmentFailureProvider')]
    public function testCreateAssessmentFailure(array $data): void
    {
        DB::shouldReceive('beginTransaction')->once();
        DB::shouldReceive('commit')->never();
        DB::shouldReceive('rollback')->once();

        // Handle for invalid subject id or question id
        $this->expectException(Exception::class);

        // Expecting a more general message that is database-independent
        $this->expectExceptionMessage('Foreign key violation');
        $this->expectExceptionMessage('violates foreign key constraint');
        $this->expectExceptionMessage('assessments');

        $this->mock(Assessment::class, function ($mock) use ($data) {
            $mock->shouldReceive('create')->with($data)->andThrow(new Exception('Database Error'));
        });

        $result = $this->assessmentService->create($data);

        $this->assertNull($result);
    }

    public static function createAssessmentSuccessProvider(): array
    {
        return [
            'withRequiredMark' => [
                [
                    'name' => 'testCreateAssessmentWithRequiredMark',
                    'subjectId' => 1,
                    'description' => 'testCreateAssessmentWithRequiredMark',
                    'duration' => 10,
                    'totalMarks' => 20,
                    'passMarks' => null,
                    'maxAttempts' => 1,
                    'validFrom' => '2021-01-01',
                    'validTo' => '2022-01-01',
                    'isPublished' => true,
                    'questions' => [
                        [
                            'id' => 1,
                            'marks' => 10,
                            'order' => 1,
                        ],
                        [
                            'id' => 2,
                            'marks' => 10,
                            'order' => 2,
                        ],
                    ],
                    'groupIds' => [1],
                    'requiredMark' => true,
                    'resultDisplayMode' => ResultDisplayMode::DisplayMarkAndAnswers,
                    'created_by' => 1,
                ],
            ],
            'withoutRequiredMark' => [
                [
                    'name' => 'testCreateAssessmentWithoutRequiredMark',
                    'subjectId' => 1,
                    'description' => 'testCreateAssessmentWithoutRequiredMark',
                    'duration' => 10,
                    'totalMarks' => null,
                    'passMarks' => null,
                    'maxAttempts' => 1,
                    'validFrom' => '2021-01-01',
                    'validTo' => '2022-01-01',
                    'isPublished' => true,
                    'questions' => [
                        [
                            'id' => 1,
                            'order' => 1,
                        ],
                        [
                            'id' => 2,
                            'order' => 2,
                        ],
                    ],
                    'groupIds' => [1],
                    'requiredMark' => false,
                    'resultDisplayMode' => null,
                    'created_by' => 1,
                ],
            ],
            'withoutDuration' => [
                [
                    'name' => 'testCreateAssessmentWithoutDuration',
                    'subjectId' => 1,
                    'description' => 'testCreateAssessmentWithoutDuration',
                    'duration' => null,
                    'totalMarks' => 20,
                    'passMarks' => 20,
                    'maxAttempts' => 1,
                    'validFrom' => '2021-01-01',
                    'validTo' => '2022-01-01',
                    'isPublished' => true,
                    'questions' => [
                        [
                            'id' => 1,
                            'marks' => 10,
                            'order' => 1,
                        ],
                        [
                            'id' => 2,
                            'marks' => 10,
                            'order' => 2,
                        ],
                    ],
                    'groupIds' => [1],
                    'requiredMark' => true,
                    'resultDisplayMode' => ResultDisplayMode::DisplayMarkAndAnswers,
                    'created_by' => 1,
                ],
            ],
            'withoutAttempts' => [
                [
                    'name' => 'testCreateAssessmentWithoutAttempts',
                    'subjectId' => 1,
                    'description' => 'testCreateAssessmentWithoutAttempts',
                    'duration' => 10,
                    'totalMarks' => 20,
                    'passMarks' => 20,
                    'maxAttempts' => null,
                    'validFrom' => '2021-01-01',
                    'validTo' => '2022-01-01',
                    'isPublished' => true,
                    'questions' => [
                        [
                            'id' => 1,
                            'marks' => 10,
                            'order' => 1,
                        ],
                        [
                            'id' => 2,
                            'marks' => 10,
                            'order' => 2,
                        ],
                    ],
                    'groupIds' => [1],
                    'requiredMark' => true,
                    'resultDisplayMode' => ResultDisplayMode::DisplayMarkAndAnswers,
                    'created_by' => 1,
                ],
            ],
        ];
    }

    public static function createAssessmentFailureProvider(): array
    {
        return [
            'invalidSubjectId' => [
                'request_data' => [
                    'name' => 'testCreateAssessmentWithInvalidSubjectId',
                    'subjectId' => 999,
                    'description' => 'testCreateAssessmentWithInvalidSubjectId',
                    'duration' => 10,
                    'totalMarks' => 20,
                    'passMarks' => 10,
                    'maxAttempts' => 1,
                    'validFrom' => '2021-01-01',
                    'validTo' => '2022-01-01',
                    'isPublished' => true,
                    'questions' => [
                        [
                            'id' => 1,
                            'marks' => 10,
                            'order' => 1,
                        ],
                        [
                            'id' => 2,
                            'marks' => 10,
                            'order' => 2,
                        ],
                    ],
                    'groupIds' => [1],
                    'requiredMark' => true,
                    'resultDisplayMode' => ResultDisplayMode::DisplayMarkAndAnswers,
                    'created_by' => 1,
                ],
                'exception' => new \Exception('Database Error'),
            ],
            'invalidQuestionId' => [
                'request_data' => [
                    'name' => 'testCreateAssessmentWithInvalidQuestionId',
                    'subjectId' => 1,
                    'description' => 'testCreateAssessmentWithInvalidQuestionId',
                    'duration' => 10,
                    'totalMarks' => 20,
                    'passMarks' => 10,
                    'maxAttempts' => 1,
                    'validFrom' => '2021-01-01',
                    'validTo' => '2022-01-01',
                    'isPublished' => true,
                    'questions' => [
                        [
                            'id' => 999,
                            'marks' => 10,
                            'order' => 1,
                        ],
                        [
                            'id' => 2,
                            'marks' => 10,
                            'order' => 2,
                        ],
                    ],
                    'groupIds' => [1],
                    'requiredMark' => true,
                    'resultDisplayMode' => ResultDisplayMode::DisplayMarkAndAnswers,
                    'created_by' => 1,
                ],
                'exception' => new \Exception('Database Error'),
            ],
        ];
    }
}
