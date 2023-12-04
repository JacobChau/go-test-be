<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Routing\Controller as BaseController;
use Symfony\Component\HttpFoundation\Response;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    public function sendResponse($result, $message, $code = Response::HTTP_OK): JsonResponse
    {
        if (isset($result['meta'])) {
            $response = [
                'data' => $result['data'],
                'meta' => $result['meta'],
                'message' => $message,
            ];
        } else {
            $response = [
                'data' => $result,
                'message' => $message,
            ];
        }

        return response()->json($response, $code);
    }
}
