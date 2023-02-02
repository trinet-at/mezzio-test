<?php

declare(strict_types=1);

namespace Trinet\MezzioTest;

use Fig\Http\Message\RequestMethodInterface;
use Laminas\Diactoros\ServerRequest;
use Laminas\Stratigility\Middleware\ErrorHandler;
use Mezzio\Application;
use Mezzio\MiddlewareFactory;
use Mezzio\Router\RouteResult;
use Mezzio\Router\RouterInterface;
use PHPUnit\Framework\Assert;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UploadedFileInterface;
use Psr\Http\Message\UriInterface;
use Psr\Http\Server\MiddlewareInterface;
use ReflectionClass;
use Throwable;

use function sprintf;

final class MezzioTestEnvironment extends Assert
{
    private Application $application;

    private string $basePath;

    private ContainerInterface $container;

    private MiddlewareFactory $middlewareFactory;

    private ?ServerRequestInterface $request = null;

    private ?ResponseInterface $response = null;

    private RouterInterface $router;

    private ?RouteResult $routeResult = null;

    public function __construct(?string $basePath = null)
    {
        \Safe\putenv('APP_TESTING=true');

        $basePath ??= Util::basePath();

        $this->basePath = Util::ensureTrailingSlash($basePath);

        \Safe\chdir($this->basePath);

        // initialize App for routes to be populated

        /** @var ContainerInterface $this- >container */
        $this->container = $this->container();

        /** @var Application $this- >application */
        $this->application = $this->application();

        /** @var MiddlewareFactory $this- >middlewareFactory */
        $this->middlewareFactory = $this->container->get(MiddlewareFactory::class);

        /** @var RouterInterface $this- >router */
        $this->router = $this->container->get(RouterInterface::class);

        ($this->requirePath('pipeline.php'))(
            $this->application,
            $this->middlewareFactory,
            $this->container
        );

        ($this->requirePath('routes.php'))(
            $this->application,
            $this->middlewareFactory,
            $this->container
        );

        // Attach an ErrorListener to the ErrorHandler
        if (!$this->container->has(ErrorHandler::class)) {
            return;
        }

        $this->container->get(ErrorHandler::class)
            ->attachListener(static fn (Throwable $error) => throw $error);
    }

    public function application(): Application
    {
        return $this->application ??= $this->container->get(Application::class);
    }

    public function assertResponseBodyContainsString(string $string): self
    {
        self::assertStringContainsString($string, (string)$this->response->getBody());

        return $this;
    }

    public function assertRouteMiddleware(string $middlewareOrResponseHandlerClass)
    {
        self::assertInstanceOf(RouteResult::class, $this->routeResult);

        $matchedMiddlewareOrResponseHandler = $this->routeResult
            ->getMatchedRoute()
            ->getMiddleware();

        $middlewareOrResponseHandlerName = (new ReflectionClass($matchedMiddlewareOrResponseHandler))
            ->getProperty('middlewareName')
            ->getValue($matchedMiddlewareOrResponseHandler);

        self::assertSame($middlewareOrResponseHandlerClass, $middlewareOrResponseHandlerName);

        $this->assertTrue(
            (new ReflectionClass($middlewareOrResponseHandlerName))
                ->implementsInterface(MiddlewareInterface::class),
            sprintf(
                'Class "%s" does not implement "%s".',
                $middlewareOrResponseHandlerClass,
                MiddlewareInterface::class
            )
        );
        return $this;
    }

    public function assertSameMatchedRouteName(string $routeName): self
    {
        self::assertSame($routeName, $this->routeResult->getMatchedRouteName());

        return $this;
    }

    public function assertSameRequestHeaders(array $headers): self
    {
        self::assertSame($headers, $this->request->getHeaders());

        return $this;
    }

    public function assertSameRequestParsedBody(array $parsedBody): self
    {
        self::assertSame($parsedBody, $this->request->getParsedBody());

        return $this;
    }

    public function assertSameRequestQueryParams(array $queryParams): self
    {
        self::assertSame($queryParams, $this->request->getQueryParams());

        return $this;
    }

    public function assertSameResponseBody(string $content): self
    {
        self::assertSame($content, (string)$this->response->getBody());

        return $this;
    }

    public function assertSameResponseHeaders(array $headers): self
    {
        self::assertSame($headers, $this->response->getHeaders());

        return $this;
    }

    public function assertSameResponseReasonPhrase(string $reasonPhrase): self
    {
        self::assertSame($reasonPhrase, $this->response->getReasonPhrase());

        return $this;
    }

    public function assertSameResponseStatusCode(int $statusCode): self
    {
        self::assertSame($statusCode, $this->response->getStatusCode());

        return $this;
    }

    public function container(): ContainerInterface
    {
        return $this->container ??= $this->requirePath('container.php');
    }

