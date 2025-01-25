<?php

namespace App\Exceptions;

use App\Traits\ApiResponse;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use PDOException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

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
                if ($e instanceof ValidationException) {
                    return $this->errorResponse($e->getMessage(), Response::HTTP_UNPROCESSABLE_ENTITY, $e->errors());
                }

                if ($e instanceof HttpException) {
                    return $this->errorResponse($e->getMessage(), $e->getStatusCode());
                }

                if ($e instanceof PDOException) {
                    return $this->errorResponse('Database Error', Response::HTTP_INTERNAL_SERVER_ERROR);
                }

                if (config('app.debug')) {
                    return parent::render($request, $e);
                }

                return $this->errorResponse('Server Error', Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        });
    }

}
