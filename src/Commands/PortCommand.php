<?php
namespace Apie\FtpServer\Commands;

use Apie\Core\Context\ApieContext;
use Apie\FtpServer\Factories\ServerFactoryInterface;
use Apie\FtpServer\Factories\SimpleFtpServerFactory;
use Apie\FtpServer\FtpConstants;
use Apie\FtpServer\Transfers\PortTransfer;
use Apie\FtpServer\Transfers\TransferInterface;
use React\Socket\ConnectionInterface;

class PortCommand implements CommandInterface
{
    public function run(ApieContext $apieContext, string $arg = ''): ApieContext
    {
        $conn = $apieContext->getContext(ConnectionInterface::class);
        $factory = $apieContext->getContext(ServerFactoryInterface::class, false) ?? new SimpleFtpServerFactory();

        // Parse the argument: h1,h2,h3,h4,p1,p2
        $parts = explode(',', $arg);
        if (count($parts) !== 6) {
            $conn->write("501 Syntax error in parameters or arguments\r\n");
            return $apieContext;
        }
        $ip = implode('.', array_slice($parts, 0, 4));
        $port = ((int)$parts[4] << 8) + (int)$parts[5];

        $transfer = new PortTransfer($factory->createConnector(), $ip, $port);
        $transfer->connectOnly()->then(
            function () use ($conn) {
                $conn->write("200 PORT command successful.\r\n");
            },
            function (\Throwable $error) use ($conn) {
                error_log($error->getMessage());
                $conn->write("425 Can't open data connection.\r\n");
            }
        );

        return $apieContext->withContext(FtpConstants::IP, $ip)
            ->withContext(FtpConstants::PORT, $port)
            ->withContext(TransferInterface::class, $transfer);
    }
}
