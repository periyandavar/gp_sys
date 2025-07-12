<?php

use System\Core\Command\Create\CreateMigration;
use System\Core\Test\TestCase;

class CreateMigrationTest extends TestCase
{
    public function testOptionsContainHelp()
    {
        $createMigration = new CreateMigration();
        $options = $createMigration->options();
        $this->assertArrayHasKey('help', $options);
    }
}