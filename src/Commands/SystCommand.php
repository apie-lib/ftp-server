<?php
namespace Apie\FtpServer\Commands;

use Apie\Core\Context\ApieContext;
use React\Socket\ConnectionInterface;

class SystCommand implements CommandInterface
{
    public function run(ApieContext $apieContext, string $arg = ''): ApieContext
    {
        $conn = $apieContext->getContext(ConnectionInterface::class);
        $conn->write("215 UNIX Type: L8\r\n");
        return $apieContext;
    }
}
