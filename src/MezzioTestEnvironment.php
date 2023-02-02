<?php

declare(strict_types=1);

namespace Trinet\MezzioTest;

use Closure;
use Fig\Http\Message\RequestMethodInterface;
use Laminas\Stratigility\Middleware\ErrorHandler;
use Mezzio\Application;
use Mezzio\MiddlewareFactory;
use Mezzio\Router\RouteResult;
use Mezzio\Router\RouterInterface;
use PHPUnit\Framework\Assert;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
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
     * @param string|null $basePath
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
        $this->application = $this->container->get(Application::class);
        $this->router = $this->container->get(RouterInterface::class);

        /** @var MiddlewareFactory $middlewareFactory */
        $middlewareFactory = $this->container->get(MiddlewareFactory::class);
        ($this->requireClosure('pipeline.php'))($this->application, $middlewareFactory, $this->container);
        ($this->requireClosure('routes.php'))($this->application, $middlewareFactory, $this->container);

        // Attach an ErrorListener to the ErrorHandler
        if (!$this->container->has(ErrorHandler::class)) {
            return;
        }

        $this->container->get(ErrorHandler::class)
            ->attachListener(static fn (Throwable $error) => throw $error);
    }

    public function application(): Application
    {
        return $this->application;
    }

    public function container(): ContainerInterface
    {
        return $this->container;
    }

    private function requireContainer(): ContainerInterface
    {
        $result = $this->requirePath('container.php');
        assert($result instanceof ContainerInterface);
        return $result;
    }

    private function requireClosure(string $path): Closure
    {
        $result = $this->requirePath($path);
        assert($result instanceof Closure);
        return $result;
    }

    /**
     * @param UriInterface|string $uri
     * @param string $method
     * @param array<string,string> $params
     * @param array<string|array<string>> $headers
     * @return ResponseInterface
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
            method:$method,
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
     * @param string $name
     * @param array<string,mixed> $routeParams
     * @param array<string,mixed> $options
     * @return string
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function generateUri(string $name, array $routeParams = [], array $options = []): string
    {
        return $this->router->generateUri($name, $routeParams, $options);
    }

    /**
     * Match a request against the known routes.
     */
    public function match(ServerRequestInterface $request): RouteResult
    {
        return $this->routeResult = $this->router->match($request);
    }

    public function router(): RouterInterface
    {
        return $this->router;
    }

    /**
     * @psalm-suppress UnresolvableInclude
     *
     * @return ContainerInterface|Closure(Application,MiddlewareFactory,ContainerInterface)
     */
    private function requirePath(string $suffix = ''): ContainerInterface|Closure
    {
        /** @var ContainerInterface|Closure $result */
        $result = require $this->basePath . '/config/' . $suffix;
        if ($result instanceof Closure) {
            /** @var Closure(Application,MiddlewareFactory,ContainerInterface) $result */
            return $result;
        }
        return $result;
    }

    public function getRouteResult(): ?RouteResult
    {
        return $this->routeResult;
    }

    public function getResponse(): ?ResponseInterface
    {
        return $this->response;
    }

    public function getRequest(): ?ServerRequestInterface
    {
        return $this->request;
    }
}
