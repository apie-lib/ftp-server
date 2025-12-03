<?php
namespace Apie\FtpServer\Factories;

use React\Socket\ServerInterface;

interface ServerFactoryInterface
{
    public function createServer(int $port): ServerInterface;
}