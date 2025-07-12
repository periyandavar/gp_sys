<?php

use System\Core\Command\Create\CreateCommand;
use System\Core\Test\TestCase;

class CreateCommandTest extends TestCase
{
    public function testOptionsContainHelp()
    {
        $createCommand = new CreateCommand();
        $options = $createCommand->options();
        $this->assertArrayHasKey('help', $options);
    }
}
