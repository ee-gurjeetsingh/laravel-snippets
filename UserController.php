<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\Users;

use Exception;
use Illuminate\Support\Arr;
use App\Http\Requests\{
    UserStoreRequest,
    UserCreateRequest,
    UserUpdateRequest,
    UserDestroyRequest,
    ProfileUpdateRequest,
    ChangePasswordRequest,
};
use App\Services\User\UserService;
use Illuminate\Contracts\View\View;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\RedirectResponse;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\{JsonResponse, Response};

class UserController extends Controller
{
    /**
     * @var UserService $userService
     */
    private UserService $userService;

    /**
     * UserController constructor.
     * Initialize all class instances.
     *
     * @param UserService $userService
     */
    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * Display a listing of the resource.
     *
     * @return View
     */
    public function index(): View
    {
        $users = $this->userService->list();
        return view('admin.users.list', ['users' => $users]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param UserCreateRequest $request
     * @return View
     */
    public function create(UserCreateRequest $request): View
    {
        return view('admin.users.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param UserStoreRequest $request
     * @return mixed
     */
    public function store(UserStoreRequest $request)
    {
        try {
            $attributes = $request->validated();
            $attributes['password'] = Hash::make(mt_rand());
            $this->userService->create($attributes);

            return redirect()->route('users.index')->with(
                'success',
                __('messages.flash_messages.user_added') . ' ' . __('user.set_password_link_sent')
            );
        } catch (ModelNotFoundException | Exception $exception) {
            abort(Response::HTTP_UNPROCESSABLE_ENTITY, $exception->getMessage());
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param string $id
     * @return View
     */
    public function edit(string $id)
    {
        $user = $this->userService->find($id);
        if (!$user) {
            abort(Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        return view('admin.users.edit', ['user' => $user]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UserUpdateRequest $request
     * @param string $id
     * @return mixed
     */
    public function update(UserUpdateRequest $request, string $id)
    {
        try {
            $this->userService->update($id, $request->validated());

            return redirect()->route('users.index')->with(
                'success',
                __('messages.flash_messages.user_updated')
            );
        } catch (ModelNotFoundException | Exception $exception) {
            abort(Response::HTTP_UNPROCESSABLE_ENTITY, $exception->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param UserDestroyRequest $request
     * @param string $id
     * @return JsonResponse
     */
    public function destroy(UserDestroyRequest $request, string $id): JsonResponse
    {
        return $this->userService->delete($id)
            ? response()->json([], Response::HTTP_OK)
            : response()->json(['message' => __('messages.flash_messages.no_record_found')], Response::HTTP_NOT_FOUND);
    }

    /**
     * Profile details.
     *
     * @return View
     */
    public function profile(): View
    {
        return view('admin.users.profile', ['user' => auth()->user()]);
    }

    /**
     * Update user profile
     *
     * @param ProfileUpdateRequest $request
     * @return mixed
     */
    public function profileUpdate(ProfileUpdateRequest $request)
    {
        try {
            $this->userService->update(auth()->id(), $request->validated());

            return redirect()->route('dashboard')->with(
                'success',
                __('messages.flash_messages.profile_updated')
            );
        } catch (ModelNotFoundException | Exception $exception) {
            abort(Response::HTTP_UNPROCESSABLE_ENTITY, $exception->getMessage());
        }
    }
}
