<?php
namespace Apie\FtpServer\Commands;

use Apie\Core\Context\ApieContext;
use React\Socket\ConnectionInterface;

class SiteCommand implements CommandInterface
{
    public function run(ApieContext $apieContext, string $arg = ''): ApieContext
    {
        $conn = $apieContext->getContext(ConnectionInterface::class);
        if ($arg === 'IDLE') {
            usleep(1);
            $conn->write("200 Idle for 1 usec\r\n");
            return $apieContext;
        }
        if ($arg === 'HELP') {
            $conn->write("200 Only SITE command I have is IDLE to wait 1 usec\r\n");
            return $apieContext;
        }
        $conn->write("502 Command not implemented\r\n");
        return $apieContext;
    }
}
