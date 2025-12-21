<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;

trait HandleResponse
{
    /**
     * Success response method.
     *
     * @param string|null $message
     * @param mixed $data
     * @param int $status
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendResponse(string $message = null, $data = null, int $status = 200): JsonResponse
    {
        $response = [
            'success' => true,
            'message' => $message ?? "Successful response returned",
        ];

        if (!empty($data)) {
            $response['data'] = $data;
        }

        return response()->json($response, $status);
    }

    /**
     * Return error response.
     *
     * @param string|null $error
     * @param array $errorMessages
     * @param int $status
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendError(string $error = null, $errorMessages = [], int $status = 404): JsonResponse
    {
        $response = [
            'success' => false,
            'message' => $error ?? "An error has occurred",
        ];

        if (!empty($errorMessages)) {
            $response['errors'] = $errorMessages;
        }

        return response()->json($response, $status);
    }
}
