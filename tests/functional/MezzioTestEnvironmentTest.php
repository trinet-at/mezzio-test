<?php

declare(strict_types=1);

namespace Trinet\Test\Functional\MezzioTest;

use Fig\Http\Message\StatusCodeInterface;
use Laminas\Diactoros\ServerRequest;
use LogicException;
use PHPUnit\Framework\TestCase;
use Trinet\MezzioTest\MezzioTestEnvironment;

use function dirname;
use function json_encode;

/**
 * @internal
 *
 * @small
 *
 * @coversDefaultClass \Trinet\MezzioTest\MezzioTestEnvironment
 */
final class MezzioTestEnvironmentTest extends TestCase
{
    private MezzioTestEnvironment $mezzio;

    protected function setUp(): void
    {
        parent::setUp();
        $basePath = dirname(__DIR__);
        $this->mezzio = new MezzioTestEnvironment($basePath);
    }

    /**
     * @covers \Trinet\MezzioTest\MezzioTestEnvironment::__construct
     * @covers \Trinet\MezzioTest\MezzioTestEnvironment::application
     * @covers \Trinet\MezzioTest\MezzioTestEnvironment::requirePath
     * @covers \Trinet\MezzioTest\MezzioTestEnvironment::container
     * @covers \Trinet\MezzioTest\MezzioTestEnvironment::dispatch
     * @covers \Trinet\MezzioTest\MezzioTestEnvironment::get
     * @covers \Trinet\MezzioTest\MezzioTestEnvironment::request
     * @covers \Trinet\MezzioTest\TestConfigProvider::isTesting
     * @covers \Trinet\MezzioTest\TestConfigProvider::load
     * @covers \Trinet\MezzioTest\TestConfigProvider::prepareConfigDir
     * @covers \Trinet\MezzioTest\Util::ensureTrailingSlash
     */
    public function testCustomErrorHandlerRethrowsException(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('I have an error');

        $this->mezzio->get('/error');
    }

    /**
     * @covers \Trinet\MezzioTest\MezzioTestEnvironment::__construct
     * @covers \Trinet\MezzioTest\MezzioTestEnvironment::application
     * @covers \Trinet\MezzioTest\MezzioTestEnvironment::assertSameResponseBody
     * @covers \Trinet\MezzioTest\MezzioTestEnvironment::assertSameResponseHeaders
     * @covers \Trinet\MezzioTest\MezzioTestEnvironment::assertSameResponseReasonPhrase
     * @covers \Trinet\MezzioTest\MezzioTestEnvironment::assertSameResponseStatusCode
     * @covers \Trinet\MezzioTest\MezzioTestEnvironment::requirePath
     * @covers \Trinet\MezzioTest\MezzioTestEnvironment::container
     * @covers \Trinet\MezzioTest\MezzioTestEnvironment::dispatch
     * @covers \Trinet\MezzioTest\MezzioTestEnvironment::get
     * @covers \Trinet\MezzioTest\MezzioTestEnvironment::request
     * @covers \Trinet\MezzioTest\TestConfigProvider::isTesting
     * @covers \Trinet\MezzioTest\TestConfigProvider::load
     * @covers \Trinet\MezzioTest\TestConfigProvider::prepareConfigDir
     * @covers \Trinet\MezzioTest\Util::ensureTrailingSlash
     */
    public function testDispatch(): void
    {
        $result = $this->mezzio->get('/');

        $this->mezzio
            ->assertSameResponseBody('Hi')
            ->assertSameResponseStatusCode(StatusCodeInterface::STATUS_OK)
            ->assertSameResponseReasonPhrase('OK')
            ->assertSameResponseHeaders([
                'content-type' => ['text/plain; charset=utf-8'],
            ]);
        self::assertSame('Hi', (string)$result->getBody());
        self::assertSame(StatusCodeInterface::STATUS_OK, $result->getStatusCode());
    }

    /**
     * @covers \Trinet\MezzioTest\MezzioTestEnvironment::__construct
     * @covers \Trinet\MezzioTest\MezzioTestEnvironment::application
     * @covers \Trinet\MezzioTest\MezzioTestEnvironment::assertResponseBodyContainsString
     * @covers \Trinet\MezzioTest\MezzioTestEnvironment::assertSameMatchedRouteName
     * @covers \Trinet\MezzioTest\MezzioTestEnvironment::assertSameResponseBody
     * @covers \Trinet\MezzioTest\MezzioTestEnvironment::assertSameResponseStatusCode
     * @covers \Trinet\MezzioTest\MezzioTestEnvironment::requirePath
     * @covers \Trinet\MezzioTest\MezzioTestEnvironment::container
     * @covers \Trinet\MezzioTest\MezzioTestEnvironment::dispatch
     * @covers \Trinet\MezzioTest\MezzioTestEnvironment::generateUri
     * @covers \Trinet\MezzioTest\MezzioTestEnvironment::get
     * @covers \Trinet\MezzioTest\MezzioTestEnvironment::request
     * @covers \Trinet\MezzioTest\TestConfigProvider::isTesting
     * @covers \Trinet\MezzioTest\TestConfigProvider::load
     * @covers \Trinet\MezzioTest\TestConfigProvider::prepareConfigDir
     * @covers \Trinet\MezzioTest\Util::ensureTrailingSlash
     */
    public function testDispatchGeneratedRoute(): void
    {
        $route = $this->mezzio->generateUri('home');

        $response = $this->mezzio->get($route);

        $this->mezzio->assertSameResponseBody('Hi')
            ->assertResponseBodyContainsString('H')
            ->assertSameMatchedRouteName('home')
            ->assertSameResponseStatusCode(StatusCodeInterface::STATUS_OK);

        self::assertSame('Hi', (string)$response->getBody());
    }

