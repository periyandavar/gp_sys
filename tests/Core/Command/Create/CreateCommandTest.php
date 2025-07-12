<?php

use System\Core\Command\Create\CreateCommand;
use System\Core\Test\TestCase;

class CreateCommandTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->context->shouldReceive('getCommand')->andReturn('testCommand');
        $this->context->shouldReceive('getArgs')->andReturn([]);
    }
    public function testOptionsContainHelp()
    {
        $createCommand = new CreateCommand();
        $options = $createCommand->options();
        $this->assertArrayHasKey('help', $options);
    }
}
