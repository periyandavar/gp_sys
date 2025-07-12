<?php

use System\Core\Test\TestCase;

class CommandsFileTest extends TestCase
{
    public function testCommandsFileReturnsArray()
    {
        $commands = include __DIR__ . '/../../../src/Core/Command/commands.php';
        $this->assertIsArray($commands);
        $this->assertArrayHasKey('run', $commands); // Runner::getName() is 'run'
    }
}
