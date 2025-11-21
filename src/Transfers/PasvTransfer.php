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

    public function __construct(
        private readonly string $passiveMinPort = '49152',
        private readonly string $passiveMaxPort = '65534',
    ) {
        $port = null;

        // Try ports until we succeed
        foreach (range($passiveMinPort, $passiveMaxPort) as $candidate) {
            try {
                $this->dataServer = new SocketServer("0.0.0.0:$candidate");
                $port = $candidate;
                break;
            } catch (\Throwable $e) {
                // Port in use — try next
            }
        }

        if ($port === null) {
            throw new \RuntimeException(
                "No available passive ports in range $passiveMinPort-$passiveMaxPort"
            );
        }
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
