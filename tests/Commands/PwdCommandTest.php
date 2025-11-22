<?php
namespace Apie\Tests\FtpServer\Commands;

use Apie\FtpServer\Commands\PwdCommand;
use Apie\Tests\FtpServer\Concerns\CreateFtpContext;
use Apie\Tests\FtpServer\FakeConnection;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use React\Socket\ConnectionInterface;

class PwdCommandTest extends TestCase
{
    use CreateFtpContext;
    
    #[Test]
    public function it_returns_current_folder(): void
    {
        $testItem = new PwdCommand();
        $context = $this->createContext('default');
        $connection = $context->getContext(ConnectionInterface::class);
        assert($connection instanceof FakeConnection);
        $result = $testItem->run($context);
        $expectedOutput = '257 "default" is current directory' . "\r\n";
        $this->assertEquals($expectedOutput, $connection->getData());
    }
}
