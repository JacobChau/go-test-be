<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class AuthService extends BaseService
{
    protected UserService $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function loginWithGoogle(string $accessToken): array
    {
        $results = $this->getUserInfoFromGoogle($accessToken);

        $user = $this->userService->firstOrCreate([
            'email' => $results->email,
        ], [
            'avatar' => $results->picture,
            'name' => $results->family_name.' '.$results->given_name,
            'email_verified_at' => now(),
        ]
        );

        return $this->loginAndReturnToken($user);
    }

    public function login(array $credentials): array
    {
        if (! $token = auth()->attempt($credentials)) {
            return [];
        }

        $rememberToken = Str::random(32);

        $this->userService->update(auth()->id(), [
            'remember_token' => $rememberToken,
        ]);

        return [
            'accessToken' => $token,
            'refreshToken' => $rememberToken,
        ];
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

    public function refreshToken(string $refreshToken): array
    {
        $user = $this->userService->findByRememberToken($refreshToken);

        if (! $user) {
            return [];
        }

        // renew remember token
        $rememberToken = Str::random(32);

        $this->userService->update($user->id, [
            'remember_token' => $rememberToken,
        ]);

        return [
            'accessToken' => auth()->tokenById($user->id),
            'refreshToken' => $rememberToken,
        ];
    }

    private function checkIfEmailVerified(Request $request): void
    {
        if ($request->user()->hasVerifiedEmail()) {
            response()->json([
                'message' => __('Email already verified'),
            ], Response::HTTP_NO_CONTENT);
        }
    }

    private function getUserInfoFromGoogle(string $accessToken)
    {
        $client = new Client();
        $response = $client->get('https://www.googleapis.com/oauth2/v3/userinfo?access_token='.$accessToken);

        return json_decode($response->getBody()->getContents());
    }

    private function loginAndReturnToken($user): array
    {
        auth()->login($user);

        $rememberToken = Str::random(32);

        $this->userService->update(auth()->id(), [
            'remember_token' => $rememberToken,
        ]);

        return [
            'accessToken' => auth()->tokenById(auth()->id()),
            'refreshToken' => $rememberToken,
        ];

    }
}
