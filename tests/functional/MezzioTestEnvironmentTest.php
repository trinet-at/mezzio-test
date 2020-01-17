<?php

declare(strict_types=1);

namespace Trinet\Test\Functional\MezzioTest;

use Fig\Http\Message\StatusCodeInterface;
use Laminas\Diactoros\ServerRequest;
use PHPUnit\Framework\TestCase;
use Trinet\MezzioTest\MezzioTestEnvironment;

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
}
