<?php

declare(strict_types=1);

namespace Trinet\Test\Functional\MezzioTest;

use Fig\Http\Message\RequestMethodInterface;
use Fig\Http\Message\StatusCodeInterface;
use Generator;
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
use Psr\Http\Message\UploadedFileInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Trinet\MezzioTest\AssertionsTrait;
use Trinet\MezzioTest\MezzioTestEnvironment;
use UnexpectedValueException;

use function dirname;
use function http_build_query;
use function Safe\json_encode;
use function sprintf;

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

    public const HTTP_DELETE = 'delete';

    public const HTTP_DELETE_JSON = 'deleteJson';

    public const HTTP_GET = 'get';

    public const HTTP_GET_JSON = 'getJson';

    public const HTTP_HEAD = 'head';

    public const HTTP_HEAD_JSON = 'headJson';

    public const HTTP_OPTIONS = 'options';

    public const HTTP_OPTIONS_JSON = 'optionsJson';

    public const HTTP_PATCH = 'patch';

    public const HTTP_PATCH_JSON = 'patchJson';

    public const HTTP_POST = 'post';

    public const HTTP_POST_JSON = 'postJson';

    public const HTTP_PUT = 'put';

    public const HTTP_PUT_JSON = 'putJson';

    public const JSON_HEADERS = [
        'Content-Type' => ['application/json'],
        'Accept' => ['application/json'],
    ];

    public const ROUTE_NAME = 'crud';

    private MezzioTestEnvironment $mezzio;

    protected function setUp(): void
    {
        parent::setUp();
        $basePath = dirname(__DIR__);
        $this->mezzio = new MezzioTestEnvironment($basePath);
    }

    public function crudDataProvider(): Generator
    {
        $emptyQueryParams = [];
        $emptyHeaders = [];
        $emptyParsedBody = [];
        $emptyUploadedFiles = [];
        $emptyCookieParams = [];
        $emptyServerParams = [];
        $headers = self::JSON_HEADERS;
        $queryParams = [
            'queryParams' => __FUNCTION__,
        ];
        $parsedBody = [
            'parsedBody' => __FUNCTION__,
        ];

        yield from [
            self::HTTP_DELETE => [
                self::HTTP_DELETE,
                RequestMethodInterface::METHOD_DELETE,
                $emptyQueryParams,
                $parsedBody,
                $emptyUploadedFiles,
                http_build_query($parsedBody),
                $emptyHeaders,
                $emptyCookieParams,
                $emptyServerParams,
            ],
            self::HTTP_DELETE_JSON => [
                self::HTTP_DELETE_JSON,
                RequestMethodInterface::METHOD_DELETE,
                $emptyQueryParams,
                $parsedBody,
                $emptyUploadedFiles,
                json_encode($parsedBody),
                $headers,
                $emptyCookieParams,
                $emptyServerParams,
            ],
            self::HTTP_GET => [
                self::HTTP_GET,
                RequestMethodInterface::METHOD_GET,
                $queryParams,
                $emptyParsedBody,
                $emptyUploadedFiles,
                http_build_query($queryParams),
                $emptyHeaders,
                $emptyCookieParams,
                $emptyServerParams,
            ],
            self::HTTP_GET_JSON => [
                self::HTTP_GET_JSON,
                RequestMethodInterface::METHOD_GET,
                $queryParams,
                $emptyParsedBody,
                $emptyUploadedFiles,
                json_encode($queryParams),
                $headers,
                $emptyCookieParams,
                $emptyServerParams,
            ],
            self::HTTP_HEAD => [
                self::HTTP_HEAD,
                RequestMethodInterface::METHOD_HEAD,
                $queryParams,
                $emptyParsedBody,
                $emptyUploadedFiles,
                http_build_query($queryParams),
                $emptyHeaders,
                $emptyCookieParams,
                $emptyServerParams,
            ],
            self::HTTP_OPTIONS => [
                self::HTTP_OPTIONS,
                RequestMethodInterface::METHOD_OPTIONS,
                $queryParams,
                $emptyParsedBody,
                $emptyUploadedFiles,
                http_build_query($queryParams),
                $emptyHeaders,
                $emptyCookieParams,
                $emptyServerParams,
            ],
            self::HTTP_OPTIONS_JSON => [
                self::HTTP_OPTIONS_JSON,
                RequestMethodInterface::METHOD_OPTIONS,
                $queryParams,
                $emptyParsedBody,
                $emptyUploadedFiles,
                json_encode($queryParams),
                $headers,
                $emptyCookieParams,
                $emptyServerParams,
            ],
            self::HTTP_PATCH => [
                self::HTTP_PATCH,
                RequestMethodInterface::METHOD_PATCH,
                $emptyQueryParams,
                $parsedBody,
                $emptyUploadedFiles,
                http_build_query($parsedBody),
                $emptyHeaders,
                $emptyCookieParams,
                $emptyServerParams,
            ],
            self::HTTP_PATCH_JSON => [
                self::HTTP_PATCH_JSON,
                RequestMethodInterface::METHOD_PATCH,
                $emptyQueryParams,
                $parsedBody,
                $emptyUploadedFiles,
                json_encode($parsedBody),
                $headers,
                $emptyCookieParams,
                $emptyServerParams,
            ],
            self::HTTP_POST => [
                self::HTTP_POST,
                RequestMethodInterface::METHOD_POST,
                $emptyQueryParams,
                $parsedBody,
                $emptyUploadedFiles,
                http_build_query($parsedBody),
                $emptyHeaders,
                $emptyCookieParams,
                $emptyServerParams,
            ],
            self::HTTP_POST_JSON => [
                self::HTTP_POST_JSON,
                RequestMethodInterface::METHOD_POST,
                $emptyQueryParams,
                $parsedBody,
                $emptyUploadedFiles,
                json_encode($parsedBody),
                $headers,
                $emptyCookieParams,
                $emptyServerParams,
            ],
            self::HTTP_PUT => [
                self::HTTP_PUT,
                RequestMethodInterface::METHOD_PUT,
                $emptyQueryParams,
                $parsedBody,
                $emptyUploadedFiles,
                http_build_query($parsedBody),
                $emptyHeaders,
                $emptyCookieParams,
                $emptyServerParams,
            ],
            self::HTTP_PUT_JSON => [
                self::HTTP_PUT_JSON,
                RequestMethodInterface::METHOD_PUT,
                $emptyQueryParams,
                $parsedBody,
                $emptyUploadedFiles,
                json_encode($parsedBody),
                $headers,
                $emptyCookieParams,
                $emptyServerParams,
            ],
        ];
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

    /**
     * @param array<string, string>                $parsedBody
     * @param array<string, string>                $queryParams
     * @param array<UploadedFileInterface>         $uploadedFiles
     * @param array<string, array<string, string>> $headers
     * @param array<string, string>                $cookieParams
     * @param array<string, string>                $serverParams
     *
     * @dataProvider crudDataProvider
     */
    public function testCrud(
        string $action,
        string $method,
        array $queryParams = [],
        array $parsedBody = [],
        array $uploadedFiles = [],
        string $body = 'php://input',
        array $headers = [],
        array $cookieParams = [],
        array $serverParams = [],
    ): void {
        $uri = $this->mezzio->generateUri(self::ROUTE_NAME);

        $response = match ($action) {
            self::HTTP_DELETE => $this->mezzio->delete($uri, $parsedBody, $headers, $cookieParams, $serverParams),
            self::HTTP_DELETE_JSON => $this->mezzio->deleteJson(
                $uri,
                $parsedBody,
                $headers,
                $cookieParams,
                $serverParams
            ),
            self::HTTP_GET => $this->mezzio->get($uri, $queryParams, $headers, $cookieParams, $serverParams),
            self::HTTP_GET_JSON => $this->mezzio->getJson($uri, $queryParams, $headers, $cookieParams, $serverParams),
            self::HTTP_HEAD => $this->mezzio->head($uri, $queryParams, $headers, $cookieParams, $serverParams),
            self::HTTP_OPTIONS => $this->mezzio->options($uri, $queryParams, $headers, $cookieParams, $serverParams),
            self::HTTP_OPTIONS_JSON => $this->mezzio->optionsJson(
                $uri,
                $queryParams,
                $headers,
                $cookieParams,
                $serverParams
            ),
            self::HTTP_PATCH => $this->mezzio->patch(
                $uri,
                $parsedBody,
                $uploadedFiles,
                $headers,
                $cookieParams,
                $serverParams
            ),
            self::HTTP_PATCH_JSON => $this->mezzio->patchJson(
                $uri,
                $parsedBody,
                $uploadedFiles,
                $headers,
                $cookieParams,
                $serverParams
            ),
            self::HTTP_PUT => $this->mezzio->put(
                $uri,
                $parsedBody,
                $uploadedFiles,
                $headers,
                $cookieParams,
                $serverParams
            ),
            self::HTTP_PUT_JSON => $this->mezzio->putJson(
                $uri,
                $parsedBody,
                $uploadedFiles,
                $headers,
                $cookieParams,
                $serverParams
            ),
            self::HTTP_POST => $this->mezzio->post(
                $uri,
                $parsedBody,
                $uploadedFiles,
                $headers,
                $cookieParams,
                $serverParams
            ),
            self::HTTP_POST_JSON => $this->mezzio->postJson(
                $uri,
                $parsedBody,
                $uploadedFiles,
                $headers,
                $cookieParams,
                $serverParams
            ),
            default => throw new UnexpectedValueException(sprintf('Unsupported action: %s', $action))
        };

        $request = $this->mezzio->getRequest();
        Assert::assertInstanceOf(ServerRequestInterface::class, $request);

        $routeResult = $this->mezzio->getRouteResult();
        Assert::assertInstanceOf(RouteResult::class, $routeResult);

        $this->assertServerRequestMethod($request, $method);
        $this->assertServerRequestHeaders($request, $headers);
        $this->assertServerRequestQueryParams($request, $queryParams);
        $this->assertServerRequestParsedBody($request, $parsedBody);
        $this->assertMatchedRouteName($routeResult, self::ROUTE_NAME);

        $this->assertResponseBody($response, $body);
        $this->assertResponseStatusCode($response, StatusCodeInterface::STATUS_OK);
        self::assertSame(StatusCodeInterface::STATUS_OK, $response->getStatusCode());
    }

    public function testCustomErrorHandlerRethrowsException(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('I have an error');

        $this->mezzio->get('/error');
    }

    public function testDelete(): void
    {
        $uri = $this->mezzio->generateUri(self::ROUTE_NAME);

        $payload = [
            'function' => __FUNCTION__,
        ];

        $response = $this->mezzio->delete($uri, $payload);
        $request = $this->mezzio->getRequest();
        Assert::assertInstanceOf(ServerRequestInterface::class, $request);

        $this->assertServerRequestMethod($request, RequestMethodInterface::METHOD_DELETE);

        $this->assertResponseBody($response, http_build_query($payload));
        $this->assertServerRequestHeaders($request, []);
        $this->assertResponseHeaders($response, [
            'content-type' => ['text/plain; charset=utf-8'],
        ]);
        $this->assertServerRequestQueryParams($request, []);
        $this->assertServerRequestParsedBody($request, $payload);

        $routeResult = $this->mezzio->getRouteResult();
        Assert::assertInstanceOf(RouteResult::class, $routeResult);
        $this->assertMatchedRouteName($routeResult, self::ROUTE_NAME);
    }

    public function testDeleteJson(): void
    {
        $uri = $this->mezzio->generateUri(self::ROUTE_NAME);

        $payload = [
            'function' => __FUNCTION__,
        ];

        $response = $this->mezzio->deleteJson($uri, $payload);
        $this->assertResponseBody($response, json_encode($payload));
        $this->assertResponseHeader($response, 'Content-Type', ['application/json']);

        $request = $this->mezzio->getRequest();
        Assert::assertInstanceOf(ServerRequestInterface::class, $request);

        $this->assertServerRequestBody($request, '');
        $this->assertServerRequestQueryParams($request, []);
        $this->assertServerRequestAttributes($request, []);
        $this->assertServerRequestCookieParams($request, []);
        $this->assertServerRequestParsedBody($request, $payload);
        $this->assertServerRequestHeaders($request, self::JSON_HEADERS);
        $this->assertServerRequestMethod($request, RequestMethodInterface::METHOD_DELETE);

        $routeResult = $this->mezzio->getRouteResult();
        Assert::assertInstanceOf(RouteResult::class, $routeResult);
        $this->assertMatchedRouteName($routeResult, self::ROUTE_NAME);
    }

    public function testDispatch(): void
    {
        $response = $this->mezzio->dispatch('/');

        $this->assertResponseBody($response, 'Hi');

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

        $this->assertServerRequestHeaders($request, $expected);
        $this->assertResponseStatusCode($response, StatusCodeInterface::STATUS_OK);
        $this->assertResponseBody($response, 'Hi');
        $this->assertServerRequestHasHeader($request, 'foo');
        $this->assertServerRequestHeader($request, 'foo', ['bar']);
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
        $this->assertServerRequestParsedBody($request, $params);
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

        $this->assertServerRequestQueryParams($request, $params);
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
