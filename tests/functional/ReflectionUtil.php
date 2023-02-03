<?php

declare(strict_types=1);

namespace Trinet\Test\Functional\MezzioTest;

use ReflectionObject;

final class ReflectionUtil
{
    public static function getReflectionProperty(object $object, string $property): mixed
    {
        $reflectionProperty = (new ReflectionObject($object))->getProperty($property);
        $reflectionProperty->setAccessible(true);
        /** @var mixed $value */
        $value = $reflectionProperty->getValue($object);
        $reflectionProperty->setAccessible(false);
        return $value;
    }

    public static function setReflectionProperty(object $object, string $property, mixed $value): void
    {
        $reflectionProperty = (new ReflectionObject($object))->getProperty($property);
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($object, $value);
        $reflectionProperty->setAccessible(false);
    }
}
