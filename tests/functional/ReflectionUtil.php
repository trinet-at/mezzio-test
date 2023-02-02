<?php

declare(strict_types=1);

namespace Trinet\Test\Functional\MezzioTest;

use ReflectionObject;

final class ReflectionUtil
{
    /**
     * @return mixed
     */
    public static function getReflectionProperty(object $object, string $property)
    {
        $reflectionProperty = (new ReflectionObject($object))->getProperty($property);
        $reflectionProperty->setAccessible(true);
        $value = $reflectionProperty->getValue($object);
        $reflectionProperty->setAccessible(false);
        return $value;
    }

    /**
     * @param mixed $value
     */
    public static function setReflectionProperty(object $object, string $property, $value): void
    {
        $reflectionProperty = (new ReflectionObject($object))->getProperty($property);
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($object, $value);
        $reflectionProperty->setAccessible(false);
    }
}
