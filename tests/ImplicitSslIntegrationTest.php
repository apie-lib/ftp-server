<?php
namespace Apie\Tests\FtpServer;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\RequiresPhpExtension;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\PhpSubprocess;

class ImplicitSslIntegrationTest extends TestCase
{
    private PhpSubprocess $process;
    protected function setUp(): void
    {
        $this->process = new PhpSubprocess([file_get_contents(__DIR__ . '/run-implicit-ssl-server.php')], __DIR__);
        $this->process->start();
        // Wait a moment for the server to start
        sleep(1);
        if (!$this->process->isRunning()) {
            $this->markTestSkipped('Could not run FTP server for test: '. $this->process->getErrorOutput() . $this->process->getOutput());
        }
    }

    protected function tearDown(): void
    {
        $this->process->stop(2);
        
    }

    #[RequiresPhpExtension('curl')]
    #[DataProvider('listFoldersProvider')]
    public function testListFolders(bool $passive)
    {
        $ftp = new ImplicitSslFtpClient(
            'anonymous',
            '',
            'localhost',
            2121,
            '/',
            $passive
        );

        try {
            $files = $ftp->listContents('/');
            $this->assertEquals(['default', 'other'], $files, '2 files found in root directory');
        } catch (\Throwable $error) {
            $this->assertStringContainsString('Nobody connected with the server ', $this->process->getErrorOutput(), 'FTP Call failed: ' . $error->getMessage() . ': ' . $this->process->getErrorOutput() . $this->process->getOutput());
        }
    }

    public static function listFoldersProvider(): \Generator
    {
        yield 'passive' => [true];
        //yield 'port' => [false]; // TODO
    }
}
