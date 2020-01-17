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
        'home'
    );
};
