<?php


use System\Core\Command\Create\Create;
use System\Core\Test\TestCase;

class CreateTest extends TestCase
{
    public function testOptionsContainHelp()
    {
        $create = new Create();
        $options = $create->options();
        $this->assertArrayHasKey('help', $options);
    }
}