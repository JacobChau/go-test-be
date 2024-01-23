<?php

namespace Tests\Unit\BaseService;

use App\Services\BaseService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Tests\TestCase;

abstract class BaseServiceTest extends TestCase
{
    use RefreshDatabase;

    protected string $modelClass;

    protected BaseService $service;

    public function setUp(): void
    {
        parent::setUp();
    }
    //        public function getList(?string $resourceClass = null, array $input = [], ?Builder $query = null, array $relations = []): array
    //    {
    //        if ($resourceClass && ! class_exists($resourceClass)) {
    //            throw new InvalidArgumentException("Invalid resource class: $resourceClass");
    //        }
    //
    //        $query = $query ?? $this->model->query();
    //        $this->addSearchParam($query, $input);
    //
    //        $perPage = (int) ($input['perPage'] ?? PaginationSetting::PER_PAGE);
    //        $orderBy = $input['orderBy'] ?? PaginationSetting::ORDER_BY;
    //        $orderDirection = $input['orderDir'] ?? PaginationSetting::ORDER_DIRECTION;
    //
    //        foreach ($relations as $relation) {
    //            $query->with($relation);
    //        }
    //
    //        if (! is_string($orderBy)) {
    //            if (is_array($orderBy)) {
    //                $query->orderBy(...$orderBy);
    //            } else {
    //                throw new InvalidArgumentException('orderBy must be a string or an array, '.gettype($orderBy).' given');
    //            }
    //        } else {
    //            $query->orderBy($orderBy, $orderDirection);
    //        }
    //
    //        $result = $query->paginate($perPage);
    //
    //        $items = $result->getCollection();
    //
    //        if ($resourceClass) {
    //            // Ensure the class is a subclass of JsonApiResource
    //            $reflectionClass = new ReflectionClass($resourceClass);
    //            if (! $reflectionClass->isSubclassOf(JsonApiResource::class) && ! $reflectionClass->isSubclassOf(JsonResource::class)) {
    //                throw new InvalidArgumentException("Invalid resource class: $resourceClass. It must be a subclass of JsonResource.");
    //            }
    //
    //            $items = $resourceClass::collection($items);
    //        }
    //
    //        return [
    //            'data' => $items,
    //            'meta' => [
    //                'total' => $result->total(),
    //                'perPage' => $result->perPage(),
    //                'currentPage' => $result->currentPage(),
    //                'lastPage' => $result->lastPage(),
    //                'path' => $result->path(),
    //            ],
    //        ];
    //    }

    public function testGetListSuccess($resourceClass = null, array $input = [], ?Builder $query = null, array $relations = []): void
    {
        $result = $this->service->getList($resourceClass, $input, $query, $relations);
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
        $this->assertIsString($result['meta']['path']);
    }

    public function testGetListWithInvalidResourceClass()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid resource class');
        $this->service->getList('InvalidResourceClass');

    }

    public function testGetListWithInvalidOrderBy()
    {
        $this->expectException(InvalidArgumentException::class);
        $input = ['orderBy' => 'valid_column_name', 'orderDir' => 'invalid_order_dir'];
        $this->expectExceptionMessage('Order direction must be "asc" or "desc".');
        $this->service->getList(null, $input);
    }

    public function testGetListWithInvalidOrderByType()
    {
        $this->expectException(InvalidArgumentException::class);
        $input = ['orderBy' => 123];
        $this->expectExceptionMessage('orderBy must be a string or an array');
        $this->service->getList(null, $input);
    }
}
