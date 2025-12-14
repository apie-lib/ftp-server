<?php
namespace Apie\Tests\FtpServer\Commands;

use Apie\FtpServer\Commands\PasvCommand;
use Apie\FtpServer\Factories\MockFactory;
use Apie\FtpServer\FtpConstants;
use Apie\FtpServer\PassivePortManager;
use Apie\FtpServer\Transfers\PasvTransfer;
use Apie\FtpServer\Transfers\TransferInterface;
use Apie\Tests\FtpServer\Concerns\CreateFtpContext;
use Apie\Tests\FtpServer\FakeConnection;
use Apie\Tests\FtpServer\FakeTransfer;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use React\EventLoop\Loop;
use React\Socket\ConnectionInterface;

class PasvCommandTest extends TestCase
{
    use CreateFtpContext;

    protected function setUp(): void
    {
        PassivePortManager::removeMocks();
    }

    public function testPasvCommand(): void
    {
        $context = $this->createContext('/');
        $context = $context
            ->withContext(FtpConstants::PASV_MIN_PORT, 6666)
            ->withContext(FtpConstants::PASV_MAX_PORT, 6666);
        $command = new PasvCommand();
        $connection = $context->getContext(ConnectionInterface::class);
        assert($connection instanceof FakeConnection);

        $newContext = $command->run($context);
        $transfer = $newContext->getContext(TransferInterface::class);
        $this->assertInstanceOf(
            PasvTransfer::class,
            $transfer
        );
        $transfer->end();
        $this->assertEquals("227 Entering Passive Mode (127,0,0,1,26,10)\r\n", $connection->getData());
        Loop::get()->run();
    }

    #[Test]
    public function throw_error_if_no_valid_port_is_available(): void
    {
        PassivePortManager::getAvailablePort(new MockFactory(), 6666, 6666);
        $context = $this->createContext('/');
        $context = $context
            ->withContext(FtpConstants::PASV_MIN_PORT, 6666)
            ->withContext(FtpConstants::PASV_MAX_PORT, 6666);
        $command = new PasvCommand();
        $connection = $context->getContext(ConnectionInterface::class);
        assert($connection instanceof FakeConnection);

        $newContext = $command->run($context);
        $transfer = $newContext->getContext(TransferInterface::class);
        $this->assertInstanceOf(
            FakeTransfer::class,
            $transfer
        );
        $transfer->end();
        $this->assertEquals("522 No port number available, use PORT instead.\r\n", $connection->getData());
        Loop::get()->run();
    }
}
