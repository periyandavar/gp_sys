<?php

use System\Core\Command\Migrator;
use System\Core\Test\TestCase;

class MigratorTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->context->shouldReceive('getCommand')->andReturn('testCommand');
        $this->context->shouldReceive('getArgs')->andReturn([]);
    }
    public function testOptionsContainHelp()
    {
        $migrator = new Migrator();
        $options = $migrator->options();
        $this->assertArrayHasKey('help', $options);
    }
}
