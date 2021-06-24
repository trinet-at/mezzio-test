<?php

declare(strict_types=1);

namespace Trinet\Test\Functional\MezzioTest\TestDouble;

use Laminas\Diactoros\Response;
use Laminas\Diactoros\ServerRequest;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class RequestLoggerCallback
{
    private ?ServerRequestInterface $request = null;

    public function __invoke(ServerRequestInterface $request): ResponseInterface
    {
        $this->request = $request;
        return new Response();
    }

    public function getRequest(): ServerRequestInterface
    {
        return $this->request ?? new ServerRequest();
    }
}
