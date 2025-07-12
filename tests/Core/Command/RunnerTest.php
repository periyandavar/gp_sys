<?php

use System\Core\Command\Runner;
use System\Core\Test\TestCase;

class RunnerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->context->shouldReceive('getCommand')->andReturn('testCommand');
        $this->context->shouldReceive('getArgs')->andReturn([]);
    }
    public function testOptionsContainCmd()
    {
        $runner = new Runner();
        $options = $runner->options();
        $this->assertArrayHasKey('cmd:', $options);
    }

    public function testRunShowsWarningIfNoCommand()
    {
        $runner = Mockery::mock(Runner::class)
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $runner->shouldReceive('get')->with('context')->andReturn($this->getConsoleContext());
        $runner->shouldReceive('showWarning')
            ->with($this->stringContains('No command specified'));

        // Simulate no command option
        $this->assertNull($runner->run());
    }
}
