<?php

declare(strict_types=1);

namespace Trinet\MezzioTest;

use Closure;
use Fig\Http\Message\RequestMethodInterface;
use Laminas\Diactoros\ServerRequest;
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
use Psr\Http\Message\UploadedFileInterface;
use Psr\Http\Message\UriInterface;
use Throwable;

use UnexpectedValueException;
use function array_merge;
use function assert;

final class MezzioTestEnvironment extends Assert
{
    use AssertionsTrait;

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

        $this->application = $this->container->get(Application::class);

        $this->router = $this->container->get(RouterInterface::class);

        $this->requireClosure('pipeline.php', 'routes.php');

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
     * Visit the given URI with a DELETE request.
     *
     * @param array<string,string>                      $parsedBody
     * @param array<string,array<string,string>|string> $headers
     * @param array<string,string>                      $cookieParams
     * @param array<string,string>                      $serverParams
     */
    public function delete(
        UriInterface|string $uri,
        array $parsedBody = [],
        array $headers = [],
        array $cookieParams = [],
        array $serverParams = [],
    ): ResponseInterface {
        return $this->dispatchRequest(
            $this->request(
                RequestMethodInterface::METHOD_DELETE,
                $uri,
                [],
                $parsedBody,
                [],
                $headers,
                $cookieParams,
                $serverParams
            )
        );
    }

