<?php

use System\Core\Base\Controller\Controller;
use System\Core\Base\Model\Model;
use System\Core\Base\Service\Service;
use System\Core\Test\TestCase;

class ControllerTest extends TestCase
{
    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testControllerInitialization()
    {
        $controller = $this->getMockForAbstractClass(Controller::class);
        $this->assertInstanceOf(Controller::class, $controller);
        $model = $this->getProperty($controller, 'model');
        $this->assertInstanceOf(Model::class, $model);
        $this->assertInstanceOf(Service::class, $this->getProperty($controller, 'service'));
    }

    public function testSetAndGetModel()
    {
        $controller = $this->getMockForAbstractClass(Controller::class);
        $mockModel = $this->createMock(Model::class);
        $controller->setModel($mockModel);
        $this->assertSame($mockModel, $this->getProperty($controller, 'model'));
    }

    public function testSetAndGetService()
    {
        $controller = $this->getMockForAbstractClass(Controller::class);
        $mockService = $this->createMock(Service::class);
        $controller->setService($mockService);
        $this->assertSame($mockService, $this->getProperty($controller, 'service'));
    }
}
