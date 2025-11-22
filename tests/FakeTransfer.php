<?php
namespace Apie\Tests\FtpServer;

use Apie\FtpServer\Transfers\TransferInterface;

class FakeTransfer implements TransferInterface
{
    private string $data = '';

    public function send(string $data, ?callable $onRejected = null): void
    {
        $this->data .= $data;
    }

    public function end(): void
    {
        // No-op
    }

    public function getData(): string
    {
        return $this->data;
    }
}
