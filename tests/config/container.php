<?php

declare(strict_types=1);

use Laminas\ServiceManager\ServiceManager;

// Load configuration
/** @var array<string,array<string>> $config */
$config = require __DIR__ . '/config.php';

/** @var array{string?: mixed} $dependencies */
$dependencies = $config['dependencies'];
$dependencies['services']['config'] = $config;

// Build container
return new ServiceManager($dependencies);
