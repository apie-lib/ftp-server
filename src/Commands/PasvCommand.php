<?php
namespace Apie\FtpServer\Commands;

use Apie\Core\Context\ApieContext;
use Apie\FtpServer\FtpConstants;
use Apie\FtpServer\Transfers\PasvTransfer;
use Apie\FtpServer\Transfers\TransferInterface;
use React\Socket\ConnectionInterface;

class PasvCommand implements CommandInterface
{
    public function run(ApieContext $apieContext, string $arg = ''): ApieContext
    {
        $conn = $apieContext->getContext(ConnectionInterface::class);
        $transfer = $apieContext->getContext(TransferInterface::class, false);
        if ($transfer instanceof PasvTransfer) {
            $transfer->end();
        }
        $transfer = new PasvTransfer();
        $address = $transfer->getAddress();
        $port = parse_url($address, PHP_URL_PORT);
        $ip = str_replace(
            '.',
            ',',
            $apieContext->getContext(FtpConstants::PUBLIC_IP, false) ?? '127.0.0.1'
        );
        $p1 = intdiv($port, 256);
        $p2 = $port % 256;

        $conn->write("227 Entering Passive Mode ($ip,$p1,$p2)\r\n");

        return $apieContext->withContext(TransferInterface::class, $transfer);
    }
}
