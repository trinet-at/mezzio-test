<?php

declare(strict_types=1);

use Laminas\ConfigAggregator\ConfigAggregator;
use Mezzio\ConfigProvider as MezzioConfig;
use Mezzio\Router\ConfigProvider as RouterConfig;
use Mezzio\Router\FastRouteRouter\ConfigProvider as FastRouteConfig;
use Trinet\MezzioTest\TestConfigPostProcessor;

$aggregator = new ConfigAggregator(
    [
        FastRouteConfig::class,
        MezzioConfig::class,
        RouterConfig::class,
    ],
    null,
    [new TestConfigPostProcessor(__DIR__ . '/autoload')]
);

return $aggregator->getMergedConfig();
