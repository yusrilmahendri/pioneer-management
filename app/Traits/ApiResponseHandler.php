<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Throwable;

trait ApiResponseHandler
{
    /**
     * Return a success JSON response.
     */
    protected function successResponse(array|object $data = [], string $message = 'Operation successful', int $statusCode = 200): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'message' => $message,
            'data' => $data,
            'timestamp' => now()->toISOString()
        ], $statusCode);
    }

    /**
     * Return an error JSON response.
     */
    protected function errorResponse(string $message = 'An error occurred', int $statusCode = 500, array $errors = [], array $data = []): JsonResponse
    {
        $response = [
            'status' => 'error',
            'message' => $message,
            'timestamp' => now()->toISOString()
        ];

        if (!empty($errors)) {
            $response['errors'] = $errors;
        }

        if (!empty($data)) {
            $response['data'] = $data;
        }

        return response()->json($response, $statusCode);
    }

    /**
     * Return a validation error response.
     */
    protected function validationErrorResponse(ValidationException $exception): JsonResponse
    {
        return $this->errorResponse(
            message: 'Validation failed',
            statusCode: 422,
            errors: $exception->errors()
        );
    }

    /**
     * Handle common exceptions and return appropriate responses.
     */
    protected function handleException(Throwable $exception): JsonResponse
    {
        // Log the exception for debugging
        \Log::error('API Exception: ' . $exception->getMessage(), [
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTraceAsString()
        ]);

        return match (true) {
            $exception instanceof ValidationException => $this->validationErrorResponse($exception),
            
            $exception instanceof ModelNotFoundException => $this->errorResponse(
                message: 'Resource not found',
                statusCode: 404
            ),
            
            $exception instanceof NotFoundHttpException => $this->errorResponse(
                message: 'Endpoint not found',
                statusCode: 404
            ),
            
            $exception instanceof AccessDeniedHttpException => $this->errorResponse(
                message: 'Access denied. Insufficient permissions.',
                statusCode: 403
            ),
            
            $exception instanceof QueryException => $this->handleDatabaseException($exception),
            
            default => $this->errorResponse(
                message: config('app.debug') ? $exception->getMessage() : 'Internal server error',
                statusCode: 500
            )
        };
    }

    /**
     * Handle database-specific exceptions.
     */
    protected function handleDatabaseException(QueryException $exception): JsonResponse
    {
        $errorCode = $exception->errorInfo[1] ?? null;
        
        return match ($errorCode) {
            1062 => $this->errorResponse( // Duplicate entry
                message: 'Duplicate entry. Record already exists.',
                statusCode: 409
            ),
            
            1452 => $this->errorResponse( // Foreign key constraint
                message: 'Invalid reference. Related record does not exist.',
                statusCode: 400
            ),
            
            1451 => $this->errorResponse( // Cannot delete parent row
                message: 'Cannot delete record. It is referenced by other records.',
                statusCode: 409
            ),
            
            default => $this->errorResponse(
                message: config('app.debug') ? $exception->getMessage() : 'Database error occurred',
                statusCode: 500
            )
        };
    }

    /**
     * Handle usecase results with consistent format.
     */
    protected function handleUsecaseResult(array $result): JsonResponse
    {
        if ($result['status'] === 'success') {
            return $this->successResponse(
                data: $result['data'] ?? [],
                message: $result['message'] ?? 'Operation successful',
                statusCode: $result['code'] ?? 200
            );
        }

        return $this->errorResponse(
            message: $result['message'] ?? 'Operation failed',
            statusCode: $this->determineErrorStatusCode($result['message'] ?? ''),
            errors: $result['errors'] ?? []
        );
    }

    /**
     * Determine appropriate HTTP status code based on error message.
     */
    private function determineErrorStatusCode(string $message): int
    {
        return match (true) {
            str_contains(strtolower($message), 'not found') => 404,
            str_contains(strtolower($message), 'unauthorized') => 401,
            str_contains(strtolower($message), 'forbidden') ||
            str_contains(strtolower($message), 'permission') ||
            str_contains(strtolower($message), 'access denied') => 403,
            str_contains(strtolower($message), 'validation') => 422,
            str_contains(strtolower($message), 'duplicate') ||
            str_contains(strtolower($message), 'already exists') => 409,
            str_contains(strtolower($message), 'invalid') ||
            str_contains(strtolower($message), 'bad request') => 400,
            default => 500
        };
    }

    /**
     * Execute a callback with exception handling.
     */
    protected function executeWithErrorHandling(callable $callback): JsonResponse
    {
        try {
            $result = $callback();
            
            // If result is array (from usecase), handle it appropriately
            if (is_array($result)) {
                return $this->handleUsecaseResult($result);
            }
            
            // If result is already a JsonResponse, return it
            if ($result instanceof JsonResponse) {
                return $result;
            }
            
            // Otherwise, wrap in success response
            return $this->successResponse($result);
            
        } catch (Throwable $exception) {
            return $this->handleException($exception);
        }
    }
}