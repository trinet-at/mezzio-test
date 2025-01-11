<?php

declare(strict_types=1);

use Laminas\ServiceManager\ServiceManager;

/** @var array{
 *     dependencies: array{
 *         aliases: array<string, string>,
 *         factories: array<string, string>,
 *         services: array<string, array<int|string, mixed>|object>,
 *     }
 * } $config
 */
$config = require __DIR__ . '/config.php';

$dependencies = $config['dependencies'];
$dependencies['services']['config'] = $config;

/**
 * @psalm-suppress ArgumentTypeCoercion unnecessary complicated
 * @phpstan-ignore-next-line argument.type unnecessary complicated
 */
return new ServiceManager($dependencies);
