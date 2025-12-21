<?php

namespace App\Services;

class HandleResponseService
{
    /**
     * success response method.
     *
     * @return \Illuminate\Http\Response
     */
    public function sendResponse($message = null, $data = null, $status = 200)
    {
        $response = [
            'success' => true,
            'message' => $message ?? "Successful response was returned",
        ];

        if(!empty($data)){
            $response['data'] = $data;
        }

        return response()->json($response, $status);
    }

    /**
     * return error response.
     *
     * @return \Illuminate\Http\Response
     */
    public function sendError($error, $errorMessages = [], $code = 404)
    {
        $response = [
            'success' => false,
            'message' => $error ?? "An error has occurred",
        ];

        if(!empty($errorMessages)){
            $response['data'] = $errorMessages;
        }

        return response()->json($response, $code);
    }
}
