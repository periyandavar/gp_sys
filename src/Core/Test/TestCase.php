<?php

namespace System\Core\Test;

use Database\Database;
use Database\DatabaseFactory;
use Database\Driver\PdoDriver;
use Loader\Config\ConfigLoader;
use Loader\Container;
use Loader\Load;
use Loader\Loader;
use Logger\Log;
use Mockery;
use Router\Request\Request as RequestRequest;
use System\Core\Base\Module\Module;
use System\Core\Http\Request\Request;
use System\Core\Http\Response\Response;

class TestCase extends \PHPUnit\Framework\TestCase
{


    protected $db;

    protected $module;

    protected $load;
    protected $loader;
    public function getDb() {
        return $this->db;
    }

    public function __construct(string|null $name = null, array $data = [], $dataName = '')
    {
        // $log = Mockery::mock('overload:'.Log::class);
        // $log->shouldReceive('getInstance')->andReturn($log);
        // $log->shouldReceive('info')->andReturn($log);
        // $log = Mockery::mock('overload:' . Log::class);
        // $log->shouldReceive('getInstance')->andReturnSelf();
        // $log->shouldReceive('info')->andReturn(true);
        // $this->db = Mockery::mock(PdoDriver::class);
        // $this->module = new Module('');
        // $this->load = Mockery::mock(Load::class);
        // $this->loader = Mockery::mock(Loader::class);
        // $this->setProperty($this->module, 'load', $this->load);
        // $this->setProperty($this->module, 'loader', $this->loader);
        // Container::set('module', $this->module);
        // Container::set('db', $this->db);
        // Container::set(RequestRequest::class, new Request());
        // Container::set(Response::class, new Response());
        // ConfigLoader::getInstance(ConfigLoader::VALUE_LOADER, [], 'config');
        // ConfigLoader::getInstance(ConfigLoader::VALUE_LOADER, [], 'db');
        parent::__construct($name, $data, $dataName);
        // $dbf = Mockery::mock('overload:'.DatabaseFactory::class);
        // $dbf->shouldReceive('setUpConfig')->andReturn();
        // $dbf->shouldReceive('get')->andReturn($this->db);
    }


    protected function setUp(): void
    {
        $log = Mockery::mock('overload:' . Log::class);
        $log->shouldReceive('getInstance')->andReturnSelf();
        $log->shouldReceive('info')->andReturn(true);
        $this->db = Mockery::mock(PdoDriver::class);
        $this->module = new Module('');
        $this->load = Mockery::mock(Load::class);
        $this->loader = Mockery::mock(Loader::class);
        $this->setProperty($this->module, 'load', $this->load);
        $this->setProperty($this->module, 'loader', $this->loader);
        Container::set('module', $this->module);
        Container::set('db', $this->db);
        Container::set(RequestRequest::class, new Request());
        Container::set(Response::class, new Response());
        ConfigLoader::getInstance(ConfigLoader::VALUE_LOADER, [], 'config');
        ConfigLoader::getInstance(ConfigLoader::VALUE_LOADER, [], 'db');
        // parent::__construct($name, $data, $dataName);
        $dbf = Mockery::mock('overload:'.DatabaseFactory::class);
        $dbf->shouldReceive('setUpConfig')->andReturn();
        $dbf->shouldReceive('get')->andReturn($this->db);
    }
    public function getModule() {
        return $this->module;
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

    /**
     * Create a mock for the given class using Mockery.
     *
     * @param string $class The class name to mock.
     * @param array $constructorArgs Optional constructor arguments.
     * @return \Mockery\MockInterface
     */
    protected function getMock(string $class, array $constructorArgs = [])
    {
        if (!class_exists(\Mockery::class)) {
            throw new \RuntimeException('Mockery is not installed. Run "composer require --dev mockery/mockery".');
        }

        if (empty($constructorArgs)) {
            return \Mockery::mock($class);
        }
        return \Mockery::mock($class, $constructorArgs);
    }

    /**
     * Clean up Mockery after each test.
     */
    protected function tearDown(): void
    {
        if (class_exists(\Mockery::class)) {
            \Mockery::close();
        }
        parent::tearDown();
    }

     /**
     * Create a mock for the Database class.
     *
     * @param array $methods Methods to mock.
     * @return \Mockery\MockInterface
     */
    protected function mockDatabase(array $methods = [])
    {
        $dbClass = '\\System\\Core\\DB\\Database';
        if (!class_exists($dbClass)) {
            throw new \RuntimeException('Database class not found: ' . $dbClass);
        }
        return empty($methods)
            ? $this->getMock($dbClass)
            : $this->getMock($dbClass)->shouldAllowMockingProtectedMethods()->makePartial()->shouldReceive(...$methods)->getMock();
    }

    protected function containerMock($module, $db)
    {
        // $container = Mockery::mock('alias:overload:'. Container::class);
        // $container->shouldReceive('get')->with('module')->andReturn($module);
        // $container->shouldReceive('get')->with('db')->andReturn($db);
    }
}
