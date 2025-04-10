<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * @param array $data
     * @return JsonResponse
     */
    protected function success(array $data = []): JsonResponse
    {
        $response = ['success' => true];
        if ($data) {
            $response = array_merge($response, $data);
        }

        return response()->json($response);
    }

    /**
     * @param int $status
     * @param array $data
     * @return JsonResponse
     */
    protected function error(int $status = 400, array $data = []): JsonResponse
    {
        $response = ['success' => false];
        if ($data) {
            $response = array_merge($response, $data);
        }

        return response()->json($response, $status);
    }
}
