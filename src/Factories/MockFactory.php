<?php
namespace Apie\FtpServer\Factories;

use Evenement\EventEmitterInterface;

class MockFactory implements ServerFactoryInterface
{
    public function createServer(int $port): MockServer
    {
        return new MockServer($port);
    }
}