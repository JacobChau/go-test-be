<?php

namespace App\Services;

use App\Enums\PaginationSetting;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Resources\Json\JsonResource;
use ReflectionClass;
use ReflectionException;
use TiMacDonald\JsonApi\JsonApiResource;

class BaseService
{
    protected Model $model;

    public function create(array $data): object
    {
        return $this->model->create($data);
    }

    public function update(int $id, array $data): void
    {
        $this->model->where('id', $id)->update($data);
    }

    public function delete(int $id): void
    {
        $this->model->where('id', $id)->delete();
    }

    public function getById(int $id, array $relations = []): object
    {
        $query = $this->model->query();
        foreach ($relations as $relation) {
            if (method_exists($this->model, $relation)) {
                $query->with($relation);
            }
        }

        return $query->find($id);
    }

    /**
     * @throws ReflectionException
     */
    public function getList(array $input = [], Builder $query = null, array $relations = [], string $resourceClass = null): array
    {
        if ($resourceClass && ! class_exists($resourceClass)) {
            throw new \InvalidArgumentException("Invalid resource class: $resourceClass");
        }

        $query = $query ?? $this->model->query();
        $perPage = (int) ($input['perPage'] ?? PaginationSetting::PER_PAGE);
        $orderBy = $input['orderBy'] ?? PaginationSetting::ORDER_BY;
        $orderDirection = $input['orderDir'] ?? PaginationSetting::ORDER_DIRECTION;

        foreach ($relations as $relation) {
            if (method_exists($this->model, $relation)) {
                $query->with($relation);
            }
        }

        $result = $query->orderBy($orderBy, $orderDirection)->paginate($perPage);

        $items = $result->getCollection();
        if ($resourceClass) {
            // Ensure the class is a subclass of JsonApiResource
            $reflectionClass = new ReflectionClass($resourceClass);
            if (! $reflectionClass->isSubclassOf(JsonApiResource::class) && ! $reflectionClass->isSubclassOf(JsonResource::class)) {
                throw new \InvalidArgumentException("Invalid resource class: $resourceClass. It must be a subclass of JsonResource.");
            }

            $items = $resourceClass::collection($items);
        }

        return [
            'data' => $items,
            'meta' => [
                'total' => $result->total(),
                'perPage' => $result->perPage(),
                'currentPage' => $result->currentPage(),
                'lastPage' => $result->lastPage(),
                'path' => $result->path(),
            ],
        ];
    }
}
