<?php

declare(strict_types=1);

namespace Trinet\MezzioTest;

use Closure;
use Mezzio\Middleware\LazyLoadingMiddleware;
use Mezzio\Router\Route;
use Mezzio\Router\RouteResult;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\Constraint\Constraint;
use PHPUnit\Framework\Constraint\LogicalNot;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UploadedFileInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use ReflectionClass;
use SebastianBergmann\Comparator\ComparisonFailure;

use function sprintf;
use function str_contains;

trait AssertionsTrait
{
    public function assertMatchedRouteName(RouteResult $routeResult, string $expected): void
    {
        $this->assert(
            $this->constraintMatchedRouteName($routeResult, $expected),
            $routeResult->getMatchedRouteName()
        );
    }

    public function assertNotMatchedRouteName(RouteResult $routeResult, string $expected): void
    {
        $this->assertNot(
            $this->constraintMatchedRouteName($routeResult, $expected),
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
            (string)$response->getBody()
        );
    }

    public function assertResponseHasHeader(ResponseInterface $response, string $name): void
    {
        $this->assert(
            $this->constraint(
                true,
                static fn (bool $expectedValue, bool $actualValue): bool => $expectedValue === $actualValue,
                sprintf('%s::hasHeader("%s")', $response::class, $name)
            ),
            $response->hasHeader($name)
        );
    }

    /**
     * @param array<string> $expected
     */
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
     * @param array<array<string>|string> $expected
     */
    public function assertRequestHeaders(ServerRequestInterface $request, array $expected): void
    {
        $this->assert(
            $this->constraint(
                $expected,
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
            (string)$response->getBody()
        );
    }

    public function assertResponseHasHeader(ResponseInterface $response, string $name): void
    {
        $this->assert(
            $this->constraint(
                true,
                static fn (bool $expectedValue, bool $actualValue): bool => $expectedValue === $actualValue,
                sprintf('%s::hasHeader("%s")', $response::class, $name)
            ),
            $response->hasHeader($name)
        );
    }

    /**
     * @param array<string> $expected
     */
    public function assertResponseHeader(ResponseInterface $response, string $name, array $expected = []): void
    {
        $this->assertResponseHasHeader($response, $name);

        $this->assert(
            $this->constraint(
                $expected,
                static fn (array $expectedValue, array $actualValue): bool => $expectedValue === $actualValue,
                sprintf('%s::getHeader("%s")', $response::class, $name)
            ),
            $response->getHeader($name)
        );
    }

    /**
     * @param array<array<string>|string> $expected
     */
    public function assertServerRequestHeaders(ServerRequestInterface $request, array $expected): void
    {
        $this->assert(
            $this->constraint(
                $expected,
                static fn (array $expectedValue, array $actualValue): bool => $expectedValue === $actualValue,
                sprintf('%s::getHeaders()', $request::class)
            ),
            $request->getHeaders()
        );
    }

    public function assertServerRequestMethod(ServerRequestInterface $request, string $expected): void
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
    public function assertServerRequestParsedBody(ServerRequestInterface $request, array $expected): void
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

    public function assertRouteMiddlewareOrResponseHandler(
        RouteResult $routeResult,
        string $middlewareOrResponseHandlerClass
    ): void {
        $matchedRoute = $routeResult->getMatchedRoute();
    public function assertServerRequestProtocolVersion(ServerRequestInterface $request, array $expected): void
    {
        $this->assert(
            $this->constraint(
                $expected,
                static fn (string $expectedValue, string $actualValue): bool => $expectedValue === $actualValue,
                sprintf('%s::getProtocolVersion()', $request::class)
            ),
            $request->getProtocolVersion()
        );
    }

        Assert::assertInstanceOf(Route::class, $matchedRoute);
        $matchedMiddlewareOrResponseHandler = $matchedRoute->getMiddleware();
    /**
     * @param array<string,mixed> $expected
     */
    public function assertServerRequestQueryParams(ServerRequestInterface $request, array $expected): void
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

        /** @var class-string $matchedMiddlewareOrResponseHandlerName */
        $matchedMiddlewareOrResponseHandlerName = match (true) {
            ($matchedMiddlewareOrResponseHandler instanceof LazyLoadingMiddleware) => (new ReflectionClass(
                $matchedMiddlewareOrResponseHandler
            ))
                ->getProperty('middlewareName')
                ->getValue($matchedMiddlewareOrResponseHandler),
            default => $matchedMiddlewareOrResponseHandler::class
        };
    public function assertServerRequestRequestTarget(ServerRequestInterface $request, string $expected): void
    {
        $this->assert(
            $this->constraint(
                $expected,
                static fn (string $expectedValue, string $actualValue): bool => $expectedValue === $actualValue,
                sprintf('%s::getRequestTarget()', $request::class)
            ),
            $request->getRequestTarget()
        );
    }

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
    /**
     * @param array<UploadedFileInterface> $expected
     */
    public function assertServerRequestUploadedFiles(ServerRequestInterface $request, array $expected): void
    {
        $this->assert(
            $this->constraint(
                $expected,
                static fn (array $expectedValue, array $actualValue): bool => $expectedValue === $actualValue,
                sprintf('%s::getUploadedFiles()', $request::class)
            ),
            $request->getUploadedFiles()
        );
    }

    private function assert(Constraint $constraint, mixed $actual): void
    {
        Assert::assertThat($actual, $constraint);
    }

    private function assertNot(Constraint $constraint, mixed $actual): void
    {
        $this->assert(new LogicalNot($constraint), $actual);
    }

    /**
     * @template TExpected
     * @template TActual
     *
     * @param TExpected                       $expected
     * @param Closure(TExpected,TActual):bool $assertion
     */
    private function constraint(
        mixed $expected,
        Closure $assertion,
        string $message = ' matches the expected result.'
    ): Constraint {
        return new class ($expected, $message, $assertion) extends Constraint {
            public function __construct(
                private mixed $expected,
                private string $message,
                private Closure $assertion,
            ) {
            }

            /**
             * @param TActual $other
             */
            protected function matches(mixed $other): bool
            {
                return match (true) {
                    ($this->assertion)($this->expected, $other) => true,
                    default => false
                };
            }

            /**
             * @param TActual $other
             */
            protected function failureDescription(mixed $other): string
            {
                $comparisonFailure = new ComparisonFailure(
                    $this->expected,
                    $other,
                    $this->exporter()
                        ->export($this->expected),
                    $this->exporter()
                        ->export($other),
                    true,
                    $this->message . ' matches the expected result.'
                );

                return $comparisonFailure->toString();
            }

            public function toString(): string
            {
                return self::class;
            }
        };
    }

    private function constraintMatchedRouteName(RouteResult $routeResult, string $expected): Constraint
    {
        return $this->constraint(
            $expected,
            static fn (string $expectedValue, string $actualValue): bool => $expectedValue === $actualValue,
            sprintf('%s::getMatchedRouteName()', $routeResult::class)
        );
    }
}
