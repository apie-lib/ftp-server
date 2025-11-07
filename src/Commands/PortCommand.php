<?php
namespace Apie\FtpServer\Commands;

use Apie\Core\Context\ApieContext;
use Apie\FtpServer\FtpConstants;
use React\Socket\ConnectionInterface;

class PortCommand implements CommandInterface
{
    public function run(ApieContext $apieContext, string $arg = ''): ApieContext
    {
        $conn = $apieContext->getContext(ConnectionInterface::class);
        // Parse the argument: h1,h2,h3,h4,p1,p2
        $parts = explode(',', $arg);
        if (count($parts) !== 6) {
            $conn->write("501 Syntax error in parameters or arguments\r\n");
            return $apieContext;
        }
        $ip = implode('.', array_slice($parts, 0, 4));
        $port = ((int)$parts[4] << 8) + (int)$parts[5];
        // Store IP and port in context for later use
        $apieContext = $apieContext->withContext(FtpConstants::IP, $ip)
                                 ->withContext(FtpConstants::PORT, $port);
        $conn->write("200 PORT command successful.\r\n");
        return $apieContext;
    }
}
