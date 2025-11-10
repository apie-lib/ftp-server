<?php
namespace Apie\FtpServer\Transfers;

interface TransferInterface
{
    public function send(string $data, ?callable $onRejected = null): void;
    public function end(): void;
}
