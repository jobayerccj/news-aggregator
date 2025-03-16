<?php

namespace App\Exceptions;

use App\Traits\ApiResponse;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use PDOException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    use ApiResponse;

    /**
     * The list of the exception types that are not reported.
     *
     * @var array<int, class-string<Throwable>>
     */
    protected $dontReport = [];

    /**
     * The list of the inputs that are never flashed for validation exceptions.
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
        $this->renderable(function (ValidationException $e, Request $request) {
            $errorMessage = 'The given data was invalid.';

            return $this->handleJsonValidationException($e, $request, $errorMessage, Response::HTTP_UNPROCESSABLE_ENTITY, $e->errors());
        });

        $this->renderable(function (AuthenticationException $e, Request $request) {
            return $this->handleJsonValidationException($e, $request, 'Unauthenticated', Response::HTTP_UNAUTHORIZED);
        });

        $this->renderable(function (ModelNotFoundException|NotFoundHttpException $e, Request $request) {
            return $this->handleJsonValidationException($e, $request, 'Resource not found, please recheck your provided data.', $e->getStatusCode());
        });

        $this->renderable(function (HttpException $e, Request $request) {
            return $this->handleJsonValidationException($e, $request, $e->getMessage(), $e->getStatusCode());
        });

        $this->renderable(function (PDOException $e, Request $request) {
            return $this->handleJsonValidationException($e, $request, 'Database Error', Response::HTTP_INTERNAL_SERVER_ERROR);
        });

        $this->renderable(function (ThrottleRequestsException $e, Request $request) {
            return $this->handleJsonValidationException($e, $request, 'Too many requests!', Response::HTTP_TOO_MANY_REQUESTS);
        });

        $this->renderable(function (Throwable $e, Request $request) {
            if ($request->wantsJson()) {
                Log::error($e);
                if (config('app.debug')) {
                    return parent::render($request, $e);
                }

                return $this->errorResponse('Something went wrong, please try again later.', Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        });
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  Request  $request
     * @param  Throwable  $exception
     * @return Response
     */
    public function render($request, Throwable $exception)
    {
        if ($exception instanceof MethodNotAllowedHttpException) {
            return $this->errorResponse('The requested method is not allowed for this route.', Response::HTTP_METHOD_NOT_ALLOWED);
        }

        return parent::render($request, $exception);
    }

    /**
     * @param mixed $e
     * @param Request $request
     * @param string $message
     * @param int $statusCode
     * @param mixed $errors
     */
    private function handleJsonValidationException($e, Request $request, $message, $statusCode, $errors = null)
    {
        if ($request->wantsJson()) {
            Log::error($message, [method_exists($e, 'errors') ? $e->errors() : null]);

            return $this->errorResponse($message, $statusCode, $errors);
        }
    }
}
