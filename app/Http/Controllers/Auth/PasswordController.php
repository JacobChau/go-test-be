<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\ChangePasswordRequest;
use App\Http\Requests\ForgotPasswordRequest;
use App\Http\Requests\ResetPasswordRequest;
use App\Services\AuthService;
use Illuminate\Support\Facades\Password;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class PasswordController extends Controller
{
    protected AuthService $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function forgot(ForgotPasswordRequest $request): JsonResponse
    {
        $status = $this->authService->forgotPassword($request->email);

        return $status === Password::RESET_LINK_SENT
            ? response()->json(['message' => $status])
            : response()->json(['message' => $status], Response::HTTP_BAD_REQUEST);
    }

    public function reset(ResetPasswordRequest $request): JsonResponse
    {
        $status = $this->authService->resetPassword($request->only(
            'email', 'password', 'token'
        ));

        return $status === Password::PASSWORD_RESET
            ? response()->json(['message' => $status])
            : response()->json(['message' => $status], Response::HTTP_BAD_REQUEST);
    }

    public function change(ChangePasswordRequest $request): JsonResponse
    {
        $this->authService->changePassword(
            $request->user(),
            $request->newPassword
        );

        return response()->json(['message' => __('Password changed successfully.')]);
    }
}
