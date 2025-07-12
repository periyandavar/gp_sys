<?php

use System\Core\Command\Migrator;
use System\Core\Test\TestCase;

class MigratorTest extends TestCase
{
    public function testOptionsContainHelp()
    {
        $migrator = new Migrator();
        $options = $migrator->options();
        $this->assertArrayHasKey('help', $options);
    }
}