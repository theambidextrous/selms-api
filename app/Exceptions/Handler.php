<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });

        $this->renderable(function (BadRequestHttpException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => $e->getMessage() ?: 'Invalid request data',
                    'errors' => [] 
                ], 400);
            }
            
            return back()->withInput()->withErrors([
                'message' => $e->getMessage() ?: 'Invalid request data'
            ]);
        });
    }

    /**
     * Render an exception into an HTTP response.
     */
    public function render($request, Throwable $exception)
    {
        if ($request->expectsJson() || $request->is('api/*')) {
            return $this->handleApiException($request, $exception);
        }

        return parent::render($request, $exception);
    }

    /**
     * Handle API exceptions
     */
    protected function handleApiException(Request $request, Throwable $exception): JsonResponse
    {
        $exception = $this->prepareException($exception);
        $statusCode = $this->getExceptionStatusCode($exception);
        $message = $this->getExceptionMessage($exception, $statusCode);

        $response = [
            'success' => false,
            'status' => $statusCode,  // Include status code here
            'message' => $message,
        ];

        if ($exception instanceof ValidationException) {
            $response['errors'] = $exception->errors();
        }

        if (config('app.debug')) {
            $response['exception'] = get_class($exception);
            $response['trace'] = $exception->getTrace();
        }

        return response()->json($response, $statusCode);
    }

    /**
     * Get the status code from the exception
     */
    protected function getExceptionStatusCode(Throwable $exception): int
    {
        if ($exception instanceof HttpException) {
            return $exception->getStatusCode();
        }

        if ($exception instanceof ValidationException) {
            return 400;
        }

        return 500;
    }

    /**
     * Get the appropriate error message
     */
    protected function getExceptionMessage(Throwable $exception, int $statusCode): string
    {
        if (config('app.debug')) {
            return $exception->getMessage();
        }

        return match ($statusCode) {
            400 => 'Bad Request',
            401 => 'Unauthorized',
            403 => 'Forbidden',
            404 => 'Not Found',
            405 => 'Method Not Allowed',
            422 => 'Validation Error',
            429 => 'Too Many Requests',
            500 => 'Internal Server Error',
            default => 'Something went wrong',
        };
    }
}