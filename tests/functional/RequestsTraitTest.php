<?php

declare(strict_types=1);

namespace Trinet\Test\Functional\MezzioTest;

use Fig\Http\Message\RequestMethodInterface;
use Fig\Http\Message\StatusCodeInterface;
use Generator;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\UploadedFileInterface;
use Trinet\MezzioTest\MezzioTestEnvironment;
use UnexpectedValueException;

use function dirname;
use function http_build_query;
use function Safe\json_encode;

/**
 * @internal
 *
 * @small
 *
 * @coversDefaultClass \RequestsTrait
 */
final class RequestsTraitTest extends TestCase
{
    public const HTTP_DELETE = 'delete';

    public const HTTP_DELETE_JSON = 'deleteJson';

    public const HTTP_GET = 'get';

    public const HTTP_GET_JSON = 'getJson';

    public const HTTP_HEAD = 'head';

    public const HTTP_HEAD_JSON = 'headJson';

    public const HTTP_OPTIONS = 'options';

    public const HTTP_OPTIONS_JSON = 'optionsJson';

    public const HTTP_PATCH = 'patch';

    public const HTTP_PATCH_JSON = 'patchJson';

    public const HTTP_POST = 'post';

    public const HTTP_POST_JSON = 'postJson';

    public const HTTP_PUT = 'put';

    public const HTTP_PUT_JSON = 'putJson';

    public const JSON_HEADERS = [
        'Content-Type' => ['application/json'],
        'Accept' => ['application/json'],
    ];

    public const ROUTE_NAME = 'crud';

    private MezzioTestEnvironment $mezzio;

    protected function setUp(): void
    {
        parent::setUp();
        $basePath = dirname(__DIR__);
        $this->mezzio = new MezzioTestEnvironment($basePath);
    }

