<?php

declare(strict_types=1);

namespace Trinet\MezzioTest;

use Mezzio\Middleware\LazyLoadingMiddleware;
use Mezzio\Router\Route;
use Mezzio\Router\RouteResult;
use PHPUnit\Framework\Assert;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use ReflectionClass;

use function assert;
use function sprintf;

trait AssertionsTrait
{
    public function assertResponseBodyContainsString(string $string): void
    public function assertMatchedRouteName(RouteResult $routeResult, string $expected): void
    {
        $this->assert(
            $this->constraint(
                $expected,
                static fn (string $expectedValue, string $actualValue): bool => $expectedValue === $actualValue,
                sprintf('%s::getMatchedRouteName()', $routeResult::class)
            ),
            $routeResult->getMatchedRouteName()
        );
    }

    {
        Assert::assertInstanceOf(ResponseInterface::class, $this->response);
        Assert::assertStringContainsString($string, (string)$this->response->getBody());
    }

    public function assertSameMatchedRouteName(string $routeName): void
    {
        Assert::assertInstanceOf(RouteResult::class, $this->routeResult);
        Assert::assertSame($routeName, $this->routeResult->getMatchedRouteName());
    }

    /**
     * @param array<array<string>|string> $headers
     */
    public function assertSameRequestHeaders(array $headers): void
    {
        Assert::assertInstanceOf(RequestInterface::class, $this->request);
        Assert::assertSame(
            $headers,
            $this->request->getHeaders(),
            'Failed asserting that RequestHeaders are identical.'
        );
    }

    public function assertSameRequestMethod(string $method): void
    {
        Assert::assertInstanceOf(RequestInterface::class, $this->request);
        Assert::assertSame($method, $this->request->getMethod());
    }

    /**
     * @param array<string,mixed> $parsedBody
     */
    public function assertSameRequestParsedBody(array $parsedBody): void
    {
        Assert::assertInstanceOf(RequestInterface::class, $this->request);
        Assert::assertSame(
            $parsedBody,
            $this->request->getParsedBody(),
            'Failed asserting that ParsedBody are identical.'
        );
    }

    /**
     * @param array<string,mixed> $queryParams
     */
    public function assertSameRequestQueryParams(array $queryParams): void
    {
        assert($this->request instanceof RequestInterface);
        Assert::assertSame(
            $queryParams,
            $this->request->getQueryParams(),
            'Failed asserting that QueryParams are identical.'
        );
    }

    public function assertSameResponseBody(string $content): void
    {
        Assert::assertInstanceOf(ResponseInterface::class, $this->response);
        Assert::assertSame(
            $content,
            (string)$this->response->getBody(),
            'Failed asserting that ResponseBody is identical.'
        );
    }

    /**
     * @param array<array<string>|string> $headers
     */
    public function assertSameResponseHeaders(array $headers): void
    {
        Assert::assertInstanceOf(ResponseInterface::class, $this->response);
        Assert::assertSame($headers, $this->response->getHeaders());
    }

    public function assertSameResponseReasonPhrase(string $reasonPhrase): void
    {
        Assert::assertInstanceOf(ResponseInterface::class, $this->response);
        Assert::assertSame($reasonPhrase, $this->response->getReasonPhrase());
    }

    public function assertSameResponseStatusCode(int $statusCode): void
    {
        Assert::assertInstanceOf(ResponseInterface::class, $this->response);
        Assert::assertSame($statusCode, $this->response->getStatusCode());
    }

    public function assertSameRouteMiddlewareOrResponseHandler(string $middlewareOrResponseHandlerClass): void
    {
        Assert::assertInstanceOf(RouteResult::class, $this->routeResult);
        $matchedRoute = $this->routeResult->getMatchedRoute();

        Assert::assertInstanceOf(Route::class, $matchedRoute);
        $matchedMiddlewareOrResponseHandler = $matchedRoute->getMiddleware();

        /** @var class-string $matchedMiddlewareOrResponseHandlerName */
        $matchedMiddlewareOrResponseHandlerName = match (true) {
            ($matchedMiddlewareOrResponseHandler instanceof LazyLoadingMiddleware) => (new ReflectionClass(
                $matchedMiddlewareOrResponseHandler
            ))
                ->getProperty('middlewareName')
                ->getValue($matchedMiddlewareOrResponseHandler),
            default => $matchedMiddlewareOrResponseHandler::class
        };

        Assert::assertSame($middlewareOrResponseHandlerClass, $matchedMiddlewareOrResponseHandlerName);
        $reflection = new ReflectionClass($matchedMiddlewareOrResponseHandlerName);

        Assert::assertTrue(
            $reflection->implementsInterface(MiddlewareInterface::class) ||
                $reflection->implementsInterface(RequestHandlerInterface::class),
            sprintf(
                'Class "%s" does not implement "%s" or "%s".',
                $matchedMiddlewareOrResponseHandlerName,
                MiddlewareInterface::class,
                RequestHandlerInterface::class
            )
        );
    }
}
