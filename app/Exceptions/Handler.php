<?php

namespace App\Exceptions;

use App\Traits\ApiResponse;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use PDOException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Illuminate\Auth\AuthenticationException;

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
        $this->renderable(function (Throwable $e, Request $request) {
            if ($request->wantsJson()) {
                Log::error($e);

                if ($e instanceof ValidationException) {
                    return $this->errorResponse($e->getMessage(), Response::HTTP_UNPROCESSABLE_ENTITY, $e->errors());
                }

                if ($e instanceof AuthenticationException) {
                    return $this->errorResponse('Unauthenticated', Response::HTTP_UNAUTHORIZED);
                }

                if ($e instanceof HttpException) {
                    return $this->errorResponse($e->getMessage(), $e->getStatusCode());
                }

                if ($e instanceof PDOException) {
                    return $this->errorResponse('Database Error', Response::HTTP_INTERNAL_SERVER_ERROR);
                }

                if ($e instanceof ThrottleRequestsException) {
                    return $this->errorResponse('Too many requests!', Response::HTTP_TOO_MANY_REQUESTS);
                }

                if (config('app.debug')) {
                    return parent::render($request, $e);
                }
                
                Log::error($e);
                return $this->errorResponse('Something went wrong, please try again later.', Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        });
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Throwable  $exception
     * @return \Illuminate\Http\Response
     */
    public function render($request, Throwable $exception)
    {
        if ($exception instanceof MethodNotAllowedHttpException) {
            return $this->errorResponse('The requested method is not allowed for this route.', Response::HTTP_METHOD_NOT_ALLOWED, ['allowed_methods' => $exception->getHeaders()['Allow'] ?? []], $exception->getHeaders());
        }

        return parent::render($request, $exception);
    }
}
