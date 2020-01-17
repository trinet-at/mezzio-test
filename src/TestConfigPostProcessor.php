<?php

declare(strict_types=1);

namespace Trinet\MezzioTest;

use Laminas\ConfigAggregator\ConfigAggregator;
use Laminas\ConfigAggregator\PhpFileProvider;

use function getenv;

final class TestConfigPostProcessor
{
    /** @var string */
    private $configDir;

    public function __construct(?string $configDir = null)
    {
        $this->setConfigDir($configDir);
    }

    /**
     * @param mixed[] $config
     * @return mixed[]
     */
    public function __invoke(array $config): array
    {
        if (!$this->isTesting()) {
            return $config;
        }
        $aggregator = new ConfigAggregator(
            [
                static function () use ($config): array {
                    return $config;
                },
                new PhpFileProvider($this->configDir . '{{,*.}testing,{,*.}testing.local}.php'),
                new PhpFileProvider($this->configDir . 'testing/{{,*.}testing,{,*.}testing.local}.php'),
            ]
        );
        return $aggregator->getMergedConfig();
    }

    private function isTesting(): bool
    {
        $testing = getenv('APP_TESTING');
        return $testing !== false;
    }

    private function setConfigDir(?string $configDir): void
    {
        if ($configDir === null) {
            $this->configDir = Util::basePath() . 'config/autoload/';
            return;
        }
        $this->configDir = Util::ensureTrailingSlash($configDir);
    }
}
