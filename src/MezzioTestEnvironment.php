<?php

/**
 * @noinspection PhpIncludeInspection
 */

declare(strict_types=1);

namespace Trinet\MezzioTest;

use Fig\Http\Message\RequestMethodInterface;
use Laminas\Diactoros\ServerRequest;
use Laminas\Stratigility\Middleware\ErrorHandler;
use Mezzio\Application;
use Mezzio\MiddlewareFactory;
use Mezzio\Router\RouterInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;
use Throwable;

use function chdir;
use function count;
use function putenv;

final class MezzioTestEnvironment
{
    private ContainerInterface|null $container = null;
    private Application|null $app = null;
    private string $basePath;

    public function __construct(string|null $basePath = null)
    {
        putenv('APP_TESTING=true');
        $this->basePath = $basePath ?? Util::basePath();
        $this->basePath = Util::ensureTrailingSlash($this->basePath);
        chdir($this->basePath);
        $this->app();   // initialize App for routes to be populated
        $this->registerErrorListener();
    }

    /**
     * @param string|UriInterface $uri
     * @param array<string, mixed> $params
     * @param array<string, string> $headers
     */
    public function dispatch(
        $uri,
        string|null $method = null,
        array $params = [],
        array $headers = [],
    ): ResponseInterface {
        if ($method === null) {
            $method = RequestMethodInterface::METHOD_GET;
        }
        $request = new ServerRequest([], [], $uri, $method);

        if (count($params) !== 0) {
            switch ($method) {
                case RequestMethodInterface::METHOD_GET:
                    $request = $request->withQueryParams($params);
                    break;
                case RequestMethodInterface::METHOD_PUT:
                case RequestMethodInterface::METHOD_PATCH:
                case RequestMethodInterface::METHOD_POST:
                    $request = $request->withParsedBody($params);
            }
        }

        foreach ($headers as $header => $value) {
            $request = $request->withHeader($header, $value);
        }

        return $this->app()->handle($request);
    }

    /**
     * @param array<string, mixed> $routeParams
     * @param array<string, mixed> $requestParams
     * @param array<string, string> $headers
     */
    public function dispatchRoute(
        string $routeName,
        array $routeParams = [],
        string|null $method = null,
        array $requestParams = [],
        array $headers = [],
    ): ResponseInterface {
        $router = $this->router();
        $route = $router->generateUri($routeName, $routeParams);
        return $this->dispatch($route, $method, $requestParams, $headers);
    }

    public function dispatchRequest(ServerRequestInterface $request): ResponseInterface
    {
        return $this->app()->handle($request);
    }

    /**
     * @psalm-suppress UnresolvableInclude
     */
    public function container(): ContainerInterface
    {
        if ($this->container !== null) {
            return $this->container;
        }
        /** @var ContainerInterface $container */
        $container = require $this->basePath . 'config/container.php';
        $this->container = $container;
        return $this->container;
    }

    public function router(): RouterInterface
    {
        return $this->container()->get(RouterInterface::class);
    }

    private function registerErrorListener(): void
    {
        if (!$this->container()->has(ErrorHandler::class)) {
            return; // @codeCoverageIgnore
        }
        $errorHandler = $this->container()->get(ErrorHandler::class);
        $errorHandler->attachListener(
            static function (Throwable $error): void {
                throw $error;
            },
        );
    }

    /**
     * @psalm-suppress UnresolvableInclude
     */
    private function app(): Application
    {
        if ($this->app !== null) {
            return $this->app;
        }
        $this->app = $this->container()->get(Application::class);
        $factory = $this->container()->get(MiddlewareFactory::class);

        /** @var callable $pipeline */
        $pipeline = require $this->basePath . 'config/pipeline.php';
        $pipeline($this->app, $factory, $this->container());
        /** @var callable $routes */
        $routes = require $this->basePath . 'config/routes.php';
        $routes($this->app, $factory, $this->container());
        return $this->app;
    }
}
