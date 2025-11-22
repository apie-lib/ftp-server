<?php
namespace Apie\Tests\FtpServer\Commands;

use Apie\ApieFileSystem\ApieFilesystem;
use Apie\ApieFileSystem\Virtual\RootFolder;
use Apie\Common\ActionDefinitionProvider;
use Apie\Core\BoundedContext\BoundedContextHashmap;
use Apie\Core\Context\ApieContext;
use Apie\Fixtures\BoundedContextFactory;
use Apie\FtpServer\Commands\CdupCommand;
use Apie\FtpServer\FtpConstants;
use Apie\Tests\FtpServer\FakeConnection;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use React\Socket\ConnectionInterface;

class CdupCommandTest extends TestCase
{
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

    private function createContext(string $currentPath): ApieContext
    {
        $hashmap = BoundedContextFactory::createHashmapWithMultipleContexts();
        $filesystem = new ApieFilesystem(
            new RootFolder($hashmap, new ActionDefinitionProvider(), new ApieContext()),
        );

        return new ApieContext(
            [
                ApieFilesystem::class => $filesystem,
                ConnectionInterface::class => new FakeConnection(),
                BoundedContextHashmap::class => $hashmap,
                FtpConstants::CURRENT_FOLDER => $filesystem->visit($currentPath),
                FtpConstants::CURRENT_PWD => trim($currentPath, '/'),
            ]
        );
    }
}
