![Check Build](https://github.com/trinet-at/mezzio-test/workflows/Check%20Build/badge.svg)

# mezzio-test

`mezzio-test` provides classes and tools to help testing [mezzio](https://github.com/mezzio/mezzio) applications.
Its API aims to be similar to [`laminas-test`](https://github.com/laminas/laminas-test) to ease migration
from Laminas MVC to Mezzio.

The package is not bound to any testing framework as it does not do any assertions. Instead it just bootstraps
the `Container` and `Application` based on your config file. Config file locations default to the
[mezzio-skeleton](https://github.com/mezzio/mezzio-skeleton), but can be reconfigured.

Also, a `TestConfigProvider` is provided for loading custom testing configuration
(custom database, custom container configuration, ...).

## Usage

Instantiate the `\Trinet\MezzioTest\MezzioTestEnvironment` class in your test setup:
```php
protected function setUp(): void
{
    parent::setUp();
    $this->mezzioApp = new MezzioTestEnvironment();
}
```
This will build your application container, bootstrap the mezzio Application (pipeline, routes) and
registers a custom `\Laminas\Stratigility\Middleware\ErrorHandler` listener, which will just re-throw
any exception. Thus, the native exception assertions can be used (eg. `$this->expectException()` in PHPUnit).

Currently, the Test environment offers three possibilities to dispatch a request:
* `dispatch(UriInterface|string $uri, ?string $method = null array $params = []): ResponseInterface`:
dispatch any URI with the given method (defaults to `GET`). `$params` will be used as query parameters for `GET`
and as parsed body for `POST` requests.
* `dispatchRoute(string $routeName, array $routeParams = [], string $method = null, array $requestParams = []): ResponseInterface`:
dispatch a given named route
* `dispatchRequest(ServerRequestInterface $request): ResponseInterface`: dispatch a `ServerRequestInterface`

If your base directory is not at the default location, a constructor parameter can be given to `MezzioTestEnvironment`.

The container and router can also be retrieved with `MezzioTestEnvironment->container()` and `->router()`, respectively.

### Configuration
The `\Trinet\MezzioTest\TestConfigProvider` can be used to load additional config files used for testing.
It will look for `*testing.php`, `*testing.local.php`, `testing/*testing.php` and `testing/*testing.local.php`
in the config directory, which defaults to `config/autoload/` in your project root, but can be configured
to anything else.

To use it, call the loader when in testing mode in your `config/config.php` file to get an array of providers:
```php
$providers = [
    \A\ConfigProvider::class,
    \B\ConfigProvider::class,
    // ...
];

if (getenv('APP_TESTING') !== false) {
    $providers = array_merge($providers, \Trinet\MezzioTest\TestConfigProvider::load());
}

$aggregator = new ConfigAggregator($providers, null, []);
```
or to use another config path:
```php
$providers = [
    \A\ConfigProvider::class,
    \B\ConfigProvider::class,
    // ...
];

if (getenv('APP_TESTING') !== false) {
    $providers = array_merge($providers, \Trinet\MezzioTest\TestConfigProvider::load('custom/path'));
}

$aggregator = new ConfigAggregator($providers, null, []);
```
