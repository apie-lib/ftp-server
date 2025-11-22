<?php
namespace Apie\Tests\FtpServer\Commands;

use Apie\FtpServer\Commands\CdupCommand;
use Apie\FtpServer\FtpConstants;
use Apie\Tests\FtpServer\Concerns\CreateFtpContext;
use Apie\Tests\FtpServer\FakeConnection;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use React\Socket\ConnectionInterface;

class CdupCommandTest extends TestCase
{
    use CreateFtpContext;
    
    #[Test]
    #[DataProvider('provideCases')]
    public function it_changes_directory_upwards(string $expectedOutput, string $expectedPath, string $path): void
    {
        $testItem = new CdupCommand();
        $context = $this->createContext($path);
        $connection = $context->getContext(ConnectionInterface::class);
        assert($connection instanceof FakeConnection);
        $result = $testItem->run($context);
        $this->assertEquals($expectedOutput, $connection->getData());
        $this->assertEquals($expectedPath, $result->getContext(FtpConstants::CURRENT_PWD));
    }

    public static function provideCases(): array
    {
        return [
            ["250 Directory successfully changed.\r\n", 'default', '/default/resources'],
            ["250 Directory successfully changed.\r\n", 'default/resources', '/default/resources/Order'],
            ["550 Already at /\r\n", '', '/'],
            ["250 Directory successfully changed.\r\n", '', '/other'],
        ];
    }
}
