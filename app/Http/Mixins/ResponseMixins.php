<?php

namespace App\Http\Mixins;

use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class ResponseMixins
{
    public function successResponse()
    {
        return function ($data, $message = null, $code = Response::HTTP_OK) {
            $message = (!$message) ? Lang::get('messages.success') : $message;
            return response()->json([
                'code' => $code,
                'status' => 1,
                'errors' => null,
                'message' => $message,
                'data' => $data
            ], $code);
        };
    }

    public function errorResponse()
    {
        return function ($message = null, $data = [], $code = Response::HTTP_BAD_REQUEST, $status = 0) {
            if ($code == 0) $code = 400;
            $message = (!$message) ? Lang::get('messages.fail_msg') : $message;
            return response()->json([
                'code' => $code,
                'status' => $status,
                'errors' => $data,
                'message' => $message,
                'data' => null
            ], $code);
        };
    }

    public function handleException()
    {
        return function (\Exception $e, string $operation, int $statusCode = 500) {
            Log::error("Failed to {$operation}: " . $e->getMessage(), [
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            // Show detailed errors in development environment only
            if (config('app.debug')) {
                return response()->errorResponse(
                    "Failed to {$operation}",
                    [
                        'message' => $e->getMessage(),
                        'file' => $e->getFile(),
                        'line' => $e->getLine(),
                        'code' => $e->getCode(),
                        'class' => get_class($e)
                    ],
                    $statusCode
                );
            }

            return response()->errorResponse("Failed to {$operation}", [], $statusCode);
        };
    }

    public function handleDatabaseException()
    {
        return function (\Illuminate\Database\QueryException $e, string $operation) {
            if ($e->getCode() === '23000') {
                return response()->errorResponse('Duplicate record found', [], 400);
            }

            // Show detailed errors in development environment only
            if (config('app.debug')) {
                return response()->errorResponse(
                    "Database error while trying to {$operation}",
                    [
                        'message' => $e->getMessage(),
                        'sql' => $e->getSql(), // This might not be available depending on Laravel version
                        'bindings' => $e->getBindings(), // This might not be available depending on Laravel version
                        'code' => $e->getCode(),
                    ],
                    500
                );
            }

            return response()->handleException($e, $operation);
        };
    }

    //exception checking for a row in the db
    public function handleModelNotFoundException()
    {
        return function (\Illuminate\Database\Eloquent\ModelNotFoundException $e, string $modelName = 'Record') {
            return response()->errorResponse("{$modelName} not found", [], 404);
        };
    }
}