    /**
     * @covers \Trinet\MezzioTest\MezzioTestEnvironment::__construct
     * @covers \Trinet\MezzioTest\MezzioTestEnvironment::application
     * @covers \Trinet\MezzioTest\MezzioTestEnvironment::assertSameRequestHeaders
     * @covers \Trinet\MezzioTest\MezzioTestEnvironment::assertSameResponseBody
     * @covers \Trinet\MezzioTest\MezzioTestEnvironment::assertSameResponseStatusCode
     * @covers \Trinet\MezzioTest\MezzioTestEnvironment::requirePath
     * @covers \Trinet\MezzioTest\MezzioTestEnvironment::container
     * @covers \Trinet\MezzioTest\MezzioTestEnvironment::dispatch
     * @covers \Trinet\MezzioTest\MezzioTestEnvironment::get
     * @covers \Trinet\MezzioTest\MezzioTestEnvironment::request
     * @covers \Trinet\MezzioTest\TestConfigProvider::isTesting
     * @covers \Trinet\MezzioTest\TestConfigProvider::load
     * @covers \Trinet\MezzioTest\TestConfigProvider::prepareConfigDir
     * @covers \Trinet\MezzioTest\Util::ensureTrailingSlash
     */
    public function testDispatchHeadersArePassedToRequest(): void
    {
        $headers = [
            'foo' => 'bar',
        ];
        $expected = [
            'foo' => ['bar'],
        ];

        $this->mezzio->get(uri: '/', headers: $headers);

        $this->mezzio->assertSameRequestHeaders($expected)
            ->assertSameResponseStatusCode(200)
            ->assertSameResponseBody('Hi');
    }

    /**
     * @covers \Trinet\MezzioTest\MezzioTestEnvironment::__construct
     * @covers \Trinet\MezzioTest\MezzioTestEnvironment::application
     * @covers \Trinet\MezzioTest\MezzioTestEnvironment::assertSameMatchedRouteName
     * @covers \Trinet\MezzioTest\MezzioTestEnvironment::assertSameRequestParsedBody
     * @covers \Trinet\MezzioTest\MezzioTestEnvironment::assertSameResponseBody
     * @covers \Trinet\MezzioTest\MezzioTestEnvironment::requirePath
     * @covers \Trinet\MezzioTest\MezzioTestEnvironment::container
     * @covers \Trinet\MezzioTest\MezzioTestEnvironment::dispatch
     * @covers \Trinet\MezzioTest\MezzioTestEnvironment::post
     * @covers \Trinet\MezzioTest\MezzioTestEnvironment::request
     * @covers \Trinet\MezzioTest\TestConfigProvider::isTesting
     * @covers \Trinet\MezzioTest\TestConfigProvider::load
     * @covers \Trinet\MezzioTest\TestConfigProvider::prepareConfigDir
     * @covers \Trinet\MezzioTest\Util::ensureTrailingSlash
     */
    public function testDispatchParamsArePassedToParsedBodyForPostRequest(): void
    {
        $params = [
            'foo' => 'bar',
        ];
        $this->mezzio->post('/post', $params);

        $this->mezzio->assertSameMatchedRouteName('post')
            ->assertSameRequestParsedBody($params)
            ->assertSameResponseBody(json_encode($params));
    }

    /**
     * @covers \Trinet\MezzioTest\MezzioTestEnvironment::__construct
     * @covers \Trinet\MezzioTest\MezzioTestEnvironment::application
     * @covers \Trinet\MezzioTest\MezzioTestEnvironment::assertSameRequestQueryParams
     * @covers \Trinet\MezzioTest\MezzioTestEnvironment::requirePath
     * @covers \Trinet\MezzioTest\MezzioTestEnvironment::container
     * @covers \Trinet\MezzioTest\MezzioTestEnvironment::dispatch
     * @covers \Trinet\MezzioTest\MezzioTestEnvironment::get
     * @covers \Trinet\MezzioTest\MezzioTestEnvironment::request
     * @covers \Trinet\MezzioTest\TestConfigProvider::isTesting
     * @covers \Trinet\MezzioTest\TestConfigProvider::load
     * @covers \Trinet\MezzioTest\TestConfigProvider::prepareConfigDir
     * @covers \Trinet\MezzioTest\Util::ensureTrailingSlash
     */
    public function testDispatchParamsArePassedToQueryForGetRequest(): void
    {
        $params = [
            'foo' => 'bar',
        ];
        $this->mezzio->get('/', $params);

        $this->mezzio->assertSameRequestQueryParams($params);
    }

