<?php

use App\Enums\PaginationSetting;
use App\Enums\UserRole;
use App\Http\Resources\AssessmentResource;
use App\Models\Assessment;
use App\Models\Group;
use App\Models\User;
use App\Services\AssessmentService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class GetListTest extends TestCase
{
    use RefreshDatabase;

    private AssessmentService $assessmentService;

    public function setUp(): void
    {
        parent::setUp();
        $this->assessmentService = $this->app->make(AssessmentService::class);
    }

    #[DataProvider('getListDataProvider')]
    public function testGetListSuccess(
        $resourceClass = AssessmentResource::class,
        $input = [],
        ?Builder $query = null,
        array $relations = [],
        int $role = UserRole::Student
    ): void {

        // Arrange
        $user = User::factory()->create();
        if ($role === UserRole::Admin) {
            $user = User::factory()->create(['role' => UserRole::Admin]);
        }

        $this->actingAs($user);

        // create groups and associate with the student
        $group = Group::factory()->count(5)->create();
        $group->each(function ($group) use ($user) {
            $group->users()->attach($user->id);
        });

        $notInGroup = Group::factory()->count(5)->create();

        $takenAssessments = Assessment::factory()->count(5)->create();
        $takenAssessments->each(function ($assessment) use ($user) {
            $assessment->groups()->attach($user->groups->random()->id);
            $assessment->attempts()->create([
                'user_id' => $user->id,
                'total_marks' => 10,
            ]);
        });

        $notTakenAssessment = Assessment::factory()->count(5)->create();
        $notTakenAssessment->each(function ($assessment) use ($user) {
            $assessment->groups()->attach($user->groups->random()->id);
        });

        $publishedAssessment = Assessment::factory()->count(5)->create(['is_published' => true]);
        $publishedAssessment->each(function ($assessment) use ($user) {
            $assessment->groups()->attach($user->groups->random()->id);
        });
        $unpublishedAssessment = Assessment::factory()->count(5)->create(['is_published' => false]);
        $unpublishedAssessment->each(function ($assessment) use ($user) {
            $assessment->groups()->attach($user->groups->random()->id);
        });

        $expiredAssessment = Assessment::factory()->count(5)->create(['is_published' => true, 'valid_to' => now()->subDay()]);
        $expiredAssessment->each(function ($assessment) use ($user) {
            $assessment->groups()->attach($user->groups->random()->id);
        });

        $notExpiredAssessment = Assessment::factory()->count(5)->create(['is_published' => true, 'valid_to' => now()->addDay()]);
        $notExpiredAssessment->each(function ($assessment) use ($user) {
            $assessment->groups()->attach($user->groups->random()->id);
        });

        $assessmentWithDuration = Assessment::factory()->count(5)->create(['duration' => 10]);
        $assessmentWithDuration->each(function ($assessment) use ($user) {
            $assessment->groups()->attach($user->groups->random()->id);
        });

        $assessmentWithoutDuration = Assessment::factory()->count(5)->create(['duration' => null]);
        $assessmentWithoutDuration->each(function ($assessment) use ($user) {
            $assessment->groups()->attach($user->groups->random()->id);
        });

        $result = $this->assessmentService->getList($resourceClass, $input, $query, $relations);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('data', $result);
        $this->assertArrayHasKey('meta', $result);
        $this->assertArrayHasKey('total', $result['meta']);
        $this->assertArrayHasKey('perPage', $result['meta']);
        $this->assertArrayHasKey('currentPage', $result['meta']);
        $this->assertArrayHasKey('lastPage', $result['meta']);
        $this->assertArrayHasKey('path', $result['meta']);

        $this->assertIsInt($result['meta']['total']);
        $this->assertIsInt($result['meta']['perPage']);
        $this->assertIsInt($result['meta']['currentPage']);
        $this->assertIsInt($result['meta']['lastPage']);

        $this->assertIsInt($result['meta']['total']);
        $this->assertIsInt($result['meta']['perPage']);
        $this->assertIsInt($result['meta']['currentPage']);
        $this->assertIsInt($result['meta']['lastPage']);
        $this->assertIsString($result['meta']['path']);

        if ($resourceClass) {
            $this->assertContainsOnlyInstancesOf($resourceClass, $result['data']);
        }

        $this->assertEquals($result['meta']['perPage'], $input['perPage'] ?? PaginationSetting::PER_PAGE);
        $this->assertEquals($result['meta']['currentPage'], $input['page'] ?? 1);

        if (isset($input['filters'])) {
            if (isset($input['filters']['isTaken']) && $input['filters']['isTaken'] === 'true' && isset($input['filters']['hasDuration']) && $input['filters']['hasDuration'] === 'true') {
                $this->assertNotContains($notTakenAssessment->pluck('id')->toArray(), $result['data']->pluck('id')->toArray());
                $this->assertNotContains($assessmentWithoutDuration->pluck('id')->toArray(), $result['data']->pluck('id')->toArray());
            }

            if (isset($input['filters']['isTaken']) && $input['filters']['isTaken'] === 'true') {
                $this->assertNotContains($notTakenAssessment->pluck('id')->toArray(), $result['data']->pluck('id')->toArray());
            }

            if (isset($input['filters']['hasDuration']) && $input['filters']['hasDuration'] === 'true') {
                $this->assertNotContains($assessmentWithoutDuration->pluck('id')->toArray(), $result['data']->pluck('id')->toArray());
            }
        }

        if ($role !== UserRole::Admin) {
            $this->assertNotContains($expiredAssessment->pluck('id')->toArray(), $result['data']->pluck('id')->toArray());
            $this->assertNotContains($takenAssessments->pluck('id')->toArray(), $result['data']->pluck('id')->toArray());
            $this->assertNotContains($unpublishedAssessment->pluck('id')->toArray(), $result['data']->pluck('id')->toArray());

            if (! isset($input['filters']['isTaken'])) {
                $this->assertNotContains($takenAssessments->pluck('id')->toArray(), $result['data']->pluck('id')->toArray());
            }

            $this->assertNotContains($notInGroup->pluck('id')->toArray(), $result['data']->pluck('id')->toArray());
        }

    }

    public static function getListDataProvider(): array
    {
        return [
            'getList with role admin' => [
                'resourceClass' => AssessmentResource::class,
                'input' => [],
                'query' => null,
                'relations' => [],
                'role' => UserRole::Admin,
            ],
            'getList with no params' => [
                'resourceClass' => AssessmentResource::class,
                'input' => [],
                'query' => null,
                'relations' => [],
            ],
            'getList with perPage' => [
                'resourceClass' => AssessmentResource::class,
                'input' => ['perPage' => 20],
                'query' => null,
                'relations' => [],
            ],
            'getList with orderBy' => [
                'resourceClass' => AssessmentResource::class,
                'input' => ['orderBy' => 'name'],
                'query' => null,
                'relations' => [],
            ],
            'getList with orderDir' => [
                'resourceClass' => AssessmentResource::class,
                'input' => ['orderDir' => 'desc'],
                'query' => null,
                'relations' => [],
            ],
            'getList with orderBy and orderDir' => [
                'resourceClass' => AssessmentResource::class,
                'input' => ['orderBy' => 'name', 'orderDir' => 'desc'],
                'query' => null,
                'relations' => [],
            ],
            'getList with orderBy and orderDir and perPage' => [
                'resourceClass' => AssessmentResource::class,
                'input' => ['orderBy' => 'name', 'orderDir' => 'desc', 'perPage' => 10],
                'query' => null,
                'relations' => [],
            ],
            'getList with no resourceClass' => [
                'resourceClass' => null,
                'input' => [],
                'query' => null,
                'relations' => [],
            ],
            'getList with filters isTaken' => [
                'resourceClass' => AssessmentResource::class,
                'input' => ['filters' => ['isTaken' => 'true']],
                'query' => null,
                'relations' => [],
            ],
            'getList with filters hasDuration' => [
                'resourceClass' => AssessmentResource::class,
                'input' => ['filters' => ['hasDuration' => 'true']],
                'query' => null,
                'relations' => [],
            ],
            'getList with filters isTaken and hasDuration' => [
                'resourceClass' => AssessmentResource::class,
                'input' => ['filters' => ['isTaken' => 'true', 'hasDuration' => 'true']],
                'query' => null,
                'relations' => [],
            ],
        ];
    }
}
