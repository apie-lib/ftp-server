<?php
namespace Apie\FtpServer\Commands;

use Apie\Core\Context\ApieContext;

interface CommandInterface
{
    public function run(ApieContext $apieContext): ApieContext;
}
