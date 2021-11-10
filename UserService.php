<?php

declare(strict_types=1);

namespace App\Services\User;

use Illuminate\Support\Arr;
use App\Repositories\User\UserRepositoryInterface;

/**
 * Class UserService
 * This class is used to maintain users
 */
class UserService
{
    
    /**
     * @var UserRepositoryInterface $userRepository
     */
    private UserRepositoryInterface $userRepository;

    /**
     * @var UserEmailService $userEmailService
     */
    private UserEmailService $userEmailService;

    /**
     * UserService constructor.
     * Initialize object/instance for classes.
     *
     * @param UserRepositoryInterface $userRepository
     * @param UserEmailService $userEmailService
     */
    public function __construct(UserRepositoryInterface $userRepository, UserEmailService $userEmailService)
    {
        $this->userRepository = $userRepository;
        $this->userEmailService = $userEmailService;
    }

    /**
     * Method to create the user.
     *
     * @param array $attributes
     * @return mixed
     */
    public function create(array $attributes)
    {
        $result = $this->userRepository->create($attributes);
        if ($result) {
            $this->userEmailService->sendSetPasswordNotification($result);
        }

        return $result;
    }

    /**
     * Method to update the user.
     *
     * @param string $id
     * @param array $attributes
     * @return bool
     */
    public function update(string $id, array $attributes): bool
    {
        return $this->userRepository->updateByModel(['id' => $id], Arr::except($attributes, ['email']));
    }

    /**
     * Method to find user by its primary key.
     *
     * @param string $id
     * @return mixed
     */
    public function find(string $id)
    {
        return $this->userRepository->find($id);
    }

    /**
     * Method to get all users except the logged-in user.
     *
     * @return mixed
     */
    public function list()
    {
        return $this->userRepository->list();
    }

    /**
     * Method to delete user by its primary key.
     *
     * @param string $id
     * @return bool
     */
    public function delete(string $id): bool
    {
        return $this->userRepository->deleteByModel(['id' => $id]);
    }
}
