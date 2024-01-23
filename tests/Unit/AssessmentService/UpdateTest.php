<?php

use App\Enums\ResultDisplayMode;
use App\Models\Assessment;
use App\Models\Group;
use App\Models\Question;
use App\Models\Subject;
use App\Models\User;
use App\Services\AssessmentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class UpdateTest extends TestCase
{
    use RefreshDatabase;

    private AssessmentService $assessmentService;

    public function setUp(): void
    {
        parent::setUp();
        $this->assessmentService = $this->app->make(AssessmentService::class);
    }

    #[DataProvider('updateAssessmentSuccessProvider')]
    public function testUpdateAssessmentSuccess(array $initial, array $update): void
    {
        Subject::factory()->create(['id' => $initial['subjectId']]);
        User::factory()->create(['id' => $initial['created_by']]);

        $unionQuestionIds = array_unique(array_merge(array_column($initial['questions'], 'id'), array_column($update['questions'], 'id')));
        $unionGroupIds = array_unique(array_merge($initial['groupIds'], $update['groupIds']));
        foreach ($unionQuestionIds as $questionId) {
            Question::factory()->create(['id' => $questionId]);
        }

        foreach ($unionGroupIds as $groupId) {
            Group::factory()->create(['id' => $groupId]);
        }

        $assessment = $this->assessmentService->create($initial);

        DB::shouldReceive('beginTransaction')->once();
        DB::shouldReceive('commit')->once();
        DB::shouldReceive('rollBack')->never();

        $this->assessmentService->update($assessment->id, $update);

        $result = Assessment::with(['questions', 'groups'])->find($assessment->id);

        $this->assertInstanceOf(Assessment::class, $result);

        $this->assertEquals($update['name'], $result->name);
        $this->assertEquals($update['subjectId'], $result->subject_id);
        $this->assertEquals($update['description'], $result->description);
        $this->assertEquals($update['duration'], $result->duration);
        $this->assertEquals($update['totalMarks'], $result->total_marks);
        $this->assertEquals($update['passMarks'], $result->pass_marks);
        $this->assertEquals($update['maxAttempts'], $result->max_attempts);
        $this->assertEquals($update['validFrom'], $result->valid_from->format('Y-m-d'));
        $this->assertEquals($update['validTo'], $result->valid_to->format('Y-m-d'));
        $this->assertEquals($update['isPublished'], $result->is_published);
        $this->assertEquals($update['requiredMark'], $result->required_mark);
        $this->assertEquals($update['resultDisplayMode'], $result->result_display_mode);

        // Assert questions
        $this->assertCount(count($update['questions']), $result->questions);
        foreach ($update['questions'] as $questionUpdate) {
            $question = $result->questions->firstWhere('id', $questionUpdate['id']);
            $this->assertNotNull($question, "Question with ID {$questionUpdate['id']} not found.");
            $this->assertEquals($questionUpdate['marks'], $question->pivot->marks);
            $this->assertEquals($questionUpdate['order'], $question->pivot->order);
        }

        // Assert groups
        $this->assertCount(count($update['groupIds']), $result->groups);
        foreach ($update['groupIds'] as $groupId) {
            $group = $result->groups->firstWhere('id', $groupId);
            $this->assertNotNull($group, "Group with ID $groupId not found.");
        }
    }

    #[DataProvider('updateAssessmentFailProvider')]
    public function testUpdateAssessmentFail(array $initial, array $update): void
    {
        Subject::factory()->create(['id' => $initial['subjectId']]);
        User::factory()->create(['id' => $initial['created_by']]);

        foreach ($initial['questions'] as $question) {
            Question::factory()->create(['id' => $question['id']]);
        }

        foreach ($initial['groupIds'] as $groupId) {
            Group::factory()->create(['id' => $groupId]);
        }

        $assessment = $this->assessmentService->create($initial);

        DB::shouldReceive('beginTransaction')->once();
        DB::shouldReceive('commit')->never();
        DB::shouldReceive('rollBack')->once();

        $this->expectException(Exception::class);
        $this->assessmentService->update($assessment->id, $update);

        $result = Assessment::with(['questions', 'groups'])->find($assessment->id);
    }

    public static function updateAssessmentSuccessProvider(): array
    {
        return [
            'update with requiredMark' => [
                'initial' => [
                    'name' => 'testUpdateAssessmentWithRequiredMark',
                    'subjectId' => 1,
                    'description' => 'testUpdateAssessmentWithRequiredMark',
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
                'update' => [
                    'name' => 'testUpdateAssessmentWithRequiredMark',
                    'subjectId' => 1,
                    'description' => 'testUpdateAssessmentWithRequiredMark',
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
            'update with questions and groups' => [
                'initial' => [
                    'name' => 'testUpdateAssessmentWithQuestionsAndGroups',
                    'subjectId' => 1,
                    'description' => 'testUpdateAssessmentWithQuestionsAndGroups',
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
                    'requiredMark' => false,
                    'resultDisplayMode' => null,
                    'created_by' => 1,
                ],
                'update' => [
                    'name' => 'testUpdateAssessmentWithQuestionsAndGroups',
                    'subjectId' => 1,
                    'description' => 'testUpdateAssessmentWithQuestionsAndGroups',
                    'duration' => 10,
                    'totalMarks' => 20,
                    'passMarks' => null,
                    'maxAttempts' => 1,
                    'validFrom' => '2021-01-01',
                    'validTo' => '2022-01-01',
                    'isPublished' => true,
                    'questions' => [
                        [
                            'id' => 3,
                            'marks' => 10,
                            'order' => 1,
                        ],
                        [
                            'id' => 4,
                            'marks' => 10,
                            'order' => 2,
                        ],
                    ],
                    'groupIds' => [2, 3],
                    'requiredMark' => true,
                    'resultDisplayMode' => ResultDisplayMode::DisplayMarkAndAnswers,
                    'created_by' => 1,
                ],
            ],
        ];
    }

    public static function updateAssessmentFailProvider(): array
    {
        return [
            'update with invalid subjectId' => [
                'initial' => [
                    'name' => 'testUpdateAssessmentWithInvalidSubjectId',
                    'subjectId' => 1,
                    'description' => 'testUpdateAssessmentWithInvalidSubjectId',
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
                    'requiredMark' => false,
                    'resultDisplayMode' => null,
                    'created_by' => 1,
                ],
                'update' => [
                    'name' => 'testUpdateAssessmentWithInvalidSubjectId',
                    'subjectId' => 999,
                    'description' => 'testUpdateAssessmentWithInvalidSubjectId',
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
                    'requiredMark' => false,
                    'resultDisplayMode' => null,
                    'created_by' => 1,
                ],
            ],
            'update with invalid questionId' => [
                'initial' => [
                    'name' => 'testUpdateAssessmentWithInvalidQuestionId',
                    'subjectId' => 1,
                    'description' => 'testUpdateAssessmentWithInvalidQuestionId',
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
                    'requiredMark' => false,
                    'resultDisplayMode' => null,
                    'created_by' => 1,
                ],
                'update' => [
                    'name' => 'testUpdateAssessmentWithInvalidQuestionId',
                    'subjectId' => 1,
                    'description' => 'testUpdateAssessmentWithInvalidQuestionId',
                    'duration' => 10,
                    'totalMarks' => 20,
                    'passMarks' => null,
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
                    'requiredMark' => false,
                    'resultDisplayMode' => null,
                    'created_by' => 1,
                ],
            ],
        ];
    }
}
