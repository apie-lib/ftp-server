<?php
namespace Apie\FtpServer\Commands;

use Apie\Core\Context\ApieContext;
use Apie\FtpServer\FtpConstants;
use React\Socket\ConnectionInterface;

class UserCommand implements CommandInterface
{
    public function run(ApieContext $apieContext, string $arg = ''): ApieContext
    {
        $conn = $apieContext->getContext(ConnectionInterface::class);
        if ($arg) {
            $conn->write("331 Username OK, need password\r\n");
            return $apieContext
                ->withContext(FtpConstants::USERNAME, $arg);
        } else {
            // TODO
        }

        return $apieContext;
    }
}
