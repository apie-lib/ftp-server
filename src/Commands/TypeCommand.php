<?php
namespace Apie\FtpServer\Commands;

use Apie\Core\Context\ApieContext;
use Apie\FtpServer\FtpConstants;
use React\Socket\ConnectionInterface;

class TypeCommand implements CommandInterface
{
    public function run(ApieContext $apieContext, string $arg = ''): ApieContext
    {
        $conn = $apieContext->getContext(ConnectionInterface::class);
        $type = strtoupper(trim($arg));
        if ($type === 'A') {
            $conn->write("200 Type set to A (ASCII)\r\n");
            return $apieContext->withContext(FtpConstants::FTP_TYPE, 'A');
        } elseif ($type === 'I') {
            $conn->write("200 Type set to I (Binary)\r\n");
            return $apieContext->withContext(FtpConstants::FTP_TYPE, 'I');
        }
        $conn->write("504 Command not implemented for that parameter\r\n");
        return $apieContext;
    }
}
