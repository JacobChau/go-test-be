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

    public function sendResponse($result, $message = null, $code = Response::HTTP_OK): JsonResponse
    {
        if (isset($result['status']) && $result['status'] !== Response::HTTP_OK) {
            return response()->json(['message' => $result['message']], $result['status']);
        }

        $data = $result['data'] ?? $result;

        if (isset($result['message'])) {
            $message = $result['message'];
        }

        if (isset($result['status'])) {
            $code = $result['status'];
        }

        if (isset($result['meta'])) {
            return response()->json([
                'data' => $result['data'],
                'meta' => $result['meta'],
                'message' => $result['message'] ?? $message,
            ], $code);
        }

        return response()->json([
            'data' => $data,
            'message' => $message,
        ], $code);
    }
}