    /**
     * @covers \Trinet\MezzioTest\MezzioTestEnvironment::__construct
     * @covers \Trinet\MezzioTest\MezzioTestEnvironment::application
     * @covers \Trinet\MezzioTest\MezzioTestEnvironment::requirePath
     * @covers \Trinet\MezzioTest\MezzioTestEnvironment::container
     * @covers \Trinet\MezzioTest\MezzioTestEnvironment::dispatch
     * @covers \Trinet\MezzioTest\TestConfigProvider::isTesting
     * @covers \Trinet\MezzioTest\TestConfigProvider::load
     * @covers \Trinet\MezzioTest\TestConfigProvider::prepareConfigDir
     * @covers \Trinet\MezzioTest\Util::ensureTrailingSlash
     */
    public function testDispatchRequest(): void
    {
        $request = new ServerRequest([], [], '/');

        $result = $this->mezzio->dispatch($request);

        self::assertSame('Hi', (string)$result->getBody());
        self::assertSame(StatusCodeInterface::STATUS_OK, $result->getStatusCode());
    }

    /**
     * @covers \Trinet\MezzioTest\MezzioTestEnvironment::__construct
     * @covers \Trinet\MezzioTest\MezzioTestEnvironment::application
     * @covers \Trinet\MezzioTest\MezzioTestEnvironment::requirePath
     * @covers \Trinet\MezzioTest\MezzioTestEnvironment::container
     * @covers \Trinet\MezzioTest\MezzioTestEnvironment::dispatch
     * @covers \Trinet\MezzioTest\MezzioTestEnvironment::dispatchRoute
     * @covers \Trinet\MezzioTest\MezzioTestEnvironment::generateUri
     * @covers \Trinet\MezzioTest\MezzioTestEnvironment::request
     * @covers \Trinet\MezzioTest\TestConfigProvider::isTesting
     * @covers \Trinet\MezzioTest\TestConfigProvider::load
     * @covers \Trinet\MezzioTest\TestConfigProvider::prepareConfigDir
     * @covers \Trinet\MezzioTest\Util::ensureTrailingSlash
     */
    public function testDispatchRoute(): void
    {
        $result = $this->mezzio->dispatchRoute('home');
        self::assertSame('Hi', (string)$result->getBody());
        self::assertSame(StatusCodeInterface::STATUS_OK, $result->getStatusCode());
    }

    /**
     * @covers \Trinet\MezzioTest\MezzioTestEnvironment::__construct
     * @covers \Trinet\MezzioTest\MezzioTestEnvironment::application
     * @covers \Trinet\MezzioTest\MezzioTestEnvironment::RequirePath
     * @covers \Trinet\MezzioTest\MezzioTestEnvironment::assertSameMatchedRouteName
     * @covers \Trinet\MezzioTest\MezzioTestEnvironment::container
     * @covers \Trinet\MezzioTest\MezzioTestEnvironment::dispatch
     * @covers \Trinet\MezzioTest\MezzioTestEnvironment::request
     * @covers \Trinet\MezzioTest\MezzioTestEnvironment::router
     * @covers \Trinet\MezzioTest\TestConfigProvider::isTesting
     * @covers \Trinet\MezzioTest\TestConfigProvider::load
     * @covers \Trinet\MezzioTest\TestConfigProvider::prepareConfigDir
     * @covers \Trinet\MezzioTest\Util::ensureTrailingSlash
     */
    public function testDispatchRouter(): void
    {
        $router = $this->mezzio->router();

        $request = $this->mezzio->request('GET', '/');
        self::assertSame('home', $router->match($request)->getMatchedRouteName());

        $this->mezzio->dispatch($request);
        $this->mezzio->assertSameMatchedRouteName('home');
    }

    /**
     * @covers \Trinet\MezzioTest\MezzioTestEnvironment::__construct
     * @covers \Trinet\MezzioTest\MezzioTestEnvironment::application
     * @covers \Trinet\MezzioTest\MezzioTestEnvironment::requirePath
     * @covers \Trinet\MezzioTest\MezzioTestEnvironment::container
     * @covers \Trinet\MezzioTest\TestConfigProvider::isTesting
     * @covers \Trinet\MezzioTest\TestConfigProvider::load
     * @covers \Trinet\MezzioTest\TestConfigProvider::prepareConfigDir
     * @covers \Trinet\MezzioTest\Util::ensureTrailingSlash
     */
    public function testRuntimeIsSetToAppTesting(): void
    {
        /** @var array<string, mixed> $config */
        $config = $this->mezzio->container()
            ->get('config');

        self::assertTrue($config['testing']);
    }
}
