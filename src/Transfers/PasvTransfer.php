<?php
namespace Apie\FtpServer\Transfers;

use React\EventLoop\Loop;
use React\Promise\Deferred;
use React\Promise\PromiseInterface;
use React\Socket\ConnectionInterface;
use React\Socket\SocketServer;

class PasvTransfer implements TransferInterface
{
    private SocketServer $dataServer;
    private ?PromiseInterface $lastAction = null;

    public function __construct(private readonly string $ipAddress = '127.0.0.1')
    {
        $this->dataServer = new SocketServer('0.0.0.0:0');
    }

    public function __destruct()
    {
        $this->end();
    }

    public function getAddress(): string
    {
        return $this->dataServer->getAddress();
    }

    /**
     * Returns a promise that resolves to the established data connection.
     */
    private function getDataConnection(float $timeout = 2.0): PromiseInterface
    {
        if ($this->lastAction) {
            return $this->lastAction;
        }

        $deferred = new Deferred();

        // Timeout if no connection is made
        $timer = Loop::get()->addTimer($timeout, function () use ($deferred) {
            $deferred->reject(new \RuntimeException("Can't open data connection"));
        });

        $this->dataServer->once('connection', function (ConnectionInterface $conn) use ($deferred, $timer) {
            Loop::get()->cancelTimer($timer);
            $deferred->resolve($conn);
        });

        $this->lastAction = $deferred->promise();
        return $this->lastAction;
    }

    public function send(string $data, ?callable $onRejected = null): void
    {
        $this->lastAction = $this->getDataConnection()->then(
            function (ConnectionInterface $conn) use ($data) {
                $conn->write($data);

                return $conn;
            },
            $onRejected
        );
    }

    public function end(): void
    {
        // Gracefully close the connection and server
        $promise = $this->getDataConnection();
        $promise->then(
            function (ConnectionInterface $conn) {
                $conn->end();
            }
        )->finally(function () {
            $this->dataServer->close();
            $this->lastAction = null;
        });
    }
}
