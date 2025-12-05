<?php
namespace Apie\Tests\FtpServer\Transfers;

use Apie\FtpServer\Transfers\PortTransfer;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use React\EventLoop\Loop;

class PortTransferTest extends TestCase
{
    #[Test]
    public function it_throws_error_on_connection_error()
    {
        $testItem = new PortTransfer('256.256.256.256', 42);
        $testItem->send('test');
        $currentReject = null;
        $testItem->send('test2', function ($reject) use (&$currentReject) {
            $currentReject = $reject;
        });
        $this->assertNull($currentReject);
        $testItem->end();
        unset($testItem);
        try {
            Loop::get()->run();
        } finally {
            $this->assertNotNull($currentReject);
        }
        Loop::get()->stop();
    }
}