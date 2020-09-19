<?php

namespace Orkhanahmadov\EloquentRepository\Repository\Concerns;

use BadMethodCallException;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Orkhanahmadov\EloquentRepository\EloquentRepository;
use Orkhanahmadov\EloquentRepository\Repository\Contracts\Cacheable;

/**
 * @property-read Builder|Model $model
 * @method Builder|Model find($modelId)
 * @method void invalidateCache($model)
 * @mixin EloquentRepository
 */
trait DeletesEntity
{
    /**
     * Finds a model with ID and deletes it.
     *
     * @param int|string $modelId
     *
     * @return bool|mixed|null
     * @throws Exception
     */
    public function findAndDelete($modelId)
    {
        $model = $this->find($modelId);

        return $this->delete($model);
    }

    /**
     * Deletes a model.
     *
     * @param Model $model
     *
     * @return bool|mixed|null
     * @throws Exception
     */
    public function delete(Model $model)
    {
        if ($this instanceof Cacheable) {
            $this->invalidateCache($model);
        }

        return $model->delete();
    }

    /**
     * Finds a soft deleted model with given ID and restores it.
     *
     * @param int|string $modelId
     *
     * @return bool|null
     */
    public function findAndRestore($modelId)
    {
        $model = $this->findFromTrashed($modelId);

        return $this->restore($model);
    }

    /**
     * Finds a soft deleted model with given ID.
     *
     * @param int|string $modelId
     *
     * @return Builder|Builder[]|Collection|Model|null
     */
    public function findFromTrashed($modelId)
    {
        if (!method_exists($this->entity, 'restore')) {
            throw new BadMethodCallException('Model is not using "soft delete" feature.');
        }

        $model = $this->model
            ->onlyTrashed()
            ->find($modelId);

        if (!$model) {
            $this->throwModelNotFoundException($modelId);
        }

        return $model;
    }

    /**
     * Restores soft deleted model.
     *
     * @param Model $model
     *
     * @return bool|null
     */
    public function restore(Model $model)
    {
        if (!method_exists($this->entity, 'restore')) {
            throw new BadMethodCallException('Model is not using "soft delete" feature.');
        }

        return $model->restore();
    }
}
