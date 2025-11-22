<?php
namespace Apie\Tests\FtpServer\Commands;

use Apie\FtpServer\Commands\NlstCommand;
use Apie\FtpServer\Transfers\TransferInterface;
use Apie\Tests\FtpServer\Concerns\CreateFtpContext;
use Apie\Tests\FtpServer\FakeConnection;
use Apie\Tests\FtpServer\FakeTransfer;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use React\Socket\ConnectionInterface;

class NlstCommandTest extends TestCase
{
    use CreateFtpContext;
    
    #[Test]
    #[DataProvider('provideCases')]
    public function it_lists_directory_contents(string $expectedDataOutput, string $expectedOutput, string $path): void
    {
        $testItem = new NlstCommand();
        $context = $this->createContext($path);
        $connection = $context->getContext(ConnectionInterface::class);
        assert($connection instanceof FakeConnection);
        $testItem->run($context);
        $this->assertEquals($expectedOutput, $connection->getData());
        $transfer = $context->getContext(TransferInterface::class);
        assert($transfer instanceof FakeTransfer);
        $this->assertEquals($expectedDataOutput, $transfer->getData());
    }

    public static function provideCases(): array
    {
        $response = "150 Here comes the directory listing.\r\n226 NLST command successful.\r\n";
        return [
            [
                implode(
                    "\n",
                    [
                        "UserWithAddress\r",
                        "UserWithAddress.csv\r",
                        "Order\r",
                        "Order.csv\r",
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
                        "default\r",
                        "other\r",
                        ""
                    ]
                ),
                $response,
                '/'
            ],
        ];
    }
}
