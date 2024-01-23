<?php

namespace App\Services;

use App\Enums\PaginationSetting;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Resources\Json\JsonResource;
use InvalidArgumentException;
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

    public function getById(int $id, array $defaultRelations = []): object
    {
        $relations = $defaultRelations;

        if (request()->has('include')) {
            $includeRelations = explode(',', request()->get('include'));
            $relations = array_unique(array_merge($defaultRelations, $includeRelations));
        }

        $query = $this->model->query();
        foreach ($relations as $relation) {
            $query->with($relation);
        }

        return $query->find($id);
    }

    public function firstOrCreate(array $conditions, array $data = []): object
    {
        return $this->model->firstOrCreate($conditions, $data);
    }

    public function getModel(): Model
    {
        return $this->model;
    }

    /**
     * @throws ReflectionException
     */
    public function getList(?string $resourceClass = null, array $input = [], ?Builder $query = null, array $relations = []): array
    {
        if ($resourceClass && ! class_exists($resourceClass)) {
            throw new InvalidArgumentException("Invalid resource class: $resourceClass");
        }

        $query = $query ?? $this->model->query();
        $this->addSearchParam($query, $input);

        $perPage = (int) ($input['perPage'] ?? PaginationSetting::PER_PAGE);
        $orderBy = $input['orderBy'] ?? PaginationSetting::ORDER_BY;
        $orderDirection = $input['orderDir'] ?? PaginationSetting::ORDER_DIRECTION;

        foreach ($relations as $relation) {
            $query->with($relation);
        }

        if (! is_string($orderBy)) {
            if (is_array($orderBy)) {
                $query->orderBy(...$orderBy);
            } else {
                throw new InvalidArgumentException('orderBy must be a string or an array, '.gettype($orderBy).' given');
            }
        } else {
            $query->orderBy($orderBy, $orderDirection);
        }

        $result = $query->paginate($perPage);

        $items = $result->getCollection();

        if ($resourceClass) {
            // Ensure the class is a subclass of JsonApiResource
            $reflectionClass = new ReflectionClass($resourceClass);
            if (! $reflectionClass->isSubclassOf(JsonApiResource::class) && ! $reflectionClass->isSubclassOf(JsonResource::class)) {
                throw new InvalidArgumentException("Invalid resource class: $resourceClass. It must be a subclass of JsonResource.");
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

    public function addSearchParam(Builder $query, array $params): void
    {
        $searchType = $params['searchType'] ?? null;
        $searchColumn = $params['searchColumn'] ?? null;
        $searchKeyword = $params['searchKeyword'] ?? null;

        if (! $searchType || ! $searchColumn || ! $searchKeyword) {
            return;
        }

        if (str_contains($searchColumn, '.')) {
            $searchColumn = explode('.', $searchColumn);
            $query->whereHas($searchColumn[0], function ($query) use ($searchType, $searchColumn, $searchKeyword) {
                $this->applySearchType($query, $searchType, $searchColumn[1], $searchKeyword);
            });
        } elseif (str_contains($searchColumn, ',')) {
            $searchColumns = explode(',', $searchColumn);
            // case: search by multiple columns (ex: name,email) so if name or email contains keyword, it will be returned
            $query->where(function ($query) use ($searchType, $searchColumns, $searchKeyword) {
                foreach ($searchColumns as $column) {
                    $query->orWhere(function ($query) use ($searchType, $column, $searchKeyword) {
                        $this->applySearchType($query, $searchType, $column, $searchKeyword);
                    });
                }
            });

        } else {
            $this->applySearchType($query, $searchType, $searchColumn, $searchKeyword);
        }
    }

    private function applySearchType(Builder $query, string $searchType, string $column, $keyword): void
    {
        switch ($searchType) {
            case 'Equal':
                $query->where($column, '=', $keyword);
                break;
            case 'Contain':
                $query->where($column, 'LIKE', "%$keyword%");
                break;
            default:
                break;
        }
    }
}
