<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\StoreUserRequest;
use App\Http\Resources\UserResource;
use App\Services\UserService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AuthController extends Controller
{
    public function __construct(private readonly UserService $userService) {
    }

    /**
     * Register user
     */
    public function register(StoreUserRequest $request) : JsonResponse
    {
        $user = $this->userService->create($request->validated());

        event(new Registered($user));

        return response()->json([
            'message' => __('Account successfully registered.'),
        ], Response::HTTP_CREATED);
    }

    /**
     * Login user
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $credentials = $request->only(['email', 'password']);

        if (!Auth::attempt($credentials)) {
            return response()->json([
                'message' => __('Invalid credentials'),
            ], Response::HTTP_UNAUTHORIZED);
        }

        $user = $this->userService->getByEmail($request['email']);

        $token = $user->createToken('authToken')->plainTextToken;

        return response()->json([
            'user' => new UserResource($user),
            'token' => $token,
        ], Response::HTTP_OK);
    }
}
