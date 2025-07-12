<?php

use System\Core\Command\Init;
use System\Core\Test\TestCase;

class InitTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->context->shouldReceive('getCommand')->andReturn('testCommand');
        $this->context->shouldReceive('getArgs')->andReturn([]);
    }
    public function testOptionsContainHelp()
    {
        $init = new Init();
        $options = $init->options();
        $this->assertArrayHasKey('help', $options);
    }
}
