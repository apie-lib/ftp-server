<?php
namespace Apie\Tests\FtpServer;

use PHPUnit\Framework\Attributes\RequiresPhpExtension;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\PhpProcess;

class IntegrationTest extends TestCase
{
    private PhpProcess $process;
    protected function setUp(): void
    {
        $this->process = new PhpProcess(file_get_contents(__DIR__ . '/run-server.php'), __DIR__);
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

    #[RequiresPhpExtension('ftp')]
    public function testListFolders()
    {

        $ftp = ftp_connect('127.0.0.1', 2121, 10);
        $this->assertNotFalse($ftp, 'Could not connect to FTP server: ' . $this->process->getErrorOutput() . $this->process->getOutput());

        // Try anonymous login (since no credentials are set up)
        $login = @ftp_login($ftp, 'anonymous', '');
        $this->assertTrue($login, 'Could not login to FTP server: ' . $this->process->getErrorOutput() . $this->process->getOutput());

        // List files in the root directory
        $files = ftp_nlist($ftp, '/');
        $this->assertIsArray($files, 'ftp_nlist did not return an array: ' . $this->process->getErrorOutput() . $this->process->getOutput());

        // Optionally, assert something about the files, e.g. not empty
        // $this->assertNotEmpty($files, 'No files found in root directory');

        ftp_close($ftp);
    }
}
