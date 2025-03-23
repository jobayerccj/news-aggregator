<?php

namespace Tests\Unit;

use App\Exceptions\Handler;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use Mockery;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Tests\TestCase;
use Throwable;

class HandlerTest extends TestCase
{
    private Handler $handler;

    protected function setUp(): void
    {
        parent::setUp();
        $this->handler = new Handler($this->app);
    }

    public function testHandleJsonValidationException()
    {
        $request = Mockery::mock(Request::class);
        $request->shouldReceive('wantsJson')->andReturn(true);

        $exception = ValidationException::withMessages(['field' => ['error']]);
        $message = 'The given data was invalid.';
        $statusCode = Response::HTTP_UNPROCESSABLE_ENTITY;
        $errors = ['field' => ['error']];

        $response = $this->invokeMethod($this->handler, 'handleJsonValidationException', [$exception, $request, $message, $statusCode, $errors]);
        $responseData = $response->getData(true);

        $this->assertEquals($statusCode, $response->getStatusCode());
        $this->assertArrayHasKey('success', $responseData);
        $this->assertArrayHasKey('message', $responseData);
        $this->assertArrayHasKey('errors', $responseData);
        $this->assertEquals(false, $responseData['success']);
        $this->assertEquals($message, $responseData['message']);
    }

    public function testRegisterValidationException()
    {
        $request = Mockery::mock(Request::class);
        $request->shouldReceive('wantsJson')->andReturn(true);

        $exception = ValidationException::withMessages(['field' => ['error']]);

        $response = $this->invokeRenderable($this->handler, ValidationException::class, $exception, $request);

        $this->assertEquals(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
    }

    public function testRegisterAuthenticationException()
    {
        $request = Mockery::mock(Request::class);
        $request->shouldReceive('wantsJson')->andReturn(true);

        $exception = new AuthenticationException();

        $response = $this->invokeRenderable($this->handler, AuthenticationException::class, $exception, $request);

        $this->assertEquals(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());
    }

    public function testRegisterModelNotFoundException()
    {
        $request = Mockery::mock(Request::class);
        $request->shouldReceive('wantsJson')->andReturn(true);
        $exception = new ModelNotFoundException();

        $response = $this->invokeRenderable($this->handler, ModelNotFoundException::class, $exception, $request);

        $this->assertEquals(404, $response->getStatusCode());
    }

    public function testRegisterHttpException()
    {
        $request = Mockery::mock(Request::class);
        $request->shouldReceive('wantsJson')->andReturn(true);

        $exception = new HttpException(400, 'Bad Request');

        $response = $this->invokeRenderable($this->handler, HttpException::class, $exception, $request);

        $this->assertEquals(400, $response->getStatusCode());
    }

    public function testRegisterPDOException()
    {
        $request = Mockery::mock(Request::class);
        $request->shouldReceive('wantsJson')->andReturn(true);

        $exception = new \PDOException();

        $response = $this->invokeRenderable($this->handler, \PDOException::class, $exception, $request);

        $this->assertEquals(Response::HTTP_INTERNAL_SERVER_ERROR, $response->getStatusCode());
    }

    public function testRegisterThrottleRequestsException()
    {
        $request = Mockery::mock(Request::class);
        $request->shouldReceive('wantsJson')->andReturn(true);

        $exception = new ThrottleRequestsException('Too many requests');

        $response = $this->invokeRenderable($this->handler, ThrottleRequestsException::class, $exception, $request);

        $this->assertEquals(Response::HTTP_TOO_MANY_REQUESTS, $response->getStatusCode());
    }

    public function testRenderMethodNotAllowedHttpException()
    {
        $request = Mockery::mock(Request::class);
        $exception = new MethodNotAllowedHttpException([]);
        $response = $this->handler->render($request, $exception);

        $this->assertEquals(Response::HTTP_METHOD_NOT_ALLOWED, $response->getStatusCode());
    }

    private function invokeMethod($object, string $methodName, array $parameters = [])
    {
        $reflection = new \ReflectionClass(get_class($object));
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $parameters);
    }

    private function invokeRenderable(Handler $handler, string $exceptionClass, Throwable $exception, Request $request)
    {
        $dummyHandler = new Handler(app());

        $dummyException = function ($e, $r) use ($exception, $request) {
            if (get_class($e) === get_class($exception)) {
                return $this->handler->render($request, $exception);
            }

            return null;
        };

        $reflection = new \ReflectionClass(Handler::class);
        $property = $reflection->getProperty('renderCallbacks');
        $property->setAccessible(true);
        $renderCallbacks = $property->getValue($dummyHandler);

        $renderCallbacks[get_class($exception)] = $dummyException;

        $property->setValue($dummyHandler, $renderCallbacks);

        return $dummyHandler->render($request, $exception);
    }
}
