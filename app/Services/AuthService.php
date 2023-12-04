<?php

namespace App\Services;

use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Symfony\Component\HttpFoundation\Response;

class AuthService extends BaseService
{
    public function login(array $credentials): string
    {
        if (! $token = auth()->attempt($credentials)) {
            return '';
        }

        return $token;
    }

    public function forgotPassword(string $email): string
    {
        return Password::sendResetLink(
            ['email' => $email]
        );
    }

    public function resetPassword(array $credentials): string
    {
        return Password::reset(
            $credentials,
            function ($user) use ($credentials) {
                $user->forceFill([
                    'password' => Hash::make($credentials['password']),
                ])->save();

                event(new PasswordReset($user));
            }
        );
    }

    public function changePassword($user, string $newPassword): void
    {
        $user->forceFill([
            'password' => Hash::make($newPassword),
        ])->save();
    }

    public function verifyEmail(EmailVerificationRequest $request): void
    {
        $this->checkIfEmailVerified($request);

        $request->fulfill();
    }

    public function resendVerificationEmail(Request $request): void
    {
        $this->checkIfEmailVerified($request);

        $request->user()->sendEmailVerificationNotification();
    }

    private function checkIfEmailVerified(Request $request) : void
    {
        if ($request->user()->hasVerifiedEmail()) {
            response()->json([
                'message' => __('Email already verified'),
            ], Response::HTTP_NO_CONTENT);
        }
    }
}
