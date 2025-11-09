<?php
namespace Apie\FtpServer\Commands;

use Apie\ApieFileSystem\ApieFilesystem;
use Apie\ApieFileSystem\Virtual\VirtualFileInterface;
use Apie\ApieFileSystem\Virtual\VirtualFolderInterface;
use Apie\Core\Context\ApieContext;
use Apie\FtpServer\FtpConstants;
use React\Socket\ConnectionInterface;

class NlstCommand implements CommandInterface
{
    public function run(ApieContext $apieContext, string $arg = ''): ApieContext
    {
        $conn = $apieContext->getContext(ConnectionInterface::class);
        $filesystem = $apieContext->getContext(ApieFilesystem::class);
        assert($filesystem instanceof ApieFilesystem);
        $currentFolder = $arg ? $filesystem->visit($arg) : $apieContext->getContext(FtpConstants::CURRENT_FOLDER);
        if ($currentFolder instanceof VirtualFolderInterface) {
            $files = array_map(
                function (VirtualFolderInterface|VirtualFileInterface $child) {
                    return $child->getName();
                },
                $currentFolder->getChildren()->toArray()
            );
            $conn->write(implode("\r\n", $files) . "\r\n");
            $conn->write("226 NLST command successful.\r\n");
        } else {
            $conn->write("550 Path is not a folder.\r\n");
        }
        return $apieContext;
    }
}
