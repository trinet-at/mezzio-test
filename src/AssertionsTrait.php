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

use function sprintf;

trait AssertionsTrait
{
    public function assertSameMatchedRouteName(string $routeName): void
    {
        Assert::assertInstanceOf(RouteResult::class, $this->routeResult);
        Assert::assertSame($routeName, $this->routeResult->getMatchedRouteName());
    }

    /**
     * @param array<string|array<string>> $headers
     * @return void
     */
    public function assertSameResponseHeaders(array $headers): void
    {
        Assert::assertInstanceOf(ResponseInterface::class, $this->response);
        Assert::assertSame($headers, $this->response->getHeaders());
    }

    public function assertSameResponseStatusCode(int $statusCode): void
    {
        Assert::assertInstanceOf(ResponseInterface::class, $this->response);
        Assert::assertSame($statusCode, $this->response->getStatusCode());
    }

    /**
     * @param array<string|array<string>> $headers
     * @return void
     */
    public function assertSameRequestHeaders(array $headers): void
    {
        Assert::assertInstanceOf(RequestInterface::class, $this->request);
        Assert::assertSame($headers, $this->request->getHeaders());
    }

    public function assertSameRouteMiddlewareOrResponseHandler(string $middlewareOrResponseHandlerClass): void
    {
        Assert::assertInstanceOf(RouteResult::class, $this->routeResult);
        $matchedRoute = $this->routeResult->getMatchedRoute();

        Assert::assertInstanceOf(Route::class, $matchedRoute);
        $matchedMiddlewareOrResponseHandler = $matchedRoute->getMiddleware();

        /** @var class-string $matchedMiddlewareOrResponseHandlerName */
        $matchedMiddlewareOrResponseHandlerName = match (true) {
            ($matchedMiddlewareOrResponseHandler instanceof LazyLoadingMiddleware) => (new ReflectionClass($matchedMiddlewareOrResponseHandler))
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

    /**
     * @param array<string,mixed> $parsedBody
     * @return void
     */
    public function assertSameRequestParsedBody(array $parsedBody): void
    {
        Assert::assertInstanceOf(RequestInterface::class, $this->request);
        Assert::assertSame($parsedBody, $this->request->getParsedBody());
    }

    /**
     * @param array<string,mixed> $queryParams
     * @return void
     */
    public function assertSameRequestQueryParams(array $queryParams): void
    {
        Assert::assertInstanceOf(RequestInterface::class, $this->request);
        Assert::assertSame($queryParams, $this->request->getQueryParams());
    }

    public function assertSameResponseReasonPhrase(string $reasonPhrase): void
    {
        Assert::assertInstanceOf(ResponseInterface::class, $this->response);
        Assert::assertSame($reasonPhrase, $this->response->getReasonPhrase());
    }

    public function assertResponseBodyContainsString(string $string): void
    {
        Assert::assertInstanceOf(ResponseInterface::class, $this->response);
        Assert::assertStringContainsString($string, (string)$this->response->getBody());
    }

    public function assertSameResponseBody(string $content): void
    {
        Assert::assertInstanceOf(ResponseInterface::class, $this->response);
        Assert::assertSame($content, (string)$this->response->getBody());
    }
}