    public function crudDataProvider(): Generator
    {
        $emptyQueryParams = [];
        $emptyHeaders = [];
        $emptyParsedBody = [];
        $emptyUploadedFiles = [];
        $emptyCookieParams = [];
        $emptyServerParams = [];
        $headers = self::JSON_HEADERS;
        $queryParams = [
            'queryParams' => __FUNCTION__,
        ];
        $parsedBody = [
            'parsedBody' => __FUNCTION__,
        ];

        yield from [
            self::HTTP_DELETE => [
                self::HTTP_DELETE,
                RequestMethodInterface::METHOD_DELETE,
                $emptyQueryParams,
                $parsedBody,
                $emptyUploadedFiles,
                http_build_query($parsedBody),
                $emptyHeaders,
                $emptyCookieParams,
                $emptyServerParams,
            ],
            self::HTTP_DELETE_JSON => [
                self::HTTP_DELETE_JSON,
                RequestMethodInterface::METHOD_DELETE,
                $emptyQueryParams,
                $parsedBody,
                $emptyUploadedFiles,
                json_encode($parsedBody),
                $headers,
                $emptyCookieParams,
                $emptyServerParams,
            ],
            self::HTTP_GET => [
                self::HTTP_GET,
                RequestMethodInterface::METHOD_GET,
                $queryParams,
                $emptyParsedBody,
                $emptyUploadedFiles,
                http_build_query($queryParams),
                $emptyHeaders,
                $emptyCookieParams,
                $emptyServerParams,
            ],
            self::HTTP_GET_JSON => [
                self::HTTP_GET_JSON,
                RequestMethodInterface::METHOD_GET,
                $queryParams,
                $emptyParsedBody,
                $emptyUploadedFiles,
                json_encode($queryParams),
                $headers,
                $emptyCookieParams,
                $emptyServerParams,
            ],
            self::HTTP_HEAD => [
                self::HTTP_HEAD,
                RequestMethodInterface::METHOD_HEAD,
                $queryParams,
                $emptyParsedBody,
                $emptyUploadedFiles,
                http_build_query($queryParams),
                $emptyHeaders,
                $emptyCookieParams,
                $emptyServerParams,
            ],
            self::HTTP_OPTIONS => [
                self::HTTP_OPTIONS,
                RequestMethodInterface::METHOD_OPTIONS,
                $queryParams,
                $emptyParsedBody,
                $emptyUploadedFiles,
                http_build_query($queryParams),
                $emptyHeaders,
                $emptyCookieParams,
                $emptyServerParams,
            ],
            self::HTTP_OPTIONS_JSON => [
                self::HTTP_OPTIONS_JSON,
                RequestMethodInterface::METHOD_OPTIONS,
                $queryParams,
                $emptyParsedBody,
                $emptyUploadedFiles,
                json_encode($queryParams),
                $headers,
                $emptyCookieParams,
                $emptyServerParams,
            ],
            self::HTTP_PATCH => [
                self::HTTP_PATCH,
                RequestMethodInterface::METHOD_PATCH,
                $emptyQueryParams,
                $parsedBody,
                $emptyUploadedFiles,
                http_build_query($parsedBody),
                $emptyHeaders,
                $emptyCookieParams,
                $emptyServerParams,
            ],
            self::HTTP_PATCH_JSON => [
                self::HTTP_PATCH_JSON,
                RequestMethodInterface::METHOD_PATCH,
                $emptyQueryParams,
                $parsedBody,
                $emptyUploadedFiles,
                json_encode($parsedBody),
                $headers,
                $emptyCookieParams,
                $emptyServerParams,
            ],
            self::HTTP_POST => [
                self::HTTP_POST,
                RequestMethodInterface::METHOD_POST,
                $emptyQueryParams,
                $parsedBody,
                $emptyUploadedFiles,
                http_build_query($parsedBody),
                $emptyHeaders,
                $emptyCookieParams,
                $emptyServerParams,
            ],
            self::HTTP_POST_JSON => [
                self::HTTP_POST_JSON,
                RequestMethodInterface::METHOD_POST,
                $emptyQueryParams,
                $parsedBody,
                $emptyUploadedFiles,
                json_encode($parsedBody),
                $headers,
                $emptyCookieParams,
                $emptyServerParams,
            ],
            self::HTTP_PUT => [
                self::HTTP_PUT,
                RequestMethodInterface::METHOD_PUT,
                $emptyQueryParams,
                $parsedBody,
                $emptyUploadedFiles,
                http_build_query($parsedBody),
                $emptyHeaders,
                $emptyCookieParams,
                $emptyServerParams,
            ],
            self::HTTP_PUT_JSON => [
                self::HTTP_PUT_JSON,
                RequestMethodInterface::METHOD_PUT,
                $emptyQueryParams,
                $parsedBody,
                $emptyUploadedFiles,
                json_encode($parsedBody),
                $headers,
                $emptyCookieParams,
                $emptyServerParams,
            ],
            //            self::HTTP_POST => [self::HTTP_PUT, RequestMethodInterface::METHOD_POST, $queryParams, $parsedBody, $emptyUploadedFiles, http_build_query($parsedBody), $emptyHeaders, $emptyCookieParams, $emptyServerParams, ],
            //            self::HTTP_POST_JSON => [self::HTTP_PUT_JSON, RequestMethodInterface::METHOD_POST],
            //            self::HTTP_PUT => [self::HTTP_PUT, RequestMethodInterface::METHOD_PUT, $queryParams, $parsedBody, $emptyUploadedFiles, http_build_query($parsedBody), $emptyHeaders, $emptyCookieParams, $emptyServerParams, ],
            //            self::HTTP_PUT_JSON => [self::HTTP_PUT_JSON, RequestMethodInterface::METHOD_PUT],
        ];
    }

