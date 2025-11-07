<?php
namespace Apie\FtpServer\Commands;

use Apie\ApieFileSystem\Virtual\VirtualFolderInterface;
use Apie\Core\Context\ApieContext;
use Apie\FtpServer\FtpConstants;
use React\Socket\ConnectionInterface;

class ListCommand implements CommandInterface
{
    public function run(ApieContext $apieContext, string $arg = ''): ApieContext
    {
        $conn = $apieContext->getContext(ConnectionInterface::class);
        $currentFolder = $apieContext->getContext(FtpConstants::CURRENT_FOLDER);
        assert($currentFolder instanceof VirtualFolderInterface);
        $conn->write("150 Here comes the directory listing\r\n");
        foreach ($currentFolder->getChildren() as $child) {
            $conn->write("-rw-r--r-- 1 user group 0 Jan 1 00:00 " . $child->getName() . "\r\n");
        }
        $conn->write("226 Directory send OK\r\n");
        return $apieContext;
    }
}