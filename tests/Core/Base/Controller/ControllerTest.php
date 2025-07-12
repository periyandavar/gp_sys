<?php

use Loader\Container;
use Loader\Load;
use Loader\Loader;
use Router\Request\Request;
use System\Core\Base\Controller\Controller;
use System\Core\Base\Model\Model;
use System\Core\Base\Module\Module;
use System\Core\Base\Service\Service;
use System\Core\Test\TestCase as TestTestCase;

class ControllerTest extends TestTestCase
{
    protected function setUp(): void
    {
        $this->mockDb();
        // Mock dependencies for Container
        $moduleMock = Mockery::mock(Module::class)->makePartial();
        $loaderMock = Mockery::mock(Loader::class);
        $moduleMock->shouldReceive('getLoader')->andReturn($loaderMock);
        $moduleMock->load = new Load();

        Container::set('module', $moduleMock);
        Container::set(Request::class, $this->createMock(Request::class));
        Container::set('log', new class() {
            public function info($msg)
            {
            }
        });
    }

    public function testControllerInitialization()
    {
        $controller = new Controller();
        $this->assertEquals(new Model(), $controller->getModel());
        $this->assertEquals(new Service(), $controller->getService());
    }

    public function testSetModelAndService()
    {
        $controller = new Controller();
        $model = $this->createMock(Model::class);
        $service = $this->createMock(Service::class);
        $controller->setModel($model);
        $controller->setService($service);
        $this->assertSame($model, $controller->getModel());
        $this->assertSame($service, $controller->getService());
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
