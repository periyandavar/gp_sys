<?php

namespace System\Core\Test;

use Database\Database;
use Database\DatabaseFactory;
use Loader\Container;
use Mockery;
use System\Core\Base\Module\Module;

class TestCase extends \PHPUnit\Framework\TestCase
{
    public function mockDb($key = 'default', $db = null)
    {
        if ($db === null) {
            $db = Mockery::mock(Database::class)->makePartial();
        }

        DatabaseFactory::set($key, $db);

        return $db;
    }

    /**
     * Invoke a private or protected method on an object.
     *
     * @param  object $object     The object to invoke the method on.
     * @param  string $methodName The name of the method to invoke.
     * @param  array  $parameters Parameters to pass to the method.
     * @return mixed  The result of the method call.
     */
    protected function invokeMethod(object $object, string $methodName, array $parameters = [])
    {
        $reflection = new \ReflectionClass($object);
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $parameters);
    }

    /**
     * Set a private or protected property on an object.
     *
     * @param object $object       The object to modify.
     * @param string $propertyName The property name.
     * @param mixed  $value        The value to set.
     */
    protected function setProperty(object $object, string $propertyName, $value): void
    {
        $reflection = new \ReflectionClass($object);
        $property = $reflection->getProperty($propertyName);
        $property->setAccessible(true);
        $property->setValue($object, $value);
    }

    /**
     * Get a private or protected property from an object.
     *
     * @param  object $object       The object to read from.
     * @param  string $propertyName The property name.
     * @return mixed  The property value.
     */
    protected function getProperty(object $object, string $propertyName)
    {
        $reflection = new \ReflectionClass($object);
        $property = $reflection->getProperty($propertyName);
        $property->setAccessible(true);

        return $property->getValue($object);
    }

    protected function setContainer(array $services)
    {
        foreach ($services as $name => $value) {
            Container::set($name, $value);
        }
    }

    protected function addMockService(array $services)
    {
        foreach ($services as $name => $value) {
            if (is_callable($value)) {
                $value = $value();
            }
            if (is_string($value)) {
                $value = Mockery::mock($value);
            }
            Container::set($name, $value);
        }
    }

    protected function moduleMock()
    {
        return Mockery::mock(Module::class)->makePartial();
    }
}
