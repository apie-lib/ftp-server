<?php
namespace Apie\Tests\FtpServer\Commands;

use Apie\Common\ActionDefinitionProvider;
use Apie\Common\LoginService;
use Apie\Core\BoundedContext\BoundedContextHashmap;
use Apie\Core\ContextConstants;
use Apie\FtpServer\Commands\PassCommand;
use Apie\FtpServer\FtpConstants;
use Apie\Serializer\Serializer;
use Apie\Tests\FtpServer\Concerns\CreateFtpContext;
use Apie\Tests\FtpServer\FakeConnection;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use React\Socket\ConnectionInterface;

class PassCommandTest extends TestCase
{
    use CreateFtpContext;
    #[Test]
    #[DataProvider('provideCases')]
    public function it_attempts_to_login_with_login_service(bool $expectAuthenticated, string $expectedOutput, string $arg, ?string $user): void
    {
        $testItem = new PassCommand();
        $context = $this->createContext('/');

        $context = $context->withContext(
            LoginService::class,
            new LoginService(
                $context->getContext(BoundedContextHashmap::class),
                $context->getContext(ActionDefinitionProvider::class),
                $context->getContext(Serializer::class),
            )
        );
        if ($user !== null) {
            $context = $context->withContext(FtpConstants::USERNAME, $user);
        }
        $connection = $context->getContext(ConnectionInterface::class);
        assert($connection instanceof FakeConnection);
        $result = $testItem->run($context, $arg);
        $this->assertEquals($expectedOutput, $connection->getData());
        $user = (bool) $result->getContext(ContextConstants::AUTHENTICATED_USER, false);
        $this->assertEquals($expectAuthenticated, $user);
    }

    public static function provideCases(): array
    {
        return [
            'Anonymous login' => [false, "230 User logged in\r\n", '', 'anonymous'],
            'Valid login' => [true, "230 User logged in\r\n", 'pass', 'user'],
            'Invalid login' => [false, "430 Invalid username/password\r\n", 'wrong_password', 'user'],
            'Error login' => [false, "430 Invalid username/password\r\n", 'wrong_password', 'error'],
        ];
    }
}
