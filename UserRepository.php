<?php

declare(strict_types=1);

namespace App\Repositories\User;

use App\Models\User;
use App\Repositories\BaseRepository;

/**
 * Class UserRepository
 * This is user repository class
 */
class UserRepository extends BaseRepository implements UserRepositoryInterface
{
    /**
     * UserRepository constructor.
     * To initialize class objects/variables.
     *
     * @param User $model
     */
    public function __construct(User $model)
    {
        $this->model = $model;
    }

    /**
     * Method to get all users except the logged-in user.
     *
     * @return mixed
     */
    public function list()
    {
        return $this->model
            ->where('id', '!=', auth()->id())
            ->orderBy('updated_at', 'desc')
            ->paginate(config('config.default_pagination'));
    }
}
