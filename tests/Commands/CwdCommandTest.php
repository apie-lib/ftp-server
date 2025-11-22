<?php
namespace Apie\Tests\FtpServer\Commands;

use Apie\FtpServer\Commands\CwdCommand;
use Apie\FtpServer\FtpConstants;
use Apie\Tests\FtpServer\Concerns\CreateFtpContext;
use Apie\Tests\FtpServer\FakeConnection;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use React\Socket\ConnectionInterface;

class CwdCommandTest extends TestCase
{
    use CreateFtpContext;
    #[Test]
    #[DataProvider('provideCases')]
    public function it_changes_directory(string $expectedOutput, string $expectedPath, string $arg, string $path): void
    {
        $testItem = new CwdCommand();
        $context = $this->createContext($path);
        $connection = $context->getContext(ConnectionInterface::class);
        assert($connection instanceof FakeConnection);
        $result = $testItem->run($context, $arg);
        $this->assertEquals($expectedOutput, $connection->getData());
        $this->assertEquals($expectedPath, $result->getContext(FtpConstants::CURRENT_PWD));
    }

    public static function provideCases(): array
    {
        return [
            ["250 Directory successfully changed.\r\n", 'default/resources', '.', '/default/resources'],
            ["250 Directory successfully changed.\r\n", 'default/resources/Order', '.', '/default/resources/Order'],
            ["250 Directory successfully changed.\r\n",  '', '.', '/'],
            ["250 Directory successfully changed.\r\n", 'other', '.', '/other'],

            ["550 Name invalid\r\n",  '', '', '/'],

            ["250 Directory successfully changed.\r\n", 'default', '..', '/default/resources'],
            ["250 Directory successfully changed.\r\n", 'default/resources', '..', '/default/resources/Order'],
            ["550 Already at /\r\n", '', '..', '/'],
            ["250 Directory successfully changed.\r\n", '', '..', '/other'],

            ["250 Directory successfully changed.\r\n", 'default/resources', 'resources', '/default'],

            ["550 Folder default/resources/missing not found\r\n", 'default/resources', 'missing', '/default/resources'],
            ["550 Failed to change directory: default/resources/Order.csv is a file.\r\n", 'default/resources', 'Order.csv', '/default/resources'],
        ];
    }
}
