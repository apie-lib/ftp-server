<?php
namespace Apie\FtpServer\Transfers;

use Apie\FtpServer\Factories\ServerFactoryInterface;
use Apie\FtpServer\PassivePortManager;
use React\EventLoop\Loop;
use React\Promise\Deferred;
use React\Promise\PromiseInterface;
use React\Socket\ConnectionInterface;
use React\Socket\ServerInterface;

class PasvTransfer implements TransferInterface
{
    private ServerInterface $dataServer;
    private ?PromiseInterface $lastAction = null;

    public function __construct(
        ServerFactoryInterface $serverFactory,
        private readonly string $passiveMinPort = '49152',
        private readonly string $passiveMaxPort = '65534',
    ) {
        $this->dataServer = PassivePortManager::getAvailablePort(
            $serverFactory,
            (int) $passiveMinPort,
            (int) $passiveMaxPort
        );
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
    private function getDataConnection(?float $timeout = 2.0): PromiseInterface
    {
        if ($this->lastAction) {
            return $this->lastAction;
        }

        $deferred = new Deferred();

        $timer = null;
        if (null !== $timeout) {
            // Timeout if no connection is made
            $timer = Loop::get()->addTimer($timeout, function () use ($deferred) {
                $deferred->reject(new \RuntimeException("Nobody connected with the server within the timeout period."));
            });
        }

        $this->dataServer->once('connection', function (ConnectionInterface $conn) use ($deferred, $timer) {
            if ($timer !== null) {
                Loop::get()->cancelTimer($timer);
            }
            $deferred->resolve($conn);
        });
        $this->dataServer->once('close', function () use ($timer) {
            if ($timer !== null) {
                Loop::get()->cancelTimer($timer);
            }
        });

        $this->lastAction = $deferred->promise();
        return $this->lastAction;
    }

    public function connectOnly(): PromiseInterface
    {
        if ($this->lastAction) {
            return $this->lastAction;
        }

        $deferred = new Deferred();
        $deferred->resolve(null);
        return $deferred->promise();
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
        $promise = $this->getDataConnection(null);
        $timer = Loop::get()->addTimer(2, function () {
            PassivePortManager::release($this->dataServer);
            $this->dataServer->close();
            $this->lastAction = null;
        });
        $promise->then(
            function (ConnectionInterface $conn) use ($timer) {
                Loop::get()->cancelTimer($timer);
                $conn->end();
            }
        )->finally(function () {
            PassivePortManager::release($this->dataServer);
            $this->lastAction = null;
        });
    }
}
