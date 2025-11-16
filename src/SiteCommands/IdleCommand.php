<?php 
namespace Apie\FtpServer\SiteCommands;

use Apie\Core\Context\ApieContext;
use React\Socket\ConnectionInterface;

class IdleCommand implements SiteCommandInterface
{
    public function getName(): string
    {
        return 'IDLE';
    }

    public function getHelpText(): string
    {
        return 'Waits for a short moment before responding.';
    }

    public function run(ApieContext $apieContext, string $arg = ''): ApieContext
    {
        $conn = $apieContext->getContext(ConnectionInterface::class);
        $conn->write("200 Idle for 1 usec\r\n");
        usleep(1);
        return $apieContext;
    }
}