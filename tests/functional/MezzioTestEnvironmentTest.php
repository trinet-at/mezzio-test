<?php

declare(strict_types=1);

namespace Trinet\Test\Functional\MezzioTest;

use Fig\Http\Message\RequestMethodInterface;
use Fig\Http\Message\StatusCodeInterface;
use Laminas\Diactoros\ServerRequest;
use Laminas\Stratigility\Middleware\CallableMiddlewareDecorator;
use Laminas\Stratigility\Middleware\RequestHandlerMiddleware;
use LogicException;
use Mezzio\Handler\NotFoundHandler;
use Mezzio\Router\Route;
use Mezzio\Router\RouteResult;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Trinet\MezzioTest\AssertionsTrait;
use Trinet\MezzioTest\MezzioTestEnvironment;

use function dirname;
use function http_build_query;

/**
 * @internal
 *
 * @small
 *
 * @coversDefaultClass \Trinet\MezzioTest\MezzioTestEnvironment
 */
final class MezzioTestEnvironmentTest extends TestCase
{
    use AssertionsTrait;

    private MezzioTestEnvironment $mezzio;

    protected function setUp(): void
    {
        parent::setUp();
        $basePath = dirname(__DIR__);
        $this->mezzio = new MezzioTestEnvironment($basePath);
    }

    public function testAddRoute(): void
    {
        $this->mezzio->addRoute(
            new Route(__FUNCTION__, new class () implements MiddlewareInterface {
                public function process(
                    ServerRequestInterface $request,
                    RequestHandlerInterface $handler
                ): ResponseInterface {
                    return $handler->handle($request);
                }
            }, [RequestMethodInterface::METHOD_GET], __FUNCTION__)
        );

        $this->mezzio->dispatchRoute(__FUNCTION__);

        $routeResult = $this->mezzio->getRouteResult();
        Assert::assertInstanceOf(RouteResult::class, $routeResult);
        $this->assertMatchedRouteName($routeResult, __FUNCTION__);
    }

    public function testCustomErrorHandlerRethrowsException(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('I have an error');

        $this->mezzio->get('/error');
    }

    public function testDispatch(): void
    {
        $response = $this->mezzio->dispatch('/');

        $this->assertResponseBody($response, 'Hi');
        self::assertSame(200, $this->mezzio->getResponseStatusCode());
        $this->assertResponseStatusCode($response, StatusCodeInterface::STATUS_OK);
        $this->assertNotResponseStatusCode($response, StatusCodeInterface::STATUS_NOT_FOUND);
        $this->assertResponseReasonPhrase($response, 'OK');
        $this->assertResponseHeaders($response, [
            'content-type' => ['text/plain; charset=utf-8'],
        ]);

        self::assertSame('Hi', (string)$response->getBody());
    }

    public function testDispatchGeneratedRoute(): void
    {
        $route = $this->mezzio->generateUri('home');

        $response = $this->mezzio->get($route);

        $request = $this->mezzio->getRequest();
        Assert::assertInstanceOf(ServerRequestInterface::class, $request);

        $routeResult = $this->mezzio->getRouteResult();
        Assert::assertInstanceOf(RouteResult::class, $routeResult);

        $this->assertResponseBody($response, 'Hi');
        $this->assertResponseBodyContainsString($response, 'H');
        $this->assertMatchedRouteName($routeResult, 'home');
        $this->assertRouteMiddlewareOrResponseHandler($routeResult, CallableMiddlewareDecorator::class);
        $this->assertResponseStatusCode($response, StatusCodeInterface::STATUS_OK);
    }

    public function testDispatchHeadersArePassedToRequest(): void
    {
        $headers = [
            'foo' => 'bar',
        ];
        $expected = [
            'foo' => ['bar'],
        ];

        $response = $this->mezzio->get(uri: '/', headers: $headers);
        $request = $this->mezzio->getRequest();
        Assert::assertInstanceOf(ServerRequestInterface::class, $request);

        $this->assertRequestHeaders($request, $expected);
        $this->assertResponseStatusCode($response, StatusCodeInterface::STATUS_OK);
        $this->assertResponseBody($response, 'Hi');
        $this->assertRequestHasHeader($request, 'foo');
        $this->assertRequestHeaderMatches($request, 'foo', ['bar']);
    }

    public function testDispatchParamsArePassedToParsedBodyForPostRequest(): void
    {
        $params = [
            'foo' => 'bar',
        ];
        $response = $this->mezzio->post('/crud', $params);

        $request = $this->mezzio->getRequest();
        Assert::assertInstanceOf(ServerRequestInterface::class, $request);

        $routeResult = $this->mezzio->getRouteResult();
        Assert::assertInstanceOf(RouteResult::class, $routeResult);

        $this->assertMatchedRouteName($routeResult, 'crud');
        $this->assertRouteMiddlewareOrResponseHandler($routeResult, RequestHandlerMiddleware::class);
        $this->assertRequestParsedBody($request, $params);
        $this->assertResponseBody($response, http_build_query($params));
    }

    public function testDispatchParamsArePassedToQueryForGetRequest(): void
    {
        $params = [
            'foo' => 'bar',
        ];
        $response = $this->mezzio->get('/', $params);

        $request = $this->mezzio->getRequest();
        Assert::assertInstanceOf(ServerRequestInterface::class, $request);

        $routeResult = $this->mezzio->getRouteResult();
        Assert::assertInstanceOf(RouteResult::class, $routeResult);

        $this->assertRequestQueryParams($request, $params);
        $this->assertResponseBody($response, 'Hi');
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
        $response = $this->mezzio->dispatchRoute('404');

        self::assertSame('Cannot GET /404', (string)$response->getBody());
        self::assertSame(StatusCodeInterface::STATUS_NOT_FOUND, $response->getStatusCode());
        $this->assertResponseStatusCode($response, StatusCodeInterface::STATUS_NOT_FOUND);

        $routeResult = $this->mezzio->getRouteResult();
        Assert::assertInstanceOf(RouteResult::class, $routeResult);
        $this->assertRouteMiddlewareOrResponseHandler($routeResult, NotFoundHandler::class);
    }

    public function testDispatchRouter(): void
    {
        $router = $this->mezzio->getRouter();

        $request = $this->mezzio->request('GET', '/');
        self::assertSame('home', $router->match($request)->getMatchedRouteName());

        $response = $this->mezzio->dispatchRequest($request);

        $routeResult = $this->mezzio->getRouteResult();
        Assert::assertInstanceOf(RouteResult::class, $routeResult);

        $this->assertResponseHeaders($response, [
            'content-type' => ['text/plain; charset=utf-8'],
        ]);
        $this->assertMatchedRouteName($routeResult, 'home');
    }

    public function testRuntimeIsSetToAppTesting(): void
    {
        /** @var array<string, mixed> $config */
        $config = $this->mezzio->getContainer()
            ->get('config');

        self::assertTrue($config['testing']);
    }
}
