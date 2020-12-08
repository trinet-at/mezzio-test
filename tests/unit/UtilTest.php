<?php

declare(strict_types=1);

namespace Trinet\Test\Unit\MezzioTest;

use PHPUnit\Framework\TestCase;
use Trinet\MezzioTest\Util;
use UnexpectedValueException;

use function dirname;

class UtilTest extends TestCase
{
    public function testBasePath(): void
    {
        $result = Util::basePath();

        self::assertSame(Util::ensureTrailingSlash(dirname(__DIR__, 2)), $result);
    }

    public function testEnsureTrailingSlash(): void
    {
        $path = 'foo/bar';

        $result = Util::ensureTrailingSlash($path);

        self::assertSame($path . '/', $result);
    }

    public function testEnsureTrailingSlashThrowsExceptionIfEmptyStringIsGiven(): void
    {
        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('Given path must not be an empty string.');

        Util::ensureTrailingSlash('');
    }

    public function testEnsureTrailingSlashThrowsExceptionIfEmptyStringWithWithespaceIsGiven(): void
    {
        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('Given path must not be an empty string.');

        Util::ensureTrailingSlash(' ');
    }

    public function testEnsureTrailingSlashBailsEarlyIfGivenPathAlreadyHasATrailingSlash(): void
    {
        $path = 'foo/bar/';

        $result = Util::ensureTrailingSlash($path);

        self::assertSame($path, $result);
    }
}
