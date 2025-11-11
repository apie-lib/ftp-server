<?php
namespace Apie\FtpServer\Commands;

use Apie\ApieFileSystem\ApieFilesystem;
use Apie\Core\Context\ApieContext;
use Apie\FtpServer\FtpConstants;
use React\Socket\ConnectionInterface;

class CdupCommand implements CommandInterface
{
    public function run(ApieContext $apieContext, string $arg = ''): ApieContext
    {
        $filesystem = $apieContext->getContext(ApieFilesystem::class);
        $conn = $apieContext->getContext(ConnectionInterface::class);
        $pwd = trim($apieContext->getContext(FtpConstants::CURRENT_PWD), '/') . '/..';
        if ($pwd === '/..') {
            $conn->write("550 Already at /\r\n");
            return $apieContext;
        }
        assert($filesystem instanceof ApieFilesystem);
        $child = $filesystem->visit($pwd);
        assert($child !== null);
        $conn->write("250 Directory successfully changed.\r\n");
        return $apieContext
            ->withContext(FtpConstants::CURRENT_FOLDER, $child)
            ->withContext(FtpConstants::CURRENT_PWD, $this->normalizePath($pwd));
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