    /**
     * Visit the given URI with a DELETE request, expecting a JSON response.
     *
     * @param array<string,string>                      $parsedBody
     * @param array<string,array<string,string>|string> $headers
     * @param array<string,string>                      $cookieParams
     * @param array<string,string>                      $serverParams
     */
    public function deleteJson(
        UriInterface|string $uri,
        array $parsedBody = [],
        array $headers = [],
        array $cookieParams = [],
        array $serverParams = [],
    ): ResponseInterface {
        return $this->dispatchRequest(
            $this->requestJson(
                RequestMethodInterface::METHOD_DELETE,
                $uri,
                [],
                $parsedBody,
                [],
                $headers,
                $cookieParams,
                $serverParams
            )
        );
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

        $request = $this->request($method, $uri, $withQueryParams, $withParsedBody, [], $headers);
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

    /**
     * Visit the given URI with a GET request.
     *
     * @param array<string,string>                      $queryParams
     * @param array<string,array<string,string>|string> $headers
     * @param array<string,string>                      $cookieParams
     * @param array<string,string>                      $serverParams
     */
    public function get(
        UriInterface|string $uri,
        array $queryParams = [],
        array $headers = [],
        array $cookieParams = [],
        array $serverParams = [],
    ): ResponseInterface {
        return $this->dispatchRequest(
            $this->request(
                RequestMethodInterface::METHOD_GET,
                $uri,
                $queryParams,
                [],
                [],
                $headers,
                $cookieParams,
                $serverParams
            )
        );
    }

    public function getApplication(): Application
    {
        return $this->application;
    }

    public function getContainer(): ContainerInterface
    {
        return $this->container;
    }

    /**
     * Visit the given URI with a GET request, expecting a JSON response.
     *
     * @param array<string,string>                      $queryParams
     * @param array<string,array<string,string>|string> $headers
     * @param array<string,string>                      $cookieParams
     * @param array<string,string>                      $serverParams
     */
    public function getJson(
        UriInterface|string $uri,
        array $queryParams = [],
        array $headers = [],
        array $cookieParams = [],
        array $serverParams = [],
    ): ResponseInterface {
        return $this->dispatchRequest(
            $this->requestJson(
                RequestMethodInterface::METHOD_GET,
                $uri,
                $queryParams,
                [],
                [],
                $headers,
                $cookieParams,
                $serverParams
            )
        );
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
     * Visit the given URI with a HEAD request.
     *
     * @param array<string,string>                      $queryParams
     * @param array<string,array<string,string>|string> $headers
     * @param array<string,string>                      $cookieParams
     * @param array<string,string>                      $serverParams
     */
    public function head(
        UriInterface|string $uri,
        array $queryParams = [],
        array $headers = [],
        array $cookieParams = [],
        array $serverParams = [],
    ): ResponseInterface {
        return $this->dispatchRequest(
            $this->request(
                RequestMethodInterface::METHOD_HEAD,
                $uri,
                $queryParams,
                [],
                [],
                $headers,
                $cookieParams,
                $serverParams
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
     * Visit the given URI with a OPTIONS request.
     *
     * @param array<string,string>                      $queryParams
     * @param array<string,array<string,string>|string> $headers
     * @param array<string,string>                      $cookieParams
     * @param array<string,string>                      $serverParams
     */
    public function options(
        UriInterface|string $uri,
        array $queryParams = [],
        array $headers = [],
        array $cookieParams = [],
        array $serverParams = [],
    ): ResponseInterface {
        return $this->dispatchRequest(
            $this->request(
                RequestMethodInterface::METHOD_OPTIONS,
                $uri,
                $queryParams,
                [],
                [],
                $headers,
                $cookieParams,
                $serverParams
            )
        );
    }

    /**
     * Visit the given URI with a OPTIONS request, expecting a JSON response.
     *
     * @param array<string,string>                      $queryParams
     * @param array<string,array<string,string>|string> $headers
     * @param array<string,string>                      $cookieParams
     * @param array<string,string>                      $serverParams
     */
    public function optionsJson(
        UriInterface|string $uri,
        array $queryParams = [],
        array $headers = [],
        array $cookieParams = [],
        array $serverParams = [],
    ): ResponseInterface {
        return $this->dispatchRequest(
            $this->requestJson(
                RequestMethodInterface::METHOD_OPTIONS,
                $uri,
                $queryParams,
                [],
                [],
                $headers,
                $cookieParams,
                $serverParams
            )
        );
    }

    /**
     * Visit the given URI with a PATCH request.
     *
     * @param array<string,string>                      $parsedBody
     * @param array<UploadedFileInterface>              $uploadedFiles
     * @param array<string,array<string,string>|string> $headers
     * @param array<string,string>                      $cookieParams
     * @param array<string,string>                      $serverParams
     */
    public function patch(
        UriInterface|string $uri,
        array $parsedBody = [],
        array $uploadedFiles = [],
        array $headers = [],
        array $cookieParams = [],
        array $serverParams = [],
    ): ResponseInterface {
        return $this->dispatchRequest(
            $this->request(
                RequestMethodInterface::METHOD_PATCH,
                $uri,
                [],
                $parsedBody,
                $uploadedFiles,
                $headers,
                $cookieParams,
                $serverParams
            )
        );
    }

    /**
     * Visit the given URI with a PATCH request, expecting a JSON response.
     *
     * @param array<string,string>                      $parsedBody
     * @param array<UploadedFileInterface>              $uploadedFiles
     * @param array<string,array<string,string>|string> $headers
     * @param array<string,string>                      $cookieParams
     * @param array<string,string>                      $serverParams
     */
    public function patchJson(
        UriInterface|string $uri,
        array $parsedBody = [],
        array $uploadedFiles = [],
        array $headers = [],
        array $cookieParams = [],
        array $serverParams = [],
    ): ResponseInterface {
        return $this->dispatchRequest(
            $this->requestJson(
                RequestMethodInterface::METHOD_PATCH,
                $uri,
                [],
                $parsedBody,
                $uploadedFiles,
                $headers,
                $cookieParams,
                $serverParams
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
     */
    public function post(
        UriInterface|string $uri,
        array $parsedBody = [],
        array $uploadedFiles = [],
        array $headers = [],
        array $cookieParams = [],
        array $serverParams = [],
    ): ResponseInterface {
        return $this->dispatchRequest(
            $this->request(
                RequestMethodInterface::METHOD_POST,
                $uri,
                [],
                $parsedBody,
                $uploadedFiles,
                $headers,
                $cookieParams,
                $serverParams
            )
        );
    }

    /**
     * Visit the given URI with a POST request, expecting a JSON response.
     *
     * @param array<string, string>                $parsedBody
     * @param array<UploadedFileInterface>         $uploadedFiles
     * @param array<string, array<string, string>> $headers
     * @param array<string, string>                $cookieParams
     * @param array<string, string>                $serverParams
     */
    public function postJson(
        UriInterface|string $uri,
        array $parsedBody = [],
        array $uploadedFiles = [],
        array $headers = [],
        array $cookieParams = [],
        array $serverParams = [],
    ): ResponseInterface {
        return $this->dispatchRequest(
            $this->requestJson(
                RequestMethodInterface::METHOD_POST,
                $uri,
                [],
                $parsedBody,
                $uploadedFiles,
                $headers,
                $cookieParams,
                $serverParams
            )
        );
    }

    /**
     * Visit the given URI with a PUT request.
     *
     * @param array<string, string>                $parsedBody
     * @param array<UploadedFileInterface>         $uploadedFiles
     * @param array<string, array<string, string>> $headers
     * @param array<string, string>                $cookieParams
     * @param array<string, string>                $serverParams
     */
    public function put(
        UriInterface|string $uri,
        array $parsedBody = [],
        array $uploadedFiles = [],
        array $headers = [],
        array $cookieParams = [],
        array $serverParams = [],
    ): ResponseInterface {
        return $this->dispatchRequest(
            $this->request(
                RequestMethodInterface::METHOD_PUT,
                $uri,
                [],
                $parsedBody,
                $uploadedFiles,
                $headers,
                $cookieParams,
                $serverParams
            )
        );
    }

    /**
     * Visit the given URI with a PUT request, expecting a JSON response.
     *
     * @param array<string, string>                $parsedBody
     * @param array<UploadedFileInterface>         $uploadedFiles
     * @param array<string, array<string, string>> $headers
     * @param array<string, string>                $cookieParams
     * @param array<string, string>                $serverParams
     */
    public function putJson(
        UriInterface|string $uri,
        array $parsedBody = [],
        array $uploadedFiles = [],
        array $headers = [],
        array $cookieParams = [],
        array $serverParams = [],
    ): ResponseInterface {
        return $this->dispatchRequest(
            $this->requestJson(
                RequestMethodInterface::METHOD_PUT,
                $uri,
                [],
                $parsedBody,
                $uploadedFiles,
                $headers,
                $cookieParams,
                $serverParams
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
     */
    public function request(
        string $method,
        string|UriInterface $uri,
        array $queryParams = [],
        array $parsedBody = [],
        array $uploadedFiles = [],
        array $headers = [],
        array $cookieParams = [],
        array $serverParams = [],
    ): ServerRequestInterface {
        return $this->request = new ServerRequest(
            $serverParams,
            $uploadedFiles,
            $uri,
            $method,
            'php://input',
            $headers,
            $cookieParams,
            $queryParams,
            $parsedBody,
        );
    }

    /**
     * Build a Request, expecting a JSON response.
     *
     * @param array<string, mixed>                        $queryParams
     * @param array<string, string>                       $parsedBody
     * @param array<UploadedFileInterface>                $uploadedFiles
     * @param array<string, array<string, string>|string> $headers
     * @param array<string, string>                       $cookieParams
     * @param array<string, string>                       $serverParams
     */
    public function requestJson(
        string $method,
        string|UriInterface $uri,
        array $queryParams = [],
        array $parsedBody = [],
        array $uploadedFiles = [],
        array $headers = [],
        array $cookieParams = [],
        array $serverParams = [],
    ): ServerRequestInterface {
        $headers = array_merge($headers, [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ]);

        return $this->request(
            $method,
            $uri,
            $queryParams,
            $parsedBody,
            $uploadedFiles,
            $headers,
            $cookieParams,
            $serverParams,
        );
    }

    private function requireClosure(string ...$paths): void
    {
        /** @var MiddlewareFactory $middlewareFactory */
        $middlewareFactory = $this->container->get(MiddlewareFactory::class);
        foreach ($paths as $path) {
            $result = $this->requirePath($path);
            assert($result instanceof Closure);
            $result($this->application, $middlewareFactory, $this->container);
        }
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
     * @return Closure(Application,MiddlewareFactory,ContainerInterface):void|ContainerInterface
     */
    private function requirePath(string $suffix = ''): ContainerInterface|Closure
    {
        /** @var Closure|ContainerInterface $result */
        $result = require $this->basePath . '/config/' . $suffix;

        /** @return Closure(Application,MiddlewareFactory,ContainerInterface):void|ContainerInterface */
        return match(true) {
            ($result instanceof Closure) => /** @return Closure(Application,MiddlewareFactory,ContainerInterface):void */ $result,
            ($result instanceof ContainerInterface) => /** @return ContainerInterface*/ $result,

            default => throw new UnexpectedValueException(sprintf('Unexpected result: %s', get_debug_type($result)))
        };
    }
}
