<?php

declare(strict_types=1);

namespace Trinet\Test\Functional\MezzioTest;

use ReflectionException;
use ReflectionObject;

final class ReflectionUtil
{
    /**
     * @return mixed
     */
    public static function getReflectionProperty(object $object, string $property)
    {
        $reflectionProperty = (new ReflectionObject($object))->getProperty($property);
        return $reflectionProperty->getValue($object);
    }

    /**
     * @param mixed $value
     * @throws ReflectionException
     */
    public static function setReflectionProperty(object $object, string $property, $value): void
    {
        $reflectionProperty = (new ReflectionObject($object))->getProperty($property);
        $reflectionProperty->setValue($object, $value);
    }
}
