<?php

declare(strict_types=1);

namespace Trinet\Test\Functional\MezzioTest;

use PHPUnit\Framework\TestCase;
use Trinet\MezzioTest\TestConfigProvider;

use function putenv;

class TestConfigProviderTest extends TestCase
{
    public function testReturnsEmptyArrayWhenNotInTestingEnvironment(): void
    {
        putenv('APP_TESTING');

        $result = TestConfigProvider::load();

        self::assertSame([], $result);
    }

    public function testAdditionalFileProvidersAreReturnedWhenTesting(): void
    {
        putenv('APP_TESTING=true');

        $result = TestConfigProvider::load();

        self::assertCount(2, $result);
    }

    public function testCustomConfigPath(): void
    {
        putenv('APP_TESTING=true');
        $path = 'my/special/config';
        $result = TestConfigProvider::load($path);

        $providerA = $result[0];
        $providerB = $result[1];
        self::assertCount(2, $result);
        $propertyA = ReflectionUtil::getReflectionProperty($providerA, 'pattern');
        self::assertIsString($propertyA);
        self::assertStringContainsString($path, $propertyA);
        $propertyB = ReflectionUtil::getReflectionProperty($providerB, 'pattern');
        self::assertIsString($propertyB);
        self::assertStringContainsString($path, $propertyB);
    }
}
