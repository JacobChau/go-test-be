<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller as BaseController;
use Symfony\Component\HttpFoundation\Response;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    public function sendResponse($result, $message, $code = Response::HTTP_OK): JsonResponse
    {
        if (isset($result['meta'])) {
            return response()->json([
                'data' => $result['data'],
                'meta' => $result['meta'],
                'message' => $message,
            ], $code);
        }

        return response()->json([
            'data' => $result,
            'message' => $message,
        ], $code);
    }
}
