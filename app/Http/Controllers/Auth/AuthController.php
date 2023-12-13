<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginGoogleRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\StoreUserRequest;
use App\Http\Resources\UserResource;
use App\Services\AuthService;
use App\Services\UserService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class AuthController extends Controller
{
    protected UserService $userService;
    protected AuthService $authService;

    public function __construct(UserService $userService, AuthService $authService) {
        $this->userService = $userService;
        $this->authService = $authService;
    }

    /**
     * Register user
     */
    public function register(StoreUserRequest $request) : JsonResponse
    {
        $user = $this->userService->create($request->validated());

        event(new Registered($user));

        return $this->sendResponse(
            null,
            'User registered successfully',
            Response::HTTP_CREATED
        );
    }

    /**
     * Login user
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $token = $this->authService->login($request->validated());

        if (! $token) {
            return response()->json([
                'message' => 'Invalid credentials'
            ], Response::HTTP_UNAUTHORIZED);
        }

        return $this->sendResponse([
            'user' => new UserResource(auth()->user()),
            'accessToken' => $token,
        ], 'User logged in successfully');
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
     * Refresh a token.
     *
     * @return JsonResponse
     */
    public function refresh(): JsonResponse
    {
        return $this->sendResponse([
            'accessToken' => auth()->refresh(),
        ], 'Token refreshed successfully');
    }

    /**
     * Login user with Google
     */
    public function loginWithGoogle(LoginGoogleRequest $request): JsonResponse {
        $validated = $request->validated();
        $token = $this->authService->loginWithGoogle($validated['accessToken']);

        if (! $token) {
            return $this->sendResponse(
                null,
                'Invalid credentials',
                Response::HTTP_UNAUTHORIZED
            );
        }

        return $this->sendResponse([
            'accessToken' => $token,
        ], 'User logged in successfully');
    }

}
