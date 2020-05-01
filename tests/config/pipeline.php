<?php

declare(strict_types=1);

use Laminas\Stratigility\Middleware\ErrorHandler;
use Mezzio\Application;
use Mezzio\Handler\NotFoundHandler;
use Mezzio\Router\Middleware\DispatchMiddleware;
use Mezzio\Router\Middleware\RouteMiddleware;

return static function (Application $app): void {
    $app->pipe(ErrorHandler::class);
    $app->pipe(RouteMiddleware::class);
    $app->pipe(DispatchMiddleware::class);
    $app->pipe(NotFoundHandler::class);
};
