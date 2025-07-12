<?php

use System\Core\Command\Runner;
use System\Core\Test\TestCase;

class RunnerTest extends TestCase
{
    public function testOptionsContainCmd()
    {
        $runner = new Runner();
        $options = $runner->options();
        $this->assertArrayHasKey('cmd:', $options);
    }

    public function testRunShowsWarningIfNoCommand()
    {
        $runner = $this->getMockBuilder(Runner::class)
            ->onlyMethods(['showWarning'])
            ->getMock();

        $runner->expects($this->once())
            ->method('showWarning')
            ->with($this->stringContains('No command specified'));

        // Simulate no command option
        $runner->run();
    }
}