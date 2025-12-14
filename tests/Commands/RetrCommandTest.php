<?php
namespace Apie\Tests\FtpServer\Commands;

use Apie\FtpServer\Commands\RetrCommand;
use Apie\FtpServer\Transfers\TransferInterface;
use Apie\Tests\FtpServer\Concerns\CreateFtpContext;
use Apie\Tests\FtpServer\FakeConnection;
use Apie\Tests\FtpServer\FakeTransfer;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use React\Socket\ConnectionInterface;

class RetrCommandTest extends TestCase
{
    use CreateFtpContext;
    
    #[Test]
    #[DataProvider('provideCases')]
    public function it_lists_directory_contents(string $expectedDataOutput, string $expectedOutput, string $path): void
    {
        $testItem = new RetrCommand();
        $context = $this->createContext(dirname($path));
        $connection = $context->getContext(ConnectionInterface::class);
        assert($connection instanceof FakeConnection);
        $testItem->run($context, basename($path));
        $this->assertEquals($expectedOutput, $connection->getData());
        $transfer = $context->getContext(TransferInterface::class);
        assert($transfer instanceof FakeTransfer);
        $this->assertEquals($expectedDataOutput, $transfer->getData());
    }

    public static function provideCases(): array
    {
        return [
            [
                chr(0xEF) . chr(0xBB) . chr(0xBF) . "id,orderStatus,optionalTags,orderLines\n",
                "150 Opening data connection\r\n226 Transfer complete\r\n",
                '/default/resources/Order.csv'
            ],
            [
                "",
                "501 Missing filename\r\n",
                '/'
            ],
        ];
    }
}
