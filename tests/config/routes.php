<?php

declare(strict_types=1);

use Laminas\Diactoros\Response\JsonResponse;
use Laminas\Diactoros\Response\TextResponse;
use Mezzio\Application;
use Mezzio\Handler\NotFoundHandler;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

return static function (Application $app): void {
    $app->get('/', static fn (): TextResponse => new TextResponse('Hi'), 'home');
    $app->get('/404', NotFoundHandler::class, '404');
    $app->get('/error', static function (): void {
        throw new LogicException('I have an error');
    }, 'error');
    $app->any('/crud', new class () implements RequestHandlerInterface {
        public function handle(ServerRequestInterface $request): ResponseInterface
        {
            $body = array_merge((array)$request->getParsedBody(), $request->getQueryParams());
            if (str_contains($request->getHeaderLine('Content-Type'), 'json')) {
                return new JsonResponse($body);
            }

            return new TextResponse(http_build_query($body));
        }
    }, 'crud');
};
