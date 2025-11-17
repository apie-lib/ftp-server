<?php
namespace Apie\FtpServer\SiteCommands;

use Apie\Core\Context\ApieContext;

interface SiteCommandInterface
{
    public function getName(): string;
    public function getHelpText(): string;
    public function run(ApieContext $apieContext, string $arg = ''): ApieContext;
}
