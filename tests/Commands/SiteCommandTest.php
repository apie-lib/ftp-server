<?php
namespace Apie\Tests\FtpServer\Commands;

use Apie\FtpServer\Commands\SiteCommand;
use Apie\FtpServer\SiteCommands\IdleCommand;
use Apie\FtpServer\SiteCommands\StoreTestCoverageCommand;
use Apie\Tests\FtpServer\Concerns\CreateFtpContext;
use Apie\Tests\FtpServer\FakeConnection;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use React\Socket\ConnectionInterface;

class SiteCommandTest extends TestCase
{
    use CreateFtpContext;
    
    #[Test]
    public function it_has_a_site_help_command(): void
    {
        $testItem = new SiteCommand(
            new IdleCommand(),
            new StoreTestCoverageCommand(),
        );
        $context = $this->createContext('/');
        $connection = $context->getContext(ConnectionInterface::class);
        assert($connection instanceof FakeConnection);
        $testItem->run($context, 'HELP');
        $expectedOutput = "214-IDLE Waits for a short moment before responding.\r
214-TEST Stores the test coverage data sent by the client (tests only).\r
214 End of SITE HELP list\r\n";
        $this->assertEquals($expectedOutput, $connection->getData());
    }

    #[Test]
    public function it_has_a_site_command(): void
    {
        $testItem = new SiteCommand(
            new IdleCommand(),
            new StoreTestCoverageCommand(),
        );
        $context = $this->createContext('/');
        $connection = $context->getContext(ConnectionInterface::class);
        assert($connection instanceof FakeConnection);
        $testItem->run($context, 'IDLE');
        $expectedOutput = "200 Idle for 1 usec\r\n";
        $this->assertEquals($expectedOutput, $connection->getData());
    }

    #[Test]
    public function it_returns_error_code_on_unknown_site_command(): void
    {
        $testItem = new SiteCommand();
        $context = $this->createContext('/');
        $connection = $context->getContext(ConnectionInterface::class);
        assert($connection instanceof FakeConnection);
        $testItem->run($context, 'IDLE');
        $expectedOutput = "502 Command not implemented\r\n";
        $this->assertEquals($expectedOutput, $connection->getData());
    }
}
