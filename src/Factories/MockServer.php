<?php
namespace Apie\FtpServer\Factories;

use Evenement\EventEmitter;
use React\Socket\ServerInterface;

class MockServer extends EventEmitter implements ServerInterface
{
    private bool $closed = false;
    
    public function __construct(public readonly int $port)
    {
    }

    public function getAddress(): string
    {
        return '127.0.0.1:' . $this->port;
    }

    public function connect(): void
    {
        $this->emit('connect');
    }

    public function pause(): void
    {
    }

    public function resume(): void
    {
    }

    public function close(): void
    {
        $this->closed = true;
    }
}