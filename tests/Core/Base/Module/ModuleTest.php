<?php

use Loader\Container;
use System\Core\Base\Log\Logger;
use System\Core\Base\Module\Module;
use System\Core\Test\TestCase;

class ModuleTest extends TestCase
{
    public function testModuleInitialization()
    {
        $logMock = Mockery::mock(Logger::class);
        $logMock->shouldReceive('info')->with(Mockery::any())->andReturnNull();
        Container::set('log', $logMock);
        $module = new Module('Test');
        $this->assertEquals('Test', $module->getName());
        $this->assertEquals('/src/Module/Test/', $module->getBasePath());
    }

    // Add more tests for routes, services, autoload, etc.
}
