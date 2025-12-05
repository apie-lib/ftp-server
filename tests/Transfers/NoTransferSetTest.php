<?php
namespace Apie\Tests\FtpServer\Transfers;

use Apie\FtpServer\Transfers\NoTransferSet;
use PHPUnit\Framework\TestCase;

class NoTransferSetTest extends TestCase
{
    public function testNoTransfer()
    {
        $testItem = new NoTransferSet();
        $testItem->send('ignored');
        $currentReject = null;
        $testItem->send('ignored', function ($reject) use (&$currentReject) {
            $currentReject = $reject;
        });

        $this->assertNotNull($currentReject);
        $testItem->end();
    }
}