<?php

use App\Exceptions\Handler as AppExceptionHandler;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
        api: __DIR__ . '/../routes/api.php',
    )
    ->withMiddleware(function (Middleware $middleware) {
        //
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->reportable(function (Throwable $e) {
            Illuminate\Support\Facades\Log::error($e);
        });

        $exceptions->renderable(function (Throwable $e, Request $request) {
            if ($request->wantsJson()) {
                $handler = new AppExceptionHandler(app());

                return $handler->render($request, $e);
            }
        });
    })->create();
