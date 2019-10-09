<?php

namespace Orkhanahmadov\EloquentRepository\Repository\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Orkhanahmadov\EloquentRepository\Repository\Contracts\Cacheable;

/**
 * @property-read string $entity
 * @property-read Builder|Model $modelInstance
 * @method Builder|Model find(int $modelId)
 * @method void invalidateCache()
 */
trait DeletesEntity
{
    /**
     * Finds a model with ID and deletes it.
     *
     * @param int|string $modelId
     *
     * @return bool|mixed|null
     * @throws \Exception
     */
    public function findAndDelete($modelId)
    {
        $model = $this->find($modelId);

        return $this->delete($model);
    }

    /**
     * Deletes a model.
     *
     * @param Model $modelInstance
     *
     * @return bool|mixed|null
     * @throws \Exception
     */
    public function delete($modelInstance)
    {
        if ($this instanceof Cacheable) {
            $this->invalidateCache($modelInstance);
        }

        return $modelInstance->delete();
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
        if (! method_exists($this->entity, 'restore')) {
            throw new \BadMethodCallException('Model is not using "soft delete" feature.');
        }

        $model = $this->modelInstance->onlyTrashed()->find($modelId);

        if (! $model) {
            throw (new ModelNotFoundException())->setModel($this->entity, $modelId);
        }

        return $model;
    }

    /**
     * Restores soft deleted model.
     *
     * @param Builder|Model $modelInstance
     *
     * @return bool|null
     */
    public function restore($modelInstance)
    {
        if (! method_exists($this->entity, 'restore')) {
            throw new \BadMethodCallException($modelInstance->getModel() . ' is not using "soft delete" feature.');
        }

        return $modelInstance->restore();
    }
}
