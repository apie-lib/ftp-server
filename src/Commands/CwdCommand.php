<?php
namespace Apie\FtpServer\Commands;

use Apie\ApieFileSystem\ApieFilesystem;
use Apie\ApieFileSystem\Virtual\VirtualFolderInterface;
use Apie\Core\Context\ApieContext;
use Apie\FtpServer\FtpConstants;
use React\Socket\ConnectionInterface;

class CwdCommand implements CommandInterface
{
    public function run(ApieContext $apieContext, string $arg = ''): ApieContext
    {
        $conn = $apieContext->getContext(ConnectionInterface::class);
        if (!$arg) {
            $conn->write("550 Name invalid\r\n");
            return $apieContext;
        }

        $filesystem = $apieContext->getContext(ApieFilesystem::class);
        assert($filesystem instanceof ApieFilesystem);
        $pwd = trim($apieContext->getContext(FtpConstants::CURRENT_PWD), '/');
        if (str_starts_with($arg, '/')) {
            $pwd = '';
        }
        if ($arg === '.') {
            $conn->write("250 Directory successfully changed.\r\n");
            return $apieContext;
        }
        if ($arg === '..') {
            return (new CdupCommand())->run($apieContext, $arg);
        }
        $path = $this->normalizePath($pwd . '/' . $arg);
    

        $child = $filesystem->visit($path);
        if (!$child) {
            $conn->write("550 Folder $path not found\r\n");
            return $apieContext;
        }
        if ($child instanceof VirtualFolderInterface) {
            $conn->write("250 Directory successfully changed.\r\n");
            return $apieContext
                ->withContext(FtpConstants::CURRENT_FOLDER, $child)
                ->withContext(FtpConstants::CURRENT_PWD, $path);
        }

        $conn->write("550 Failed to change directory: $path is a file.\r\n");
        return $apieContext;
    }

    private function normalizePath($path)
    {
        $parts = explode('/', $path);
        $stack = [];

        foreach ($parts as $part) {
            if ($part === '' || $part === '.') {
                continue;
            }
            if ($part === '..') {
                array_pop($stack);
            } else {
                $stack[] = $part;
            }
        }

        return implode('/', $stack);
    }
}
