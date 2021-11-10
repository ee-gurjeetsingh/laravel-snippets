<?php

declare(strict_types=1);

namespace App\Repositories\User;

/**
 * Interface UserRepositoryInterface
 */
interface UserRepositoryInterface
{
    /**
     * Method to get all users except the logged-in user.
     *
     * @return mixed
     */
    public function list();
}
