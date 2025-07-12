<?php

use System\Core\Base\Context\ConsoleContext;
use System\Core\Test\TestCase;

class ConsoleContextTest extends TestCase
{
    public function setUp(): void
    {
        global $argv, $argc;
        $argv = ['script.php', 'arg1', '--opt=val'];
        $argc = 3;
    }

    public function testConsoleContextGetters()
    {
        $consoleContext = ConsoleContext::getInstance([
            'command' => 'run',
            'action' => 'start',
            'args' => ['foo']
        ]);
        $this->assertEquals('run', $consoleContext->getCommand());
        $this->assertEquals('start', $consoleContext->getAction());
        $this->assertEquals(['foo'], $consoleContext->getArgs());
        $this->assertEquals(['script.php', 'arg1', '--opt=val'], $consoleContext->getArgv());
        $this->assertEquals(3, $consoleContext->getArgc());
        $this->assertEquals('script.php', $consoleContext->getScriptName());
        $this->assertEquals('val', $consoleContext->getOption('opt'));
    }

    public function testConsoleContextToString()
    {
        $consoleContext = ConsoleContext::getInstance([
            'command' => 'run',
            'action' => 'start',
            'args' => ['foo']
        ]);
        $consoleContext->setLogConfig(['command', 'action']);
        $str = (string) $consoleContext;
        $this->assertStringContainsString('run', $str);
        $this->assertStringContainsString('start', $str);
    }
}
