<?php

use System\Core\Command\Create\CreateModule;
use System\Core\Test\TestCase;

class CreateModuleTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->context->shouldReceive('getCommand')->andReturn('testCommand');
        $this->context->shouldReceive('getArgs')->andReturn([]);
    }
    public function testOptionsContainHelp()
    {
        $createModule = new CreateModule();
        $options = $createModule->options();
        $this->assertArrayHasKey('help', $options);
    }
}
