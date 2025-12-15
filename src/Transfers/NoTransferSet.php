<?php
namespace Apie\FtpServer\Transfers;

use function React\Promise\reject;

class NoTransferSet implements TransferInterface
{
    public function connectOnly(): \React\Promise\PromiseInterface
    {
        return reject(new \RuntimeException('No transfer mode (PORT or PASV) set.'));
    }

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
