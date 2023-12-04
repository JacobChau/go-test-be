<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class UserController extends Controller
{
    public function __construct(private readonly UserService $userService)
    {
    }

    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        try {
            $relations = [];
            if (request()->has('include')) {
                $relations = explode(',', request()->get('include'));
            }

            $users = $this->userService->getList(request()->all(), null, $relations, UserResource::class);
        } catch (Throwable $th) {
            return response()->json([
                'message' => $th->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return $this->sendResponse($users, 'Users retrieved successfully');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreUserRequest $request) : JsonResponse
    {
        // admin can create user with verified email
        $this->userService->forceCreate($request->validated() + ['email_verified_at' => now()]);

        return response()->json([
            'message' => __('User successfully created.'),
        ], Response::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        try {
            $relations = [];
            if (request()->has('include')) {
                $relations = explode(',', request()->get('include'));
            }

            $user = $this->userService->getById($id, $relations);

        } catch (Throwable $th) {
            return response()->json([
                'message' => $th->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

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
}
