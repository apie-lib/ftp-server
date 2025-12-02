<?php
namespace Apie\Tests\FtpServer\Commands;

use Apie\FtpServer\Commands\CdupCommand;
use Apie\FtpServer\Commands\TypeCommand;
use Apie\FtpServer\Commands\UserCommand;
use Apie\FtpServer\FtpConstants;
use Apie\Tests\FtpServer\Concerns\CreateFtpContext;
use Apie\Tests\FtpServer\FakeConnection;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use React\Socket\ConnectionInterface;

class UserCommandTest extends TestCase
{
    use CreateFtpContext;
    
    #[Test]
    #[DataProvider('provideCases')]
    public function it_sets_username_to_login(string $expectedOutput, ?string $expectedUsername, string $input): void
    {
        $testItem = new UserCommand();
        $context = $this->createContext('/');
        $connection = $context->getContext(ConnectionInterface::class);
        assert($connection instanceof FakeConnection);
        $result = $testItem->run($context, $input);
        $this->assertEquals($expectedUsername, $result->getContext(FtpConstants::USERNAME, false));
        $this->assertEquals($expectedOutput, $connection->getData());

        if (str_starts_with($expectedOutput, '5')) {
            $this->assertSame($result, $context);
        } else {
            $this->assertNotSame($result, $context);
        }
    }

    public static function provideCases(): array
    {
        $correct = "331 Username OK, need password\r\n";
        $incorrect = "530 Login incorrect.\r\n";
        return [
            'valid username' => [$correct, 'testuser', 'testuser'],
            'valid username (trimmed spaces)' => [$correct, 'testuser', '  testuser  '],
            'empty username' => [$incorrect, null, ''],
        ];
    }
}
