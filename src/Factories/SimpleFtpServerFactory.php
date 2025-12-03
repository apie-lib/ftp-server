<?php
namespace Apie\FtpServer\Factories;

use React\Socket\ServerInterface;
use React\Socket\SocketServer;

class SimpleFtpServerFactory implements ServerFactoryInterface
{
    public function createServer(int $port): ServerInterface
    {
        return new SocketServer('0.0.0.0:' . $port);
    }
}