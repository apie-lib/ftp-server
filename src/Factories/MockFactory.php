<?php
namespace Apie\FtpServer\Factories;

use React\Socket\Connector;
use React\Socket\ConnectorInterface;

class MockFactory implements ServerFactoryInterface
{
    public function createConnector(): ConnectorInterface
    {
        // TODO: mock?
        return new Connector();
    }

    public function createServer(int $port): MockServer
    {
        return new MockServer($port);
    }
}
