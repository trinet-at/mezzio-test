<?php

declare(strict_types=1);

namespace Trinet\MezzioTest;

use Fig\Http\Message\RequestMethodInterface;
use Laminas\Diactoros\ServerRequest;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UploadedFileInterface;
use Psr\Http\Message\UriInterface;

trait RequestsTrait
{
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
                method: RequestMethodInterface::METHOD_DELETE,
                uri: $uri,
                parsedBody: $parsedBody,
                headers: $headers,
                cookieParams: $cookieParams,
                serverParams: $serverParams
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
                method: RequestMethodInterface::METHOD_DELETE,
                uri: $uri,
                parsedBody: $parsedBody,
                headers: $headers,
                cookieParams: $cookieParams,
                serverParams: $serverParams
            )
        );
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
                method: RequestMethodInterface::METHOD_GET,
                uri: $uri,
                queryParams: $queryParams,
                headers: $headers,
                cookieParams: $cookieParams,
                serverParams: $serverParams
            )
        );
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
                method: RequestMethodInterface::METHOD_GET,
                uri: $uri,
                queryParams: $queryParams,
                headers: $headers,
                cookieParams: $cookieParams,
                serverParams: $serverParams
            )
        );
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
                method: RequestMethodInterface::METHOD_HEAD,
                uri: $uri,
                queryParams: $queryParams,
                headers: $headers,
                cookieParams: $cookieParams,
                serverParams: $serverParams
            )
        );
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
                method: RequestMethodInterface::METHOD_OPTIONS,
                uri: $uri,
                queryParams: $queryParams,
                headers: $headers,
                cookieParams: $cookieParams,
                serverParams: $serverParams
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
                method: RequestMethodInterface::METHOD_OPTIONS,
                uri: $uri,
                queryParams: $queryParams,
                headers: $headers,
                cookieParams: $cookieParams,
                serverParams: $serverParams
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
        string $body = 'php://input',
        array $headers = [],
        array $cookieParams = [],
        array $serverParams = [],
    ): ResponseInterface {
        return $this->dispatchRequest(
            $this->request(
                method: RequestMethodInterface::METHOD_PATCH,
                uri: $uri,
                parsedBody: $parsedBody,
                uploadedFiles: $uploadedFiles,
                body: $body,
                headers: $headers,
                cookieParams: $cookieParams,
                serverParams: $serverParams
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
        string $body = 'php://input',
        array $headers = [],
        array $cookieParams = [],
        array $serverParams = [],
    ): ResponseInterface {
        return $this->dispatchRequest(
            $this->requestJson(
                method: RequestMethodInterface::METHOD_PATCH,
                uri: $uri,
                parsedBody: $parsedBody,
                uploadedFiles: $uploadedFiles,
                body: $body,
                headers: $headers,
                cookieParams: $cookieParams,
                serverParams: $serverParams
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
        string $body = 'php://input',
        array $headers = [],
        array $cookieParams = [],
        array $serverParams = [],
    ): ResponseInterface {
        return $this->dispatchRequest(
            $this->request(
                method: RequestMethodInterface::METHOD_POST,
                uri: $uri,
                parsedBody: $parsedBody,
                uploadedFiles: $uploadedFiles,
                body: $body,
                headers: $headers,
                cookieParams: $cookieParams,
                serverParams: $serverParams
            )
        );
    }

                uri: $uri,
                parsedBody: $parsedBody,
                uploadedFiles: $uploadedFiles,
                body: $body,
                headers: $headers,
                cookieParams: $cookieParams,
                serverParams: $serverParams
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
        string $body = 'php://input',
        array $headers = [],
        array $cookieParams = [],
        array $serverParams = [],
    ): ResponseInterface {
        return $this->dispatchRequest(
            $this->request(
                method: RequestMethodInterface::METHOD_POST,
                uri: $uri,
                parsedBody: $parsedBody,
                uploadedFiles: $uploadedFiles,
                body: $body,
                headers: $headers,
                cookieParams: $cookieParams,
                serverParams: $serverParams
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
        string $body = 'php://input',
        array $headers = [],
        array $cookieParams = [],
        array $serverParams = [],
        string $protocol = '1.1'
    ): ServerRequestInterface {
        return $this->request = new ServerRequest(
            $serverParams,
            $uploadedFiles,
            $uri,
            $method,
            $body,
            $headers,
            $cookieParams,
            $queryParams,
            $parsedBody,
            $protocol
        );
    }
}
