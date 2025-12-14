<?php
namespace Apie\FtpServer\Commands;

use Apie\Core\Context\ApieContext;
use React\Socket\ConnectionInterface;

class PbszCommand implements CommandInterface
{
    public function run(ApieContext $apieContext, string $arg = ''): ApieContext
    {
        $conn = $apieContext->getContext(ConnectionInterface::class);
        $arg = trim($arg);
        if ($arg === '0') {
            $conn->write("200 PBSZ=0\r\n");
        } else {
            $conn->write("501 Syntax error in parameters or arguments. Only PBSZ 0 is supported.\r\n");
        }
        return $apieContext;
    }
}
