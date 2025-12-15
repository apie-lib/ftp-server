<?php
namespace Apie\FtpServer\Transfers;

use React\Promise\PromiseInterface;
use React\Socket\ConnectionInterface;
use React\Socket\ConnectorInterface;

class PortTransfer implements TransferInterface
{
    private PromiseInterface $connectComplete;

    public function __construct(
        private readonly ConnectorInterface $connector,
        private readonly string $ip,
        private readonly int $port
    ) {
    }

    public function connectOnly(): PromiseInterface
    {
        if (!isset($this->connectComplete)) {
            $this->connectComplete = $this->connector->connect($this->ip . ':' . $this->port);
        }
        return $this->connectComplete;
    }

    public function send(string $data, ?callable $onRejected = null): void
    {
        
        $this->connectOnly()->then(
            function (ConnectionInterface $connection) use ($data) {
                $connection->write($data);
            },
            $onRejected
        );
    }

    public function end(): void
    {
        $this->connectOnly()->then(
            function (ConnectionInterface $connection) {
                $connection->end();
                unset($this->connectComplete);
            }
        );
    }
}
