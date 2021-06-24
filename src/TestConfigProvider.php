<?php

declare(strict_types=1);

namespace Trinet\MezzioTest;

use Laminas\ConfigAggregator\PhpFileProvider;

use function getenv;

final class TestConfigProvider
{
    /**
     * @return list<mixed>
     */
    public static function load(?string $configDir = null): array
    {
        if (!self::isTesting()) {
            return [];
        }
        $configDir = self::prepareConfigDir($configDir);
        return
            [
                new PhpFileProvider($configDir . '{{,*.}testing,{,*.}testing.local}.php'),
                new PhpFileProvider($configDir . 'testing/{{,*.}testing,{,*.}testing.local}.php'),
            ];
    }

    private static function isTesting(): bool
    {
        $testing = getenv('APP_TESTING');
        return $testing !== false;
    }

    private static function prepareConfigDir(?string $configDir): string
    {
        if ($configDir === null) {
            return Util::basePath() . 'config/autoload/';
        }
        return Util::ensureTrailingSlash($configDir);
    }
}
