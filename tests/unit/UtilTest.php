<?php

declare(strict_types=1);

namespace Trinet\Test\Unit\MezzioTest;

use PHPUnit\Framework\TestCase;
use Trinet\MezzioTest\Util;
use UnexpectedValueException;

use function dirname;

/**
 * @internal
 *
 * @small
 *
 * @coversDefaultClass \Trinet\MezzioTest\Util
 */
final class UtilTest extends TestCase
{
    /**
     * @covers \Trinet\MezzioTest\Util::basePath
     * @covers \Trinet\MezzioTest\Util::ensureTrailingSlash
     */
    public function testBasePath(): void
    {
        $result = Util::basePath();

        self::assertSame(Util::ensureTrailingSlash(dirname(__DIR__, 2)), $result);
    }

    /**
     * @covers \Trinet\MezzioTest\Util::ensureTrailingSlash
     */
    public function testEnsureTrailingSlash(): void
    {
        $path = 'foo/bar';

        $result = Util::ensureTrailingSlash($path);

        self::assertSame($path . '/', $result);
    }

    /**
     * @covers \Trinet\MezzioTest\Util::ensureTrailingSlash
     */
    public function testEnsureTrailingSlashBailsEarlyIfGivenPathAlreadyHasATrailingSlash(): void
    {
        $path = 'foo/bar/';

        $result = Util::ensureTrailingSlash($path);

        self::assertSame($path, $result);
    }

    /**
     * @covers \Trinet\MezzioTest\Util::ensureTrailingSlash
     */
    public function testEnsureTrailingSlashThrowsExceptionIfEmptyStringIsGiven(): void
    {
        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('Given path must not be an empty string.');

        Util::ensureTrailingSlash('');
    }

    /**
     * @covers \Trinet\MezzioTest\Util::ensureTrailingSlash
     */
    public function testEnsureTrailingSlashThrowsExceptionIfEmptyStringWithWithespaceIsGiven(): void
    {
        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('Given path must not be an empty string.');

        Util::ensureTrailingSlash(' ');
    }
}
