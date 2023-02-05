<?php

declare(strict_types=1);

namespace Trinet\MezzioTest;

use Closure;
use Fig\Http\Message\RequestMethodInterface;
use Laminas\Stratigility\Middleware\ErrorHandler;
use Mezzio\Application;
use Mezzio\MiddlewareFactory;
use Mezzio\Router\Route;
use Mezzio\Router\RouteResult;
use Mezzio\Router\RouterInterface;
use PHPUnit\Framework\Assert;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;
use Throwable;

use function assert;

final class MezzioTestEnvironment extends Assert
{
    use AssertionsTrait;
    use RequestsTrait;

    private Application $application;

    private string $basePath;

    private ContainerInterface $container;

    private ?ServerRequestInterface $request = null;

    private ?ResponseInterface $response = null;

    private RouterInterface $router;

    private ?RouteResult $routeResult = null;

    /**
     * @psalm-suppress PossiblyInvalidFunctionCall
     */
    public function __construct(?string $basePath = null)
    {
        \Safe\putenv('APP_TESTING=true');

        $basePath ??= Util::basePath();

        $this->basePath = Util::ensureTrailingSlash($basePath);

        \Safe\chdir($this->basePath);

        // initialize App for routes to be populated

        $this->container = $this->requireContainer();

        /** @var Application $this->application */
        $this->application = $this->container->get(Application::class);

        /** @var RouterInterface $this->router */
        $this->router = $this->container->get(RouterInterface::class);

        /** @var MiddlewareFactory $middlewareFactory */
        $middlewareFactory = $this->container->get(MiddlewareFactory::class);
        ($this->requireClosure('pipeline.php'))($this->application, $middlewareFactory, $this->container);
        ($this->requireClosure('routes.php'))($this->application, $middlewareFactory, $this->container);

        // Attach an ErrorListener to the ErrorHandler
        if (! $this->container->has(ErrorHandler::class)) {
            return;
        }

        /** @var ErrorHandler $errorHandler */
        $errorHandler = $this->container->get(ErrorHandler::class);
        $errorHandler->attachListener(static fn (Throwable $error) => throw $error);
    }

    public function addRoute(Route $route): void
    {
        $this->router->addRoute($route);
    }

    /**
     * @param array<string,string>                      $params
     * @param array<string,array<string,string>|string> $headers
     */
    public function dispatch(
        UriInterface|string $uri,
        string $method = RequestMethodInterface::METHOD_GET,
        array $params = [],
        array $headers = []
    ): ResponseInterface {
        $withQueryParams = $method === RequestMethodInterface::METHOD_GET ? $params : [];
        $withParsedBody = $method !== RequestMethodInterface::METHOD_GET ? $params : [];

        $request = $this->request(
            method: $method,
            uri: $uri,
            queryParams: $withQueryParams,
            parsedBody: $withParsedBody,
            headers: $headers
        );
        return $this->dispatchRequest($request);
    }

    public function dispatchRequest(ServerRequestInterface $request): ResponseInterface
    {
        $this->request = $request;

        $this->match($request);

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

        return $this->dispatchRequest($this->request($method, $routeUrl, $queryParams, $headers));
    }

    /**
     * Generate a URI from the named route.
     *
     * @param array<string,mixed> $routeParams
     * @param array<string,mixed> $options
     */
    public function generateUri(string $name, array $routeParams = [], array $options = []): string
    {
        return $this->router->generateUri($name, $routeParams, $options);
    }

    public function getApplication(): Application
    {
        return $this->application;
    }

    public function getContainer(): ContainerInterface
    {
        return $this->container;
    }

    public function getRequest(): ?ServerRequestInterface
    {
        return $this->request;
    }

    public function getResponse(): ?ResponseInterface
    {
        return $this->response;
    }

    public function getRouter(): RouterInterface
    {
        return $this->router;
    }

    public function getRouteResult(): ?RouteResult
    {
        return $this->routeResult;
    }

    /**
     * Match a request against the known routes.
     */
    public function match(ServerRequestInterface $request): RouteResult
    {
        return $this->routeResult = $this->router->match($request);
    }

    private function requireClosure(string $path): Closure
    {
        $result = $this->requirePath($path);
        assert($result instanceof Closure);
        return $result;
    }

    private function requireContainer(): ContainerInterface
    {
        $result = $this->requirePath('container.php');
        assert($result instanceof ContainerInterface);
        return $result;
    }

    /**
     * @psalm-suppress UnresolvableInclude
     *
     * @return Closure(Application,MiddlewareFactory,ContainerInterface)|ContainerInterface
     */
    private function requirePath(string $suffix = ''): ContainerInterface|Closure
    {
        /** @var Closure|ContainerInterface $result */
        $result = require $this->basePath . '/config/' . $suffix;
        if ($result instanceof Closure) {
            /** @var Closure(Application,MiddlewareFactory,ContainerInterface) $result */
            return $result;
        }
        return $result;
    }
}
