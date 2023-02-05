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

    public function assertNotResponseStatusCode(ResponseInterface $response, int $expected): void
    {
        $this->assertNot(
            $this->constraint(
                $expected,
                static fn (int $expectedValue, int $actualValue): bool => $expectedValue === $actualValue,
                sprintf('%s::getStatusCode()', $response::class)
            ),
            $response->getStatusCode()
        );
    }

    public function assertRequestHasHeader(ServerRequestInterface $request, string $expected): void
    {
        $this->assert(
            $this->constraint(
                true,
                static fn (bool $expectedValue, bool $actualValue): bool => $expectedValue === $actualValue,
                sprintf('%s::hasHeader("%s")', $request::class, $expected)
            ),
            $request->hasHeader($expected)
        );
    }

    public function assertRequestHeaderMatches(ServerRequestInterface $request, string $name, array $expected): void
    {
        $this->assert(
            $this->constraint(
                $expected,
                static fn (array $expectedValue, array $actualValue): bool => $expectedValue === $actualValue,
                sprintf('%s::getHeader("%s")', $request::class, $name)
            ),
            $request->getHeader($name)
        );
    }

    /**
     * @param array<array<string>|string> $headers
     */
    public function assertRequestHeaders(ServerRequestInterface $request, array $headers): void
    {
        $this->assert(
            $this->constraint(
                $headers,
                static fn (array $expectedValue, array $actualValue): bool => $expectedValue === $actualValue,
                sprintf('%s::getHeaders()', $request::class)
            ),
            $request->getHeaders()
        );
    }

    public function assertRequestMethod(ServerRequestInterface $request, string $expected): void
    {
        $this->assert(
            $this->constraint(
                $expected,
                static fn (string $expectedValue, string $actualValue): bool => $expectedValue === $actualValue,
                sprintf('%s::getMethod()', $request::class)
            ),
            $request->getMethod()
        );
    }

    /**
     * @param array<string,mixed> $expected
     */
    public function assertRequestParsedBody(ServerRequestInterface $request, array $expected): void
    {
        $this->assert(
            $this->constraint(
                $expected,
                static fn (array $expectedValue, array $actualValue): bool => $expectedValue === $actualValue,
                sprintf('%s::getParsedBody()', $request::class)
            ),
            $request->getParsedBody()
        );
    }

    /**
     * @param array<string,mixed> $expected
     */
    public function assertRequestQueryParams(ServerRequestInterface $request, array $expected): void
    {
        $this->assert(
            $this->constraint(
                $expected,
                static fn (array $expectedValue, array $actualValue): bool => $expectedValue === $actualValue,
                sprintf('%s::getQueryParams()', $request::class)
            ),
            $request->getQueryParams()
        );
    }

    public function assertResponseBody(ResponseInterface $response, string $expected): void
    {
        $this->assert(
            $this->constraint(
                $expected,
                static fn (string $expectedValue, string $actualValue): bool => $expectedValue === $actualValue,
                $response::class . '::getBody()'
            ),
            (string)$response->getBody()
        );
    }

    public function assertResponseBodyContainsString(ResponseInterface $response, string $expected): void
    {
        $this->assert(
            $this->constraint(
                $expected,
                static fn (string $expectedValue, string $actualValue): bool => str_contains(
                    $actualValue,
                    $expectedValue
                ),
                sprintf('%s::getBody()', $response::class)
            ),
            (string) $response->getBody()
        );
    }

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
