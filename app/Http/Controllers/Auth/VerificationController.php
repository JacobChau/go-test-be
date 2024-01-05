<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\AuthService;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerificationController extends Controller
{
    protected AuthService $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;

        $this->middleware(['auth', 'throttle:6,1'])->only('verify', 'resend');
    }

    public function verify(EmailVerificationRequest $request): JsonResponse
    {
        $this->authService->verifyEmail($request);

        return response()->json([
            'message' => 'Email verified successfully',
        ], Response::HTTP_OK);
    }

    public function resend(Request $request): JsonResponse
    {
        $this->authService->resendVerificationEmail($request);

        return response()->json([
            'message' => 'Email verification link sent on your email id',
        ], Response::HTTP_ACCEPTED);
    }
}
