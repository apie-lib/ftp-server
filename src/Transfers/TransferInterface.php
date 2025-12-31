<?php
namespace Apie\FtpServer\Transfers;

use React\Promise\PromiseInterface;

interface TransferInterface
{
    /**
     * @return PromiseInterface<void>
    */
    public function connectOnly(): PromiseInterface;
    public function send(string $data, ?callable $onRejected = null): void;
    public function end(): void;
}
