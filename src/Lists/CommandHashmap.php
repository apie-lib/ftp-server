<?php
namespace Apie\FtpServer\Lists;

use Apie\Core\Lists\ItemHashmap;
use Apie\FtpServer\Commands\CommandInterface;

final class CommandHashmap extends ItemHashmap
{
    protected bool $mutable = false;

    public function offsetGet(mixed $offset): CommandInterface
    {
        return parent::offsetGet($offset);
    }
}