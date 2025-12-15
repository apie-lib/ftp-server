<?php
namespace Apie\FtpServer\Commands;

use Apie\Core\Context\ApieContext;
use Apie\FtpServer\Factories\ServerFactoryInterface;
use Apie\FtpServer\Factories\SimpleFtpServerFactory;
use Apie\FtpServer\FtpConstants;
use Apie\FtpServer\Transfers\PortTransfer;
use Apie\FtpServer\Transfers\TransferInterface;
use React\Socket\ConnectionInterface;

class EprtCommand implements CommandInterface
{
    public function run(ApieContext $apieContext, string $arg = ''): ApieContext
    {
        $conn = $apieContext->getContext(ConnectionInterface::class);
        $factory = $apieContext->getContext(ServerFactoryInterface::class, false) ?? new SimpleFtpServerFactory();

        // EPRT format: |af|host|port|
        // The delimiter is the first character.
        if ($arg === '') {
            $conn->write("501 Syntax error in parameters or arguments\r\n");
            return $apieContext;
        }

        $delim = $arg[0];
        $parts = explode($delim, $arg);

        if (count($parts) !== 5) {
            $conn->write("501 Syntax error in parameters or arguments\r\n");
            return $apieContext;
        }

        [$empty, $af, $host, $port, $empty2] = $parts;

        // Only IPv4 supported
        if ($af !== '1') {
            $conn->write("522 Network protocol not supported (IPv6 not available)\r\n");
            return $apieContext;
        }

        if (!filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            $conn->write("501 Invalid IP address\r\n");
            return $apieContext;
        }

        $port = (int)$port;
        if ($port <= 0 || $port > 65535) {
            $conn->write("501 Invalid port number\r\n");
            return $apieContext;
        }

        $transfer = new PortTransfer($factory->createConnector(), $host, $port);
        $transfer->connectOnly()->then(
            function() use ($conn) {
                $conn->write("200 EPRT command successful.\r\n");
            },
            function (\Throwable $error) use ($conn) {
                error_log($error->getMessage());
                $conn->write("425 Can't open data connection.\r\n");
            }
        );

        return $apieContext
            ->withContext(FtpConstants::IP, $host)
            ->withContext(FtpConstants::PORT, $port)
            ->withContext(TransferInterface::class, $transfer);
    }
}
