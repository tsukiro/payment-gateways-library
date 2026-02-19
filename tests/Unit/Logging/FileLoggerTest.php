<?php

namespace Tests\Unit\Logging;

use PHPUnit\Framework\TestCase;
use Psr\Log\LogLevel;
use Raion\Gateways\Logging\FileLogger;

class FileLoggerTest extends TestCase
{
    private string $logDir;
    private string $logFile;

    protected function setUp(): void
    {
        $this->logDir = sys_get_temp_dir() . '/raion_logs_test_' . uniqid();
        $this->logFile = $this->logDir . '/app.log';
    }

    protected function tearDown(): void
    {
        // Limpiar archivos de prueba
        if (file_exists($this->logFile)) {
            unlink($this->logFile);
        }
        if (is_dir($this->logDir)) {
            rmdir($this->logDir);
        }
    }

    public function testCreateDirectoryIfNotExists(): void
    {
        $this->assertDirectoryDoesNotExist($this->logDir);
        
        $logger = new FileLogger($this->logFile);
        $logger->info('Test message');
        
        $this->assertDirectoryExists($this->logDir);
        $this->assertFileExists($this->logFile);
    }

    public function testLogMessageWithLevel(): void
    {
        $logger = new FileLogger($this->logFile);
        $logger->info('Test info message');
        
        $content = file_get_contents($this->logFile);
        
        $this->assertStringContainsString('INFO:', $content);
        $this->assertStringContainsString('Test info message', $content);
    }

    public function testLogMessageWithContext(): void
    {
        $logger = new FileLogger($this->logFile);
        $logger->info('User {username} logged in', ['username' => 'john']);
        
        $content = file_get_contents($this->logFile);
        
        $this->assertStringContainsString('User john logged in', $content);
    }

    public function testLogLevelFiltering(): void
    {
        $logger = new FileLogger($this->logFile, LogLevel::WARNING);
        
        $logger->debug('Debug message');
        $logger->info('Info message');
        $logger->warning('Warning message');
        $logger->error('Error message');
        
        $content = file_get_contents($this->logFile);
        
        // DEBUG e INFO no deberían aparecer
        $this->assertStringNotContainsString('Debug message', $content);
        $this->assertStringNotContainsString('Info message', $content);
        
        // WARNING y ERROR sí deberían aparecer
        $this->assertStringContainsString('Warning message', $content);
        $this->assertStringContainsString('Error message', $content);
    }

    public function testAllLogLevels(): void
    {
        $logger = new FileLogger($this->logFile);
        
        $logger->debug('Debug');
        $logger->info('Info');
        $logger->notice('Notice');
        $logger->warning('Warning');
        $logger->error('Error');
        $logger->critical('Critical');
        $logger->alert('Alert');
        $logger->emergency('Emergency');
        
        $content = file_get_contents($this->logFile);
        
        $this->assertStringContainsString('DEBUG:', $content);
        $this->assertStringContainsString('INFO:', $content);
        $this->assertStringContainsString('NOTICE:', $content);
        $this->assertStringContainsString('WARNING:', $content);
        $this->assertStringContainsString('ERROR:', $content);
        $this->assertStringContainsString('CRITICAL:', $content);
        $this->assertStringContainsString('ALERT:', $content);
        $this->assertStringContainsString('EMERGENCY:', $content);
    }

    public function testContextJsonEncoding(): void
    {
        $logger = new FileLogger($this->logFile);
        $context = [
            'user_id' => 123,
            'action' => 'payment',
            'amount' => 1000
        ];
        
        $logger->info('Transaction processed', $context);
        
        $content = file_get_contents($this->logFile);
        
        $this->assertStringContainsString('"user_id":123', $content);
        $this->assertStringContainsString('"action":"payment"', $content);
        $this->assertStringContainsString('"amount":1000', $content);
    }

    public function testMultipleLogEntries(): void
    {
        $logger = new FileLogger($this->logFile);
        
        $logger->info('First entry');
        $logger->info('Second entry');
        $logger->info('Third entry');
        
        $content = file_get_contents($this->logFile);
        $lines = explode("\n", trim($content));
        
        $this->assertCount(3, $lines);
    }
}
