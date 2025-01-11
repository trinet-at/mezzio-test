<?php

declare(strict_types=1);

use Laminas\Diactoros\Response\TextResponse;
use Mezzio\Application;

return static function (Application $app): void {
    $app->get(
        '/',
        static function (): TextResponse {
            return new TextResponse('Hi');
        },
        'home',
    );
    $app->get(
        '/error',
        static function (): void {
            throw new LogicException('I have an error');
        },
        'error',
    );
};
