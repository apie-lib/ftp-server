<?php
namespace Apie\FtpServer\Commands;

use Apie\Core\Context\ApieContext;
use Apie\FtpServer\Factories\ServerFactoryInterface;
use Apie\FtpServer\Factories\SimpleFtpServerFactory;
use Apie\FtpServer\FtpConstants;
use Apie\FtpServer\Transfers\PasvTransfer;
use Apie\FtpServer\Transfers\TransferInterface;
use React\Socket\ConnectionInterface;
use RuntimeException;

class PasvCommand implements CommandInterface
{
    public function run(ApieContext $apieContext, string $arg = ''): ApieContext
    {
        $conn = $apieContext->getContext(ConnectionInterface::class);
        $transfer = $apieContext->getContext(TransferInterface::class, false);
        if ($transfer instanceof PasvTransfer) {
            $transfer->end();
        }
        try {
            $transfer = new PasvTransfer(
                $apieContext->getContext(ServerFactoryInterface::class, false) ?? new SimpleFtpServerFactory(),
                $apieContext->getContext(FtpConstants::PASV_MIN_PORT, false) ?? '49152',
                $apieContext->getContext(FtpConstants::PASV_MAX_PORT, false) ?? '65534',
            );
        } catch (RuntimeException $error) {
            error_log($error->getMessage());
            $conn->write("522 No port number available, use PORT instead.\r\n");
            return $apieContext;
        }
        $address = $transfer->getAddress();
        $port = parse_url($address, PHP_URL_PORT);
        $ip = str_replace(
            '.',
            ',',
            $apieContext->getContext(FtpConstants::PUBLIC_IP, false) ?? '127.0.0.1'
        );
        $p1 = intdiv($port, 256);
        $p2 = $port % 256;
        $transfer->connectOnly()->then(
            function () use ($conn, $ip, $p1, $p2) {
                $conn->write("227 Entering Passive Mode ($ip,$p1,$p2)\r\n");
            },
            function (\Throwable $error) use ($conn) {
                error_log($error->getMessage());
                $conn->write("425 Can't open data connection.\r\n");
            }
        );

        return $apieContext->withContext(TransferInterface::class, $transfer);
    }
}
