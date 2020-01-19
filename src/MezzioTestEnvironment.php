<?php

/**
 * @noinspection PhpIncludeInspection
 */

declare(strict_types=1);

namespace Trinet\MezzioTest;

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

final class MezzioTestEnvironment
{
    /** @var ContainerInterface */
    private $container;
    /** @var Application */
    private $app;
    /** @var string */
    private $basePath;

    public function __construct(?string $basePath = null)
    {
        \Safe\putenv('APP_TESTING=true');
        $this->basePath = $basePath ?? Util::basePath();
        $this->basePath = Util::ensureTrailingSlash($this->basePath);
        \Safe\chdir($this->basePath);
        $this->app();   // initialize App for routes to be populated
        $this->registerErrorListener();
    }

    /**
     * @param string|UriInterface $uri
     */
    public function dispatch($uri, ?string $method = null): ResponseInterface
    {
        $request = new ServerRequest([], [], $uri, $method);
        return $this->app()->handle($request);
    }

    public function dispatchRoute(
        string $routeName,
        ?string $method = null
    ): ResponseInterface {
        $router = $this->router();
        $route = $router->generateUri($routeName);
        return $this->dispatch($route, $method);
    }

    public function dispatchRequest(ServerRequestInterface $request): ResponseInterface
    {
        return $this->app()->handle($request);
    }

    public function container(): ContainerInterface
    {
        if ($this->container !== null) {
            return $this->container;
        }
        $this->container = require $this->basePath . 'config/container.php';
        return $this->container;
    }

    public function router(): RouterInterface
    {
        return $this->container()->get(RouterInterface::class);
    }

    private function registerErrorListener(): void
    {
        if (!$this->container()->has(ErrorHandler::class)) {
            return;
        }
        $errorHandler = $this->container()->get(ErrorHandler::class);
        $errorHandler->attachListener(
            static function (Throwable $error) {
                throw $error;
            }
        );
    }

    private function app(): Application
    {
        if ($this->app !== null) {
            return $this->app;
        }
        $this->app = $this->container()->get(Application::class);
        $factory = $this->container()->get(MiddlewareFactory::class);

        (require $this->basePath . 'config/pipeline.php')($this->app, $factory, $this->container());
        (require $this->basePath . 'config/routes.php')($this->app, $factory, $this->container());
        return $this->app;
    }
}