    public function dispatch(ServerRequestInterface $request): ResponseInterface
    {
        $this->request = $request;

        $this->routeResult = $this->router->match($request);

        return $this->response = $this->application->handle($request);
    }

    /**
     * @param array<string, mixed>  $routeParams
     * @param array<string, mixed>  $queryParams
     * @param array<string, string> $headers
     */
    public function dispatchRoute(
        string $routeName,
        array $routeParams = [],
        string $method = RequestMethodInterface::METHOD_GET,
        array $queryParams = [],
        array $headers = []
    ): ResponseInterface {
        $routeUrl = $this->generateUri($routeName, $routeParams);

        self::assertSame('/', $routeUrl);

        return $this->dispatch($this->request($method, $routeUrl, $queryParams, $headers));
    }

    /**
     * Generate a URI from the named route.
     */
    public function generateUri(string $name, array $routeParams = [], array $options = []): string
    {
        return $this->router->generateUri($name, $routeParams, $options);
    }

    /**
     * Visit the given URI with a GET request.
     *
     * @param array<string, mixed>                        $queryParams
     * @param array<string, array<string, string>|string> $headers
     * @param array<string, string>                       $cookieParams
     * @param array<string, string>                       $serverParams
     *
     */
    public function get(
        UriInterface|string $uri,
        array $queryParams = [],
        array $headers = [],
        array $cookieParams = [],
        array $serverParams = [],
    ): ResponseInterface {
        return $this->dispatch(
            $this->request(
                method: RequestMethodInterface::METHOD_GET,
                uri: $uri,
                queryParams: $queryParams,
                headers: $headers,
                cookieParams: $cookieParams,
                serverParams: $serverParams
            )
        );
    }

    /**
     * Match a request against the known routes.
     */
    public function match(ServerRequestInterface $request): RouteResult
    {
        return $this->routeResult = $this->router->match($request);
    }

    /**
     * Visit the given URI with a PATCH request.
     *
     * @param array<string, string>                $parsedBody
     * @param array<UploadedFileInterface>         $uploadedFiles
     * @param array<string, array<string, string>> $headers
     * @param array<string, string>                $cookieParams
     * @param array<string, string>                $serverParams
     *
     */
    public function patch(
        UriInterface|string $uri,
        array $parsedBody = [],
        array $uploadedFiles = [],
        string $body = 'php://input',
        array $headers = [],
        array $cookieParams = [],
        array $serverParams = [],
    ): ResponseInterface {
        return $this->dispatch(
            $this->request(
                method: RequestMethodInterface::METHOD_PATCH,
                uri: $uri,
                parsedBody: $parsedBody,
                uploadedFiles: $uploadedFiles,
                body: $body,
                headers: $headers,
                cookieParams: $cookieParams,
                serverParams: $serverParams
            )
        );
    }

    /**
     * Visit the given URI with a POST request.
     *
     * @param array<string, string>                $parsedBody
     * @param array<UploadedFileInterface>         $uploadedFiles
     * @param array<string, array<string, string>> $headers
     * @param array<string, string>                $cookieParams
     * @param array<string, string>                $serverParams
     *
     */
    public function post(
        UriInterface|string $uri,
        array $parsedBody = [],
        array $uploadedFiles = [],
        string $body = 'php://input',
        array $headers = [],
        array $cookieParams = [],
        array $serverParams = [],
    ): ResponseInterface {
        return $this->dispatch(
            $this->request(
                method: RequestMethodInterface::METHOD_POST,
                uri: $uri,
                parsedBody: $parsedBody,
                uploadedFiles: $uploadedFiles,
                body: $body,
                headers: $headers,
                cookieParams: $cookieParams,
                serverParams: $serverParams
            )
        );
    }

    /**
     * Build a Request.
     *
     * @param array<string, mixed>                        $queryParams
     * @param array<string, string>                       $parsedBody
     * @param array<UploadedFileInterface>                $uploadedFiles
     * @param array<string, array<string, string>|string> $headers
     * @param array<string, string>                       $cookieParams
     * @param array<string, string>                       $serverParams
     *
     */
    public function request(
        string $method,
        string|UriInterface $uri,
        array $queryParams = [],
        array $parsedBody = [],
        array $uploadedFiles = [],
        string $body = 'php://input',
        array $headers = [],
        array $cookieParams = [],
        array $serverParams = [],
        string $protocol = '1.1'
    ): ServerRequestInterface {
        return $this->request = new ServerRequest(
            $serverParams,
            $uploadedFiles,
            $uri,
            $method,
            $body,
            $headers,
            $cookieParams,
            $queryParams,
            $parsedBody,
            $protocol
        );
    }

    public function router(): RouterInterface
    {
        return $this->router;
    }

    /**
     * @psalm-suppress UnresolvableInclude
     *
     * @return array|callable
     */
    private function requirePath(?string $suffix = null): mixed
    {
        return require $this->basePath . '/config/' . $suffix;
    }
}
