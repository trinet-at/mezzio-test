<?php

declare(strict_types=1);

namespace Trinet\Test\Functional\MezzioTest;

use Fig\Http\Message\StatusCodeInterface;
use Laminas\Diactoros\ServerRequest;
use Laminas\Stratigility\Middleware\CallableMiddlewareDecorator;
use Laminas\Stratigility\Middleware\RequestHandlerMiddleware;
use LogicException;
use Mezzio\Handler\NotFoundHandler;
use PHPUnit\Framework\TestCase;
use Trinet\MezzioTest\MezzioTestEnvironment;

use function dirname;
use function Safe\json_encode;

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
     * @covers \Trinet\MezzioTest\MezzioTestEnvironment::dispatchRequest
     * @covers \Trinet\MezzioTest\MezzioTestEnvironment::match
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
     * @covers \Trinet\MezzioTest\MezzioTestEnvironment::dispatchRequest
     * @covers \Trinet\MezzioTest\MezzioTestEnvironment::match
     * @covers \Trinet\MezzioTest\MezzioTestEnvironment::get
     * @covers \Trinet\MezzioTest\MezzioTestEnvironment::request
     * @covers \Trinet\MezzioTest\TestConfigProvider::isTesting
     * @covers \Trinet\MezzioTest\TestConfigProvider::load
     * @covers \Trinet\MezzioTest\TestConfigProvider::prepareConfigDir
     * @covers \Trinet\MezzioTest\Util::ensureTrailingSlash
     */
    public function testDispatch(): void
    {
        $result = $this->mezzio->dispatch('/');

        $this->mezzio->assertSameResponseBody('Hi');
        $this->mezzio->assertSameResponseStatusCode(StatusCodeInterface::STATUS_OK);
        $this->mezzio->assertSameResponseReasonPhrase('OK');
        $this->mezzio->assertSameResponseHeaders([
            'content-type' => ['text/plain; charset=utf-8'],
        ]);

        self::assertSame('Hi', (string)$result->getBody());
    }

    /**
     * @covers \Trinet\MezzioTest\MezzioTestEnvironment::__construct
     * @covers \Trinet\MezzioTest\MezzioTestEnvironment::application
     * @covers \Trinet\MezzioTest\MezzioTestEnvironment::assertResponseBodyContainsString
     * @covers \Trinet\MezzioTest\MezzioTestEnvironment::assertSameRouteMiddlewareOrResponseHandler
     * @covers \Trinet\MezzioTest\MezzioTestEnvironment::assertSameMatchedRouteName
     * @covers \Trinet\MezzioTest\MezzioTestEnvironment::assertSameResponseBody
     * @covers \Trinet\MezzioTest\MezzioTestEnvironment::assertSameResponseStatusCode
     * @covers \Trinet\MezzioTest\MezzioTestEnvironment::requirePath
     * @covers \Trinet\MezzioTest\MezzioTestEnvironment::container
     * @covers \Trinet\MezzioTest\MezzioTestEnvironment::dispatchRequest
     * @covers \Trinet\MezzioTest\MezzioTestEnvironment::match
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

        $this->mezzio->assertSameResponseBody('Hi');
        $this->mezzio->assertResponseBodyContainsString('H');
        $this->mezzio->assertSameMatchedRouteName('home');
        $this->mezzio->assertSameRouteMiddlewareOrResponseHandler(CallableMiddlewareDecorator::class);
        $this->mezzio->assertSameResponseStatusCode(StatusCodeInterface::STATUS_OK);
    }

    /**
     * @covers \Trinet\MezzioTest\MezzioTestEnvironment::__construct
     * @covers \Trinet\MezzioTest\MezzioTestEnvironment::application
     * @covers \Trinet\MezzioTest\MezzioTestEnvironment::assertSameRequestHeaders
     * @covers \Trinet\MezzioTest\MezzioTestEnvironment::assertSameResponseBody
     * @covers \Trinet\MezzioTest\MezzioTestEnvironment::assertSameResponseStatusCode
     * @covers \Trinet\MezzioTest\MezzioTestEnvironment::requirePath
     * @covers \Trinet\MezzioTest\MezzioTestEnvironment::container
     * @covers \Trinet\MezzioTest\MezzioTestEnvironment::dispatchRequest
     * @covers \Trinet\MezzioTest\MezzioTestEnvironment::match
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

        $this->mezzio->assertSameRequestHeaders($expected);
        $this->mezzio->assertSameResponseStatusCode(200);
        $this->mezzio->assertSameResponseBody('Hi');
    }

    /**
     * @covers \Trinet\MezzioTest\MezzioTestEnvironment::__construct
     * @covers \Trinet\MezzioTest\MezzioTestEnvironment::application
     * @covers \Trinet\MezzioTest\MezzioTestEnvironment::assertSameRouteMiddlewareOrResponseHandler
     * @covers \Trinet\MezzioTest\MezzioTestEnvironment::assertSameMatchedRouteName
     * @covers \Trinet\MezzioTest\MezzioTestEnvironment::assertSameRequestParsedBody
     * @covers \Trinet\MezzioTest\MezzioTestEnvironment::assertSameResponseBody
     * @covers \Trinet\MezzioTest\MezzioTestEnvironment::container
     * @covers \Trinet\MezzioTest\MezzioTestEnvironment::dispatchRequest
     * @covers \Trinet\MezzioTest\MezzioTestEnvironment::match
     * @covers \Trinet\MezzioTest\MezzioTestEnvironment::requirePath
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

        $this->mezzio->assertSameMatchedRouteName('post');
        $this->mezzio->assertSameRouteMiddlewareOrResponseHandler(RequestHandlerMiddleware::class);
        $this->mezzio->assertSameRequestParsedBody($params);
        $this->mezzio->assertSameResponseBody(json_encode($params));
    }

    /**
     * @covers \Trinet\MezzioTest\MezzioTestEnvironment::__construct
     * @covers \Trinet\MezzioTest\MezzioTestEnvironment::application
     * @covers \Trinet\MezzioTest\MezzioTestEnvironment::assertSameRequestQueryParams
     * @covers \Trinet\MezzioTest\MezzioTestEnvironment::requirePath
     * @covers \Trinet\MezzioTest\MezzioTestEnvironment::container
     * @covers \Trinet\MezzioTest\MezzioTestEnvironment::dispatchRequest
     * @covers \Trinet\MezzioTest\MezzioTestEnvironment::match
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
     * @covers \Trinet\MezzioTest\MezzioTestEnvironment::dispatchRequest
     * @covers \Trinet\MezzioTest\MezzioTestEnvironment::match
     * @covers \Trinet\MezzioTest\TestConfigProvider::isTesting
     * @covers \Trinet\MezzioTest\TestConfigProvider::load
     * @covers \Trinet\MezzioTest\TestConfigProvider::prepareConfigDir
     * @covers \Trinet\MezzioTest\Util::ensureTrailingSlash
     */
    public function testDispatchRequest(): void
    {
        $request = new ServerRequest([], [], '/');

        $result = $this->mezzio->dispatchRequest($request);

        self::assertSame('Hi', (string)$result->getBody());
        self::assertSame(StatusCodeInterface::STATUS_OK, $result->getStatusCode());
    }

    /**
     * @covers \Trinet\MezzioTest\MezzioTestEnvironment::__construct
     * @covers \Trinet\MezzioTest\MezzioTestEnvironment::application
     * @covers \Trinet\MezzioTest\MezzioTestEnvironment::requirePath
     * @covers \Trinet\MezzioTest\MezzioTestEnvironment::container
     * @covers \Trinet\MezzioTest\MezzioTestEnvironment::dispatchRequest
     * @covers \Trinet\MezzioTest\MezzioTestEnvironment::match
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
        $result = $this->mezzio->dispatchRoute('404');
        self::assertSame('Cannot GET /404', (string)$result->getBody());
        self::assertSame(StatusCodeInterface::STATUS_NOT_FOUND, $result->getStatusCode());
        $this->mezzio->assertSameResponseStatusCode(StatusCodeInterface::STATUS_NOT_FOUND);
        $this->mezzio->assertSameRouteMiddlewareOrResponseHandler(NotFoundHandler::class);
    }

    /**
     * @covers \Trinet\MezzioTest\MezzioTestEnvironment::__construct
     * @covers \Trinet\MezzioTest\MezzioTestEnvironment::application
     * @covers \Trinet\MezzioTest\MezzioTestEnvironment::RequirePath
     * @covers \Trinet\MezzioTest\MezzioTestEnvironment::assertSameMatchedRouteName
     * @covers \Trinet\MezzioTest\MezzioTestEnvironment::container
     * @covers \Trinet\MezzioTest\MezzioTestEnvironment::dispatchRequest
     * @covers \Trinet\MezzioTest\MezzioTestEnvironment::match
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

        $this->mezzio->dispatchRequest($request);
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
