<?php
namespace Apie\FtpServer\Commands;

use Apie\Core\Context\ApieContext;
use Apie\FtpServer\Transfers\PasvTransfer;
use Apie\FtpServer\Transfers\TransferInterface;
use React\Socket\ConnectionInterface;

class EpsvCommand implements CommandInterface
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

        $conn->write("229 Entering Extended Passive Mode (|||$port|)\r\n");

        return $apieContext->withContext(TransferInterface::class, $transfer);
    }
}
