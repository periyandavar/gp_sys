<?php

use Loader\Config\ConfigLoader;
use Loader\Container;
use Loader\Load;
use Loader\Loader;
use Router\Request\Request;
use System\Core\Base\Controller\Controller;
use System\Core\Base\Model\Model;
use System\Core\Base\Service\Service;
use System\Core\Test\TestCase as TestTestCase;

class ControllerTest extends TestTestCase
{
    protected function setUp(): void
    {
        $this->mockDb();
        // Mock dependencies for Container
        $moduleMock = $this->createMock(Module::class);
        $loaderMock = Mockery::mock(Loader::class);
        $moduleMock->method('getLoader')->willReturn($loaderMock);
        $moduleMock->load = new Load();

        Container::set('module', $moduleMock);
        Container::set(Request::class, $this->createMock(Request::class));
        Container::set('log', new class() {
            public function info($msg)
            {
            }
        });

        // Mock config loader
        // ConfigLoader::setConfig('config', ['foo' => 'bar']);
    }

    public function testControllerInitialization()
    {
        $controller = $this->getMockForAbstractClass(Controller::class);
        $this->assertInstanceOf(Controller::class, $controller);
        $model = $this->getProperty($controller, 'model');
        $this->assertInstanceOf(Model::class, $model);
        $this->assertInstanceOf(Service::class, $this->getProperty($controller, 'service'));
        $controller = new Controller();
        $this->assertInstanceOf(Model::class, $controller->model);
        $this->assertInstanceOf(Service::class, $controller->service);
        $this->assertNotNull($controller->input);
        $this->assertNotNull($controller->config);
        $this->assertNotNull($controller->loader);
        $this->assertNotNull($controller->load);
        $this->assertNotNull($controller->module);
    }

    public function testSetModelAndService()
    {
        $controller = new Controller();
        $model = $this->createMock(Model::class);
        $service = $this->createMock(Service::class);
        $controller->setModel($model);
        $controller->setService($service);
        $this->assertSame($model, $controller->model);
        $this->assertSame($service, $controller->service);
    }

    public function testMagicSetGetIsset()
    {
        $controller = new Controller();
        $controller->foo = 'bar';
        $this->assertEquals('bar', $controller->foo);
        $this->assertTrue(isset($controller->foo));
        $this->assertFalse(isset($controller->notset));
    }
}

