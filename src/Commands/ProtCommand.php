<?php
namespace Apie\FtpServer\Commands;

use Apie\Core\Context\ApieContext;
use Apie\FtpServer\Factories\ImplicitSslFtpServerFactory;
use Apie\FtpServer\Factories\ServerFactoryInterface;
use React\Socket\ConnectionInterface;

class ProtCommand implements CommandInterface
{
    public function run(ApieContext $apieContext, string $arg = ''): ApieContext
    {
        $conn = $apieContext->getContext(ConnectionInterface::class);
        $arg = strtoupper(trim($arg));
        $serverFactory = $apieContext->getContext(ServerFactoryInterface::class, false);
        if ($serverFactory instanceof ImplicitSslFtpServerFactory) {
            $conn->write(
                $arg === 'P'
                ? "200 Protection level set to Private (TLS/SSL)\r\n"
                : "534 Only 'Private' protection level is supported.\r\n"
            );
        } else {
            $conn->write(
                $arg === 'C'
                ? "200 Protection level set to Clear\r\n"
                : "534 Only 'Clear' protection level is supported.\r\n"
            );
        }
        return $apieContext;
    }
}
