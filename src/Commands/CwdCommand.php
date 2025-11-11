<?php
namespace Apie\FtpServer\Commands;

use Apie\ApieFileSystem\Virtual\VirtualFolderInterface;
use Apie\Core\Context\ApieContext;
use Apie\FtpServer\FtpConstants;
use React\Socket\ConnectionInterface;

class CwdCommand implements CommandInterface
{
    public function run(ApieContext $apieContext, string $arg = ''): ApieContext
    {
        $conn = $apieContext->getContext(ConnectionInterface::class);
        $currentFolder = $apieContext->getContext(FtpConstants::CURRENT_FOLDER);
        assert($currentFolder instanceof VirtualFolderInterface);
        $pwd = trim($apieContext->getContext(FtpConstants::CURRENT_PWD), '/');
        $arg = trim($arg, '/');
        if (!$arg || preg_match('#/#', $arg)) {
            $conn->write("550 Name invalid\r\n");
            return $apieContext;
        }
        if ($arg === '..') {
            return (new CdupCommand())->run($apieContext, $arg);
        }
        $child = $currentFolder->getChild($arg);
        if (!$child) {
            $conn->write("550 Folder $pwd/$arg not found\r\n");
            return $apieContext;
        }
        if ($child instanceof VirtualFolderInterface) {
            $conn->write("250 Directory successfully changed.\r\n");
            return $apieContext
                ->withContext(FtpConstants::CURRENT_FOLDER, $child)
                ->withContext(FtpConstants::CURRENT_PWD, $pwd . '/' . $arg);
        }

        $conn->write("550 Failed to change directory: $pwd/$arg is a file.\r\n");
        return $apieContext;
    }
}
