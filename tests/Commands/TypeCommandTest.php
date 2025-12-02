<?php
namespace Apie\Tests\FtpServer\Commands;

use Apie\FtpServer\Commands\CdupCommand;
use Apie\FtpServer\Commands\TypeCommand;
use Apie\FtpServer\FtpConstants;
use Apie\Tests\FtpServer\Concerns\CreateFtpContext;
use Apie\Tests\FtpServer\FakeConnection;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use React\Socket\ConnectionInterface;

class TypeCommandTest extends TestCase
{
    use CreateFtpContext;
    
    #[Test]
    #[DataProvider('provideCases')]
    public function it_changes_download_upload_transfer_type(string $expectedOutput, ?string $expectedFtpType, string $input): void
    {
        $testItem = new TypeCommand();
        $context = $this->createContext('/');
        $connection = $context->getContext(ConnectionInterface::class);
        assert($connection instanceof FakeConnection);
        $result = $testItem->run($context, $input);
        $this->assertEquals($expectedFtpType, $result->getContext(FtpConstants::FTP_TYPE, false));
        $this->assertEquals($expectedOutput, $connection->getData());

        if (str_starts_with($expectedOutput, '5')) {
            $this->assertSame($result, $context);
        } else {
            $this->assertNotSame($result, $context);
        }
    }

    public static function provideCases(): array
    {
        $asciiMode = "200 Type set to A (ASCII)\r\n";
        $binaryMode = "200 Type set to I (Binary)\r\n";
        $invalidMode = "504 Command not implemented for that parameter\r\n";
        return [
            'ascii mode' => [$asciiMode, 'A', 'A'],
            'binary mode' => [$binaryMode, 'I', 'I'],
            'ascii mode (lower case)' => [$asciiMode, 'A', 'a'],
            'binary mode (upper case)' => [$binaryMode, 'I', 'i'],
            'invalid mode' => [$invalidMode, null, 'Z'],
            'empty' => [$invalidMode, null, ''],
        ];
    }
}
