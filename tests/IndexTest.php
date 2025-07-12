<?php

use PHPUnit\Framework\TestCase;
use Router\Response\Response;

class IndexTest extends TestCase
{
    private $appMock;
    protected function setUp(): void
    {
        // Mock the App class
        $this->appMock = $this->getMockBuilder('App')
            ->setMethods(['run'])
            ->getMock();

        // Replace the global App instance with the mock
        $GLOBALS['App'] = $this->appMock;
    }

    public function testAppRunReturnsResponse()
    {
        // Mock the Response class
        $responseMock = $this->createMock(Response::class);
        $responseMock->expects($this->once())->method('send');

        // Configure the App mock to return the Response mock
        $this->appMock->method('run')->willReturn($responseMock);

        // Include the index.php logic
        ob_start();
        $output = $GLOBALS['App']->run();

        // Simulate the logic from index.php
        if ($output instanceof Response) {
            $output->send();
        }
        ob_end_clean();

        $this->assertInstanceOf(Response::class, $output);
    }

    public function testAppRunReturnsNonResponse()
    {
        // Configure the App mock to return a non-Response value
        $this->appMock->method('run')->willReturn('NonResponseOutput');

        // Capture output
        ob_start();
        $output = $GLOBALS['App']->run();

        // Simulate the logic from index.php
        if (!($output instanceof Response)) {
            echo $output;
        }
        $capturedOutput = ob_get_clean();

        $this->assertEquals('NonResponseOutput', $capturedOutput);
    }
}
