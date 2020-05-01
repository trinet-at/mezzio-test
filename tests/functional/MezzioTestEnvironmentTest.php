<?php

declare(strict_types=1);

namespace Trinet\Test\Functional\MezzioTest;

use Fig\Http\Message\RequestMethodInterface;
use Fig\Http\Message\StatusCodeInterface;
use Laminas\Diactoros\Response;
use Laminas\Diactoros\ServerRequest;
use LogicException;
use Mezzio\Application;
use PHPUnit\Framework\TestCase;
use Trinet\MezzioTest\MezzioTestEnvironment;
use Trinet\Test\Functional\MezzioTest\TestDouble\RequestLoggerCallback;

class MezzioTestEnvironmentTest extends TestCase
{
    /** @var MezzioTestEnvironment */
    private $mezzio;

    protected function setUp(): void
    {
        parent::setUp();
        $basePath = dirname(__DIR__);
        $this->mezzio = new MezzioTestEnvironment($basePath);
    }

    public function testDispatch(): void
    {
        $result = $this->mezzio->dispatch('/');

        self::assertSame('Hi', (string)$result->getBody());
        self::assertSame(StatusCodeInterface::STATUS_OK, $result->getStatusCode());
    }

    public function testDispatchRequest(): void
    {
        $request = new ServerRequest([], [], '/');

        $result = $this->mezzio->dispatchRequest($request);

        self::assertSame('Hi', (string)$result->getBody());
        self::assertSame(StatusCodeInterface::STATUS_OK, $result->getStatusCode());
    }

    public function testDispatchRoute(): void
    {
        $result = $this->mezzio->dispatchRoute('home');

        self::assertSame('Hi', (string)$result->getBody());
        self::assertSame(StatusCodeInterface::STATUS_OK, $result->getStatusCode());
    }

    public function testDispatchGeneratedRoute(): void
    {
        $router = $this->mezzio->router();
        $route = $router->generateUri('home');

        $result = $this->mezzio->dispatch($route);

        self::assertSame('Hi', (string)$result->getBody());
        self::assertSame(StatusCodeInterface::STATUS_OK, $result->getStatusCode());
    }

    public function testRuntimeIsSetToAppTesting(): void
    {
        $config = $this->mezzio->container()->get('config');

        self::assertTrue($config['testing']);
    }

    public function testDispatchParamsArePassedToQueryForGetRequest(): void
    {
        $appMock = $this->createMock(Application::class);
        $logger = new RequestLoggerCallback();
        $appMock->method('handle')->willReturn(new Response())->willReturnCallback($logger);
        ReflectionUtil::setReflectionProperty($this->mezzio, 'app', $appMock);

        $params = ['foo' => 'bar'];
        $this->mezzio->dispatch('/', null, $params);

        $request = $logger->getRequest();
        self::assertSame($request->getQueryParams(), $params);
    }

    public function testDispatchParamsArePassedToParsedBodyForPostRequest(): void
    {
        $appMock = $this->createMock(Application::class);
        $logger = new RequestLoggerCallback();
        $appMock->method('handle')->willReturn(new Response())->willReturnCallback($logger);
        ReflectionUtil::setReflectionProperty($this->mezzio, 'app', $appMock);

        $params = ['foo' => 'bar'];
        $this->mezzio->dispatch('/', RequestMethodInterface::METHOD_POST, $params);

        $request = $logger->getRequest();
        self::assertSame($request->getParsedBody(), $params);
    }

    public function testCustomErrorHandlerRethrowsException(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('I have an error');

        $this->mezzio->dispatch('/error');
    }
}
