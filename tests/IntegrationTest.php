<?php
namespace Apie\Tests\FtpServer;

use PHPUnit\Framework\Attributes\DataProvider;
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
    #[DataProvider('listFoldersProvider')]
    public function testListFolders(bool $passive)
    {
        $coverageFile = $this->getCoverageFilePath('listFolders_' . ($passive ? 'passive' : 'port') . '.cov');
        $ftp = ftp_connect('127.0.0.1', 2121, 10);
        $this->assertNotFalse($ftp, 'Could not connect to FTP server: ' . $this->process->getErrorOutput() . $this->process->getOutput());
        try {
            // Try anonymous login (since no credentials are set up)
            $login = @ftp_login($ftp, 'anonymous', '');
            $this->assertTrue($login, 'Could not login to FTP server: ' . $this->process->getErrorOutput() . $this->process->getOutput());
            
            ftp_pasv($ftp, $passive);
            // List files in the root directory
            $files = ftp_nlist($ftp, '/');
            if ($passive && $files === false) {
                $this->assertStringContainsString("Can't open data connection", $this->process->getErrorOutput());
            } else {
                $this->assertIsArray($files, 'ftp_nlist did not return an array: ' . $this->process->getErrorOutput() . $this->process->getOutput());
                // Optionally, assert something about the files, e.g. not empty
                $this->assertEquals(['default', 'other'], $files, '2 files found in root directory');
            }
        } finally {
            ftp_site($ftp, 'TEST ' . $coverageFile);
            ftp_close($ftp);
        }
        
    }

    #[RequiresPhpExtension('ftp')]
    public function testSiteHelpCommandWorksAsIntended()
    {
        $coverageFile = $this->getCoverageFilePath('siteCommand.cov');
        $ftp = ftp_connect('127.0.0.1', 2121, 10);
        $this->assertNotFalse($ftp, 'Could not connect to FTP server: ' . $this->process->getErrorOutput() . $this->process->getOutput());
        try {
            // Try anonymous login (since no credentials are set up)
            $login = @ftp_login($ftp, 'anonymous', '');
            $this->assertTrue($login, 'Could not login to FTP server: ' . $this->process->getErrorOutput() . $this->process->getOutput());
            
            $result = ftp_site($ftp, 'HELP');
            $this->assertTrue($result);
            $result = ftp_raw($ftp, "SITE HELP");
            $expected = [
                "214-TEST Stores the test coverage data sent by the client (tests only).",
                "214-IDLE Waits for a short moment before responding.",
                "214 End of SITE HELP list"
            ];
            $this->assertEquals($expected, $result, 'SITE HELP did not return expected output: ' . implode("\n", $result));
        } finally {
            ftp_site($ftp, 'TEST ' . $coverageFile);
            ftp_close($ftp);
        }
        
    }

    private function getCoverageFilePath(string $fileName): string
    {
        $path = realpath(__DIR__ . '/../../../coverage/');
        if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
            $path = realpath(__DIR__ . '/../coverage/') ;
        }
        if ($path === false) {
            throw new \RuntimeException('Could not determine coverage path');
        }
        return $path . '/' . $fileName;
    }

    public static function listFoldersProvider(): \Generator
    {
        yield 'passive' => [true];
        yield 'port' => [false];
    }
}
