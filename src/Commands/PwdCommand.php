<?php
namespace Apie\FtpServer\Commands;

use Apie\Core\Context\ApieContext;
use Apie\FtpServer\FtpConstants;
use React\Socket\ConnectionInterface;

class PwdCommand implements CommandInterface
{
    public function run(ApieContext $apieContext, string $arg = ''): ApieContext
    {
        $conn = $apieContext->getContext(ConnectionInterface::class);
        $conn->write('257 "' . $apieContext->getContext(FtpConstants::CURRENT_PWD) . '" is current directory' . "\r\n");
        return $apieContext;
    }
}