    /**
     * @param array<string, string>                $parsedBody
     * @param array<string, string>                $queryParams
     * @param array<UploadedFileInterface>         $uploadedFiles
     * @param array<string, array<string, string>> $headers
     * @param array<string, string>                $cookieParams
     * @param array<string, string>                $serverParams
     *
     * @dataProvider crudDataProvider
     */
    public function testCrud(
        string $action,
        string $method,
        array $queryParams = [],
        array $parsedBody = [],
        array $uploadedFiles = [],
        string $body = 'php://input',
        array $headers = [],
        array $cookieParams = [],
        array $serverParams = [],
    ): void {
        $uri = $this->mezzio->generateUri(self::ROUTE_NAME);

        $response = match ($action) {
            self::HTTP_DELETE => $this->mezzio->delete($uri, $parsedBody, $headers, $cookieParams, $serverParams),
            self::HTTP_DELETE_JSON => $this->mezzio->deleteJson(
                $uri,
                $parsedBody,
                $headers,
                $cookieParams,
                $serverParams
            ),
            self::HTTP_GET => $this->mezzio->get($uri, $queryParams, $headers, $cookieParams, $serverParams),
            self::HTTP_GET_JSON => $this->mezzio->getJson($uri, $queryParams, $headers, $cookieParams, $serverParams),
            self::HTTP_HEAD => $this->mezzio->head($uri, $queryParams, $headers, $cookieParams, $serverParams),
            self::HTTP_OPTIONS => $this->mezzio->options($uri, $queryParams, $headers, $cookieParams, $serverParams),
            self::HTTP_OPTIONS_JSON => $this->mezzio->optionsJson(
                $uri,
                $queryParams,
                $headers,
                $cookieParams,
                $serverParams
            ),
            self::HTTP_PATCH => $this->mezzio->patch(
                $uri,
                $parsedBody,
                $uploadedFiles,
                $headers,
                $cookieParams,
                $serverParams
            ),
            self::HTTP_PATCH_JSON => $this->mezzio->patchJson(
                $uri,
                $parsedBody,
                $uploadedFiles,
                $headers,
                $cookieParams,
                $serverParams
            ),
            self::HTTP_PUT => $this->mezzio->put(
                $uri,
                $parsedBody,
                $uploadedFiles,
                $headers,
                $cookieParams,
                $serverParams
            ),
            self::HTTP_PUT_JSON => $this->mezzio->putJson(
                $uri,
                $parsedBody,
                $uploadedFiles,
                $headers,
                $cookieParams,
                $serverParams
            ),
            self::HTTP_POST => $this->mezzio->post(
                $uri,
                $parsedBody,
                $uploadedFiles,
                $headers,
                $cookieParams,
                $serverParams
            ),
            self::HTTP_POST_JSON => $this->mezzio->postJson(
                $uri,
                $parsedBody,
                $uploadedFiles,
                $headers,
                $cookieParams,
                $serverParams
            ),
            default => throw new UnexpectedValueException("Unsupported action: {$action}")
        };

        $this->mezzio->assertSameRequestMethod($method);
        $this->mezzio->assertSameRequestHeaders($headers);
        $this->mezzio->assertSameRequestQueryParams($queryParams);
        $this->mezzio->assertSameRequestParsedBody($parsedBody);
        $this->mezzio->assertSameMatchedRouteName(self::ROUTE_NAME);

        $this->mezzio->assertSameResponseBody($body);
        $this->mezzio->assertSameResponseStatusCode(StatusCodeInterface::STATUS_OK);
        self::assertSame(StatusCodeInterface::STATUS_OK, $response->getStatusCode());
    }

    public function testDelete(): void
    {
        $uri = $this->mezzio->generateUri(self::ROUTE_NAME);

        $payload = [
            'function' => __FUNCTION__,
        ];

        $this->mezzio->delete($uri, $payload);

        $this->mezzio->assertSameRequestMethod(RequestMethodInterface::METHOD_DELETE);

        $this->mezzio->assertSameResponseBody(http_build_query($payload));
        $this->mezzio->assertSameRequestHeaders([]);
        $this->mezzio->assertSameRequestQueryParams([]);
        $this->mezzio->assertSameRequestParsedBody($payload);
        $this->mezzio->assertSameMatchedRouteName(self::ROUTE_NAME);
    }

    public function testDeleteJson(): void
    {
        $uri = $this->mezzio->generateUri(self::ROUTE_NAME);

        $payload = [
            'function' => __FUNCTION__,
        ];

        $this->mezzio->deleteJson($uri, $payload);

        $this->mezzio->assertSameRequestMethod(RequestMethodInterface::METHOD_DELETE);

        $this->mezzio->assertSameRequestQueryParams([]);
        $this->mezzio->assertSameRequestParsedBody($payload);
        $this->mezzio->assertSameMatchedRouteName(self::ROUTE_NAME);
        $this->mezzio->assertSameRequestHeaders(self::JSON_HEADERS);

        $this->mezzio->assertSameResponseBody(json_encode($payload));
    }
}
