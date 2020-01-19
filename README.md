# mezzio-test

`mezzio-test` provides classes and tools to help testing [mezzio](https://github.com/mezzio/mezzio) applications.
Its API aims to be similar to [`laminas-test`](https://github.com/laminas/laminas-test) to ease migration
from Laminas MVC to Mezzio.

The package is not bound to any testing framework as it does not do any assertions. Instead it just bootstraps
the `Container` and `Application` based on your config file. Config file locations default to the
[mezzio-skeleton](https://github.com/mezzio/mezzio-skeleton), but can be reconfigured.

Also, a `TestConfigPostProcessor` is provided for injection custom testing configuration
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
* `dispatch(UriInterface|string $uri, ?string $method = null): ResponseInterface`: dispatch any URI with the
given method (defaults to `GET`)
* `dispatchRoute(string $routeName, ?string $method = null): ResponseInterface`: dispatch a given named route
* `dispatchRequest(ServerRequestInterface $request): ResponseInterface`: dispatch a `ServerRequestInterface`

If your base directory is not at the default location, a constructor parameter can be given to `MezzioTestEnvironment`.

The container and router can also be retrieved with `MezzioTestEnvironment->container()` and `->router()`, respectively.

### Configuration
The `\Trinet\MezzioTest\TestConfigPostProcessor` can be used to load additional config files used for testing.
It will look for `*testing.php`, `*testing.local.php`, `testing/*testing.php` and `testing/*testing.local.php`
in the config directory, which defaults to `config/autoload/` in your project root, but can be configured
to anything else.

To use it, add the class to the array in the third parameter of the `ConfigAggregator` in your `config/config.php` file:
```php
$aggregator = new ConfigAggregator([], $cacheStuff, [TestConfigPostProcessor::class]);
```
or to use another config path:
```php
$aggregator = new ConfigAggregator([], $cacheStuff, [new TestConfigPostProcessor('custom/config/path/')]);
```
