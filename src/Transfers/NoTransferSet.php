<?php
namespace Apie\FtpServer\Transfers;

class NoTransferSet implements TransferInterface
{
    public function send(string $data, ?callable $onRejected = null): void
    {
        if ($onRejected !== null) {
            call_user_func($onRejected, new \RuntimeException('No transfer mode (PORT or PASV) set.'));
        }
    }

    public function end(): void
    {
    }
}
