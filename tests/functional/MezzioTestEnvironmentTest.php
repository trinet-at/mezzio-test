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
        $result = $this->mezzio->dispatch('/');

        $this->mezzio->assertSameResponseBody('Hi');
        $this->mezzio->assertSameResponseStatusCode(StatusCodeInterface::STATUS_OK);
        $this->mezzio->assertSameResponseReasonPhrase('OK');
        $this->mezzio->assertSameResponseHeaders([
            'content-type' => ['text/plain; charset=utf-8'],
        ]);

        self::assertSame('Hi', (string)$result->getBody());
    }

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

    public function testDispatchParamsArePassedToParsedBodyForPostRequest(): void
    {
        $params = [
            'foo' => 'bar',
        ];
        $this->mezzio->post('/crud', $params);

        $this->mezzio->assertSameMatchedRouteName('crud');
        $this->mezzio->assertSameRouteMiddlewareOrResponseHandler(RequestHandlerMiddleware::class);
        $this->mezzio->assertSameRequestParsedBody($params);
        $this->mezzio->assertSameResponseBody(http_build_query($params));
    }

    public function testDispatchParamsArePassedToQueryForGetRequest(): void
    {
        $params = [
            'foo' => 'bar',
        ];
        $this->mezzio->get('/', $params);

        $this->mezzio->assertSameRequestQueryParams($params);
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
        $result = $this->mezzio->dispatchRoute('404');
        self::assertSame('Cannot GET /404', (string)$result->getBody());
        self::assertSame(StatusCodeInterface::STATUS_NOT_FOUND, $result->getStatusCode());
        $this->mezzio->assertSameResponseStatusCode(StatusCodeInterface::STATUS_NOT_FOUND);
        $this->mezzio->assertSameRouteMiddlewareOrResponseHandler(NotFoundHandler::class);
    }

    public function testDispatchRouter(): void
    {
        $router = $this->mezzio->getRouter();

        $request = $this->mezzio->request('GET', '/');
        self::assertSame('home', $router->match($request)->getMatchedRouteName());

        $this->mezzio->dispatchRequest($request);
        $this->mezzio->assertSameMatchedRouteName('home');
    }

    public function testRuntimeIsSetToAppTesting(): void
    {
        /** @var array<string, mixed> $config */
        $config = $this->mezzio->getContainer()
            ->get('config');

        self::assertTrue($config['testing']);
    }
}
