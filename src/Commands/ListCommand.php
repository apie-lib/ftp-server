<?php
namespace Apie\FtpServer\Commands;

use Apie\ApieFileSystem\ApieFilesystem;
use Apie\ApieFileSystem\Virtual\VirtualFileInterface;
use Apie\ApieFileSystem\Virtual\VirtualFolderInterface;
use Apie\Core\Context\ApieContext;
use Apie\FtpServer\FtpConstants;
use React\Socket\ConnectionInterface;

class ListCommand implements CommandInterface
{
    public function run(ApieContext $apieContext, string $arg = ''): ApieContext
    {
        $conn = $apieContext->getContext(ConnectionInterface::class);
        $filesystem = $apieContext->getContext(ApieFilesystem::class);
        assert($filesystem instanceof ApieFilesystem);
        $currentFolder = $arg ? $filesystem->visit($arg) : $apieContext->getContext(FtpConstants::CURRENT_FOLDER);
        $conn->write("150 Here comes the directory listing\r\n");
        foreach ($currentFolder->getChildren() as $child) {
            $size = '';
            if ($child instanceof VirtualFileInterface) {
                $size = $child->getSize() ?? '0';
            }
            $conn->write(
                ($currentFolder instanceof VirtualFolderInterface ? 'd' : '-')
                . "rw-r--r-- 1 user group "
                . $size
                . " Jan 1 00:00 " . $child->getName() . "\r\n"
            );
        }
        $conn->write("226 Directory send OK\r\n");
        return $apieContext;
    }
}
