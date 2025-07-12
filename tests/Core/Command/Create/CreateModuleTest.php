<?php

use System\Core\Command\Create\CreateModule;
use System\Core\Test\TestCase;

class CreateModuleTest extends TestCase
{
    public function testOptionsContainHelp()
    {
        $createModule = new CreateModule();
        $options = $createModule->options();
        $this->assertArrayHasKey('help', $options);
    }
}
