<?php
namespace Apie\Tests\FtpServer;

use Apie\Core\Context\ApieContext;
use Apie\FtpServer\Commands\PasvCommand;
use Apie\FtpServer\Commands\SiteCommand;
use Apie\FtpServer\FtpServerRunner;
use Apie\FtpServer\Lists\CommandHashmap;
use Apie\FtpServer\SiteCommands\StoreTestCoverageCommand;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use React\Socket\ConnectionInterface;

class FtpServerRunnerTest extends TestCase
{
    #[Test]
    public function it_can_run_a_feat_command()
    {
        $testItem = new FtpServerRunner(
            new CommandHashmap([
                'PASV' => new PasvCommand(),
                'SITE' => SiteCommand::create(new StoreTestCoverageCommand()),
            ])
        );
        $connection = new FakeConnection();
        $apieContext = new ApieContext([
            ConnectionInterface::class => $connection
        ]);
        $testItem->run($apieContext, 'FEAT');
        $expected = "211-Features:\r\n";
        $expected .= "211-PASV\r\n";
        $expected .= "211-SITE TEST IDLE\r\n";
        $expected .= "211 End\r\n";
        $this->assertEquals($expected, $connection->getData());
    }

    #[Test]
    public function it_responds_to_uknown_commands()
    {
        $testItem = FtpServerRunner::create();
        $connection = new FakeConnection();
        $apieContext = new ApieContext([
            ConnectionInterface::class => $connection
        ]);
        $testItem->run($apieContext, 'BLAH');
        $expected = "502 Command not implemented\r\n";
        $this->assertEquals($expected, $connection->getData());
    }
}