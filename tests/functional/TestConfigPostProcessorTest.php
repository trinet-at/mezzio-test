<?php

declare(strict_types=1);

namespace Trinet\Test\Functional\MezzioTest;

use PHPUnit\Framework\TestCase;
use ReflectionObject;
use Trinet\MezzioTest\TestConfigPostProcessor;
use Trinet\MezzioTest\Util;

class TestConfigPostProcessorTest extends TestCase
{
    /** @var TestConfigPostProcessor */
    private $processor;

    protected function setUp(): void
    {
        parent::setUp();
        $configDir = \Safe\realpath(__DIR__ . '/../config/autoload');
        $this->processor = new TestConfigPostProcessor($configDir);
    }

    public function testReturnsConfigAsIsWhenNotInTestingEnvironment(): void
    {
        \Safe\putenv('APP_TESTING');
        $config = ['foo' => 'bar', 'bar' => 'baz'];

        $result = ($this->processor)($config);

        self::assertSame($config, $result);
    }

    public function testAdditionalConfigIsLoadedWhenTesting(): void
    {
        \Safe\putenv('APP_TESTING=true');
        $config = ['foo' => 'bar', 'bar' => 'baz'];
        $expectedConfig = $this->mergedTestConfig($config);

        $result = ($this->processor)($config);

        self::assertNotSame($config, $result);
        self::assertSame($expectedConfig, $result);
    }

    public function testTestingConfigurationOverridesBaseConfiguration(): void
    {
        \Safe\putenv('APP_TESTING=true');
        $config = ['testing' => false];

        $result = ($this->processor)($config);

        self::assertNotSame($config, $result);
        self::assertTrue($result['testing']);
    }

    public function testConfigurationInTestingSubDirectoryOverridesSettingsInDefaultTestingConfigFiles(): void
    {
        \Safe\putenv('APP_TESTING=true');
        $config = ['foo' => 'bar'];

        $result = ($this->processor)($config);

        self::assertTrue($result['testing-override']);
    }

    public function testConfigPathIsSetAutomaticallyIfNoCustomPathIsGiven(): void
    {
        $processor = new TestConfigPostProcessor();

        $configPath = $this->getReflectionProperty($processor, 'configDir');

        self::assertSame(Util::basePath() . 'config/autoload/', $configPath);
    }

    /**
     * @param mixed[] $config
     * @return mixed[]
     */
    private function mergedTestConfig(array $config): array
    {
        $basePath = dirname(__DIR__);
        return array_merge(
            $config,
            include $basePath . '/config/autoload/dummy-file.testing.php',
            include $basePath . '/config/autoload/testing/another-dummy-file.testing.php'
        );
    }

    /**
     * @return mixed
     */
    private function getReflectionProperty(object $object, string $property)
    {
        $reflectionProperty = (new ReflectionObject($object))->getProperty($property);
        $reflectionProperty->setAccessible(true);
        $value = $reflectionProperty->getValue($object);
        $reflectionProperty->setAccessible(false);
        return $value;
    }
}
