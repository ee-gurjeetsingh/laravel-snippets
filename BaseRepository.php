<?php

declare(strict_types=1);

namespace App\Repositories;

/**
 * Class BaseRepository
 * This class contains common crud functions
 */
abstract class BaseRepository implements BaseRepositoryInterface
{
    /**
     * Object of particular model
     *
     * @var $model
     */
    protected $model;

    /**
     * Method to create new record.
     *
     * @param array $attributes
     * @return mixed
     */
    public function create(array $attributes)
    {
        return $this->model->create($attributes);
    }

    /**
     * Method to find record by its primary key.
     *
     * @param mixed $id
     * @return mixed
     */
    public function find($id)
    {
        return $this->model->find($id);
    }

    /**
     * Method to update existing record.
     * It will not use 'mass update' via eloquent, so that it will fire eloquent events while updating.
     *
     * @param mixed $id
     * @param array $attributes
     * @return mixed
     */
    public function update($id, array $attributes)
    {
        return $this->model->where('id', $id)->update($attributes);
    }

    /**
     * To find record by matching multiple attributes
     *
     * @param array $attributes
     * @return mixed
     */
    public function findBy(array $attributes)
    {
        return $this->model->where($attributes)->first();
    }

    /**
     * To delete record by matching multiple attributes
     *
     * @param array $attributes
     * @return mixed
     */
    public function deleteBy(array $attributes)
    {
        return $this->model->where($attributes)->delete();
    }

    /**
     * To update record by matching multiple attributes
     *
     * @param array $whereAttributes
     * @param array $attributes
     * @return mixed
     */
    public function updateBy(array $whereAttributes, array $attributes)
    {
        return $this->model->where($whereAttributes)->update($attributes);
    }

    /**
     * Method to delete record by its primary key.
     *
     * @param mixed $id
     * @return int
     */
    public function delete($id): int
    {
        return $this->model->where('id', $id)->delete();
    }

    /**
     * Method to get records.
     *
     * @return mixed
     */
    public function list()
    {
        return $this->model
            ->orderBy('updated_at', 'desc')
            ->paginate(config('config.default_pagination'));
    }

    /**
     * Method to update existing record and track user activity.
     *
     * @param array $whereAttributes
     * @param array $attributes
     * @return bool
     */
    public function updateByModel(array $whereAttributes, array $attributes): bool
    {
        return $this->findBy($whereAttributes)->update($attributes);
    }

    /**
     * Method to delete existing record and track user activity.
     *
     * @param array $whereAttributes
     * @return bool
     */
    public function deleteByModel(array $whereAttributes): bool
    {
        return $this->findBy($whereAttributes)->delete();
    }
}
