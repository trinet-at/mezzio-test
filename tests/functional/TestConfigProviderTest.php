<?php

declare(strict_types=1);

namespace Trinet\Test\Functional\MezzioTest;

use PHPUnit\Framework\TestCase;
use Trinet\MezzioTest\TestConfigProvider;

class TestConfigProviderTest extends TestCase
{
    public function testReturnsEmptyArrayWhenNotInTestingEnvironment(): void
    {
        \Safe\putenv('APP_TESTING');

        $result = TestConfigProvider::load();

        self::assertSame([], $result);
    }

    public function testAdditionalFileProvidersAreReturnedWhenTesting(): void
    {
        \Safe\putenv('APP_TESTING=true');

        $result = TestConfigProvider::load();

        self::assertCount(2, $result);
    }

    public function testCustomConfigPath(): void
    {
        \Safe\putenv('APP_TESTING=true');
        $path = 'my/special/config';
        $result = TestConfigProvider::load($path);

        $providerA = $result[0];
        $providerB = $result[1];
        self::assertCount(2, $result);
        self::assertStringContainsString($path, ReflectionUtil::getReflectionProperty($providerA, 'pattern'));
        self::assertStringContainsString($path, ReflectionUtil::getReflectionProperty($providerB, 'pattern'));
    }
}
