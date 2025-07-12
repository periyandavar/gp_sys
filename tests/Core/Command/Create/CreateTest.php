<?php

use System\Core\Command\Create\Create;
use System\Core\Test\TestCase;

class CreateTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->context->shouldReceive('getCommand')->andReturn('testCommand');
        $this->context->shouldReceive('getArgs')->andReturn([]);
    }
    public function testOptionsContainHelp()
    {
        $create = new Create();
        $options = $create->options();
        $this->assertArrayHasKey('help', $options);
    }
}
