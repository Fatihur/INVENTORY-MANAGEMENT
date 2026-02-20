<?php

namespace App\Repositories;

use App\Contracts\Repositories\BaseRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

abstract class BaseRepository implements BaseRepositoryInterface
{
    protected Model $model;
    protected array $relations = [];

    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    public function all(array $columns = ['*']): Collection
    {
        return $this->model->with($this->relations)->get($columns);
    }

    public function paginate(int $perPage = 15, array $columns = ['*']): LengthAwarePaginator
    {
        return $this->model->with($this->relations)->paginate($perPage, $columns);
    }

    public function find(int $id, array $columns = ['*']): ?Model
    {
        return $this->model->with($this->relations)->find($id, $columns);
    }

    public function findBy(string $field, mixed $value, array $columns = ['*']): ?Model
    {
        return $this->model->with($this->relations)
            ->where($field, $value)
            ->first($columns);
    }

    public function create(array $attributes): Model
    {
        return $this->model->create($attributes);
    }

    public function update(int $id, array $attributes): bool
    {
        $model = $this->find($id);
        return $model ? $model->update($attributes) : false;
    }

    public function delete(int $id): bool
    {
        $model = $this->find($id);
        return $model ? $model->delete() : false;
    }

    public function with(array $relations): self
    {
        $this->relations = $relations;
        return $this;
    }
}
