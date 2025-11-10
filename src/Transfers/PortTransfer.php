<?php
namespace Apie\FtpServer\Transfers;

use React\Promise\PromiseInterface;
use React\Socket\ConnectionInterface;
use React\Socket\Connector;

class PortTransfer implements TransferInterface
{
    private Connector $connector;

    private PromiseInterface $connectComplete;

    public function __construct(
        private readonly string $ip,
        private readonly int $port
    ) {
        $this->connector = new Connector();
    }

    public function send(string $data, ?callable $onRejected = null): void
    {
        if (!isset($this->connectComplete)) {
            $this->connectComplete = $this->connector->connect($this->ip . ':' . $this->port);
        }
        $this->connectComplete->then(
            function (ConnectionInterface $connection) use ($data) {
                $connection->write($data);
            },
            $onRejected
        );
    }

    public function end(): void
    {
        if (!isset($this->connectComplete)) {
            $this->connectComplete = $this->connector->connect($this->ip . ':' . $this->port);
        }
        $this->connectComplete->then(
            function (ConnectionInterface $connection) {
                $connection->end();
                unset($this->connectComplete);
            }
        );
    }
}
