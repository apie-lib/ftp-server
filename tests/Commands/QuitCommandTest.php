<?php
namespace Apie\Tests\FtpServer\Commands;

use Apie\FtpServer\Commands\QuitCommand;
use Apie\Tests\FtpServer\Concerns\CreateFtpContext;
use Apie\Tests\FtpServer\FakeConnection;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use React\Socket\ConnectionInterface;

class QuitCommandTest extends TestCase
{
    use CreateFtpContext;
    
    #[Test]
    public function it_quits_command(): void
    {
        $testItem = new QuitCommand();
        $context = $this->createContext('/');
        $connection = $context->getContext(ConnectionInterface::class);
        assert($connection instanceof FakeConnection);
        $result = $testItem->run($context);
        $expectedOutput = "221 Goodbye\r\n";
        $this->assertEquals($expectedOutput, $connection->getData());
        $this->assertTrue($connection->ended);
    }
}
