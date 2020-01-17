<?php

declare(strict_types=1);

namespace Trinet\MezzioTest;

use RuntimeException;
use UnexpectedValueException;

use function file_exists;
use function strlen;
use function trim;

final class Util
{
    public static function basePath(): string
    {
        $path = \Safe\realpath(dirname(__DIR__));
        if (file_exists($path . '/vendor')) {
            return self::ensureTrailingSlash($path);
        }
        // @codeCoverageIgnoreStart
        $path = \Safe\realpath(dirname(__DIR__, 4));
        if (file_exists($path . '/vendor')) {
            return self::ensureTrailingSlash($path);
        }
        throw new RuntimeException('Could not find base path.');
        // @codeCoverageIgnoreEnd
    }

    public static function ensureTrailingSlash(string $path): string
    {
        if (trim($path) === '') {
            throw new UnexpectedValueException('Given path must not be an empty string.');
        }
        if ($path[strlen($path) - 1] === '/') {
            return $path;
        }
        return $path . '/';
    }
}
