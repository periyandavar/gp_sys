<?php

class TestCase extends \PHPUnit\Framework\TestCase
{
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
}
