<?php
namespace Apie\Tests\FtpServer\Commands;

use Apie\FtpServer\Commands\SystCommand;
use Apie\Tests\FtpServer\Concerns\CreateFtpContext;
use Apie\Tests\FtpServer\FakeConnection;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use React\Socket\ConnectionInterface;

class SystCommandTest extends TestCase
{
    use CreateFtpContext;
    
    #[Test]
    public function it_fakes_unix_ftp_server(): void
    {
        $testItem = new SystCommand();
        $context = $this->createContext('/');
        $connection = $context->getContext(ConnectionInterface::class);
        assert($connection instanceof FakeConnection);
        $result = $testItem->run($context);
        $this->assertSame($result, $context);
        $expectedOutput = "215 UNIX Type: L8\r\n";
        $this->assertEquals($expectedOutput, $connection->getData());
    }
}
