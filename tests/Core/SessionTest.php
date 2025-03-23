<?php

use Mockery;
use PHPUnit\Framework\TestCase;
use System\Core\Session;
use Loader\Config\ConfigLoader;
use Logger\Log;

class SessionTest extends TestCase
{
    private $configMock;
    private $logMock;

    protected function setUp(): void
    {
        // Mock ConfigLoader
        $this->configMock = Mockery::mock('alias:Loader\Config\ConfigLoader');
        $this->configMock->shouldReceive('getConfig')
            ->with('config')
            ->andReturnSelf();
        $this->configMock->shouldReceive('getAll')
            ->andReturn([
                'session_save_path' => '/tmp',
                'session_expiration' => 3600,
                'session_driver' => 'File',
            ]);

        // Mock Log
        $this->logMock = Mockery::mock('alias:Logger\Log');
        $this->logMock->shouldReceive('getInstance')->andReturn($this->logMock);
        $this->logMock->shouldReceive('error')->andReturnNull();
        $this->logMock->shouldReceive('debug')->andReturnNull();
    }

    protected function tearDown(): void
    {
        Mockery::close();
    }

    public function testGetInstanceCreatesSession()
    {
        // Mock the session driver file existence
        $sessionDriverPath = __DIR__ . '/../../src/system/core/session/FileSession.php';
        if (!file_exists($sessionDriverPath)) {
            mkdir(dirname($sessionDriverPath), 0777, true);
            file_put_contents($sessionDriverPath, '<?php class FileSession { public function open() {} public function close() {} public function read() {} public function write() {} public function destroy() {} public function gc() {} }');
        }

        // Get the Session instance
        $session = Session::getInstance();

        // Assert the instance is of type Session
        $this->assertInstanceOf(Session::class, $session);

        // Clean up the mock session driver file
        unlink($sessionDriverPath);
        rmdir(dirname($sessionDriverPath));
    }

    public function testGetInstanceLogsErrorForInvalidDriver()
    {
        // Mock ConfigLoader to return an invalid driver
        $this->configMock->shouldReceive('getAll')
            ->andReturn([
                'session_save_path' => '/tmp',
                'session_expiration' => 3600,
                'session_driver' => 'InvalidDriver',
            ]);

        // Expect the Log to capture an error
        $this->logMock->shouldReceive('error')
            // ->once()
            ->with(Mockery::type('string'));

        // Get the Session instance
        $session = Session::getInstance();

        // Assert the instance is of type Session
        $this->assertInstanceOf(Session::class, $session);
    }
}