<?php

use System\Core\Command\Create\CreateMigration;
use System\Core\Test\TestCase;

class CreateMigrationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->context->shouldReceive('getCommand')->andReturn('testCommand');
        $this->context->shouldReceive('getArgs')->andReturn([]);
    }
    public function testOptionsContainHelp()
    {
        $createMigration = new CreateMigration();
        $options = $createMigration->options();
        $this->assertArrayHasKey('help', $options);
    }
}
