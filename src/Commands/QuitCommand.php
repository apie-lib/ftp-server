<?php
namespace Apie\FtpServer\Commands;

use Apie\Core\Context\ApieContext;
use React\Socket\ConnectionInterface;

class QuitCommand implements CommandInterface
{
    public function run(ApieContext $apieContext, string $arg = ''): ApieContext
    {
        $conn = $apieContext->getContext(ConnectionInterface::class);
        $conn->write("221 Goodbye\r\n");
        $conn->end();
        return $apieContext;
    }
}