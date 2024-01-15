<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class UserController extends Controller
{
    protected UserService $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $relations = [];
        if (request()->has('include')) {
            $relations = explode(',', request()->get('include'));
        }

        $users = $this->userService->getList(UserResource::class, request()->all(), null, $relations);

        return $this->sendResponse($users, 'Users retrieved successfully');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreUserRequest $request): JsonResponse
    {
        $this->userService->create($request->validated() + ['email_verified_at' => now()]);

        return $this->sendResponse(
            null,
            'User created successfully',
            Response::HTTP_CREATED
        );
    }

    /**
     * Display the specified resource.
     */
    public function show(int $id): JsonResponse
    {
        $relations = [];
        if (request()->has('include')) {
            $relations = explode(',', request()->get('include'));
        }

        $user = $this->userService->getById($id, $relations);

        return response()->json(['data' => new UserResource($user), 'message' => 'User retrieved successfully']);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUserRequest $request, User $user): JsonResponse
    {
        $this->userService->update($user->id, $request->validated());

        return response()->json([
            'message' => 'User updated successfully',
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user): JsonResponse
    {
        $this->userService->delete($user->id);

        return response()->json([
            'message' => 'User deleted successfully',
        ], Response::HTTP_NO_CONTENT);
    }

    public function me(): JsonResponse
    {
        $user = auth()->user();

        return response()->json(['data' => new UserResource($user), 'message' => 'User retrieved successfully']);
    }
}
