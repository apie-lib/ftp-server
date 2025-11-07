<?php
namespace Apie\FtpServer\Commands;

use Apie\ApieFileSystem\Virtual\VirtualFolderInterface;
use Apie\Core\Context\ApieContext;
use Apie\FtpServer\FtpConstants;
use Apie\Webdav\Dav\ApieDirectory;
use React\Socket\ConnectionInterface;

class RetrCommand implements CommandInterface
{
    public function run(ApieContext $apieContext, string $arg = ''): ApieContext
    {
        $conn = $apieContext->getContext(ConnectionInterface::class);
        if ($arg) {
            $conn->write("501 Missing filename\r\n");
        } else {
            $folder = $apieContext->getContext(FtpConstants::CURRENT_FOLDER);
            assert($folder instanceof VirtualFolderInterface);
            $file = $folder->getChild($arg);
            if ($file === null) {
                $conn->write("550 File not found\r\n");
            } else {
                $conn->write("150 Opening data connection\r\n");
                $conn->write($file->getContents());
                $conn->write("\r\n");
                $conn->write("226 Transfer complete\r\n");
            }
        }
        return $apieContext;
    }
}