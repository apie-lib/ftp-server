<?php
namespace Apie\Tests\FtpServer\Commands;

use Apie\FtpServer\Commands\ListCommand;
use Apie\FtpServer\Transfers\TransferInterface;
use Apie\Tests\FtpServer\Concerns\CreateFtpContext;
use Apie\Tests\FtpServer\FakeConnection;
use Apie\Tests\FtpServer\FakeTransfer;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use React\Socket\ConnectionInterface;

class ListCommandTest extends TestCase
{
    use CreateFtpContext;
    
    #[Test]
    #[DataProvider('provideCases')]
    public function it_changes_directory_upwards(string $expectedDataOutput, string $expectedOutput, string $path): void
    {
        $testItem = new ListCommand();
        $context = $this->createContext($path);
        $connection = $context->getContext(ConnectionInterface::class);
        assert($connection instanceof FakeConnection);
        $result = $testItem->run($context);
        $this->assertEquals($expectedOutput, $connection->getData());
        $transfer = $context->getContext(TransferInterface::class);
        assert($transfer instanceof FakeTransfer);
        $this->assertEquals($expectedDataOutput, $transfer->getData());
    }

    public static function provideCases(): array
    {
        $response = "150 Here comes the directory listing\r\n212 Directory send OK\r\n";
        return [
            [
                implode(
                    "\n",
                    [
                        "drw-r--r-- 1 user group  Jan 1 00:00 UserWithAddress\r",
                        "drw-r--r-- 1 user group 0 Jan 1 00:00 UserWithAddress.csv\r",
                        "drw-r--r-- 1 user group  Jan 1 00:00 Order\r",
                        "drw-r--r-- 1 user group 0 Jan 1 00:00 Order.csv\r",
                        ""
                    ]
                ),
                $response,
                '/default/resources'
            ],
            [
                implode(
                    "\n",
                    [
                        "drw-r--r-- 1 user group  Jan 1 00:00 default\r",
                        "drw-r--r-- 1 user group  Jan 1 00:00 other\r",
                        ""
                    ]
                ),
                $response,
                '/'
            ],
        ];
    }
}
