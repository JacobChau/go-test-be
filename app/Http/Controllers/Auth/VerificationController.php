<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerificationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('signed')->only('verify');
        $this->middleware('throttle:6,1')->only('verify', 'resend');
    }

    public function verify(EmailVerificationRequest $request) : JsonResponse
    {
        $this->checkIfEmailVerified($request);

        $request->fulfill();

        return response()->json([
            'message' => __('Email verified'),
        ], Response::HTTP_OK);
    }

    public function resend(Request $request) : JsonResponse
    {
        $this->checkIfEmailVerified($request);

        $request->user()->sendEmailVerificationNotification();

        return response()->json([
            'message' => __('Email verification link sent on your email id'),
        ], Response::HTTP_ACCEPTED);
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
