<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\StoreUserRequest;
use App\Http\Resources\UserResource;
use App\Services\UserService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
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

        return $this->sendResponse(
            __('User registered successfully'),
            Response::HTTP_CREATED
        );
    }

    /**
     * Login user
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $credentials = $request->only(['email', 'password']);

        if (! $token = auth()->attempt($credentials)) {
            return response()->json([
                'message' => __('Invalid credentials'),
            ], Response::HTTP_UNAUTHORIZED);
        }

        return $this->sendResponse([
            'user' => new UserResource(auth()->user()),
            'access_token' => $token,
        ], __('User logged in successfully'));
    }

    /**
     * Get the authenticated User.
     *
     * @return JsonResponse
     */
    public function me(): JsonResponse
    {
        return response()->json(auth()->user());
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return JsonResponse
     */
    public function logout(): JsonResponse
    {
        auth()->logout();

        return response()->json(['message' => 'Successfully logged out']);
    }

    /**
     * Refresh a token.
     *
     * @return JsonResponse
     */
    public function refresh(): JsonResponse
    {
        return $this->sendResponse([
            'access_token' => auth()->refresh(),
        ], __('Token refreshed successfully'));
    }
}
