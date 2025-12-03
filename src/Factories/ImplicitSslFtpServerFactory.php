<?php
namespace Apie\FtpServer\Factories;

use Evenement\EventEmitterInterface;
use React\Socket\SecureServer;
use React\Socket\SocketServer;

class ImplicitSslFtpServerFactory implements ServerFactoryInterface
{
    public function createServer(int $port): SecureServer
    {
        return new SecureServer(
            new SocketServer('0.0.0.0:' . $port)
        );
    }
}