<?php
//
//use App\Enums\UserRole;
//use App\Http\Resources\AssessmentDetailResource;
//use App\Models\User;
//use Illuminate\Database\Eloquent\Builder;
//use Illuminate\Database\Eloquent\Collection;
//use PHPUnit\Framework\Attributes\DataProvider;
//use Tests\TestCase;
//use App\Services\AssessmentService;
//use Illuminate\Foundation\Testing\RefreshDatabase;
//
//class GetListTest extends TestCase
//{
//    use RefreshDatabase;
//
//    private AssessmentService $assessmentService;
//
//    public function setUp(): void
//    {
//        parent::setUp();
//        $this->assessmentService = $this->app->make(AssessmentService::class);
//    }
//
//    #[DataProvider('getListDataProvider')]
//    public function testGetListAsAdmin(int $userRole, array $expectedQueryMethodCalls) : void
//    {
//        $adminUser = User::factory()->create(['role' => UserRole::Admin]);
//
//        $this->actingAs($adminUser);
//
//        $input = ['filters' => ['isTaken' => true]];
//
//        $query = $this->createMock(Builder::class);
//
//        foreach ($expectedQueryMethodCalls as $method) {
//            $query->expects($this->once())->method($method)->willReturn($query);
//        }
//
//        $expectedResult = ['data' => new Collection(), 'meta' => ['total' => 0]];
//        $this->assertEquals($expectedResult, $this->assessmentService->getList(AssessmentDetailResource::class, $input, $query));
//    }
//
//    public static function getListDataProvider() : array {
//        return [
//            'admin user' => [
//                'userRole' => UserRole::Admin,
//                'expectedQueryMethodCalls' => [] // Admins might not have additional query constraints
//            ],
//            'teacher user' => [
//                'userRole' => UserRole::Teacher,
//                'expectedQueryMethodCalls' => ['published', 'notExpired', 'isNotTaken', 'whereHas'],
//            ],
//            'student user' => [
//                'userRole' => UserRole::Student,
//                'expectedQueryMethodCalls' => ['published', 'notExpired', 'isNotTaken', 'whereHas'],
//            ],
//        ];
//    }
//
//}
