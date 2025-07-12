<?php

use System\Core\Command\Init;
use System\Core\Test\TestCase;

class InitTest extends TestCase
{
    public function testOptionsContainHelp()
    {
        $init = new Init();
        $options = $init->options();
        $this->assertArrayHasKey('help', $options);
    }
}