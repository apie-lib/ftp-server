<?php
namespace Apie\FtpServer\Transfers;

use React\EventLoop\Loop;
use React\Socket\ConnectionInterface;
use React\Socket\SocketServer;

class PasvTransfer implements TransferInterface
{
    private SocketServer $dataServer;
    private ConnectionInterface $dataConnection;

    public function __construct(private readonly string $ipAddress = '127.0.0.1')
    {
        $this->dataServer = new SocketServer('0.0.0.0:0');
        $this->dataServer->on('connection', function (ConnectionInterface $conn) {
            $this->dataConnection = $conn;
        });
    }

    public function __destruct()
    {
        $this->end();
        $this->dataServer->close();
    }

    public function getAddress(): string
    {
        return $this->dataServer->getAddress();
    }

    public function send(string $data, ?callable $onRejected = null): void
    {
        if (!isset($this->dataConnection)) {
            $timeout = 2.0;
            $timer = Loop::get()->addTimer($timeout, function () use ($onRejected) {
                if (!isset($this->dataConnection) && $onRejected) {
                    call_user_func($onRejected, new \RuntimeException("Can't open data connection"));
                }
            });

            $this->dataServer->on('connection', function ($conn) use ($timer, $data) {
                Loop::get()->cancelTimer($timer);
                $this->dataConnection = $conn;
                $this->dataConnection->write($data);
            });
        } else {
            $this->dataConnection->write($data);
        }
    }
    public function end(): void
    {
        if (isset($this->dataConnection)) {
            Loop::futureTick(function () {
                $this->dataConnection->end();
                unset($this->dataConnection);
            });
        } else {
            $this->dataServer->on('connection', function ($conn) {
                $this->dataConnection = $conn;
                Loop::futureTick(function () {
                    $this->dataConnection->end();
                    unset($this->dataConnection);
                });
            });
        }
    }
}
